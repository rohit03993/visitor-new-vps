<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSession extends Model
{
    protected $primaryKey = 'session_id';
    
    protected $fillable = [
        'visitor_id',
        'purpose',
        'status',
        'outcome',
        'outcome_notes',
        'started_at',
        'completed_at',
        'started_by',
        'completed_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the visitor for this session
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id', 'visitor_id');
    }

    /**
     * Get the staff who started this session
     */
    public function starter(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'started_by', 'user_id');
    }

    /**
     * Get the staff who completed this session
     */
    public function completer(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'completed_by', 'user_id');
    }

    /**
     * Get all interaction history for this session
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(InteractionHistory::class, 'session_id', 'session_id');
    }

    /**
     * Scope to get only active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
