<?php

/**
 * Clear Visitor Data Script
 * 
 * This script safely clears ONLY visitor data and interactions
 * while preserving staff, courses, locations, and other essential data.
 */

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧹 Task Book - Visitor Data Cleanup Script\n";
echo "==========================================\n\n";

try {
    // Disable foreign key checks temporarily
    echo "🔧 Disabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // Count records before deletion
    $visitorCount = DB::table('visitors')->count();
    $interactionCount = DB::table('interaction_history')->count();
    $remarkCount = DB::table('remarks')->count();
    $sessionCount = DB::table('student_sessions')->count();
    
    echo "📊 Current Data Count:\n";
    echo "   • Visitors: {$visitorCount}\n";
    echo "   • Interactions: {$interactionCount}\n";
    echo "   • Remarks: {$remarkCount}\n";
    echo "   • Student Sessions: {$sessionCount}\n\n";
    
    // Clear visitor-related data in correct order (respecting foreign keys)
    echo "🗑️  Clearing visitor-related data...\n";
    
    // Helper function to check if table exists
    $tableExists = function($tableName) {
        $tables = DB::select("SHOW TABLES LIKE '{$tableName}'");
        return !empty($tables);
    };
    
    // 1. Clear remarks (depends on interactions)
    if ($tableExists('remarks')) {
        echo "   → Clearing remarks...\n";
        DB::table('remarks')->truncate();
    } else {
        echo "   → remarks table doesn't exist, skipping...\n";
    }
    
    // 2. Clear interaction history (depends on visitors and sessions)
    if ($tableExists('interaction_history')) {
        echo "   → Clearing interaction history...\n";
        DB::table('interaction_history')->truncate();
    } else {
        echo "   → interaction_history table doesn't exist, skipping...\n";
    }
    
    // 3. Clear student sessions (depends on visitors)
    if ($tableExists('student_sessions')) {
        echo "   → Clearing student sessions...\n";
        DB::table('student_sessions')->truncate();
    } else {
        echo "   → student_sessions table doesn't exist, skipping...\n";
    }
    
    // 4. Clear visitor-tag relationships (pivot table)
    if ($tableExists('visitor_tag')) {
        echo "   → Clearing visitor-tag relationships...\n";
        DB::table('visitor_tag')->truncate();
    } else {
        echo "   → visitor_tag table doesn't exist, skipping...\n";
    }
    
    // 5. Clear visitors (main table)
    if ($tableExists('visitors')) {
        echo "   → Clearing visitors...\n";
        DB::table('visitors')->truncate();
    } else {
        echo "   → visitors table doesn't exist, skipping...\n";
    }
    
    // 6. Clear notification files if they exist
    echo "   → Clearing notification files...\n";
    $notificationDir = storage_path('app/notifications');
    if (is_dir($notificationDir)) {
        $files = glob($notificationDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "     Cleared " . count($files) . " notification files\n";
    }
    
    // Re-enable foreign key checks
    echo "🔧 Re-enabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    // Verify deletion
    $newVisitorCount = DB::table('visitors')->count();
    $newInteractionCount = DB::table('interaction_history')->count();
    $newRemarkCount = DB::table('remarks')->count();
    $newSessionCount = DB::table('student_sessions')->count();
    
    echo "\n✅ Data Cleanup Completed Successfully!\n";
    echo "==========================================\n";
    echo "📊 After Cleanup:\n";
    echo "   • Visitors: {$newVisitorCount} (was {$visitorCount})\n";
    echo "   • Interactions: {$newInteractionCount} (was {$interactionCount})\n";
    echo "   • Remarks: {$newRemarkCount} (was {$remarkCount})\n";
    echo "   • Student Sessions: {$newSessionCount} (was {$sessionCount})\n\n";
    
    // Verify preserved data
    $staffCount = DB::table('vms_users')->where('role', 'staff')->count();
    $courseCount = DB::table('courses')->count();
    $locationCount = DB::table('addresses')->count();
    $tagCount = DB::table('tags')->count();
    
    echo "✅ Preserved Data (Unchanged):\n";
    echo "   • Staff Users: {$staffCount}\n";
    echo "   • Courses: {$courseCount}\n";
    echo "   • Locations: {$locationCount}\n";
    echo "   • Tags: {$tagCount}\n\n";
    
    echo "🎯 Your Task Book CRM is now clean and ready for fresh data!\n";
    echo "🔔 Notification system will continue working for new assignments.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    echo "📋 Stack trace: " . $e->getTraceAsString() . "\n";
    
    // Re-enable foreign key checks even if there was an error
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        echo "🔧 Foreign key checks re-enabled.\n";
    } catch (Exception $fkError) {
        echo "⚠️  Warning: Could not re-enable foreign key checks: " . $fkError->getMessage() . "\n";
    }
}

echo "\n🏁 Script completed.\n";
