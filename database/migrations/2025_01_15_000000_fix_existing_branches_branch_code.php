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
        // Update existing branches that don't have branch_code
        $branches = DB::table('branches')->whereNull('branch_code')->get();
        
        foreach ($branches as $branch) {
            $branchCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $branch->branch_name), 0, 10));
            
            // Ensure uniqueness
            $counter = 1;
            $originalCode = $branchCode;
            while (DB::table('branches')->where('branch_code', $branchCode)->exists()) {
                $branchCode = $originalCode . $counter;
                $counter++;
            }
            
            DB::table('branches')
                ->where('branch_id', $branch->branch_id)
                ->update(['branch_code' => $branchCode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
    }
};
