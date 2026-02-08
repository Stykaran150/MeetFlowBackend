<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTaskOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $user = $request->user();
        
        // Check if user is assigned to the task or is a team member
        $isAssigned = $task->assignees->contains($user->id);
        $isTeamMember = $user->teams->contains($task->team_id);

        if (!$isAssigned && !$isTeamMember) {
            return response()->json(['message' => 'Unauthorized. You do not have access to this task.'], 403);
        }

        return $next($request);
    }
}
