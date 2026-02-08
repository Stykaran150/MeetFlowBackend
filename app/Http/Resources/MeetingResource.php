<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'title' => $this->title,
            'description' => $this->description,
            'transcript' => $this->transcript,
            'participants' => $this->participants,
            'meeting_date' => $this->meeting_date?->toIso8601String(),
            'status' => $this->status,
            'processing_error' => $this->processing_error,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'team' => new TeamResource($this->whenLoaded('team')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'follow_up_messages' => FollowUpMessageResource::collection($this->whenLoaded('followUpMessages')),
        ];
    }
}
