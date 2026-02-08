<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected AIService $aiService;

    public function __construct(TaskService $taskService, AIService $aiService)
    {
        $this->taskService = $taskService;
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['team_id', 'meeting_id', 'status', 'priority', 'assigned_to', 'overdue', 'assignment_status']);
        
        // Scope to user's teams
        if (isset($filters['team_id'])) {
            // Check if user belongs to this specific team
            if (!$request->user()->teams->contains($filters['team_id'])) {
                return $this->errorResponse('Unauthorized', 403);
            }
        } else {
            // Filter by all teams the user belongs to
            $filters['team_ids'] = $request->user()->teams->pluck('id')->toArray();
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to'] === 'me') {
            $filters['assigned_to'] = $request->user()->id;
        }

        $tasks = $this->taskService->getTasks($filters);

        return $this->successResponse($tasks);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($task->load([
            'meeting',
            'team',
            'assignees',
            'kanbanBoard',
            'kanbanColumn',
            'movements.movedBy',
            'riskAlerts',
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $user = $request->user();
        $isOwner = $task->team->owner_id === $user->id;
        $isAssignee = $task->assignees->contains($user->id);

        if (!$isOwner && !$isAssignee) {
             return $this->errorResponse('You must be the team owner or an assignee to update this task.', 403);
        }

        // If not owner, ensure ONLY status is being updated
        if (!$isOwner) {
            $data = $request->all();
            // Remove 'status' from the data keys
            unset($data['status']);
            // If there's anything left, it means they tried to update something else
            if (!empty($data)) {
                return $this->errorResponse('As an assignee, you can only update the task status.', 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $task = $this->taskService->updateTask($task, $validator->validated());

        return $this->successResponse($task->load(['assignees', 'kanbanColumn']), 'Task updated successfully');
    }

    /**
     * Assign task to user.
     */
    public function assign(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:owner,assignee,reviewer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::findOrFail($request->user_id);
        $assignment = $this->taskService->assignTask($task, $user, $request->role ?? 'assignee');

        return $this->successResponse([
            'assignment' => $assignment,
        ], 'Task assigned successfully');
    }

    /**
     * Move task on kanban board.
     */
    public function move(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'column_id' => 'required|exists:kanban_columns,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $column = $task->kanbanBoard->columns()->findOrFail($request->column_id);
        
        $kanbanService = app(\App\Services\KanbanService::class);
        $movement = $kanbanService->moveTask($task, $column, $request->user(), $request->notes ?? null);

        return $this->successResponse([
            'task' => $task->fresh()->load('kanbanColumn'),
            'movement' => $movement,
        ], 'Task moved successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $this->taskService->deleteTask($task);

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Generate WhatsApp follow-up message for task.
     */
    public function generateWhatsAppMessage(Request $request, Task $task): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $owner = $task->assignees->first();
        $taskData = [
            'title' => $task->title,
            'owner' => $owner?->name ?? 'Team',
            'deadline' => $task->deadline?->format('Y-m-d') ?? 'Not specified',
            'priority' => $task->priority,
        ];

        try {
            $message = $this->aiService->generateWhatsAppMessage($taskData);
            return $this->successResponse([
                'message' => $message,
                'task_id' => $task->id,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate WhatsApp message: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Analyze task risk using AI.
     */
    public function analyzeRisk(Request $request, Task $task): JsonResponse
    {
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $taskData = [
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'deadline' => $task->deadline?->format('Y-m-d'),
            'assignees' => $task->assignees->pluck('name')->toArray(),
        ];

        try {
            $analysis = $this->aiService->analyzeTaskRisk($taskData);
            return $this->successResponse($analysis);
        } catch (\Exception $e) {
            return $this->errorResponse('Risk analysis failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate follow-up email draft using AI.
     */
    public function generateFollowUpEmail(Request $request, Task $task): JsonResponse
    {
        if (!$request->user()->teams->contains($task->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $taskData = [
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'deadline' => $task->deadline?->format('Y-m-d'),
            'assignees' => $task->assignees->pluck('name')->toArray(),
        ];

        try {
            $email = $this->aiService->generateFollowUpEmail($taskData);
            return $this->successResponse($email);
        } catch (\Exception $e) {
            return $this->errorResponse('Email generation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get productivity score for team (AI Enhanced).
     */
    public function getProductivityScore(Request $request): JsonResponse
    {
        $teamId = $request->query('team_id');
        
        if (!$teamId) {
            return $this->errorResponse('Team ID is required', 400);
        }

        if (!$request->user()->teams->contains($teamId)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Calculate quantitative metrics
        $totalTasks = \App\Models\Task::where('team_id', $teamId)->count();
        $completedTasks = \App\Models\Task::where('team_id', $teamId)
            ->where('status', 'completed')
            ->count();
        $productivityScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
        $pendingTasks = $totalTasks - $completedTasks;

        // Get AI Insights
        $recentTasks = \App\Models\Task::where('team_id', $teamId)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'title' => $t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'deadline' => $t->deadline?->format('Y-m-d')
            ])->toArray();

        $aiInsights = [
            'completion_estimate_percentage' => $productivityScore, // Default
            'execution_health' => $productivityScore > 75 ? 'Good' : 'Average',
            'recommendation' => 'Keep pushing!'
        ];

        try {
            if (!empty($recentTasks)) {
                $aiInsights = $this->aiService->getProductivityInsights($recentTasks);
            }
        } catch (\Exception $e) {
            // Fallback to defaults on error, log it
            \Illuminate\Support\Facades\Log::warning('AI Productivity Insight Failed: ' . $e->getMessage());
        }

        return $this->successResponse([
            'team_id' => $teamId,
            'productivity_score' => $productivityScore,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'ai_insights' => $aiInsights
        ]);
    }
}
