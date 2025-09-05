<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing "Walk-in" records to "In-Campus"
        DB::statement("UPDATE interaction_history SET mode = 'In-Campus' WHERE mode = 'Walk-in'");
        
        // Then modify the enum to remove "Walk-in" option
        DB::statement("ALTER TABLE interaction_history MODIFY COLUMN mode ENUM('In-Campus', 'Out-Campus', 'Telephonic') NOT NULL DEFAULT 'In-Campus'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back "Walk-in" option
        DB::statement("ALTER TABLE interaction_history MODIFY COLUMN mode ENUM('Walk-in', 'In-Campus', 'Out-Campus', 'Telephonic') NOT NULL DEFAULT 'Walk-in'");
        
        // Convert "In-Campus" back to "Walk-in" for records that were originally "Walk-in"
        // Note: This is approximate since we can't know which were originally "Walk-in"
        DB::statement("UPDATE interaction_history SET mode = 'Walk-in' WHERE mode = 'In-Campus' LIMIT 100");
    }
};
