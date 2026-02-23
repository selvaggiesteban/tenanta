<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('position')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->string('source')->nullable(); // web, referral, cold_call, etc.
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('pipeline_id')->nullable();
            $table->unsignedBigInteger('pipeline_stage_id')->nullable();
            $table->foreignId('converted_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
