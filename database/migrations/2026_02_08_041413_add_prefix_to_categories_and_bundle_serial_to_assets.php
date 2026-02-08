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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('prefix', 10)->nullable()->after('name');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('bundle_serial')->nullable()->index()->after('asset_code');
            $table->integer('bundle_number')->nullable()->after('bundle_serial');
        });

        // Initialize prefixes for requested categories
        DB::table('categories')->where('name', 'Computers')->update(['prefix' => 'S01']);
        DB::table('categories')->where('name', 'Printers')->update(['prefix' => 'S02']);
        DB::table('categories')->where('name', 'Network')->update(['prefix' => 'S03']);
        
        // Add default prefixes for others if they don't have one
        DB::table('categories')->whereNull('prefix')->where('name', 'كراسي')->update(['prefix' => 'S04']);
        DB::table('categories')->whereNull('prefix')->where('name', 'طرابيزات')->update(['prefix' => 'S05']);
        DB::table('categories')->whereNull('prefix')->where('name', 'PORT')->update(['prefix' => 'S06']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['bundle_serial', 'bundle_number']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }
};
