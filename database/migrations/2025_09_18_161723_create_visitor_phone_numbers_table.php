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
        Schema::create('visitor_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id');
            $table->string('phone_number', 15); // Support +91 prefix
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Foreign key constraint to visitors table
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
            
            // Ensure phone numbers are unique per visitor
            $table->unique(['visitor_id', 'phone_number']);
            
            // Index for faster searches
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_phone_numbers');
    }
};
