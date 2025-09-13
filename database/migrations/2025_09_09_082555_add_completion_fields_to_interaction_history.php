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
            $table->boolean('is_completed')->default(false)->after('created_by_role');
            $table->timestamp('completed_at')->nullable()->after('is_completed');
            $table->unsignedBigInteger('completed_by')->nullable()->after('completed_at');
            
            $table->foreign('completed_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['is_completed', 'completed_at', 'completed_by']);
        });
    }
};
