<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'team_id' => $this->team_id,
            'kanban_board_id' => $this->kanban_board_id,
            'kanban_column_id' => $this->kanban_column_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'deadline' => $this->deadline?->toIso8601String(),
            'status' => $this->status,
            'position' => $this->position,
            'confidence_score' => $this->confidence_score,
            'suggested_deadline' => $this->suggested_deadline?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'meeting' => new MeetingResource($this->whenLoaded('meeting')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'kanban_board' => new KanbanBoardResource($this->whenLoaded('kanbanBoard')),
            'kanban_column' => new KanbanColumnResource($this->whenLoaded('kanbanColumn')),
            'assignees' => UserResource::collection($this->whenLoaded('assignees')),
            'movements' => TaskMovementResource::collection($this->whenLoaded('movements')),
            'risk_alerts' => RiskAlertResource::collection($this->whenLoaded('riskAlerts')),
        ];
    }
}
