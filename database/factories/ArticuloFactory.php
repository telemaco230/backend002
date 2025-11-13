<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Articulo>
 */
class ArticuloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiposIva = ['general', 'reducido', 'superreducido', 'exento'];
        $porcentajesIva = [
            'general' => 21.00,
            'reducido' => 10.00,
            'superreducido' => 4.00,
            'exento' => 0.00,
        ];

        $tipoIva = $this->faker->randomElement($tiposIva);

        return [
            'identificador_externo' => 'ART-' . strtoupper($this->faker->unique()->bothify('???-####')),
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->paragraph(),
            'precio_base' => $this->faker->randomFloat(2, 10, 1000),
            'tipo_iva' => $tipoIva,
            'porcentaje_iva' => $porcentajesIva[$tipoIva],
        ];
    }
}
