# Casos de Uso - Modelo Proveedor

Este documento contiene ejemplos prácticos de uso del modelo Proveedor y su relación con Artículos.

## Caso 1: Crear Proveedor Básico (Siempre Disponible)

```php
use App\Models\Proveedor;

// Crear un distribuidor que opera 24/7
$proveedor = Proveedor::create([
    'identificador_externo' => 'DIST-24H-001',
    'nombre_comercial' => 'Distribuidora Global 24H',
    'calendario_siempre_abierto' => true,
]);

echo "Proveedor creado: {$proveedor->nombre_comercial}";
```

## Caso 2: Crear Proveedor con Horario de Oficina

```php
use App\Models\Proveedor;

// Proveedor con horario de lunes a viernes, 9:00 a 18:00
$proveedor = Proveedor::create([
    'identificador_externo' => 'PROV-OFFICE-001',
    'nombre_comercial' => 'Suministros de Oficina SA',
    'calendario_siempre_abierto' => false,
    'horario_lunes' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_martes' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_miercoles' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_jueves' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_viernes' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_sabado' => null, // Cerrado
    'horario_domingo' => null, // Cerrado
]);
```

## Caso 3: Proveedor con Horarios Especiales de Fin de Semana

```php
use App\Models\Proveedor;

// Restaurante con horarios extendidos en fin de semana
$proveedor = Proveedor::create([
    'identificador_externo' => 'REST-001',
    'nombre_comercial' => 'Restaurante La Buena Mesa',
    'calendario_siempre_abierto' => false,
    'horario_lunes' => ['apertura' => '12:00', 'cierre' => '16:00'],
    'horario_martes' => ['apertura' => '12:00', 'cierre' => '16:00'],
    'horario_miercoles' => ['apertura' => '12:00', 'cierre' => '16:00'],
    'horario_jueves' => ['apertura' => '12:00', 'cierre' => '23:00'],
    'horario_viernes' => ['apertura' => '12:00', 'cierre' => '00:00'],
    'horario_sabado' => ['apertura' => '12:00', 'cierre' => '00:00'],
    'horario_domingo' => ['apertura' => '12:00', 'cierre' => '18:00'],
]);
```

## Caso 4: Asociar Artículos a un Proveedor

```php
use App\Models\Proveedor;
use App\Models\Articulo;

$proveedor = Proveedor::find(1);

// Obtener artículos que queremos asociar
$articulo1 = Articulo::where('identificador_externo', 'ART-001')->first();
$articulo2 = Articulo::where('identificador_externo', 'ART-002')->first();

// Asociar artículos individualmente
$proveedor->articulos()->attach($articulo1->id);
$proveedor->articulos()->attach($articulo2->id);

// O asociar múltiples a la vez
$proveedor->articulos()->attach([
    $articulo1->id,
    $articulo2->id,
]);

echo "Artículos asociados: " . $proveedor->articulos()->count();
```

## Caso 5: Listar Proveedores de un Artículo

```php
use App\Models\Articulo;

$articulo = Articulo::where('identificador_externo', 'ART-001')->first();
$proveedores = $articulo->proveedores;

echo "El artículo '{$articulo->nombre}' está disponible en:\n";
foreach ($proveedores as $proveedor) {
    echo "- {$proveedor->nombre_comercial}\n";
    
    if ($proveedor->calendario_siempre_abierto) {
        echo "  (Disponible 24/7)\n";
    } else {
        echo "  (Con horario específico)\n";
    }
}
```

## Caso 6: Verificar Disponibilidad de un Proveedor

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Verificar disponibilidad general por día
$dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

foreach ($dias as $dia) {
    $disponible = $proveedor->estaDisponible($dia);
    $status = $disponible ? '✓ Abierto' : '✗ Cerrado';
    echo ucfirst($dia) . ": $status\n";
}

// Verificar disponibilidad a una hora específica
$horaActual = date('H:i');
$diaActual = strtolower(date('l')); // Asumiendo sistema en inglés

