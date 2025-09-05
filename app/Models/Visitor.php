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
}
