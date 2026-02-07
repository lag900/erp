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
        Schema::create('media_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fb_page_url')->nullable();
            $table->string('fb_page_id')->nullable();
            $table->text('fb_access_token')->nullable();
            $table->boolean('fb_enabled')->default(false);
            $table->boolean('fb_auto_publish')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_settings');
    }
};
