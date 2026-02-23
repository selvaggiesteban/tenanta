<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('ARS');
            $table->string('image_path', 500)->nullable();
            $table->string('video_preview_url', 500)->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('duration_hours')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('requirements')->nullable();
            $table->json('objectives')->nullable();
            $table->json('meta')->nullable(); // SEO and additional metadata
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
