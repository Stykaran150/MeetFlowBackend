<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpMessageResource extends JsonResource
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
            'meeting_id' => $this->meeting_id,
            'task_id' => $this->task_id,
            'subject' => $this->subject,
            'body' => $this->body,
            'recipients' => $this->recipients,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'meeting' => new MeetingResource($this->whenLoaded('meeting')),
            'task' => new TaskResource($this->whenLoaded('task')),
        ];
    }
}
