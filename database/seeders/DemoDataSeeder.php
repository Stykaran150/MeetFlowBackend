<?php

namespace Database\Seeders;

use App\Models\FollowUpMessage;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Meeting;
use App\Models\RiskAlert;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Core Users
        $admin = User::firstOrCreate(
            ['email' => 'demo@meetflow.ai'],
            ['name' => 'Alex Demo', 'password' => Hash::make('password')]
        );

        $pm = User::firstOrCreate(
            ['email' => 'sarah@meetflow.ai'],
            ['name' => 'Sarah PM', 'password' => Hash::make('password')]
        );

        $dev = User::firstOrCreate(
            ['email' => 'mike@meetflow.ai'],
            ['name' => 'Mike Dev', 'password' => Hash::make('password')]
        );

        $designer = User::firstOrCreate(
            ['email' => 'jenna@meetflow.ai'],
            ['name' => 'Jenna Design', 'password' => Hash::make('password')]
        );

        // 2. Create Primary Team
        $team = Team::firstOrCreate(
            ['name' => 'MeetFlow Product Launch'],
            [
                'owner_id' => $admin->id,
                'description' => 'Core team responsible for the Q1 product launch of MeetFlow AI.',
            ]
        );

        // Attach users to team
        if (!$team->users()->where('user_id', $admin->id)->exists()) {
            $team->users()->attach($admin->id, ['role' => 'owner']);
        }
        foreach ([$pm, $dev, $designer] as $user) {
            if (!$team->users()->where('user_id', $user->id)->exists()) {
                $team->users()->attach($user->id, ['role' => 'member']);
            }
        }

        // 3. Create Kanban Board
        $board = KanbanBoard::firstOrCreate(
            ['name' => 'Launch Sprint', 'team_id' => $team->id],
            ['created_by' => $admin->id, 'is_default' => true]
        );

        // Create Columns
        $columns = [
            ['name' => 'Backlog', 'position' => 0, 'color' => '#64748b'],
            ['name' => 'In Progress', 'position' => 1, 'color' => '#3b82f6'],
            ['name' => 'Review', 'position' => 2, 'color' => '#eab308'],
            ['name' => 'Done', 'position' => 3, 'color' => '#22c55e', 'is_done_column' => true],
        ];

        foreach ($columns as $colData) {
            KanbanColumn::firstOrCreate(
                ['kanban_board_id' => $board->id, 'name' => $colData['name']],
                $colData
            );
        }

        $backlogCol = $board->columns()->where('name', 'Backlog')->first();
        $inProgressCol = $board->columns()->where('name', 'In Progress')->first();
        $doneCol = $board->columns()->where('name', 'Done')->first();

        // 4. Create a Realistic Meeting
        $transcript = "Alex: Okay team, let's discuss the Q1 roadmap. Sarah, what's the status on the AI integration?
Sarah: The AI model is training well, but we need more data for the summarization feature. Mike is handling that.
Mike: Yeah, I'm scraping some dataset now. It should be ready by Friday.
Jenna: What about the UI? I have the designs ready for the dashboard.
Alex: Great. We need to prioritize the dashboard implementation.
Sarah: Also, we need to send follow-up emails to our beta testers.
Alex: Agreed. Let's add that as a task.";

        $meeting = Meeting::create([
            'team_id' => $team->id,
            'title' => 'Q1 Product Roadmap Review',
            'transcript' => $transcript,
            'summary' => 'The team discussed the Q1 roadmap. The AI model training is in progress but requires more data. Dashboard designs are ready. Priority is given to dashboard implementation and beta tester follow-ups.',
            'created_by' => $admin->id,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        // 5. Create Tasks
        $task1 = Task::create([
            'team_id' => $team->id,
            'meeting_id' => $meeting->id,
            'kanban_board_id' => $board->id,
            'kanban_column_id' => $inProgressCol->id,
            'title' => 'Process additional training data',
            'description' => 'Scrape and clean dataset for the summarization model.',
            'status' => 'in_progress',
            'priority' => 'high',
            'deadline' => now()->addDays(2),
        ]);
        $task1->assignees()->attach($dev->id, ['role' => 'assignee']);

        $task2 = Task::create([
            'team_id' => $team->id,
            'meeting_id' => $meeting->id,
            'kanban_board_id' => $board->id,
            'kanban_column_id' => $backlogCol->id,
            'title' => 'Implement Dashboard UI',
            'description' => 'Convert Jenna\'s designs into Vue components.',
            'status' => 'pending',
            'priority' => 'high',
        ]);
        $task2->assignees()->attach($admin->id, ['role' => 'assignee']);

        $task3 = Task::create([
            'team_id' => $team->id,
            'meeting_id' => $meeting->id,
            'kanban_board_id' => $board->id,
            'kanban_column_id' => $doneCol->id,
            'title' => 'Design Dashboard Mockups',
            'description' => 'Figma designs for the main dashboard widget.',
            'status' => 'completed',
            'priority' => 'medium',
        ]);
        $task3->assignees()->attach($designer->id, ['role' => 'assignee']);

        // 6. Create Risk Alerts
        RiskAlert::create([
            'team_id' => $team->id,
            'task_id' => $task1->id,
            'type' => 'deadline_approaching',
            'severity' => 'high',
            'message' => 'Deadline approaching for "Process additional training data". Only 2 days left!',
            'is_resolved' => false,
        ]);

        // 7. Create Follow-up Draft
        FollowUpMessage::create([
            'meeting_id' => $meeting->id,
            'subject' => 'Follow-up: Q1 Roadmap Next Steps',
            'body' => "Hi Team,\n\nHere is a summary of our meeting:\n- AI Training is ongoing.\n- Dashboard implementation is top priority.\n\nAction Items:\n- Mike: Finish data scraping.\n- Alex: Start dashboard coding.\n\nBest,\nAlex",
            'recipients' => ['team@meetflow.ai'],
            'status' => 'draft',
        ]);
    }
}
