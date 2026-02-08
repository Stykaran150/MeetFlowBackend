<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanban_board_id',
        'name',
        'color',
        'position',
        'is_done_column',
    ];

    protected $casts = [
        'is_done_column' => 'boolean',
    ];

    /**
     * Get the board that the column belongs to.
     */
    public function kanbanBoard(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class);
    }

    /**
     * Get the tasks in the column.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    /**
     * Get task movements from this column.
     */
    public function movementsFrom(): HasMany
    {
        return $this->hasMany(TaskMovement::class, 'from_column_id');
    }

    /**
     * Get task movements to this column.
     */
    public function movementsTo(): HasMany
    {
        return $this->hasMany(TaskMovement::class, 'to_column_id');
    }
}
