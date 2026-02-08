<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskMovementResource extends JsonResource
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
            'from_column_id' => $this->from_column_id,
            'to_column_id' => $this->to_column_id,
            'moved_by' => $this->moved_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'moved_by_user' => new UserResource($this->whenLoaded('movedBy')),
            'from_column' => new KanbanColumnResource($this->whenLoaded('fromColumn')),
            'to_column' => new KanbanColumnResource($this->whenLoaded('toColumn')),
        ];
    }
}
