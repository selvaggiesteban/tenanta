<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->boolean('is_billable')->default(true);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_running')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_overtime')->default(false);
            $table->foreignId('overtime_authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overtime_authorized_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'project_id']);
            $table->index(['user_id', 'is_running']);
            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
