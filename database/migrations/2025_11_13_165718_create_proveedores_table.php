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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('identificador_externo')->unique()->comment('Identificador externo del proveedor');
            $table->string('nombre_comercial')->comment('Nombre comercial del proveedor');
            $table->boolean('calendario_siempre_abierto')->default(true)->comment('Indica si el proveedor está disponible 24/7');
            
            // Horarios por día de la semana (formato JSON)
            // Formato: {"apertura": "HH:MM", "cierre": "HH:MM"}
            $table->json('horario_lunes')->nullable()->comment('Horario de apertura y cierre para lunes');
            $table->json('horario_martes')->nullable()->comment('Horario de apertura y cierre para martes');
            $table->json('horario_miercoles')->nullable()->comment('Horario de apertura y cierre para miércoles');
            $table->json('horario_jueves')->nullable()->comment('Horario de apertura y cierre para jueves');
            $table->json('horario_viernes')->nullable()->comment('Horario de apertura y cierre para viernes');
            $table->json('horario_sabado')->nullable()->comment('Horario de apertura y cierre para sábado');
            $table->json('horario_domingo')->nullable()->comment('Horario de apertura y cierre para domingo');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
