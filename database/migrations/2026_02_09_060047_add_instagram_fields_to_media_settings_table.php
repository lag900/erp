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
        Schema::table('media_settings', function (Blueprint $table) {
            $table->string('ig_page_url')->nullable();
            $table->boolean('ig_enabled')->default(false);
            $table->text('ig_embed_token')->nullable(); // Using text for potential long embed codes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_settings', function (Blueprint $table) {
            $table->dropColumn(['ig_page_url', 'ig_enabled', 'ig_embed_token']);
        });
    }
};
