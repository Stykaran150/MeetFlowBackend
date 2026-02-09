<?php

namespace Database\Seeders;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class KanbanBoardsTableSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'MeetFlow Product Launch')->first();
        $owner = User::where('email', 'demo@meetflow.ai')->first();

        if (!$team || !$owner) return;

        $board = KanbanBoard::firstOrCreate(
            ['name' => 'Launch Sprint', 'team_id' => $team->id],
            ['created_by' => $owner->id, 'is_default' => true]
        );

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
    }
}
