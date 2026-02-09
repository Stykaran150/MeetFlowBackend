<?php

namespace Database\Seeders;

use App\Models\RiskAlert;
use App\Models\Task;
use Illuminate\Database\Seeder;

class RiskAlertsTableSeeder extends Seeder
{
    public function run(): void
    {
        $task = Task::where('title', 'Process additional training data')->first();

        if (!$task) return;

        RiskAlert::create([
            'team_id' => $task->team_id,
            'task_id' => $task->id,
            'type' => 'deadline_approaching',
            'severity' => 'high',
            'message' => 'Deadline approaching for "Process additional training data". Only 2 days left!',
            'is_resolved' => false,
        ]);
    }
}
