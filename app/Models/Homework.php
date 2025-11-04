<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'description',
        'type',
        'file_path',
        'content',
        'external_link',
    ];

    // Relationships
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(HomeworkUser::class, 'teacher_id');
    }

    public function views()
    {
        return $this->hasMany(HomeworkView::class, 'homework_id');
    }

    public function notifications()
    {
        return $this->hasMany(HomeworkNotification::class);
    }

    // Helper methods for statistics
    public function getTotalStudentsCountAttribute()
    {
        return $this->schoolClass->students()->count();
    }

    public function getViewedStudentsCountAttribute()
    {
        return $this->views()->count();
    }

    public function getViewPercentageAttribute()
    {
        $total = $this->total_students_count;
        if ($total === 0) return 0;
        return round(($this->viewed_students_count / $total) * 100, 1);
    }
}

