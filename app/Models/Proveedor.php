<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identificador_externo',
        'nombre_comercial',
        'calendario_siempre_abierto',
        'horario_lunes',
        'horario_martes',
        'horario_miercoles',
        'horario_jueves',
        'horario_viernes',
        'horario_sabado',
        'horario_domingo',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'proveedores';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'calendario_siempre_abierto' => 'boolean',
            'horario_lunes' => 'array',
            'horario_martes' => 'array',
            'horario_miercoles' => 'array',
            'horario_jueves' => 'array',
            'horario_viernes' => 'array',
            'horario_sabado' => 'array',
            'horario_domingo' => 'array',
        ];
    }

    /**
     * Relación muchos a muchos con Artículos.
     * Un proveedor puede disponer de múltiples artículos.
     */
    public function articulos()
    {
        return $this->belongsToMany(Articulo::class, 'articulo_proveedor', 'proveedor_id', 'articulo_id')
                    ->withTimestamps();
    }

    /**
     * Verifica si el proveedor está disponible en un día y hora específicos.
     *
     * @param string $diaSemana (lunes, martes, miercoles, jueves, viernes, sabado, domingo)
     * @param string|null $hora Formato HH:MM (opcional)
     * @return bool
     */
    public function estaDisponible(string $diaSemana, ?string $hora = null): bool
    {
        // Si el calendario está siempre abierto
        if ($this->calendario_siempre_abierto) {
            return true;
        }

        // Obtener el horario del día específico
        $horarioDia = $this->{"horario_" . strtolower($diaSemana)};

        // Si no hay horario definido para el día, no está disponible
        if (empty($horarioDia)) {
            return false;
        }

        // Si no se especifica hora, solo verificar que tenga horario
        if ($hora === null) {
            return true;
        }

        // Verificar si la hora está dentro del rango
        // Formato esperado del horario: ['apertura' => 'HH:MM', 'cierre' => 'HH:MM']
        if (isset($horarioDia['apertura']) && isset($horarioDia['cierre'])) {
            return $hora >= $horarioDia['apertura'] && $hora <= $horarioDia['cierre'];
        }

        return false;
    }
}
