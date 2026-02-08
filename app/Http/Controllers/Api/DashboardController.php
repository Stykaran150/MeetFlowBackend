<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\RiskAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');

        // Total Meetings (All meetings in user's teams)
        $totalMeetings = Meeting::whereIn('team_id', $teamIds)->count();

        // Base Query for Tasks (Scoped to user's assignments or unassigned in their teams)
        $taskQuery = Task::whereIn('team_id', $teamIds)
            ->where(function ($query) use ($user) {
                $query->whereHas('assignees', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereDoesntHave('assignees');
            });

        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = (clone $taskQuery)->where('status', 'completed')->count();
        $tasksPending = (clone $taskQuery)->where('status', '!=', 'completed')->count();
        
        // Active Risk Alerts
        $activeAlerts = RiskAlert::whereIn('task_id', function($query) use ($teamIds) {
                $query->select('id')->from('tasks')->whereIn('team_id', $teamIds);
            })
            ->where('is_resolved', false)
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'total_meetings' => $totalMeetings,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $tasksPending,
                'active_alerts' => $activeAlerts,
            ]
        ]);
    }

    /**
     * Get recent activity feed (meetings and tasks).
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');

        // Recent Meetings
        $meetings = Meeting::whereIn('team_id', $teamIds)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($meeting) {
                return [
                    'type' => 'meeting',
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'status' => $meeting->status,
                    'created_at' => $meeting->created_at,
                    'team_name' => $meeting->team->name ?? 'Unknown Team'
                ];
            });

        // Recent Tasks (assigned to user)
        $tasks = Task::whereIn('team_id', $teamIds)
            ->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($task) {
                return [
                    'type' => 'task',
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'created_at' => $task->created_at,
                    'team_name' => $task->team->name ?? 'Unknown Team'
                ];
            });

        // Merge and sort
        $activity = $meetings->concat($tasks)
            ->sortByDesc('created_at')
            ->values()
            ->take(100); // Limit total items

        return response()->json([
            'status' => true,
            'data' => $activity
        ]);
    }

    /**
     * Get upcoming deadlines.
     */
    public function getUpcomingDeadlines(Request $request): JsonResponse
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');

        $tasks = Task::whereIn('team_id', $teamIds)
            ->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [now(), now()->addDays(7)])
            ->orderBy('deadline')
            ->take(5)
            ->with('team:id,name') // Eager load team name
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'deadline' => $task->deadline,
                    'priority' => $task->priority,
                    'team' => $task->team->name
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $tasks
        ]);
    }
}
