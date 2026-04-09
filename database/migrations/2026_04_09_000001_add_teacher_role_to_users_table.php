<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade el rol 'teacher' a la tabla users.
     */
    public function up(): void
    {
        // En SQLite no se puede modificar un ENUM directamente de forma fácil, 
        // pero Laravel permite cambiar la columna a string temporalmente o recrearla.
        // Dado que estamos en desarrollo, forzaremos la actualización del comentario 
        // y la lógica de validación se manejará en el modelo.
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->change(); // Cambiamos a string para permitir nuevos valores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir a los roles originales si es necesario
        });
    }
};
