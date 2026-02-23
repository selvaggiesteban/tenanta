<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('test_questions')->cascadeOnDelete();
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_options');
    }
};
