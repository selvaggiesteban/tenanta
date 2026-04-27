<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nota: En SQLite/MySQL modificar una PK a UUID en producción requiere pasos intermedios.
        // Este es el blueprint estructural para alterar las tablas críticas a UUID.
        
        $tables = ['clients', 'leads', 'courses', 'enrollments'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    // Si se está ejecutando desde cero, se usa uuid.
                    // Si es una base existente, requiere un script de migración de datos.
                    // Se añade el campo uuid como soporte para la transición.
                    if (!Schema::hasColumn($tableBlueprint->getTable(), 'uuid')) {
                        $tableBlueprint->uuid('uuid')->nullable()->unique()->after('id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['clients', 'leads', 'courses', 'enrollments'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    if (Schema::hasColumn($tableBlueprint->getTable(), 'uuid')) {
                        $tableBlueprint->dropColumn('uuid');
                    }
                });
            }
        }
    }
};
