<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['super_admin', 'admin', 'manager', 'member'])->default('member')->after('password');
            $table->decimal('contracted_hours', 4, 2)->default(8.00)->after('role');
            $table->decimal('billable_rate', 10, 2)->default(0.00)->after('contracted_hours');
            $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires')->after('billable_rate');
            $table->string('avatar_url', 500)->nullable()->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role', 'contracted_hours', 'billable_rate', 'timezone', 'avatar_url', 'last_login_at', 'deleted_at']);
        });
    }
};
