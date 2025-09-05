<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename visit_id to interaction_id in interaction_history table
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->renameColumn('visit_id', 'interaction_id');
        });
        
        // Rename visit_id to interaction_id in remarks table
        Schema::table('remarks', function (Blueprint $table) {
            $table->renameColumn('visit_id', 'interaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Rename interaction_id back to visit_id in interaction_history table
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->renameColumn('interaction_id', 'visit_id');
        });
        
        // Reverse: Rename interaction_id back to visit_id in remarks table
        Schema::table('remarks', function (Blueprint $table) {
            $table->renameColumn('interaction_id', 'visit_id');
        });
    }
};
