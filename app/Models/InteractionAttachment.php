<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionAttachment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'interaction_id',
        'original_filename',
        'file_type',
        'file_size',
        'google_drive_file_id',
        'google_drive_url',
        'uploaded_by',
    ];
    
    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function interaction()
    {
        return $this->belongsTo(InteractionHistory::class, 'interaction_id', 'interaction_id');
    }
    
    public function uploadedBy()
    {
        return $this->belongsTo(VmsUser::class, 'uploaded_by', 'user_id');
    }
    
    // Helper methods
    public function getFileSizeFormatted()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function getFileIcon()
    {
        switch (strtolower($this->file_type)) {
            case 'pdf':
                return 'fas fa-file-pdf text-danger';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'webp':
                return 'fas fa-file-image text-primary';
            case 'mp3':
            case 'wav':
                return 'fas fa-file-audio text-success';
            default:
                return 'fas fa-file text-secondary';
        }
    }
    
    public function isImage()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'webp']);
    }
    
    public function isAudio()
    {
        return in_array(strtolower($this->file_type), ['mp3', 'wav']);
    }
    
    public function isPdf()
    {
        return strtolower($this->file_type) === 'pdf';
    }
}
