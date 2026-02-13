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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system', 'tool']);
            $table->text('content')->nullable();
            $table->json('tool_calls')->nullable();
            $table->json('tool_results')->nullable();
            $table->string('tool_call_id')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->string('model')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
