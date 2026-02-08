<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUpMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FollowUpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $meetingId = $request->query('meeting_id');
        
        $query = FollowUpMessage::with(['meeting', 'task']);
        
        if ($meetingId) {
            $query->where('meeting_id', $meetingId);
        } else {
            // Only show messages from user's team meetings
            $teamIds = $request->user()->teams->pluck('id');
            $query->whereHas('meeting', function ($q) use ($teamIds) {
                $q->whereIn('team_id', $teamIds);
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse($messages);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, FollowUpMessage $followUpMessage): JsonResponse
    {
        // Check if user is member of team
        $meeting = $followUpMessage->meeting;
        if (!$request->user()->teams->contains($meeting->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($followUpMessage->load(['meeting', 'task']));
    }

    /**
     * Send follow-up message.
     */
    public function send(Request $request, FollowUpMessage $followUpMessage): JsonResponse
    {
        // Check if user is member of team
        $meeting = $followUpMessage->meeting;
        if (!$request->user()->teams->contains($meeting->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($followUpMessage->status === 'sent') {
            return $this->errorResponse('Message has already been sent', 400);
        }

        // TODO: Implement actual email sending
        // For now, just mark as sent
        $followUpMessage->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $this->successResponse([
            'follow_up_message' => $followUpMessage->fresh(),
        ], 'Follow-up message sent successfully');
    }
}
