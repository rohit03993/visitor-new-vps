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
        Schema::table('vms_users', function (Blueprint $table) {
            // Add new fields
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->string('mobile_number', 15)->nullable()->after('branch_id');
            $table->boolean('can_view_remarks')->default(false)->after('mobile_number');
            $table->boolean('can_download_excel')->default(false)->after('can_view_remarks');
            
            // Add foreign key constraint
            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('set null');
        });
        
        // Set default branch for existing users (Rajpur Chungi as default)
        DB::statement("UPDATE vms_users SET branch_id = (SELECT branch_id FROM branches WHERE branch_name = 'Rajpur Chungi' LIMIT 1)");
        
        // Give admin users full permissions
        DB::statement("UPDATE vms_users SET can_view_remarks = true, can_download_excel = true WHERE role = 'admin'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vms_users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'mobile_number', 'can_view_remarks', 'can_download_excel']);
        });
    }
};
