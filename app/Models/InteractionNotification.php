<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InteractionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'interaction_id',
        'user_id',
        'subscribed_by',
        'privacy_level',
        'is_active',
        'can_view_remarks',
        'subscribed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_view_remarks' => 'boolean',
        'subscribed_at' => 'datetime'
    ];

    // Relationship to InteractionHistory
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(InteractionHistory::class, 'interaction_id', 'interaction_id');
    }

    // Relationship to VmsUser
    public function user(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'user_id', 'user_id');
    }

    // Scope for active notifications
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for public notifications
    public function scopePublic($query)
    {
        return $query->where('privacy_level', 'public');
    }

    // Scope for private notifications
    public function scopePrivate($query)
    {
        return $query->where('privacy_level', 'private');
    }
}