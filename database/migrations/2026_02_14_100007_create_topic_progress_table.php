<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('course_topics')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('watch_time_seconds')->default(0);
            $table->integer('last_position_seconds')->default(0); // For video resume
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'topic_id']);
            $table->index(['enrollment_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_progress');
    }
};
