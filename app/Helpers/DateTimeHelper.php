<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Format date and time in Indian format with 12-hour time
     */
    public static function formatIndianDateTime($dateTime, $format = 'M d, Y g:i A')
    {
        if (!$dateTime) {
            return null;
        }

        $carbon = Carbon::parse($dateTime)->setTimezone('Asia/Kolkata');
        return $carbon->format($format);
    }

    /**
     * Format date in Indian format
     */
    public static function formatIndianDate($dateTime, $format = 'M d, Y')
    {
        if (!$dateTime) {
            return null;
        }

        $carbon = Carbon::parse($dateTime)->setTimezone('Asia/Kolkata');
        return $carbon->format($format);
    }

    /**
     * Format time in 12-hour format with AM/PM
     */
    public static function formatIndianTime($dateTime, $format = 'g:i A')
    {
        if (!$dateTime) {
            return null;
        }

        $carbon = Carbon::parse($dateTime)->setTimezone('Asia/Kolkata');
        return $carbon->format($format);
    }

    /**
     * Get current Indian time
     */
    public static function now()
    {
        return Carbon::now('Asia/Kolkata');
    }

    /**
     * Get current Indian date
     */
    public static function today()
    {
        return Carbon::today('Asia/Kolkata');
    }
}
