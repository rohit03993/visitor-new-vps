<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';
    protected $primaryKey = 'visitor_id';

    protected $fillable = [
        'mobile_number',
        'name',
        'purpose',
        'address_id',
        'course_id',
        'father_name',
        'created_by',
        'last_updated_by',
    ];

    // Relationships
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(VmsUser::class, 'last_updated_by');
    }

    public function interactions()
    {
        return $this->hasMany(InteractionHistory::class, 'visitor_id');
    }

    /**
     * Get the course selected by this visitor
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get all student sessions for this visitor
     */
    public function studentSessions()
    {
        return $this->hasMany(StudentSession::class, 'visitor_id', 'visitor_id');
    }

    /**
     * Get active student sessions for this visitor
     */
    public function activeSessions()
    {
        return $this->studentSessions()->where('status', 'active');
    }

    /**
     * Get all tags associated with this visitor
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'visitor_tags', 'visitor_id', 'tag_id');
    }

    // Helper methods
    public function getLatestInteraction()
    {
        return $this->interactions()->latest()->first();
    }

    public function getFirstInteraction()
    {
        return $this->interactions()->oldest()->first();
    }

    public function getTotalInteractions()
    {
        return $this->interactions()->count();
    }

    /**
     * Check if visitor is a student (has course selected)
     */
    public function isStudent(): bool
    {
        return !is_null($this->course_id);
    }

    /**
     * Check if visitor has active sessions
     */
    public function hasActiveSessions(): bool
    {
        return $this->activeSessions()->exists();
    }

    /**
     * Get the latest active session
     */
    public function getLatestActiveSession()
    {
        return $this->activeSessions()->latest('started_at')->first();
    }
}
