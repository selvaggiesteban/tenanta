<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Logos y favicon
            $table->string('logo_light', 500)->nullable()->after('logo_url');
            $table->string('logo_dark', 500)->nullable()->after('logo_light');
            $table->string('favicon', 500)->nullable()->after('logo_dark');

            // Colores adicionales
            $table->string('secondary_color', 7)->default('#8592a3')->after('primary_color');

            // Información de contacto
            $table->string('contact_email')->nullable()->after('settings');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');
            $table->text('contact_address')->nullable()->after('contact_phone');

            // Redes sociales (JSON)
            $table->json('social_links')->nullable()->after('contact_address');

            // SEO
            $table->string('meta_title')->nullable()->after('social_links');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');

            // Configuración regional
            $table->string('locale', 10)->default('es_AR')->after('meta_keywords');
            $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires')->after('locale');
            $table->string('currency', 3)->default('ARS')->after('timezone');
            $table->string('date_format', 20)->default('DD/MM/YYYY')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo_light',
                'logo_dark',
                'favicon',
                'secondary_color',
                'contact_email',
                'contact_phone',
                'contact_address',
                'social_links',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'locale',
                'timezone',
                'currency',
                'date_format',
            ]);
        });
    }
};
