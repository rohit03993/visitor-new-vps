<?php

namespace App\Helpers;

use App\Models\InteractionNotification;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\VmsUser;

class RemarkPermissionHelper
{
    /**
     * Check if a user can view remarks for a specific interaction
     * 
     * Rules:
     * 1. Creator of the remark can always view their own remarks
     * 2. Admin can always view all remarks
     * 3. Current assignee (meeting_with) can always view remarks
     * 4. Other users need can_view_remarks=true in their subscription
     * 
     * @param int $interactionId
     * @param int $userId
     * @param int|null $remarkCreatorId (optional - for checking specific remark)
     * @return bool
     */
    public static function canViewRemarks($interactionId, $userId, $remarkCreatorId = null)
    {
        // Get current user
        $currentUser = VmsUser::find($userId);
        
        if (!$currentUser) {
            return false;
        }
        
        // Rule 1: Admins can always see all remarks
        if ($currentUser->role === 'admin') {
            return true;
        }
        
        // Rule 2: Remark creator can always see their own remarks
        if ($remarkCreatorId && $remarkCreatorId === $userId) {
            return true;
        }
        
        // Rule 3: Current assignee can always see remarks
        $interaction = InteractionHistory::find($interactionId);
        if ($interaction && $interaction->meeting_with === $userId) {
            return true;
        }
        
        // Rule 4: Check subscription permission
        $subscription = InteractionNotification::where('interaction_id', $interactionId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        
        if ($subscription) {
            return $subscription->can_view_remarks ?? true;
        }
        
        // Default: No access
        return false;
    }
    
    /**
     * Get permission status for a user on an interaction
     * 
     * @param int $interactionId
     * @param int $userId
     * @return array ['can_view' => bool, 'reason' => string]
     */
    public static function getPermissionStatus($interactionId, $userId)
    {
        $currentUser = VmsUser::find($userId);
        
        if (!$currentUser) {
            return ['can_view' => false, 'reason' => 'User not found'];
        }
        
        // Check admin
        if ($currentUser->role === 'admin') {
            return ['can_view' => true, 'reason' => 'Admin'];
        }
        
        // Check current assignee
        $interaction = InteractionHistory::find($interactionId);
        if ($interaction && $interaction->meeting_with === $userId) {
            return ['can_view' => true, 'reason' => 'Current Assignee'];
        }
        
        // Check subscription
        $subscription = InteractionNotification::where('interaction_id', $interactionId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        
        if ($subscription) {
            $canView = $subscription->can_view_remarks ?? true;
            return [
                'can_view' => $canView, 
                'reason' => $canView ? 'Has Permission' : 'Restricted'
            ];
        }
        
        return ['can_view' => false, 'reason' => 'Not Subscribed'];
    }
    
    /**
     * Get masked remark text for restricted users
     * 
     * @return string
     */
    public static function getMaskedRemarkText()
    {
        return '🔒 Restricted Content - Contact Admin for Access';
    }
    
    /**
     * Check if a specific remark can be viewed by user
     * 
     * @param Remark $remark
     * @param int $userId
     * @return bool
     */
    public static function canViewRemark(Remark $remark, $userId)
    {
        return self::canViewRemarks(
            $remark->interaction_id, 
            $userId, 
            $remark->added_by
        );
    }
}

