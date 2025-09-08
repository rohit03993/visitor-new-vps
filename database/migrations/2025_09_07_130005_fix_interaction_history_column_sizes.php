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
        // Check if interaction_history table exists, if not create it
        if (!Schema::hasTable('interaction_history')) {
            Schema::create('interaction_history', function (Blueprint $table) {
                $table->id('interaction_id');
                $table->unsignedBigInteger('visitor_id');
                $table->string('name_entered', 255);
                $table->string('mode', 50)->default('In-Campus');
                $table->string('purpose', 255); // Increased size
                $table->unsignedBigInteger('address_id')->nullable();
                $table->unsignedBigInteger('meeting_with');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
                $table->foreign('address_id')->references('address_id')->on('addresses')->onDelete('set null');
                $table->foreign('meeting_with')->references('user_id')->on('vms_users')->onDelete('cascade');
                $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('cascade');
                
                // Indexes
                $table->index('visitor_id');
                $table->index('meeting_with');
                $table->index('created_by');
                $table->index('created_at');
            });
        } else {
            // Table exists, just modify the purpose column size
            Schema::table('interaction_history', function (Blueprint $table) {
                $table->string('purpose', 255)->change(); // Increase size to 255 characters
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->string('purpose', 50)->change(); // Revert to smaller size
        });
    }
};