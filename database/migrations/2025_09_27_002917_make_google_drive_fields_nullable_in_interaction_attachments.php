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
        Schema::table('interaction_attachments', function (Blueprint $table) {
            $table->string('google_drive_file_id')->nullable()->change();
            $table->string('google_drive_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interaction_attachments', function (Blueprint $table) {
            $table->string('google_drive_file_id')->nullable(false)->change();
            $table->string('google_drive_url')->nullable(false)->change();
        });
    }
};
