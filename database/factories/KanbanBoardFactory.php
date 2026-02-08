<?php

namespace Database\Factories;

use App\Models\KanbanBoard;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KanbanBoardFactory extends Factory
{
    protected $model = KanbanBoard::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_default' => false,
            'created_by' => User::factory(),
        ];
    }
}
