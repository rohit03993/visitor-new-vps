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
        // Add composite index for efficient "assigned to purpose" queries
        Schema::table('interaction_history', function (Blueprint $table) {
            // Use Laravel's schema introspection instead of Doctrine
            $indexes = DB::select("SHOW INDEX FROM interaction_history WHERE Key_name = 'idx_visitor_purpose_assignee'");
            
            if (empty($indexes)) {
                $table->index(['visitor_id', 'purpose', 'meeting_with', 'is_completed'], 'idx_visitor_purpose_assignee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->dropIndex('idx_visitor_purpose_assignee');
        });
    }
};
