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
            $table->string('arabic_name')->nullable()->after('name');
            $table->string('university_logo')->nullable()->after('description');
            $table->string('department_logo')->nullable()->after('university_logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['arabic_name', 'university_logo', 'department_logo']);
        });
    }
};
