<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'team_id',
        'kanban_board_id',
        'kanban_column_id',
        'title',
        'description',
        'priority',
        'deadline',
        'status',
        'position',
        'confidence_score',
        'suggested_deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'suggested_deadline' => 'datetime',
    ];

    /**
     * Get the meeting that the task belongs to.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Get the team that owns the task.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the kanban board that the task belongs to.
     */
    public function kanbanBoard(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class);
    }

    /**
     * Get the kanban column that the task is in.
     */
    public function kanbanColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class);
    }

    /**
     * Get the users assigned to the task.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the task assignments.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Get the task movements.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(TaskMovement::class);
    }

    /**
     * Get the follow-up messages for the task.
     */
    public function followUpMessages(): HasMany
    {
        return $this->hasMany(FollowUpMessage::class);
    }

    /**
     * Get the risk alerts for the task.
     */
    public function riskAlerts(): HasMany
    {
        return $this->hasMany(RiskAlert::class);
    }
}
