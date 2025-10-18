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
        Schema::table('remarks', function (Blueprint $table) {
            // Only drop is_editable_by (interaction_mode is still in use!)
            // Check if column exists before dropping
            if (Schema::hasColumn('remarks', 'is_editable_by')) {
                // Drop foreign key if it exists
                try {
                    $table->dropForeign(['is_editable_by']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, that's okay
                }
                
                // Drop the column
                $table->dropColumn('is_editable_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remarks', function (Blueprint $table) {
            // Re-add is_editable_by if rollback is needed
            $table->unsignedBigInteger('is_editable_by')->nullable()->after('added_by_name');
            
            // Re-add the foreign key constraint
            $table->foreign('is_editable_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }
};
