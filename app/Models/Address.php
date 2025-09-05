<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';
    protected $primaryKey = 'address_id';

    protected $fillable = [
        'address_name',
        'full_address',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function interactions()
    {
        return $this->hasMany(InteractionHistory::class, 'address_id');
    }

    // Helper methods
    public function getInteractionsCount()
    {
        return $this->interactions()->count();
    }

    // Search method for auto-suggestions
    public static function search($query, $limit = 10)
    {
        return static::where('address_name', 'LIKE', "%{$query}%")
            ->orWhere('full_address', 'LIKE', "%{$query}%")
            ->orderBy('address_name')
            ->limit($limit)
            ->get();
    }

    // Auto-create address if it doesn't exist
    public static function findOrCreate($addressName, $fullAddress = null, $createdBy = null)
    {
        $address = static::where('address_name', $addressName)->first();
        
        if (!$address) {
            $address = static::create([
                'address_name' => $addressName,
                'full_address' => $fullAddress ?: $addressName,
                'created_by' => $createdBy,
            ]);
        }
        
        return $address;
    }
}
