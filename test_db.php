<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
use App\Models\Remark;
$remark = Remark::latest()->first();
echo 'ID: ' . $remark->id . PHP_EOL;
echo 'Meeting Duration: ' . ($remark->meeting_duration ?? 'NULL') . PHP_EOL;
echo 'Remark Text: ' . $remark->remark_text . PHP_EOL;
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
