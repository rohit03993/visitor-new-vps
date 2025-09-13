<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class VmsUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'vms_users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'username',
        'password',
        'temp_password',
        'role',
        'branch_id',
        'mobile_number',
        'can_view_remarks',
        'can_download_excel',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'last_updated_by');
    }

    public function createdInteractions()
    {
        return $this->hasMany(InteractionHistory::class, 'created_by');
    }

    public function assignedInteractions()
    {
        return $this->hasMany(InteractionHistory::class, 'meeting_with');
    }

    public function addedRemarks()
    {
        return $this->hasMany(Remark::class, 'added_by');
    }

    public function editableRemarks()
    {
        return $this->hasMany(Remark::class, 'is_editable_by');
    }

    public function createdAddresses()
    {
        return $this->hasMany(Address::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function branchPermissions()
    {
        return $this->hasMany(UserBranchPermission::class, 'user_id');
    }

    // Role checking methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }


    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Permission checking methods
    public function canViewRemarks()
    {
        return $this->isAdmin() || $this->can_view_remarks;
    }

    public function canDownloadExcel()
    {
        return $this->isAdmin() || $this->can_download_excel;
    }

    // Branch-specific permission methods
    public function canViewRemarksForBranch($branchId)
    {
        // Admin can view all remarks
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if user has permission for this specific branch
        return $this->branchPermissions()
            ->where('branch_id', $branchId)
            ->where('can_view_remarks', true)
            ->exists();
    }

    public function canDownloadExcelForBranch($branchId)
    {
        // Admin can download from all branches
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if user has permission for this specific branch
        return $this->branchPermissions()
            ->where('branch_id', $branchId)
            ->where('can_download_excel', true)
            ->exists();
    }

    public function canViewRemarksForInteraction($interaction)
    {
        // Admin can view all remarks
        if ($this->isAdmin()) {
            return true;
        }
        
        // User can always view their own remarks
        if ($interaction->remarks) {
            foreach ($interaction->remarks as $remark) {
                if ($remark->added_by == $this->user_id) {
                    return true; // Can view at least their own remark
                }
            }
        }
        
        // Check if user has permission for any of the branches where remarks were added
        if ($interaction->remarks) {
            foreach ($interaction->remarks as $remark) {
                $branchId = $remark->addedBy->branch_id ?? null;
                if ($branchId && $this->canViewRemarksForBranch($branchId)) {
                    return true; // Can view at least one remark from a permitted branch
                }
            }
        }
        
        return false;
    }

    public function canViewRemark($remark)
    {
        // Admin can view all remarks
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if user can view remarks for this interaction
        return $this->canViewRemarksForInteraction($remark->interaction);
    }

    public function getAllowedBranchIds($permissionType = 'can_view_remarks')
    {
        // Admin can access all branches
        if ($this->isAdmin()) {
            return Branch::pluck('branch_id')->toArray();
        }
        
        // Get branch IDs where user has the specified permission
        return $this->branchPermissions()
            ->where($permissionType, true)
            ->pluck('branch_id')
            ->toArray();
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->branch_name : 'No Branch Assigned';
    }
}