// Mapeo de días en inglés a español
$mapaDias = [
    'monday' => 'lunes',
    'tuesday' => 'martes',
    'wednesday' => 'miercoles',
    'thursday' => 'jueves',
    'friday' => 'viernes',
    'saturday' => 'sabado',
    'sunday' => 'domingo',
];

$diaEspanol = $mapaDias[$diaActual];

if ($proveedor->estaDisponible($diaEspanol, $horaActual)) {
    echo "El proveedor está disponible ahora mismo!\n";
} else {
    echo "El proveedor está cerrado en este momento.\n";
}
```

## Caso 7: Sincronizar Artículos de un Proveedor

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Reemplazar completamente los artículos asociados
// (elimina las relaciones anteriores y crea las nuevas)
$nuevosArticulos = [1, 3, 5, 7, 9];
$proveedor->articulos()->sync($nuevosArticulos);

echo "El proveedor ahora dispone de " . count($nuevosArticulos) . " artículos.";
```

## Caso 8: Encontrar Artículos Comunes entre Proveedores

```php
use App\Models\Proveedor;

$proveedor1 = Proveedor::find(1);
$proveedor2 = Proveedor::find(2);

// Obtener IDs de artículos de cada proveedor
$articulos1 = $proveedor1->articulos->pluck('id')->toArray();
$articulos2 = $proveedor2->articulos->pluck('id')->toArray();

// Encontrar artículos comunes
$articulosComunes = array_intersect($articulos1, $articulos2);

echo "Artículos disponibles en ambos proveedores:\n";
foreach ($articulosComunes as $articuloId) {
    $articulo = \App\Models\Articulo::find($articuloId);
    echo "- {$articulo->nombre}\n";
}
```

## Caso 9: Buscar Proveedores Disponibles un Día Específico

```php
use App\Models\Proveedor;

// Obtener todos los proveedores
$proveedores = Proveedor::all();

// Filtrar los que están disponibles el sábado
$disponiblesSabado = $proveedores->filter(function ($proveedor) {
    return $proveedor->estaDisponible('sabado');
});

echo "Proveedores disponibles los sábados:\n";
foreach ($disponiblesSabado as $proveedor) {
    echo "- {$proveedor->nombre_comercial}\n";
}
```

## Caso 10: Actualizar Horario de un Proveedor

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Cambiar de siempre abierto a horario específico
$proveedor->update([
    'calendario_siempre_abierto' => false,
    'horario_lunes' => ['apertura' => '08:00', 'cierre' => '20:00'],
    'horario_martes' => ['apertura' => '08:00', 'cierre' => '20:00'],
    'horario_miercoles' => ['apertura' => '08:00', 'cierre' => '20:00'],
    'horario_jueves' => ['apertura' => '08:00', 'cierre' => '20:00'],
    'horario_viernes' => ['apertura' => '08:00', 'cierre' => '22:00'],
    'horario_sabado' => ['apertura' => '10:00', 'cierre' => '22:00'],
    'horario_domingo' => ['apertura' => '10:00', 'cierre' => '18:00'],
]);

echo "Horario actualizado para {$proveedor->nombre_comercial}";
```

## Caso 11: Obtener Proveedores con Más Artículos

```php
use App\Models\Proveedor;

// Obtener proveedores ordenados por cantidad de artículos
$proveedores = Proveedor::withCount('articulos')
    ->orderBy('articulos_count', 'desc')
    ->limit(10)
    ->get();

echo "Top 10 Proveedores por cantidad de artículos:\n";
foreach ($proveedores as $index => $proveedor) {
    echo ($index + 1) . ". {$proveedor->nombre_comercial} - {$proveedor->articulos_count} artículos\n";
}
```

## Caso 12: Buscar Artículos por Proveedor con Filtros

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Obtener artículos del proveedor con precio menor a 100
$articulosBaratos = $proveedor->articulos()
    ->where('precio_base', '<', 100)
    ->get();

// Obtener artículos del proveedor con IVA general
$articulosIvaGeneral = $proveedor->articulos()
    ->where('tipo_iva', 'general')
    ->get();

echo "Artículos con precio < 100: " . $articulosBaratos->count() . "\n";
echo "Artículos con IVA general: " . $articulosIvaGeneral->count() . "\n";
```

