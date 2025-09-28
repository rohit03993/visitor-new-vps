<?php

// Script to update existing remarks with default meeting duration
// Run this with: php update_existing_remarks.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Remark;

echo "Updating existing remarks with default meeting duration...\n";

// Get all remarks that don't have meeting_duration set
$remarks = Remark::whereNull('meeting_duration')->get();

echo "Found " . $remarks->count() . " remarks to update.\n";

$updated = 0;
foreach ($remarks as $remark) {
    // Set a default meeting duration (you can change this value)
    $remark->meeting_duration = 15; // Default to 15 minutes
    $remark->save();
    $updated++;
}

echo "Updated " . $updated . " remarks with default meeting duration of 15 minutes.\n";
echo "Done!\n";
