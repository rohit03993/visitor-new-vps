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
        // Only drop is_editable_by (interaction_mode is still in use!)
        // Check if column exists before dropping
        if (Schema::hasColumn('remarks', 'is_editable_by')) {
            // First, check and drop foreign key if it exists
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'remarks' 
                AND COLUMN_NAME = 'is_editable_by'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                Schema::table('remarks', function (Blueprint $table) use ($foreignKeys) {
                    $table->dropForeign($foreignKeys[0]->CONSTRAINT_NAME);
                });
            }
            
            // Then drop the column
            Schema::table('remarks', function (Blueprint $table) {
                $table->dropColumn('is_editable_by');
            });
        }
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
