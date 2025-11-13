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
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('identificador_externo')->unique()->comment('Identificador externo del artículo');
            $table->string('nombre')->comment('Nombre del artículo');
            $table->text('descripcion')->nullable()->comment('Descripción detallada del artículo');
            $table->decimal('precio_base', 10, 2)->comment('Precio base del artículo sin IVA');
            $table->string('tipo_iva', 50)->comment('Tipo de IVA aplicable (general, reducido, superreducido, exento)');
            $table->decimal('porcentaje_iva', 5, 2)->comment('Porcentaje de IVA a aplicar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
