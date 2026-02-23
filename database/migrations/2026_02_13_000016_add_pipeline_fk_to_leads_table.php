<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('pipeline_id')
                ->references('id')
                ->on('pipelines')
                ->nullOnDelete();

            $table->foreign('pipeline_stage_id')
                ->references('id')
                ->on('pipeline_stages')
                ->nullOnDelete();

            $table->index(['tenant_id', 'pipeline_id']);
            $table->index(['tenant_id', 'pipeline_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['pipeline_id']);
            $table->dropForeign(['pipeline_stage_id']);
            $table->dropIndex(['tenant_id', 'pipeline_id']);
            $table->dropIndex(['tenant_id', 'pipeline_stage_id']);
        });
    }
};
