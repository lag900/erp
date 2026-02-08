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
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('permission_group_id')->nullable()->after('id')->constrained('permission_groups')->nullOnDelete();
            $table->text('description')->nullable()->after('name');
            $table->boolean('status')->default(true)->after('description');
            
            // If the category column from the previous migration exists, we can drop it after migrating data 
            // but for now let's just drop it as we'll use permission_group_id
            if (Schema::hasColumn('permissions', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_group_id']);
            $table->dropColumn(['permission_group_id', 'description', 'status']);
            $table->string('category')->nullable()->after('name');
        });
    }
};
