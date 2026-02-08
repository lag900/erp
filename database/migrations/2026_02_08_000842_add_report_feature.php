<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Feature;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Feature::firstOrCreate([
            'key' => 'reports',
        ], [
            'name' => 'Reports and Analytics',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Feature::where('key', 'reports')->delete();
    }
};
