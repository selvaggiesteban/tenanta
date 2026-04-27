<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('source_type'); // 'course', 'kb_article'
            $table->unsignedBigInteger('source_id');
            $table->string('title');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->string('hash');
            $table->timestamps();

            $table->index(['tenant_id', 'source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_indices');
    }
};
