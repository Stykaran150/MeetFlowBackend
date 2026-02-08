<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'transcript',
        'participants',
        'meeting_date',
        'status',
        'processing_error',
        'created_by',
    ];

    protected $casts = [
        'participants' => 'array',
        'meeting_date' => 'datetime',
    ];

    /**
     * Get the team that owns the meeting.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created the meeting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the tasks extracted from the meeting.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the follow-up messages for the meeting.
     */
    public function followUpMessages(): HasMany
    {
        return $this->hasMany(FollowUpMessage::class);
    }
}
