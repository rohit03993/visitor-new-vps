<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorPhoneNumber extends Model
{
    use HasFactory;
    
    protected $table = 'visitor_phone_numbers';
    
    protected $fillable = [
        'visitor_id',
        'phone_number',
        'is_primary',
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
    ];
    
    /**
     * Get the visitor that owns this phone number
     */
    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'visitor_id', 'visitor_id');
    }
    
    /**
     * Scope to get only primary phone numbers
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
    
    /**
     * Scope to get only additional (non-primary) phone numbers
     */
    public function scopeAdditional($query)
    {
        return $query->where('is_primary', false);
    }
}
