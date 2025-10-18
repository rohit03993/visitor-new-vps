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
        Schema::table('interaction_notifications', function (Blueprint $table) {
            // Add can_view_remarks column with default TRUE (less restrictive)
            $table->boolean('can_view_remarks')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_notifications', function (Blueprint $table) {
            $table->dropColumn('can_view_remarks');
        });
    }
};

