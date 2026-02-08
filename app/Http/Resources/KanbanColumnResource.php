<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KanbanColumnResource extends JsonResource
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
            'kanban_board_id' => $this->kanban_board_id,
            'name' => $this->name,
            'color' => $this->color,
            'position' => $this->position,
            'is_done_column' => $this->is_done_column,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
