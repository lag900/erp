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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Actor Information
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('role')->nullable(); // Role at the time of action
            
            // Context
            $table->string('action_type'); // e.g., create, update, delete, login
            $table->string('module');      // e.g., Users, Assets, Finance
            $table->string('status')->default('success'); // success, failed
            $table->string('severity')->default('info'); // info, warning, critical

            // System Context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();

            // Affected Resource (Polymorphic)
            $table->nullableMorphs('auditable');
            
            // Data Changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            $table->timestamps();
            
            // Indexes for Performance
            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['severity']);
            $table->index(['action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
