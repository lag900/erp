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
            $table->unsignedBigInteger('parent_id')->nullable()->after('peered_asset_id');
            $table->boolean('is_parent')->default(false)->after('parent_id');
            
            $table->foreign('parent_id')->references('id')->on('assets')->onDelete('cascade');
            
            $table->index('parent_id');
            $table->index('is_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_parent']);
        });
    }
};
