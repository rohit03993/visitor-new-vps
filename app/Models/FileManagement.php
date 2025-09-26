<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileManagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'server_path',
        'file_type',
        'file_size',
        'google_drive_file_id',
        'google_drive_url',
        'status',
        'uploaded_by',
        'transferred_by',
        'interaction_id',
        'mime_type',
        'transferred_at',
        'transfer_notes'
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'file_size' => 'integer'
    ];

    // Relationships
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'uploaded_by', 'user_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'transferred_by', 'user_id');
    }

    public function interaction(): BelongsTo
    {
        return $this->belongsTo(InteractionHistory::class, 'interaction_id', 'interaction_id');
    }

    // Helper methods
    public function getFileUrlAttribute(): string
    {
        if ($this->status === 'drive' && $this->google_drive_url) {
            return $this->google_drive_url;
        }
        
        return asset('storage/uploads/' . basename($this->server_path));
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isOnServer(): bool
    {
        return $this->status === 'server';
    }

    public function isOnDrive(): bool
    {
        return $this->status === 'drive';
    }

    public function isPendingTransfer(): bool
    {
        return $this->status === 'pending';
    }

    public function hasFailedTransfer(): bool
    {
        return $this->status === 'failed';
    }
}
