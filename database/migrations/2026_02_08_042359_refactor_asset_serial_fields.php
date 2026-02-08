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
        Schema::table('assets', function (Blueprint $table) {
            // Rename existing bundle_number if it exists from previous turn
            if (Schema::hasColumn('assets', 'bundle_number')) {
                $table->renameColumn('bundle_number', 'bundle_group_number');
            } else {
                $table->integer('bundle_group_number')->nullable()->after('bundle_serial');
            }

            $table->string('category_prefix', 10)->nullable()->index()->after('category_id');
            $table->string('full_serial')->nullable()->index()->after('asset_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['category_prefix', 'full_serial']);
            if (Schema::hasColumn('assets', 'bundle_group_number')) {
                $table->renameColumn('bundle_group_number', 'bundle_number');
            }
        });
    }
};