## Caso 13: Verificar si Proveedor Dispone de un Artículo

```php
use App\Models\Proveedor;
use App\Models\Articulo;

$proveedor = Proveedor::find(1);
$articulo = Articulo::find(5);

if ($proveedor->articulos()->where('articulo_id', $articulo->id)->exists()) {
    echo "✓ El proveedor '{$proveedor->nombre_comercial}' dispone del artículo '{$articulo->nombre}'";
} else {
    echo "✗ El proveedor NO dispone de este artículo";
}
```

## Caso 14: Crear Proveedor con Factory y Asociar Artículos

```php
use App\Models\Proveedor;
use App\Models\Articulo;

// Crear proveedor con factory
$proveedor = Proveedor::factory()->conHorario()->create();

// Crear o usar artículos existentes
$articulos = Articulo::factory()->count(5)->create();

// Asociar todos los artículos al proveedor
$proveedor->articulos()->attach($articulos->pluck('id'));

echo "Proveedor '{$proveedor->nombre_comercial}' creado con {$articulos->count()} artículos";
```

## Caso 15: Desvincular Artículos Específicos

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Desvincular artículos específicos
$articulosAEliminar = [1, 3, 5];
$proveedor->articulos()->detach($articulosAEliminar);

echo "Artículos desvinculados del proveedor";
```

## Caso 16: Consulta Compleja - Proveedores con Artículos de Cierto Precio

```php
use App\Models\Proveedor;

// Proveedores que tienen al menos un artículo con precio > 500
$proveedores = Proveedor::whereHas('articulos', function ($query) {
    $query->where('precio_base', '>', 500);
})
->with(['articulos' => function ($query) {
    $query->where('precio_base', '>', 500);
}])
->get();

foreach ($proveedores as $proveedor) {
    echo "Proveedor: {$proveedor->nombre_comercial}\n";
    echo "Artículos premium:\n";
    foreach ($proveedor->articulos as $articulo) {
        echo "  - {$articulo->nombre}: €{$articulo->precio_base}\n";
    }
}
```

## Caso 17: Verificar Disponibilidad en Rango de Horas

```php
use App\Models\Proveedor;

$proveedor = Proveedor::find(1);

// Verificar si está abierto durante el almuerzo (12:00 - 14:00)
$disponibleAlmuerzo = $proveedor->estaDisponible('lunes', '12:00') 
                   && $proveedor->estaDisponible('lunes', '14:00');

if ($disponibleAlmuerzo) {
    echo "El proveedor está disponible durante el horario de almuerzo";
}
```

## Caso 18: Exportar Información de Proveedor y sus Artículos

```php
use App\Models\Proveedor;

$proveedor = Proveedor::with('articulos')->find(1);

$datos = [
    'proveedor' => [
        'identificador' => $proveedor->identificador_externo,
        'nombre' => $proveedor->nombre_comercial,
        'siempre_abierto' => $proveedor->calendario_siempre_abierto,
    ],
    'articulos' => $proveedor->articulos->map(function ($articulo) {
        return [
            'identificador' => $articulo->identificador_externo,
            'nombre' => $articulo->nombre,
            'precio' => $articulo->precio_base,
        ];
    }),
    'total_articulos' => $proveedor->articulos->count(),
];

// Convertir a JSON
$json = json_encode($datos, JSON_PRETTY_PRINT);
echo $json;
```

## Notas sobre Rendimiento

Para consultas con muchos proveedores y artículos, usa **Eager Loading**:

```php
// ✓ Buena práctica
$proveedores = Proveedor::with('articulos')->get();

// ✗ Evitar (problema N+1)
$proveedores = Proveedor::all();
foreach ($proveedores as $proveedor) {
    $articulos = $proveedor->articulos; // Consulta adicional por cada proveedor
}
```
