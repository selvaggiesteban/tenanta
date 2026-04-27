<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('omnichannel_messages', function (Blueprint $table) {
            $table->enum('type', ['message', 'note'])->default('message')->after('direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('omnichannel_messages', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
