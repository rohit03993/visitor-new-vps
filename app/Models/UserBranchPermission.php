<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBranchPermission extends Model
{
    protected $table = 'user_branch_permissions';
    
    protected $fillable = [
        'user_id',
        'branch_id',
        'can_view_remarks',
        'can_download_excel',
    ];

    protected $casts = [
        'can_view_remarks' => 'boolean',
        'can_download_excel' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(VmsUser::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
