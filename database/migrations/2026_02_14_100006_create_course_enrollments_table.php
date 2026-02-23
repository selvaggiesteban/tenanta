<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
