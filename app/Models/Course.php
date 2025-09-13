<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $primaryKey = 'course_id';
    
    protected $fillable = [
        'course_name',
        'course_code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this course
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(VmsUser::class, 'created_by', 'user_id');
    }

    /**
     * Get all visitors who selected this course
     */
    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class, 'course_id', 'course_id');
    }

    /**
     * Scope to get only active courses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
