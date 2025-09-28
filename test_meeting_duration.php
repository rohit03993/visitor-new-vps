<?php

// Simple test script to check meeting duration functionality
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Remark;
use Illuminate\Support\Facades\Schema;

echo "=== Testing Meeting Duration Functionality ===\n\n";

// 1. Check if column exists
echo "1. Checking if meeting_duration column exists...\n";
try {
    $columns = Schema::getColumnListing('remarks');
    if (in_array('meeting_duration', $columns)) {
        echo "✅ meeting_duration column exists!\n";
    } else {
        echo "❌ meeting_duration column does NOT exist!\n";
        echo "Available columns: " . implode(', ', $columns) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking columns: " . $e->getMessage() . "\n";
}

// 2. Check if we can create a remark with meeting duration
echo "\n2. Testing remark creation with meeting duration...\n";
try {
    $remark = new Remark();
    $remark->interaction_id = 'test123';
    $remark->remark_text = 'Test remark with meeting duration';
    $remark->meeting_duration = 30;
    $remark->outcome = 'in_process';
    $remark->added_by = 1;
    $remark->added_by_name = 'Test User';
    
    echo "✅ Remark object created successfully!\n";
    echo "Meeting duration set to: " . $remark->meeting_duration . " minutes\n";
} catch (Exception $e) {
    echo "❌ Error creating remark: " . $e->getMessage() . "\n";
}

// 3. Check if meeting_duration is in fillable array
echo "\n3. Checking fillable array...\n";
$fillable = (new Remark())->getFillable();
if (in_array('meeting_duration', $fillable)) {
    echo "✅ meeting_duration is in fillable array!\n";
} else {
    echo "❌ meeting_duration is NOT in fillable array!\n";
    echo "Fillable fields: " . implode(', ', $fillable) . "\n";
}

echo "\n=== Test Complete ===\n";
