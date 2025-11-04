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
        Schema::create('homework_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id'); // References visitors.visitor_id
            $table->foreignId('homework_id')->constrained('homework')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Foreign key to visitors table
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_notifications');
    }
};

