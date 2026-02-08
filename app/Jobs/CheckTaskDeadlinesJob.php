<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\RiskAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckTaskDeadlinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $warningDays = config('kanban.deadline_warning_days', 3);
        $warningDate = Carbon::now()->addDays($warningDays);

        // Check for overdue tasks
        $overdueTasks = Task::where('deadline', '<', Carbon::now())
            ->where('status', '!=', 'completed')
            ->whereDoesntHave('riskAlerts', function ($query) {
                $query->where('type', 'overdue')->where('is_resolved', false);
            })
            ->get();

        foreach ($overdueTasks as $task) {
            RiskAlert::create([
                'task_id' => $task->id,
                'team_id' => $task->team_id,
                'type' => 'overdue',
                'severity' => 'high',
                'message' => "Task '{$task->title}' is overdue",
                'is_resolved' => false,
            ]);
        }

        // Check for tasks approaching deadline
        $approachingTasks = Task::whereBetween('deadline', [Carbon::now(), $warningDate])
            ->where('status', '!=', 'completed')
            ->whereDoesntHave('riskAlerts', function ($query) {
                $query->where('type', 'deadline_approaching')->where('is_resolved', false);
            })
            ->get();

        foreach ($approachingTasks as $task) {
            $daysUntilDeadline = Carbon::now()->diffInDays($task->deadline);
            
            RiskAlert::create([
                'task_id' => $task->id,
                'team_id' => $task->team_id,
                'type' => 'deadline_approaching',
                'severity' => $daysUntilDeadline <= 1 ? 'high' : 'medium',
                'message' => "Task '{$task->title}' deadline is approaching in {$daysUntilDeadline} day(s)",
                'is_resolved' => false,
            ]);
        }

        // Check for unassigned high-priority tasks
        $unassignedTasks = Task::where('priority', 'high')
            ->orWhere('priority', 'urgent')
            ->where('status', '!=', 'completed')
            ->whereDoesntHave('assignees')
            ->whereDoesntHave('riskAlerts', function ($query) {
                $query->where('type', 'unassigned')->where('is_resolved', false);
            })
            ->get();

        foreach ($unassignedTasks as $task) {
            RiskAlert::create([
                'task_id' => $task->id,
                'team_id' => $task->team_id,
                'type' => 'unassigned',
                'severity' => $task->priority === 'urgent' ? 'critical' : 'high',
                'message' => "High-priority task '{$task->title}' is unassigned",
                'is_resolved' => false,
            ]);
        }

        Log::info('Task deadline check completed', [
            'overdue_count' => $overdueTasks->count(),
            'approaching_count' => $approachingTasks->count(),
            'unassigned_count' => $unassignedTasks->count(),
        ]);
    }
}
