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
        try {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropForeign(['level_id']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist
        }

        try {
            Schema::table('rooms', function (Blueprint $table) {
                $table->foreign('level_id')
                      ->references('id')
                      ->on('levels')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Constraint might already exist
        }

        // 2. Ensure rooms -> assets cascade
        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist
        }

        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->foreign('room_id')
                      ->references('id')
                      ->on('rooms')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Constraint might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert since cascade is better
    }
};
