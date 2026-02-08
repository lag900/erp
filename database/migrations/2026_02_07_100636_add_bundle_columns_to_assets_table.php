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
        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('is_bundle_parent')->default(false)->after('is_parent');
            $table->integer('component_no')->after('series_no')->nullable();
            
            // To be strictly compliant with "series_no int", 
            // but keep it safe, we'll ensure we have a numeric series tracker.
            // Since series_no is already varchar, we'll keep it for display logic
            // but we can add an index or helper if needed. 
            // For now, adding is_bundle_parent and component_no is the priority.
        });

        // Sync existing is_parent to is_bundle_parent
        DB::table('assets')->where('is_parent', true)->update(['is_bundle_parent' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['is_bundle_parent', 'component_no']);
        });
    }
};
