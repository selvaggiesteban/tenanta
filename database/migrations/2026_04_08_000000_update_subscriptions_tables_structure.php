<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('currency');
            }
            if (!Schema::hasColumn('subscription_plans', 'trial_days')) {
                $table->integer('trial_days')->default(0)->after('duration_days');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('ends_at');
            }
            if (!Schema::hasColumn('subscriptions', 'payment_provider')) {
                $table->string('payment_provider', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('subscriptions', 'payment_provider_id')) {
                $table->string('payment_provider_id')->nullable()->after('payment_provider');
            }
            if (!Schema::hasColumn('subscriptions', 'last_payment_at')) {
                $table->timestamp('last_payment_at')->nullable()->after('payment_provider_id');
            }
            if (!Schema::hasColumn('subscriptions', 'next_payment_at')) {
                $table->timestamp('next_payment_at')->nullable()->after('last_payment_at');
            }
            if (!Schema::hasColumn('subscriptions', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('next_payment_at');
            }
            if (!Schema::hasColumn('subscriptions', 'currency')) {
                $table->string('currency', 3)->default('ARS')->after('amount');
            }
            if (!Schema::hasColumn('subscriptions', 'external_reference')) {
                $table->string('external_reference')->nullable()->unique()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'trial_days']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'trial_ends_at',
                'payment_provider',
                'payment_provider_id',
                'last_payment_at',
                'next_payment_at',
                'amount',
                'currency',
                'external_reference'
            ]);
        });
    }
};
