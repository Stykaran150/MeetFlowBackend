<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RiskAlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $teamId = $request->query('team_id');
        $resolved = $request->query('resolved');
        
        $query = RiskAlert::with(['task', 'team', 'resolver']);
        
        if ($teamId) {
            $query->where('team_id', $teamId);
        } else {
            $teamIds = $request->user()->teams->pluck('id');
            $query->whereIn('team_id', $teamIds);
        }

        if ($resolved !== null) {
            $query->where('is_resolved', filter_var($resolved, FILTER_VALIDATE_BOOLEAN));
        }

        $alerts = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse($alerts);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, RiskAlert $riskAlert): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($riskAlert->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($riskAlert->load(['task', 'team', 'resolver']));
    }

    /**
     * Resolve a risk alert.
     */
    public function resolve(Request $request, RiskAlert $riskAlert): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($riskAlert->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($riskAlert->is_resolved) {
            return $this->errorResponse('Alert is already resolved', 400);
        }

        $riskAlert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
        ]);

        return $this->successResponse([
            'risk_alert' => $riskAlert->fresh()->load('resolver'),
        ], 'Risk alert resolved successfully');
    }
}
