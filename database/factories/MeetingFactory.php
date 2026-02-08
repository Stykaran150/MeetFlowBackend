<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'transcript' => fake()->text(),
            'participants' => json_encode(fake()->words(5)),
            'meeting_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement(['pending', 'processing', 'processed', 'failed']),
            'created_by' => User::factory(),
        ];
    }
}
