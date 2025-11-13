# Modelo Proveedor - Documentación

## Descripción General

El modelo `Proveedor` representa a los proveedores que pueden disponer de artículos en el sistema eCommerce. Cada proveedor tiene un calendario de disponibilidad configurable que puede estar siempre abierto o con horarios específicos por día de la semana.

## Estructura de la Base de Datos

### Tabla: `proveedores`

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | bigint unsigned | PK, auto-increment | Identificador único |
| identificador_externo | string | unique, not null | Identificador externo del proveedor |
| nombre_comercial | string | not null | Nombre comercial del proveedor |
| calendario_siempre_abierto | boolean | default: true | Indica si está disponible 24/7 |
| horario_lunes | json | nullable | Horario de lunes |
| horario_martes | json | nullable | Horario de martes |
| horario_miercoles | json | nullable | Horario de miércoles |
| horario_jueves | json | nullable | Horario de jueves |
| horario_viernes | json | nullable | Horario de viernes |
| horario_sabado | json | nullable | Horario de sábado |
| horario_domingo | json | nullable | Horario de domingo |
| created_at | timestamp | | Fecha de creación |
| updated_at | timestamp | | Fecha de última actualización |

### Tabla Pivot: `articulo_proveedor`

Relación muchos a muchos entre artículos y proveedores.

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | bigint unsigned | PK, auto-increment | Identificador único |
| articulo_id | bigint unsigned | FK → articulos.id, cascade | ID del artículo |
| proveedor_id | bigint unsigned | FK → proveedores.id, cascade | ID del proveedor |
| created_at | timestamp | | Fecha de creación de la relación |
| updated_at | timestamp | | Fecha de última actualización |
| | | UNIQUE(articulo_id, proveedor_id) | Evita duplicados |

## Modelo Eloquent

### Atributos Mass Assignable

```php
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
```

### Casts

```php
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
```

## Relaciones

### Con Artículos (Many to Many)

```php
public function articulos()
{
    return $this->belongsToMany(Articulo::class, 'articulo_proveedor', 'proveedor_id', 'articulo_id')
                ->withTimestamps();
}
```

Un proveedor puede disponer de múltiples artículos, y un artículo puede ser dispuesto por múltiples proveedores.

## Formato de Horarios

Los horarios se almacenan en formato JSON con la siguiente estructura:

```json
{
    "apertura": "HH:MM",
    "cierre": "HH:MM"
}
```

### Ejemplos:

**Horario de 9:00 a 18:00:**
```json
{
    "apertura": "09:00",
    "cierre": "18:00"
}
```

**Sin horario (cerrado):**
```json
null
```

## Métodos Personalizados

### `estaDisponible(string $diaSemana, ?string $hora = null): bool`

Verifica si el proveedor está disponible en un día y hora específicos.

**Parámetros:**
- `$diaSemana`: Nombre del día en minúsculas (lunes, martes, miercoles, jueves, viernes, sabado, domingo)
- `$hora`: Hora en formato HH:MM (opcional)

**Retorna:** `true` si está disponible, `false` en caso contrario

**Ejemplos de uso:**

```php
$proveedor = Proveedor::find(1);

// Verificar si está disponible el lunes (sin importar la hora)
if ($proveedor->estaDisponible('lunes')) {
    echo "Disponible el lunes";
}

// Verificar si está disponible el lunes a las 14:30
if ($proveedor->estaDisponible('lunes', '14:30')) {
    echo "Disponible el lunes a las 14:30";
}
```

## Uso del Modelo

### Crear un Proveedor Siempre Abierto

```php
$proveedor = Proveedor::create([
    'identificador_externo' => 'PROV-001',
    'nombre_comercial' => 'Distribuidora ABC',
    'calendario_siempre_abierto' => true,
]);
```

### Crear un Proveedor con Horario Específico

```php
$proveedor = Proveedor::create([
    'identificador_externo' => 'PROV-002',
    'nombre_comercial' => 'Tienda XYZ',
    'calendario_siempre_abierto' => false,
    'horario_lunes' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_martes' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_miercoles' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_jueves' => ['apertura' => '09:00', 'cierre' => '18:00'],
    'horario_viernes' => ['apertura' => '09:00', 'cierre' => '20:00'],
    'horario_sabado' => ['apertura' => '10:00', 'cierre' => '14:00'],
    'horario_domingo' => null, // Cerrado
]);
```

