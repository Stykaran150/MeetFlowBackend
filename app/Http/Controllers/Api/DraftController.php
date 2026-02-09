<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DraftController extends Controller
{
    /**
     * Display a listing of user drafts (meetings with status 'draft').
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');

        $drafts = Meeting::whereIn('team_id', $teamIds)
            ->where('status', 'draft')
            ->with(['team', 'creator']) // Eager load team and creator
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $drafts
        ]);
    }
}
