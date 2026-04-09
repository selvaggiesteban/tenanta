<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Importación masiva de estructura Academicus (LMS + Tienda) a Tenanta.
     */
    public function up(): void
    {
        // 1. ESTRUCTURA EDUCATIVA (LMS)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('course_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_block_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_free')->default(false);
            $table->timestamps();
        });

        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('event_name');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // 2. ESTRUCTURA COMERCIAL (TIENDA)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            // Datos de facturación localizados LATAM
            $table->string('billing_name')->nullable();
            $table->string('billing_tax_id')->nullable(); // CUIT/CUIL/RUT
            $table->string('billing_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
        Schema::dropIfExists('course_topics');
        Schema::dropIfExists('course_blocks');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('orders');
    }
};
