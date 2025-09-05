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
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('purpose')->nullable()->after('name');
            $table->unsignedBigInteger('address_id')->nullable()->after('purpose');
            $table->unsignedBigInteger('created_by')->nullable()->after('address_id');
            
            // Make existing last_updated_by field nullable
            $table->unsignedBigInteger('last_updated_by')->nullable()->change();
            
            // Add foreign key constraints
            $table->foreign('address_id')->references('address_id')->on('addresses')->onDelete('set null');
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['purpose', 'address_id', 'created_by']);
        });
    }
};
