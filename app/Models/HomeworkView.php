<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkView extends Model
{
    protected $table = 'homework_views';

    protected $fillable = [
        'homework_id',
        'student_id',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function homework()
    {
        return $this->belongsTo(Homework::class, 'homework_id');
    }

    public function student()
    {
        return $this->belongsTo(HomeworkUser::class, 'student_id');
    }

    // Alias for backward compatibility (if needed)
    public function visitor()
    {
        return $this->student();
    }
}

