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
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->after('meeting_with');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('scheduled_date');
            $table->boolean('is_scheduled')->default(false)->after('assigned_by');
            
            // Add foreign key for assigned_by
            $table->foreign('assigned_by')->references('user_id')->on('vms_users')->onDelete('set null');
            
            // Add index for scheduled_date for better query performance
            $table->index('scheduled_date');
            $table->index(['is_scheduled', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropIndex(['interaction_history_scheduled_date_index']);
            $table->dropIndex(['interaction_history_is_scheduled_scheduled_date_index']);
            $table->dropColumn(['scheduled_date', 'assigned_by', 'is_scheduled']);
        });
    }
};
