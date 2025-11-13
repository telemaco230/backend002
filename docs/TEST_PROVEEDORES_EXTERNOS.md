# Test de Identificadores Externos en API de Artículos

## Objetivo

Verificar que el API de artículos acepta correctamente identificadores externos de proveedores en lugar de IDs internos.

## Prerequisitos

1. Tener proveedores creados en la base de datos con identificadores externos
2. Tener un token JWT válido

## Script de Creación de Proveedores de Prueba

Ejecuta este código PHP para crear proveedores de prueba:

```php
<?php
// Script para crear proveedores de prueba
// Ejecutar desde tinker o un seeder

use App\Models\Proveedor;

// Crear proveedor 1
$proveedor1 = Proveedor::create([
    'identificador_externo' => 'PROV-001',
    'nombre_comercial' => 'Distribuidora ABC',
    'calendario_siempre_abierto' => true,
]);

// Crear proveedor 2
$proveedor2 = Proveedor::create([
    'identificador_externo' => 'PROV-002',
    'nombre_comercial' => 'Mayorista XYZ',
    'calendario_siempre_abierto' => false,
    'horario_lunes_inicio' => '09:00:00',
    'horario_lunes_fin' => '18:00:00',
    'horario_martes_inicio' => '09:00:00',
    'horario_martes_fin' => '18:00:00',
    'horario_miercoles_inicio' => '09:00:00',
    'horario_miercoles_fin' => '18:00:00',
    'horario_jueves_inicio' => '09:00:00',
    'horario_jueves_fin' => '18:00:00',
    'horario_viernes_inicio' => '09:00:00',
    'horario_viernes_fin' => '18:00:00',
]);

// Crear proveedor 3
$proveedor3 = Proveedor::create([
    'identificador_externo' => 'PROV-003',
    'nombre_comercial' => 'Tienda 24/7',
    'calendario_siempre_abierto' => true,
]);

echo "Proveedores creados exitosamente:\n";
echo "- {$proveedor1->identificador_externo} (ID: {$proveedor1->id})\n";
echo "- {$proveedor2->identificador_externo} (ID: {$proveedor2->id})\n";
echo "- {$proveedor3->identificador_externo} (ID: {$proveedor3->id})\n";
```

**Ejecutar desde terminal:**
```bash
php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use App\Models\Proveedor; \$p1 = Proveedor::create(['identificador_externo' => 'PROV-001', 'nombre_comercial' => 'Distribuidora ABC', 'calendario_siempre_abierto' => true]); \$p2 = Proveedor::create(['identificador_externo' => 'PROV-002', 'nombre_comercial' => 'Mayorista XYZ', 'calendario_siempre_abierto' => true]); \$p3 = Proveedor::create(['identificador_externo' => 'PROV-003', 'nombre_comercial' => 'Tienda 24/7', 'calendario_siempre_abierto' => true]); echo 'Proveedores creados: ' . Proveedor::count();"
```

---

## Casos de Prueba

### Test 1: Crear Artículo con Proveedores (Identificadores Externos)

**Request:**
```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "identificador_externo=ART-TEST-001" \
  -F "nombre=Artículo de Prueba 1" \
  -F "descripcion=Prueba de identificadores externos" \
  -F "precio_base=100.00" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00" \
  -F "proveedores[]=PROV-001" \
  -F "proveedores[]=PROV-002"
```

**Resultado Esperado:**
- Status: 201 Created
- El artículo debe crearse correctamente
- La respuesta debe incluir `proveedores` con solo `identificador_externo`, `nombre_comercial` y `calendario_siempre_abierto`
- NO debe incluir `id` ni `pivot` en los proveedores

**Verificación en Base de Datos:**
```sql
-- Ver el artículo creado
SELECT * FROM articulos WHERE identificador_externo = 'ART-TEST-001';

-- Ver relaciones en tabla pivot (debe haber 2 registros)
SELECT ap.*, p.identificador_externo, p.nombre_comercial
FROM articulo_proveedor ap
INNER JOIN proveedores p ON ap.proveedor_id = p.id
INNER JOIN articulos a ON ap.articulo_id = a.id
WHERE a.identificador_externo = 'ART-TEST-001';
```

---

### Test 2: Actualizar Artículo - Cambiar Proveedores

**Request:**
```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "_method=PUT" \
  -F "proveedores[]=PROV-003"
```

**Resultado Esperado:**
- Status: 200 OK
- Los proveedores antiguos (PROV-001, PROV-002) deben eliminarse
- Solo debe quedar asociado PROV-003
- La respuesta debe mostrar solo PROV-003 en el array de proveedores

