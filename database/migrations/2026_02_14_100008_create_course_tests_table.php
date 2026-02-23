<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained('course_blocks')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'exam', 'practice'])->default('quiz');
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('passing_score')->default(70); // Percentage
            $table->integer('max_attempts')->default(3); // 0 = unlimited
            $table->boolean('show_answers_after')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('is_required')->default(false); // Required to complete course
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_tests');
    }
};
