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
            $table->string('sidebar_label')->nullable()->after('description');
            $table->string('route_name')->nullable()->after('sidebar_label');
            $table->string('icon')->nullable()->after('route_name');
            $table->boolean('is_sidebar_item')->default(false)->after('icon');
            $table->integer('sort_order')->default(0)->after('is_sidebar_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['sidebar_label', 'route_name', 'icon', 'is_sidebar_item', 'sort_order']);
        });
    }
};
