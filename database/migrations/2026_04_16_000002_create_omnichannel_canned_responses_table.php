<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omnichannel_canned_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('shortcut')->index(); // Ej: "bienvenida"
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'shortcut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omnichannel_canned_responses');
    }
};
