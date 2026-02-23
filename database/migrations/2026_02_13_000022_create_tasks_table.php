<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'review', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('pipeline_stage_id')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'project_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assignee_id']);
            $table->index(['project_id', 'sort_order']);
        });

        // Task dependencies pivot table
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
    }
};
