<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('preview_text')->nullable();
            $table->longText('html_content');
            $table->longText('text_content')->nullable();

            // Template type and category
            $table->string('type')->default('marketing'); // marketing, transactional, notification
            $table->string('category')->nullable(); // welcome, newsletter, promotion, reminder, etc.

            // Design settings
            $table->json('design_settings')->nullable(); // colors, fonts, logo, etc.
            $table->string('thumbnail')->nullable();

            // Variables/placeholders available in this template
            $table->json('variables')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Metadata
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
