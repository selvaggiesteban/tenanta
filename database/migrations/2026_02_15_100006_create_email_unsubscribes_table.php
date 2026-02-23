<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();

            $table->string('email');
            $table->string('reason')->nullable();
            $table->text('feedback')->nullable();

            // Unsubscribe scope
            $table->string('scope')->default('all'); // all, campaign_type, list

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribes');
    }
};
