<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_department', function (Blueprint $row) {
            $row->id();
            $row->foreignId('category_id')->constrained()->cascadeOnDelete();
            $row->foreignId('department_id')->constrained()->cascadeOnDelete();
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_department');
    }
};
