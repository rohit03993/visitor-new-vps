<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'description',
    ];

    // Relationships
    public function students()
    {
        return $this->belongsToMany(HomeworkUser::class, 'class_students', 'class_id', 'student_id');
    }

    public function homework()
    {
        return $this->hasMany(Homework::class, 'class_id');
    }

    public function getStudentsCountAttribute()
    {
        return $this->students()->count();
    }

    public function getHomeworkCountAttribute()
    {
        return $this->homework()->count();
    }
}

