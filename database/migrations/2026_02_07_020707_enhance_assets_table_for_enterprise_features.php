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
        Schema::table('assets', function (Blueprint $blueprint) {
            $blueprint->string('serial_number')->nullable()->after('note');
            $blueprint->enum('condition', ['active', 'maintenance', 'damaged', 'disposed'])->default('active')->after('serial_number');
            $blueprint->boolean('is_shared')->default(false)->after('condition');
            $blueprint->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null')->after('is_shared');
        });

        Schema::create('asset_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('to_room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_department');
        Schema::table('assets', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['created_by_id']);
            $blueprint->dropColumn(['serial_number', 'condition', 'is_shared', 'created_by_id']);
        });
    }
};
