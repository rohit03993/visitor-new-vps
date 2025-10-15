<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'interaction_id',
        'user_id',
        'triggered_by',
        'notification_type',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Relationship to InteractionHistory
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(InteractionHistory::class, 'interaction_id', 'interaction_id');
    }

    // Relationship to VmsUser (who gets the notification)
    public function user(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'user_id', 'user_id');
    }

    // Relationship to VmsUser (who triggered the action)
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'triggered_by', 'user_id');
    }

    // Scope for unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope for read notifications
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Scope for specific notification types
    public function scopeType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}