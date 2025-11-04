<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkUserPhoneNumber extends Model
{
    protected $table = 'homework_user_phone_numbers';

    protected $fillable = [
        'homework_user_id',
        'phone_number',
        'whatsapp_enabled',
    ];

    protected $casts = [
        'whatsapp_enabled' => 'boolean',
    ];

    /**
     * Get the homework user that owns this phone number
     */
    public function homeworkUser()
    {
        return $this->belongsTo(HomeworkUser::class, 'homework_user_id');
    }
}

