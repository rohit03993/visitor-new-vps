<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            // Update the mode column to accept new values
            // We'll use a raw SQL statement to modify the enum values
        });
        
        // Use raw SQL to modify the enum values
        DB::statement("ALTER TABLE interaction_history MODIFY COLUMN mode ENUM('Walk-in', 'In-Campus', 'Out-Campus', 'Telephonic') NOT NULL DEFAULT 'Walk-in'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            // Revert back to original values
        });
        
        // Revert back to original enum values
        DB::statement("ALTER TABLE interaction_history MODIFY COLUMN mode ENUM('Walk-in', 'Telephonic') NOT NULL DEFAULT 'Walk-in'");
    }
};
