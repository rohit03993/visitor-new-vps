<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitHistory extends Model
{
    protected $table = 'visit_history';
    protected $primaryKey = 'visit_id';

    protected $fillable = [
        'visitor_id',
        'name_entered',
        'mode',
        'purpose',
        'location_id',
        'meeting_with',
        'created_by',
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

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
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
        return $this->hasMany(Remark::class, 'visit_id');
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
}
