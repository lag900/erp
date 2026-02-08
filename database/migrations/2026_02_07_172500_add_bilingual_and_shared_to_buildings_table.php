<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->boolean('is_shared')->default(false)->after('code');
        });

        // Migrate existing data
        DB::table('buildings')->update([
            'name_en' => DB::raw('name')
        ]);

        // Make name_en required and add unique constraint
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('name_en')->nullable(false)->change();
            $table->unique(['location_id', 'name_en'], 'buildings_location_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropUnique('buildings_location_name_unique');
            $table->dropColumn(['name_en', 'name_ar', 'is_shared']);
        });
    }
};
