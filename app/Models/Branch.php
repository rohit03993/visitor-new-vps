<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(VmsUser::class, 'branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function interactions()
    {
        return $this->hasManyThrough(InteractionHistory::class, VmsUser::class, 'branch_id', 'created_by', 'branch_id', 'user_id');
    }

    // Helper methods
    public function getUsersCount()
    {
        return $this->users()->count();
    }

    public function getInteractionsCount()
    {
        return $this->interactions()->count();
    }
}
