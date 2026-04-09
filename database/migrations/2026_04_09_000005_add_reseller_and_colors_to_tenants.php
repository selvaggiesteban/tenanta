<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('primary_color')->default('#673DE6');
            $table->string('secondary_color')->default('#00A9A5');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['reseller_id', 'primary_color', 'secondary_color']);
        });
    }
};
