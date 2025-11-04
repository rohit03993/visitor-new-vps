<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HomeworkUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'homework_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mobile_number',
        'password',
        'password_plain',
        'role',
        'roll_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========== RELATIONSHIPS ==========

    /**
     * Get all classes this student is enrolled in
     */
    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_students', 'student_id', 'class_id');
    }

    /**
     * Get all homework views for this student
     */
    public function homeworkViews()
    {
        return $this->hasMany(HomeworkView::class, 'student_id');
    }

    /**
     * Get all homework notifications for this student
     */
    public function homeworkNotifications()
    {
        return $this->hasMany(HomeworkNotification::class, 'student_id');
    }

    /**
     * Get all phone numbers for this user
     */
    public function phoneNumbers()
    {
        return $this->hasMany(HomeworkUserPhoneNumber::class, 'homework_user_id');
    }

    /**
     * Get additional phone numbers (alias for phoneNumbers for backward compatibility)
     */
    public function additionalPhoneNumbers()
    {
        return $this->phoneNumbers();
    }

    /**
     * Get homework created by this teacher
     */
    public function createdHomework()
    {
        return $this->hasMany(Homework::class, 'teacher_id');
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get count of classes for student
     */
    public function getClassesCountAttribute(): int
    {
        if ($this->isStudent()) {
            return $this->schoolClasses()->count();
        }
        return 0;
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadHomeworkNotificationsCountAttribute(): int
    {
        return $this->homeworkNotifications()->where('is_read', false)->count();
    }

    /**
     * Get all phone numbers including primary mobile number
     */
    public function getAllPhoneNumbers(): array
    {
        $phones = collect([$this->mobile_number])->filter();
        $phones = $phones->merge($this->phoneNumbers()->pluck('phone_number'));
        return $phones->filter()->unique()->values()->toArray();
    }
}

