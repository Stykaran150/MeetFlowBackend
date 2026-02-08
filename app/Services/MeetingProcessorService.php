<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\Task;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Team;
use App\Models\FollowUpMessage;
use App\Models\RiskAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeetingProcessorService
{
    protected AIService $aiService;
    protected KanbanService $kanbanService;

    public function __construct(AIService $aiService, KanbanService $kanbanService)
    {
        $this->aiService = $aiService;
        $this->kanbanService = $kanbanService;
    }

    /**
     * Process a meeting transcript and extract tasks.
     *
     * @param Meeting $meeting
     * @return void
     */
    public function processMeeting(Meeting $meeting): void
    {
        DB::beginTransaction();

        try {
            $meeting->update(['status' => 'processing']);

            // Fetch team members for smart assignment
            $teamMembers = $meeting->team->users()->get(['name', 'email'])->toArray();

            // Extract tasks using AI
            $aiResponse = $this->aiService->extractTasksFromTranscript(
                $meeting->transcript,
                $meeting->participants ?? [],
                $teamMembers
            );

            // Handle new response structure with language detection
            $extractedTasks = $aiResponse['tasks'] ?? $aiResponse; // Fallback if array is direct
            $detectedLanguage = $aiResponse['detected_language'] ?? 'Unknown';
            
            Log::info("Meeting processed. Language: {$detectedLanguage}. Tasks found: " . count($extractedTasks));

            if (empty($extractedTasks)) {
                $meeting->update(['status' => 'processed']);
                DB::commit();
                return;
            }

            // Get or create default kanban board for team
            $board = $this->kanbanService->getOrCreateDefaultBoard($meeting->team_id);

            // Create tasks
            foreach ($extractedTasks as $taskData) {
                $this->createTaskFromExtraction($meeting, $board, $taskData);
            }

            // Generate follow-up message
            $this->generateFollowUpMessage($meeting, $extractedTasks);

            // Assess and create risk alerts
            $this->assessAndCreateRisks($meeting->team, $extractedTasks);

            $meeting->update(['status' => 'processed']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $meeting->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            Log::error('Failed to process meeting', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a task from extracted data.
     *
     * @param Meeting $meeting
     * @param KanbanBoard $board
     * @param array $taskData
     * @return Task
     */
    protected function createTaskFromExtraction(
        Meeting $meeting,
        KanbanBoard $board,
        array $taskData
    ): Task {
        // Get default "To Do" column
        $column = $board->columns()
            ->where('name', 'To Do')
            ->first() ?? $board->columns()->first();

        $task = Task::create([
            'meeting_id' => $meeting->id,
            'team_id' => $meeting->team_id,
            'kanban_board_id' => $board->id,
            'kanban_column_id' => $column?->id,
            'title' => $taskData['title'] ?? 'Untitled Task',
            'description' => $taskData['description'] ?? '',
            'priority' => $taskData['priority'] ?? 'medium',
            'deadline' => $this->parseDeadline($taskData['deadline'] ?? null),
            'status' => 'pending',
            'position' => $this->getNextPosition($column),
            'confidence_score' => $taskData['confidence_score'] ?? null,
            'suggested_deadline' => $this->parseDeadline($taskData['suggested_deadline'] ?? null),
        ]);

        // Assign task to user if specified
        // AI now prioritizes returning the email in 'owner' field
        $assignee = $taskData['owner'] ?? $taskData['assigned_to'] ?? null;
        if (!empty($assignee) && $assignee !== 'Unassigned') {
            $this->assignTask($task, $assignee, $meeting->team);
        }

        return $task;
    }

    /**
     * Assign task to a user.
     *
     * @param Task $task
     * @param string $assigneeIdentifier
     * @param Team $team
     * @return void
     */
    protected function assignTask(Task $task, string $assigneeIdentifier, Team $team): void
    {
        // Try to find user by email or name
        $user = User::where('email', $assigneeIdentifier)
            ->orWhere('name', 'like', "%{$assigneeIdentifier}%")
            ->first();

        // If user not found, try to find in team members
        if (!$user) {
            $user = $team->users()
                ->where('name', 'like', "%{$assigneeIdentifier}%")
                ->orWhere('email', $assigneeIdentifier)
                ->first();
        }

        if ($user) {
            TaskAssignment::firstOrCreate([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
        }
    }

    /**
     * Parse deadline string to DateTime.
     *
     * @param string|null $deadline
     * @return \DateTime|null
     */
    protected function parseDeadline(?string $deadline): ?\DateTime
    {
        if (empty($deadline)) {
            return null;
        }

        try {
            return new \DateTime($deadline);
        } catch (\Exception $e) {
            Log::warning('Failed to parse deadline', [
                'deadline' => $deadline,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get next position for task in column.
     *
     * @param KanbanColumn|null $column
     * @return int
     */
    protected function getNextPosition(?KanbanColumn $column): int
    {
        if (!$column) {
            return 0;
        }

        $lastTask = $column->tasks()->orderBy('position', 'desc')->first();
        return $lastTask ? $lastTask->position + 1 : 0;
    }

    /**
     * Generate follow-up message for meeting.
     *
     * @param Meeting $meeting
     * @param array $tasks
     * @return void
     */
    protected function generateFollowUpMessage(Meeting $meeting, array $tasks): void
    {
        try {
            $messageData = $this->aiService->generateFollowUpMessage(
                $tasks,
                $meeting->title
            );

            $recipients = $meeting->participants ?? [];
            if (empty($recipients)) {
                $recipients = $meeting->team->users()->pluck('email')->toArray();
            }

            FollowUpMessage::create([
                'meeting_id' => $meeting->id,
                'subject' => $messageData['subject'],
                'body' => $messageData['body'],
                'recipients' => $recipients,
                'status' => 'draft',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate follow-up message', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Assess risks and create alerts.
     *
     * @param Team $team
     * @param array $tasks
     * @return void
     */
    protected function assessAndCreateRisks(Team $team, array $tasks): void
    {
        try {
            $risks = $this->aiService->assessRisks($tasks);

            foreach ($risks as $riskData) {
                $task = Task::where('title', $riskData['task_title'] ?? '')
                    ->where('team_id', $team->id)
                    ->first();

                if ($task) {
                    RiskAlert::create([
                        'task_id' => $task->id,
                        'team_id' => $team->id,
                        'type' => $riskData['type'] ?? 'deadline_approaching',
                        'severity' => $riskData['severity'] ?? 'medium',
                        'message' => $riskData['message'] ?? 'Risk identified',
                        'is_resolved' => false,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to assess risks', [
                'team_id' => $team->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
