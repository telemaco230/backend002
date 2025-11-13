<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'articulo_id',
        'nombre_archivo',
        'ruta',
        'tipo_mime',
        'tamanio',
        'descripcion',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tamanio' => 'integer',
        ];
    }

    /**
     * Relación con el artículo al que pertenece el documento.
     */
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
}
