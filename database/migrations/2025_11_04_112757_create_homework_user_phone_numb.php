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
        Schema::create('homework_user_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_user_id')->constrained('homework_users')->onDelete('cascade');
            $table->string('phone_number', 15); // +91XXXXXXXXXX format
            $table->boolean('whatsapp_enabled')->default(true); // Admin can enable/disable WhatsApp
            $table->timestamps();
            
            // Ensure no duplicate phone numbers for same user
            $table->unique(['homework_user_id', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_user_phone_numbers');
    }
};
