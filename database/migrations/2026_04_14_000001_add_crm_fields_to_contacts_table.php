<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('company')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('company');
            $table->string('industry')->nullable()->after('job_title');
            $table->text('activity')->nullable()->after('industry');
            $table->string('linkedin_url')->nullable()->after('activity');
            $table->string('maps_url')->nullable()->after('linkedin_url');
            $table->string('address_details')->nullable()->after('maps_url');
            $table->string('city', 100)->nullable()->after('address_details');
            $table->string('province', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('province');
            $table->string('deliverability_status')->nullable()->after('country');
            $table->timestamp('whatsapp_received_at')->nullable()->after('deliverability_status');
            $table->string('entity_type')->nullable()->after('whatsapp_received_at');
            $table->string('assigned_sender')->nullable()->after('entity_type');
            $table->json('custom_fields')->nullable()->after('assigned_sender');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'company',
                'job_title',
                'industry',
                'activity',
                'linkedin_url',
                'maps_url',
                'address_details',
                'city',
                'province',
                'country',
                'deliverability_status',
                'whatsapp_received_at',
                'entity_type',
                'assigned_sender',
                'custom_fields'
            ]);
        });
    }
};
