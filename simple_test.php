<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
use App\Models\Remark;
 = Remark::latest()->first();
echo 'Latest remark ID: ' . ->id . PHP_EOL;
echo 'Meeting duration: ' . (->meeting_duration ?? 'NULL') . PHP_EOL;
echo 'Remark text: ' . ->remark_text . PHP_EOL;