### Asociar Artículos a un Proveedor

```php
$proveedor = Proveedor::find(1);
$articulo = Articulo::find(1);

// Asociar un artículo
$proveedor->articulos()->attach($articulo->id);

// Asociar múltiples artículos
$proveedor->articulos()->attach([1, 2, 3]);

// Sincronizar (reemplaza las relaciones existentes)
$proveedor->articulos()->sync([1, 2, 3]);
```

### Obtener Artículos de un Proveedor

```php
$proveedor = Proveedor::with('articulos')->find(1);

foreach ($proveedor->articulos as $articulo) {
    echo $articulo->nombre;
}
```

### Obtener Proveedores de un Artículo

```php
$articulo = Articulo::with('proveedores')->find(1);

foreach ($articulo->proveedores as $proveedor) {
    echo $proveedor->nombre_comercial;
}
```

### Verificar si un Proveedor Dispone de un Artículo

```php
$proveedor = Proveedor::find(1);
$articuloId = 5;

if ($proveedor->articulos()->where('articulo_id', $articuloId)->exists()) {
    echo "El proveedor dispone de este artículo";
}
```

### Desvincular Artículos

```php
$proveedor = Proveedor::find(1);

// Desvincular un artículo específico
$proveedor->articulos()->detach(1);

// Desvincular múltiples artículos
$proveedor->articulos()->detach([1, 2, 3]);

// Desvincular todos los artículos
$proveedor->articulos()->detach();
```

## Factory

### Crear Proveedor con Factory (Siempre Abierto)

```php
use App\Models\Proveedor;

// Crear un proveedor siempre abierto
$proveedor = Proveedor::factory()->create();
```

### Crear Proveedor con Horario

```php
// Crear un proveedor con horario específico
$proveedor = Proveedor::factory()->conHorario()->create();
```

### Crear Múltiples Proveedores

```php
// 10 proveedores siempre abiertos
Proveedor::factory()->count(10)->create();

// 5 proveedores con horario
Proveedor::factory()->conHorario()->count(5)->create();
```

## Ejemplos de Consultas

### Obtener Proveedores que Disponen de un Artículo Específico

```php
$articulo = Articulo::find(1);
$proveedores = $articulo->proveedores;
```

### Obtener Proveedores Siempre Abiertos

```php
$proveedoresSiempreAbiertos = Proveedor::where('calendario_siempre_abierto', true)->get();
```

### Obtener Proveedores con Horario Específico

```php
$proveedoresConHorario = Proveedor::where('calendario_siempre_abierto', false)->get();
```

### Contar Artículos de un Proveedor

```php
$proveedor = Proveedor::withCount('articulos')->find(1);
echo "Total artículos: " . $proveedor->articulos_count;
```

### Buscar Proveedores que Dispongan de Múltiples Artículos

```php
$proveedores = Proveedor::whereHas('articulos', function ($query) {
    $query->whereIn('articulo_id', [1, 2, 3]);
})->get();
```

## Validaciones Recomendadas

Al crear o actualizar proveedores, se recomienda validar:

```php
$validated = $request->validate([
    'identificador_externo' => 'required|string|unique:proveedores,identificador_externo',
    'nombre_comercial' => 'required|string|max:255',
    'calendario_siempre_abierto' => 'required|boolean',
    'horario_lunes' => 'nullable|array',
    'horario_lunes.apertura' => 'required_with:horario_lunes|date_format:H:i',
    'horario_lunes.cierre' => 'required_with:horario_lunes|date_format:H:i|after:horario_lunes.apertura',
    // Repetir para cada día de la semana...
]);
```

## Notas Importantes

1. **Calendario Siempre Abierto**: Si `calendario_siempre_abierto` es `true`, los horarios específicos son ignorados.

2. **Horarios NULL**: Un horario `null` indica que el proveedor está cerrado ese día.

3. **Formato de Hora**: Los horarios deben seguir el formato 24 horas "HH:MM" (ej: "09:00", "18:30").

4. **Relación Bidireccional**: La relación entre Artículo y Proveedor es bidireccional, puedes acceder desde cualquiera de los dos modelos.

5. **Eliminación en Cascada**: Al eliminar un proveedor o artículo, las relaciones en la tabla pivot se eliminan automáticamente.

6. **Timestamps en Pivot**: La tabla pivot incluye `created_at` y `updated_at` gracias a `withTimestamps()`.
