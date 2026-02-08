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
        // 1. Ensure levels -> rooms cascade
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->foreign('level_id')
                  ->references('id')
                  ->on('levels')
                  ->onDelete('cascade');
        });

        // 2. Ensure rooms -> assets cascade
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->foreign('room_id')
                  ->references('id')
                  ->on('rooms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert since cascade is better
    }
};
