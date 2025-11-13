<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedor>
 */
class ProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identificador_externo' => 'PROV-' . strtoupper($this->faker->unique()->bothify('???-####')),
            'nombre_comercial' => $this->faker->company(),
            'calendario_siempre_abierto' => true,
            'horario_lunes' => null,
            'horario_martes' => null,
            'horario_miercoles' => null,
            'horario_jueves' => null,
            'horario_viernes' => null,
            'horario_sabado' => null,
            'horario_domingo' => null,
        ];
    }

    /**
     * Estado para un proveedor con horario específico.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function conHorario(): Factory
    {
        return $this->state(function (array $attributes) {
            $horarioTipo = $this->faker->randomElement(['completo', 'laborable', 'personalizado']);

            $horarioCompleto = [
                'apertura' => '09:00',
                'cierre' => '18:00',
            ];

            switch ($horarioTipo) {
                case 'completo':
                    // Mismo horario todos los días
                    return [
                        'calendario_siempre_abierto' => false,
                        'horario_lunes' => $horarioCompleto,
                        'horario_martes' => $horarioCompleto,
                        'horario_miercoles' => $horarioCompleto,
                        'horario_jueves' => $horarioCompleto,
                        'horario_viernes' => $horarioCompleto,
                        'horario_sabado' => $horarioCompleto,
                        'horario_domingo' => $horarioCompleto,
                    ];

                case 'laborable':
                    // Solo días laborables
                    return [
                        'calendario_siempre_abierto' => false,
                        'horario_lunes' => $horarioCompleto,
                        'horario_martes' => $horarioCompleto,
                        'horario_miercoles' => $horarioCompleto,
                        'horario_jueves' => $horarioCompleto,
                        'horario_viernes' => $horarioCompleto,
                        'horario_sabado' => null,
                        'horario_domingo' => null,
                    ];

                case 'personalizado':
                    // Horarios variados
                    return [
                        'calendario_siempre_abierto' => false,
                        'horario_lunes' => ['apertura' => '08:00', 'cierre' => '20:00'],
                        'horario_martes' => ['apertura' => '08:00', 'cierre' => '20:00'],
                        'horario_miercoles' => ['apertura' => '08:00', 'cierre' => '20:00'],
                        'horario_jueves' => ['apertura' => '08:00', 'cierre' => '20:00'],
                        'horario_viernes' => ['apertura' => '08:00', 'cierre' => '22:00'],
                        'horario_sabado' => ['apertura' => '10:00', 'cierre' => '22:00'],
                        'horario_domingo' => ['apertura' => '10:00', 'cierre' => '18:00'],
                    ];
            }

            return [];
        });
    }
}
