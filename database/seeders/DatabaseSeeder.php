<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Primary Test User
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Create some random users
        $randomUsers = User::factory(10)->create();

        // 3. Create a Team for the Test User
        $team = Team::factory()->create([
            'owner_id' => $testUser->id,
            'name' => 'MeetFlow Engineering',
            'description' => 'Main Engineering Team',
        ]);

        // 4. Attach users to team
        $team->users()->attach($testUser->id, ['role' => 'owner']);
        foreach ($randomUsers as $user) {
            $team->users()->attach($user->id, ['role' => 'member']);
        }

        // 5. Create a Kanban Board
        $board = KanbanBoard::factory()->create([
            'team_id' => $team->id,
            'created_by' => $testUser->id,
            'name' => 'Main Sprint Board',
            'is_default' => true,
        ]);

        // 6. Create Kanban Columns
        $todo = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
            'position' => 0,
        ]);

        $inProgress = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'In Progress',
            'position' => 1,
            'color' => '#3b82f6', // blue
        ]);

        $review = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'In Review',
            'position' => 2,
            'color' => '#eab308', // yellow
        ]);

        $done = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'Done',
            'position' => 3,
            'is_done_column' => true,
            'color' => '#22c55e', // green
        ]);

        // 7. Create Meetings (Past and Future)
        $meetings = Meeting::factory(5)->create([
            'team_id' => $team->id,
            'created_by' => $testUser->id,
        ]);

        // 8. Create Tasks for Meetings
        foreach ($meetings as $meeting) {
            // Create pending tasks (To Do)
            Task::factory(2)->create([
                'team_id' => $team->id,
                'meeting_id' => $meeting->id,
                'kanban_board_id' => $board->id,
                'kanban_column_id' => $todo->id,
                'status' => 'pending',
            ]);

            // Create in-progress tasks
            Task::factory(1)->create([
                'team_id' => $team->id,
                'meeting_id' => $meeting->id,
                'kanban_board_id' => $board->id,
                'kanban_column_id' => $inProgress->id,
                'status' => 'in_progress',
            ]);
            
             // Create completed tasks
            Task::factory(1)->create([
                'team_id' => $team->id,
                'meeting_id' => $meeting->id,
                'kanban_board_id' => $board->id,
                'kanban_column_id' => $done->id,
                'status' => 'completed',
            ]);
        }
    }
}
