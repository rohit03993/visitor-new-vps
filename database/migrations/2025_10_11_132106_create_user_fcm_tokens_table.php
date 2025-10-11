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
        Schema::create('user_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('fcm_token', 500)->unique();
            $table->string('device_info')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->index(['user_id', 'fcm_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_fcm_tokens');
    }
};
