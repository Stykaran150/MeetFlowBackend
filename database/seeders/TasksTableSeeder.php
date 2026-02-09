<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'MeetFlow Product Launch')->first();
        $board = KanbanBoard::where('name', 'Launch Sprint')->where('team_id', $team->id)->first();
        $meeting = Meeting::where('title', 'Q1 Product Roadmap Review')->first();
        
        $admin = User::where('email', 'demo@meetflow.ai')->first();
        $dev = User::where('email', 'mike@meetflow.ai')->first();
        $designer = User::where('email', 'jenna@meetflow.ai')->first();

        if (!$board || !$meeting) return;

        $columns = $board->columns;
        $backlogCol = $columns->where('name', 'Backlog')->first();
        $inProgressCol = $columns->where('name', 'In Progress')->first();
        $doneCol = $columns->where('name', 'Done')->first();

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
    }
}
