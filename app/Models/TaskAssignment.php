<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssignment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'role',
    ];

    /**
     * Get the task that the assignment belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user that is assigned.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
