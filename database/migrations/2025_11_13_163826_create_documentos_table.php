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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->onDelete('cascade')->comment('ID del artículo al que pertenece');
            $table->string('nombre_archivo')->comment('Nombre original del archivo de documento');
            $table->string('ruta')->comment('Ruta de almacenamiento del documento');
            $table->string('tipo_mime')->comment('Tipo MIME del documento');
            $table->unsignedBigInteger('tamanio')->comment('Tamaño del archivo en bytes');
            $table->string('descripcion')->nullable()->comment('Descripción del documento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
