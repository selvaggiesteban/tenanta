<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            // List type
            $table->string('type')->default('static'); // static, dynamic

            // For dynamic lists - filter criteria
            $table->json('filters')->nullable();

            // Statistics
            $table->unsignedInteger('subscriber_count')->default(0);
            $table->unsignedInteger('active_count')->default(0);
            $table->unsignedInteger('unsubscribed_count')->default(0);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Pivot table for list subscribers
        Schema::create('email_list_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('email_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // For non-user subscribers
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->json('custom_fields')->nullable();

            // Subscription status
            $table->string('status')->default('subscribed'); // subscribed, unsubscribed, cleaned, pending
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_reason')->nullable();

            // Source tracking
            $table->string('source')->nullable(); // import, signup, manual, api

            $table->timestamps();

            $table->unique(['list_id', 'user_id']);
            $table->unique(['list_id', 'email']);
            $table->index(['list_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_list_subscribers');
        Schema::dropIfExists('email_lists');
    }
};
