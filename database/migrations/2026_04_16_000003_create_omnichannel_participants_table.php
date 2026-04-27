<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omnichannel_participants', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('agent'); // agent, observer, manager
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('omnichannel_conversations')->cascadeOnDelete();
            $table->unique(['conversation_id', 'user_id']);
        });

        // Añadir columna current_agent_id a la tabla de conversaciones para asignación primaria
        Schema::table('omnichannel_conversations', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omnichannel_participants');
    }
};
