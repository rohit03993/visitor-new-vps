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
        Schema::create('homework_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homework')->onDelete('cascade');
            $table->unsignedBigInteger('visitor_id'); // References visitors.visitor_id
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
            
            // Ensure unique combination of homework and student
            $table->unique(['homework_id', 'visitor_id']);
            
            // Foreign key to visitors table
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_views');
    }
};

