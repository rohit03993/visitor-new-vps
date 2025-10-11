<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFcmToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fcm_token',
        'device_info',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the FCM token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope to get the latest token for a user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->latest();
    }

    /**
     * Update the last used timestamp.
     */
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }
}