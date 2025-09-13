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
        // Set created_by for existing branches that don't have it
        // Use the first admin user, or user_id 1 if no admin exists
        $adminUser = DB::table('vms_users')->where('role', 'admin')->first();
        $defaultUserId = $adminUser ? $adminUser->user_id : 1;
        
        DB::table('branches')
            ->whereNull('created_by')
            ->update(['created_by' => $defaultUserId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
    }
};
