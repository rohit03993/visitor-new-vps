<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';
    protected $primaryKey = 'visitor_id';

    protected $fillable = [
        'mobile_number',
        'name',
        'purpose',
        'address_id',
        'course_id',
        'student_name',
        'father_name',
        'created_by',
        'last_updated_by',
    ];

    // Relationships
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(VmsUser::class, 'created_by');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(VmsUser::class, 'last_updated_by');
    }

    public function interactions()
    {
        return $this->hasMany(InteractionHistory::class, 'visitor_id');
    }

    /**
     * Get the course selected by this visitor
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get all student sessions for this visitor
     */
    public function studentSessions()
    {
        return $this->hasMany(StudentSession::class, 'visitor_id', 'visitor_id');
    }

    /**
     * Get active student sessions for this visitor
     */
    public function activeSessions()
    {
        return $this->studentSessions()->where('status', 'active');
    }

    /**
     * Get all tags associated with this visitor
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'visitor_tags', 'visitor_id', 'tag_id');
    }

    // Helper methods
    public function getLatestInteraction()
    {
        return $this->interactions()->latest()->first();
    }

    public function getFirstInteraction()
    {
        return $this->interactions()->oldest()->first();
    }

    public function getTotalInteractions()
    {
        return $this->interactions()->count();
    }

    /**
     * Check if visitor is a student (has course selected)
     */
    public function isStudent(): bool
    {
        return !is_null($this->course_id);
    }

    /**
     * Check if visitor has active sessions
     */
    public function hasActiveSessions(): bool
    {
        return $this->activeSessions()->exists();
    }

    /**
     * Get the latest active session
     */
    public function getLatestActiveSession()
    {
        return $this->activeSessions()->latest('started_at')->first();
    }

    // ========== PHONE NUMBER RELATIONSHIPS (NEW FEATURE) ==========
    
    /**
     * Get all additional phone numbers for this visitor
     */
    public function additionalPhoneNumbers()
    {
        return $this->hasMany(VisitorPhoneNumber::class, 'visitor_id', 'visitor_id');
    }
    
    /**
     * Get all phone numbers (including primary from mobile_number field)
     */
    public function getAllPhoneNumbers()
    {
        $phones = collect();
        
        // Add primary phone number from mobile_number field
        if (!empty($this->mobile_number)) {
            $phones->push([
                'phone_number' => $this->mobile_number,
                'is_primary' => true,
                'source' => 'primary'
            ]);
        }
        
        // Add additional phone numbers
        $additionalPhones = $this->additionalPhoneNumbers()->get();
        foreach ($additionalPhones as $phone) {
            $phones->push([
                'phone_number' => $phone->phone_number,
                'is_primary' => false,
                'source' => 'additional',
                'id' => $phone->id
            ]);
        }
        
        return $phones;
    }
    
    /**
     * Get count of all phone numbers (including primary)
     */
    public function getTotalPhoneCount()
    {
        $count = 0;
        
        // Count primary phone number
        if (!empty($this->mobile_number)) {
            $count++;
        }
        
        // Count additional phone numbers
        $count += $this->additionalPhoneNumbers()->count();
        
        return $count;
    }
    
    /**
     * Check if visitor can add more phone numbers (max 4 total)
     */
    public function canAddMorePhoneNumbers()
    {
        return $this->getTotalPhoneCount() < 4;
    }
    
    /**
     * Get all phone numbers with masking for privacy
     */
    public function getAllPhoneNumbersMasked()
    {
        $phones = collect();
        
        // Add primary phone number from mobile_number field (already masked in controller)
        if (!empty($this->mobile_number)) {
            $phones->push([
                'phone_number' => $this->mobile_number, // Already masked by controller
                'is_primary' => true,
                'source' => 'primary'
            ]);
        }
        
        // Add additional phone numbers with masking
        $additionalPhones = $this->additionalPhoneNumbers()->get();
        foreach ($additionalPhones as $phone) {
            $phones->push([
                'phone_number' => $this->maskPhoneNumber($phone->phone_number),
                'original_phone_number' => $phone->phone_number, // Store original for functionality
                'is_primary' => false,
                'source' => 'additional',
                'id' => $phone->id
            ]);
        }
        
        return $phones;
    }
    
    /**
     * Mask phone number for privacy (same logic as StaffController)
     */
    private function maskPhoneNumber($mobileNumber)
    {
        if (empty($mobileNumber)) {
            return $mobileNumber;
        }
        
        // Remove any spaces or special characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $mobileNumber);
        
        // If number is too short, return as is
        if (strlen($cleaned) < 8) {
            return $mobileNumber;
        }
        
        // Extract country code (+91) and mask the middle
        if (strpos($cleaned, '+91') === 0) {
            $countryCode = '+91';
            $number = substr($cleaned, 3); // Remove +91
        } else {
            $countryCode = '';
            $number = $cleaned;
        }
        
        // Show first 2 digits and last 2 digits, mask the rest
        if (strlen($number) >= 6) {
            $firstTwo = substr($number, 0, 2);
            $lastTwo = substr($number, -2);
            $masked = $firstTwo . str_repeat('X', strlen($number) - 4) . $lastTwo;
        } else {
            $masked = $number;
        }
        
        return $countryCode . $masked;
    }
}
