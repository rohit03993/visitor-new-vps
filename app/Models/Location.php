<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'location_id';

    protected $fillable = [
        'location_name',
        'created_by',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function interactions()
    {
        return $this->hasMany(InteractionHistory::class, 'location_id');
    }

    // Helper methods
    public static function searchByName($query)
    {
        return self::where('location_name', 'like', '%' . $query . '%')->get();
    }
}
