<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tenants', 'services')) {
                $table->json('services')->nullable()->after('features');
            }
            if (!Schema::hasColumn('tenants', 'faqs')) {
                $table->json('faqs')->nullable()->after('services');
            }
            if (!Schema::hasColumn('tenants', 'reviews')) {
                $table->json('reviews')->nullable()->after('faqs');
            }
            if (!Schema::hasColumn('tenants', 'seo_metadata')) {
                $table->json('seo_metadata')->nullable()->after('reviews');
            }
            if (!Schema::hasColumn('tenants', 'business_hours')) {
                $table->json('business_hours')->nullable()->after('seo_metadata');
            }
            if (!Schema::hasColumn('tenants', 'google_map_url')) {
                $table->text('google_map_url')->nullable()->after('business_hours');
            }
            if (!Schema::hasColumn('tenants', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('contact_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'services',
                'faqs',
                'reviews',
                'seo_metadata',
                'business_hours',
                'google_map_url',
                'whatsapp_number'
            ]);
        });
    }
};
