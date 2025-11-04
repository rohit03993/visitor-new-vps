<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkNotification extends Model
{
    protected $table = 'homework_notifications';

    protected $fillable = [
        'student_id',
        'homework_id',
        'title',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    // Relationships
    public function student()
    {
        return $this->belongsTo(HomeworkUser::class, 'student_id');
    }

    // Alias for backward compatibility (if needed)
    public function visitor()
    {
        return $this->student();
    }

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }
}

