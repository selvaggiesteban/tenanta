<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('subject');
            $table->text('preview_text')->nullable();
            $table->longText('html_content');
            $table->longText('text_content')->nullable();

            // Campaign type
            $table->string('type')->default('regular'); // regular, automated, ab_test

            // Targeting
            $table->string('audience_type')->default('all'); // all, segment, manual
            $table->json('audience_filters')->nullable(); // filters for segment-based targeting
            $table->json('recipient_ids')->nullable(); // specific user IDs for manual targeting

            // Scheduling
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent, paused, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Sender info
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();

            // Statistics
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('bounced_count')->default(0);
            $table->unsignedInteger('unsubscribed_count')->default(0);
            $table->unsignedInteger('complained_count')->default(0);

            // Settings
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
