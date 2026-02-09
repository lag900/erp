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
        Schema::table('departments', function (Blueprint $table) {
            $table->string('director_name')->nullable();
            $table->string('director_title')->nullable();
            $table->string('director_image')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('status')->default('active'); // active, inactive, hidden
            $table->string('type')->default('department'); // department, faculty
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['director_name', 'director_title', 'director_image', 'display_order', 'status', 'type']);
        });
    }
};
