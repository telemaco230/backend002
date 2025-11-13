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
        Schema::create('articulo_proveedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->onDelete('cascade')->comment('ID del artículo');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade')->comment('ID del proveedor');
            $table->timestamps();
            
            // Índice único para evitar duplicados en la relación
            $table->unique(['articulo_id', 'proveedor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_proveedor');
    }
};
