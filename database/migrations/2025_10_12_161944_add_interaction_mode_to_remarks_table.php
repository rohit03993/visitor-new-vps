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
        Schema::table('remarks', function (Blueprint $table) {
            $table->enum('interaction_mode', ['In-Campus', 'Out-Campus', 'Telephonic'])->nullable()->after('remark_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remarks', function (Blueprint $table) {
            $table->dropColumn('interaction_mode');
        });
    }
};
