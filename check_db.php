<?php
require 'vendor/autoload.php';
 = require 'bootstrap/app.php';
->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Remark;
 = Remark::orderBy('created_at', 'desc')->take(3)->get(['id', 'remark_text', 'meeting_duration', 'created_at']);
foreach( as ) {
    echo 'ID: ' . ->id . ', Text: ' . ->remark_text . ', Duration: ' . (->meeting_duration ?? 'NULL') . ', Created: ' . ->created_at . PHP_EOL;
}