**Verificación:**
```sql
-- Debe mostrar solo PROV-003
SELECT p.identificador_externo, p.nombre_comercial
FROM articulo_proveedor ap
INNER JOIN proveedores p ON ap.proveedor_id = p.id
WHERE ap.articulo_id = 1;
```

---

### Test 3: Error - Identificador Externo No Existe

**Request:**
```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "identificador_externo=ART-TEST-002" \
  -F "nombre=Artículo de Prueba 2" \
  -F "precio_base=50.00" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00" \
  -F "proveedores[]=PROV-999"
```

**Resultado Esperado:**
- Status: 400 Bad Request
- Mensaje de error de validación indicando que el identificador externo no existe

**Respuesta Esperada:**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "proveedores.0": [
                "The selected proveedores.0 is invalid."
            ]
        }
    }
}
```

---

### Test 4: Consultar Artículo - Verificar Formato de Respuesta

**Request:**
```bash
curl -X GET http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

**Resultado Esperado:**
```json
{
    "success": true,
    "message": "Artículo encontrado",
    "data": {
        "id": 1,
        "identificador_externo": "ART-TEST-001",
        "nombre": "Artículo de Prueba 1",
        "proveedores": [
            {
                "identificador_externo": "PROV-001",
                "nombre_comercial": "Distribuidora ABC",
                "calendario_siempre_abierto": true
            },
            {
                "identificador_externo": "PROV-002",
                "nombre_comercial": "Mayorista XYZ",
                "calendario_siempre_abierto": false
            }
        ]
    }
}
```

**Verificar que NO aparezcan:**
- `id` en los proveedores
- `pivot` en los proveedores
- `created_at`, `updated_at` en los proveedores

---

### Test 5: Listar Artículos - Verificar Formato

**Request:**
```bash
curl -X GET http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

**Resultado Esperado:**
- Todos los artículos deben incluir proveedores en el formato simplificado
- Consistencia en el formato de respuesta para todos los artículos

---

## Verificación de Conversión Interna

Para verificar que la conversión de identificadores externos a IDs internos funciona correctamente:

**Script de Verificación:**
```php
<?php
// Verificar conversión de identificadores externos a IDs

use App\Models\Proveedor;

$identificadoresExternos = ['PROV-001', 'PROV-002', 'PROV-003'];

echo "Identificadores externos solicitados:\n";
print_r($identificadoresExternos);

$proveedorIds = Proveedor::whereIn('identificador_externo', $identificadoresExternos)
    ->pluck('id')
    ->toArray();

echo "\nIDs internos correspondientes:\n";
print_r($proveedorIds);

// Verificar que la cantidad coincida
if (count($identificadoresExternos) === count($proveedorIds)) {
    echo "\n✓ Todos los identificadores externos fueron encontrados\n";
} else {
    echo "\n✗ Error: Algunos identificadores externos no existen\n";
}
```

---

## Checklist de Validación

- [ ] Crear proveedores de prueba con identificadores externos
- [ ] Test 1: Crear artículo con proveedores usando identificadores externos
- [ ] Test 2: Actualizar artículo cambiando proveedores
- [ ] Test 3: Verificar validación con identificador externo inválido
- [ ] Test 4: Verificar formato de respuesta (sin IDs internos ni pivot)
- [ ] Test 5: Verificar listado de artículos
- [ ] Verificar que la tabla pivot contiene los IDs correctos
- [ ] Verificar que las respuestas NO incluyen IDs internos de proveedores

---

## Resumen de Cambios

### Antes (IDs internos)
```bash
-F "proveedores[]=1"
-F "proveedores[]=2"
```

### Después (Identificadores externos)
```bash
-F "proveedores[]=PROV-001"
-F "proveedores[]=PROV-002"
```

### Respuesta Antes
```json
"proveedores": [
    {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "ABC",
        "pivot": { ... }
    }
]
```

### Respuesta Después
```json
"proveedores": [
    {
        "identificador_externo": "PROV-001",
        "nombre_comercial": "ABC",
        "calendario_siempre_abierto": true
    }
]
```

---

## Notas Importantes

1. **Internamente** el sistema sigue usando IDs en la tabla pivot `articulo_proveedor`
2. **Externamente** (API) solo se usan identificadores externos
3. La conversión se realiza automáticamente en el controlador
4. Las validaciones verifican que el identificador externo exista en la tabla proveedores
5. Las respuestas están simplificadas para ocultar detalles de implementación interna
