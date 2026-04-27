<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Configuración de Canales por Tenant
        Schema::create('omnichannel_channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['whatsapp', 'messenger', 'email_smtp', 'email_gmail']);
            $table->string('name'); // Ej: "Soporte Ventas"
            $table->string('provider_id')->nullable(); // Ej: Phone ID de Meta
            $table->json('credentials'); // Tokens, API Keys, SMTP Config (Encriptado en Modelo)
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Webhook URLs, auto-replies logic
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Cabeceras de Conversación
        Schema::create('omnichannel_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('channel_id')->constrained('omnichannel_channels');
            $table->foreignId('contact_id')->nullable()->constrained('contacts'); // Vínculo directo al CRM
            $table->string('external_id')->index(); // ID de la conversación en el proveedor (ej: email o psid)
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'pending', 'closed', 'archived'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Agente humano asignado
            $table->timestamp('last_message_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 3. Historial de Mensajes
        Schema::create('omnichannel_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('omnichannel_conversations')->onDelete('cascade');
            $table->string('external_id')->nullable()->index(); // ID del mensaje en la red (WhatsApp Msg ID)
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('sender_name')->nullable();
            $table->string('sender_identifier')->nullable(); // Email o Teléfono
            $table->text('content');
            $table->enum('content_type', ['text', 'image', 'video', 'document', 'audio'])->default('text');
            $table->string('attachment_url')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->json('raw_payload')->nullable(); // Payload original del webhook para auditoría
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omnichannel_messages');
        Schema::dropIfExists('omnichannel_conversations');
        Schema::dropIfExists('omnichannel_channels');
    }
};
