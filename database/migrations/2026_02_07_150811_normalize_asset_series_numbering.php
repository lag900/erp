<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Data Cleaning: Strip 'S' from existing series_no to prepare for integer conversion
        DB::table('assets')
            ->where('series_no', 'like', 'S%')
            ->update([
                'series_no' => DB::raw("REPLACE(series_no, 'S', '')")
            ]);

        // 2. Change column type to integer for better sorting and integrity
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('series_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('series_no')->nullable()->change();
        });
    }
};
