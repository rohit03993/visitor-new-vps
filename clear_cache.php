<?php
// Simple cache clearing script
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Clear all caches
\Illuminate\Support\Facades\Cache::flush();
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('route:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

echo "Cache cleared successfully!\n";
echo "Staff list should now be available in the dropdown.\n";
?>
