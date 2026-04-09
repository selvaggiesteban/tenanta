<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('name');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->string('hero_image')->nullable()->after('hero_subtitle');
            $table->json('features')->nullable()->after('hero_image');
            $table->text('about_text')->nullable()->after('features');
            $table->string('cta_text')->nullable()->after('about_text');
            $table->string('cta_url')->nullable()->after('cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title',
                'hero_subtitle',
                'hero_image',
                'features',
                'about_text',
                'cta_text',
                'cta_url',
            ]);
        });
    }
};
