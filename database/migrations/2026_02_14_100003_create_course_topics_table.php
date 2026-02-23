<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('course_blocks')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('video_provider', 20)->nullable(); // youtube, vimeo, wistia, local
            $table->integer('video_duration_seconds')->default(0);
            $table->string('pdf_url', 500)->nullable();
            $table->json('attachments')->nullable(); // Array of {name, url, type, size}
            $table->integer('sort_order')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->timestamps();

            $table->index(['block_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_topics');
    }
};
