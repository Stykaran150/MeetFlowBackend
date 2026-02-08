<?php

namespace Database\Factories;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'team_id' => Team::factory(),
            'kanban_board_id' => KanbanBoard::factory(),
            'kanban_column_id' => KanbanColumn::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'deadline' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'position' => fake()->numberBetween(0, 100),
        ];
    }
}
