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
        // Check if condition exists before renaming
        if (Schema::hasColumn('assets', 'condition')) {
            Schema::table('assets', function (Blueprint $table) {
                // For SQLite/basic drivers, renameColumn works. 
                // For MySQL/Postgres with Enums, it can be tricky.
                $table->renameColumn('condition', 'status');
            });
        }

        // Expand enum options
        // We use string instead of enum for better flexibility across drivers during migration, 
        // but we'll enforce values in the application layer.
        Schema::table('assets', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('status', 'condition');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->enum('condition', ['active', 'maintenance', 'damaged', 'disposed'])->default('active')->change();
        });
    }
};
