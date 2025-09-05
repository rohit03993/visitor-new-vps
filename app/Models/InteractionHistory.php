<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractionHistory extends Model
{
    protected $table = 'interaction_history';
    protected $primaryKey = 'interaction_id';

    protected $fillable = [
        'visitor_id',
        'name_entered',
        'mode',
        'purpose',
        'address_id',
        'meeting_with',
        'created_by',
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

    public function remarks()
    {
        return $this->hasMany(Remark::class, 'interaction_id');
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
        return !$this->hasPendingRemarks();
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
