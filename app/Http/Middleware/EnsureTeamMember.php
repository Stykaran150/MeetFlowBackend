<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = $request->route('team')?->id ?? $request->route('team_id') ?? $request->input('team_id');

        if (!$teamId) {
            return response()->json(['message' => 'Team ID is required'], 400);
        }

        $user = $request->user();
        
        if (!$user || !$user->teams->contains($teamId)) {
            return response()->json(['message' => 'Unauthorized. You are not a member of this team.'], 403);
        }

        return $next($request);
    }
}
