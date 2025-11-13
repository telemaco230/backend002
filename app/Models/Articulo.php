<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identificador_externo',
        'nombre',
        'descripcion',
        'precio_base',
        'tipo_iva',
        'porcentaje_iva',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_base' => 'decimal:2',
            'porcentaje_iva' => 'decimal:2',
        ];
    }

    /**
     * Relación con las imágenes del artículo.
     */
    public function imagenes()
    {
        return $this->hasMany(Imagen::class);
    }

    /**
     * Relación con los documentos del artículo.
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Relación muchos a muchos con Proveedores.
     * Un artículo puede ser dispuesto por múltiples proveedores.
     */
    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'articulo_proveedor', 'articulo_id', 'proveedor_id')
                    ->withTimestamps();
    }
}
