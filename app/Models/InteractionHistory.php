<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractionHistory extends Model
{
    protected $table = 'interaction_history';
    protected $primaryKey = 'interaction_id';

    protected $fillable = [
        'visitor_id',
        'session_id',
        'name_entered',
        'mobile_number',
        'mode',
        'purpose',
        'initial_notes',
        'address_id',
        'meeting_with',
        'scheduled_date',
        'assigned_by',
        'is_scheduled',
        'created_by',
        'created_by_role',
        'is_completed',
        'completed_at',
        'completed_by',
    ];

    // Visit mode constants
    const MODE_IN_CAMPUS = 'In-Campus';
    const MODE_OUT_CAMPUS = 'Out-Campus';
    const MODE_TELEPHONIC = 'Telephonic';

    // Available modes array
    public static $availableModes = [
        self::MODE_IN_CAMPUS,
        self::MODE_OUT_CAMPUS,
        self::MODE_TELEPHONIC,
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    // Relationships
    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function meetingWith()
    {
        return $this->belongsTo(VmsUser::class, 'meeting_with');
    }

    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(VmsUser::class, 'completed_by');
    }

    /**
     * Get the student session this interaction belongs to
     */
    public function studentSession()
    {
        return $this->belongsTo(StudentSession::class, 'session_id', 'session_id');
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class, 'interaction_id');
    }

    public function attachments()
    {
        return $this->hasMany(InteractionAttachment::class, 'interaction_id', 'interaction_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(VmsUser::class, 'assigned_by', 'user_id');
    }

    // Helper methods
    public function getLatestRemark()
    {
        return $this->remarks()->latest()->first();
    }

    public function hasPendingRemarks()
    {
        $latestRemark = $this->getLatestRemark();
        return $latestRemark && $latestRemark->remark_text === 'NA';
    }

    public function isCompleted()
    {
        return $this->is_completed;
    }

    public function markAsCompleted($userId = null)
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => $userId ?? auth()->id(),
        ]);
    }

    // Get badge color for mode display
    public function getModeBadgeColor()
    {
        switch ($this->mode) {
            case self::MODE_IN_CAMPUS:
                return 'success'; // Green (as requested)
            case self::MODE_OUT_CAMPUS:
                return 'warning'; // Yellow
            case self::MODE_TELEPHONIC:
                return 'info'; // Blue
            default:
                return 'secondary'; // Grey
        }
    }
}
