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
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->onDelete('cascade')->comment('ID del artículo al que pertenece');
            $table->string('nombre_archivo')->comment('Nombre original del archivo de imagen');
            $table->string('ruta')->comment('Ruta de almacenamiento de la imagen');
            $table->string('tipo_mime')->comment('Tipo MIME de la imagen');
            $table->unsignedBigInteger('tamanio')->comment('Tamaño del archivo en bytes');
            $table->unsignedInteger('orden')->default(0)->comment('Orden de visualización de la imagen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes');
    }
};
