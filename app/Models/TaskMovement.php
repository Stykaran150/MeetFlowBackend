<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMovement extends Model
{
    protected $fillable = [
        'task_id',
        'from_column_id',
        'to_column_id',
        'moved_by',
        'notes',
    ];

    /**
     * Get the task that was moved.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the column the task was moved from.
     */
    public function fromColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'from_column_id');
    }

    /**
     * Get the column the task was moved to.
     */
    public function toColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'to_column_id');
    }

    /**
     * Get the user who moved the task.
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
