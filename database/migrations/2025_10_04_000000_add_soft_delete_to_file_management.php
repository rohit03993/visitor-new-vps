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
        Schema::table('file_management', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('transferred_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            $table->string('deletion_reason')->nullable()->after('deleted_by');
            
            // Add foreign key for deleted_by
            $table->foreign('deleted_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_management', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'deletion_reason']);
        });
    }
};

