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
        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
        
        // Insert default branches
        DB::table('branches')->insert([
            ['branch_name' => 'Rajpur Chungi', 'created_at' => now(), 'updated_at' => now()],
            ['branch_name' => 'Rohta', 'created_at' => now(), 'updated_at' => now()],
            ['branch_name' => 'Dev Nagar', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
