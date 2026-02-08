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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_code')->nullable()->unique()->index()->after('id');
            $table->string('series_id')->nullable()->index()->after('asset_code');
            $table->integer('sequence_number')->default(0)->after('series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['asset_code', 'series_id', 'sequence_number']);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
