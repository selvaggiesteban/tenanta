<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->nullOnDelete();
            $table->integer('order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
        });

        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('kb_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->integer('views')->default(0);
            $table->integer('helpful_yes')->default(0);
            $table->integer('helpful_no')->default(0);
            $table->json('tags')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('kb_categories');
    }
};
