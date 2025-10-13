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
            // First drop the foreign key constraint
            $table->dropForeign(['is_editable_by']);
            
            // Then drop the columns
            $table->dropColumn(['is_editable_by', 'interaction_mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remarks', function (Blueprint $table) {
            // Re-add the fields if rollback is needed
            $table->unsignedBigInteger('is_editable_by')->nullable()->after('added_by_name');
            $table->string('interaction_mode', 50)->nullable()->after('remark_text');
            
            // Re-add the foreign key constraint
            $table->foreign('is_editable_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }
};
