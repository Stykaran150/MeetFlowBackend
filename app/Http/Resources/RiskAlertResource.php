<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskAlertResource extends JsonResource
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
            'task_id' => $this->task_id,
            'team_id' => $this->team_id,
            'type' => $this->type,
            'severity' => $this->severity,
            'message' => $this->message,
            'is_resolved' => $this->is_resolved,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolved_by' => $this->resolved_by,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'task' => new TaskResource($this->whenLoaded('task')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'resolver' => new UserResource($this->whenLoaded('resolver')),
        ];
    }
}
