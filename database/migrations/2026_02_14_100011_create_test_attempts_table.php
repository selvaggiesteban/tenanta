<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('course_tests')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->integer('score')->default(0); // Points earned
            $table->integer('total_points')->default(0); // Total possible points
            $table->integer('percentage')->default(0); // Score percentage
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable(); // {question_id: [selected_option_ids]}
            $table->json('results')->nullable(); // {question_id: {correct: bool, points: int}}
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'test_id']);
            $table->index(['enrollment_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};
