<?php

namespace Database\Factories;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

class KanbanColumnFactory extends Factory
{
    protected $model = KanbanColumn::class;

    public function definition(): array
    {
        return [
            'kanban_board_id' => KanbanBoard::factory(),
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'position' => fake()->numberBetween(0, 10),
            'is_done_column' => false,
        ];
    }
}
