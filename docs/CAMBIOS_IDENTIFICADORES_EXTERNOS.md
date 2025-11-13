# Cambios: Uso de Identificadores Externos en API de Artículos

**Fecha:** 13 de noviembre de 2025

## Resumen

Se ha modificado el API de artículos para que los proveedores se especifiquen mediante su **identificador externo** (`identificador_externo`) en lugar de sus IDs internos. Esto hace que el API sea más amigable y menos dependiente de la implementación interna de la base de datos.

---

## Archivos Modificados

### 1. **ArticuloController.php**
**Ubicación:** `app/Http/Controllers/ArticuloController.php`

#### Cambios en Validación

**Antes:**
```php
'proveedores.*' => 'nullable|integer|exists:proveedores,id',
```

**Después:**
```php
'proveedores.*' => 'nullable|string|exists:proveedores,identificador_externo',
```

#### Cambios en store() - Crear Artículo

**Antes:**
```php
if ($request->has('proveedores') && is_array($request->proveedores)) {
    $articulo->proveedores()->sync($request->proveedores);
}
```

**Después:**
```php
if ($request->has('proveedores') && is_array($request->proveedores)) {
    // Convertir identificadores externos a IDs internos
    $proveedorIds = Proveedor::whereIn('identificador_externo', $request->proveedores)
        ->pluck('id')
        ->toArray();
    
    $articulo->proveedores()->sync($proveedorIds);
    Log::debug($prefix . "Proveedores asociados: " . count($proveedorIds));
}
```

#### Cambios en update() - Actualizar Artículo

**Antes:**
```php
if ($request->has('proveedores')) {
    if (is_array($request->proveedores)) {
        $articulo->proveedores()->sync($request->proveedores);
    } else {
        $articulo->proveedores()->sync([]);
    }
}
```

**Después:**
```php
if ($request->has('proveedores')) {
    if (is_array($request->proveedores) && count($request->proveedores) > 0) {
        // Convertir identificadores externos a IDs internos
        $proveedorIds = Proveedor::whereIn('identificador_externo', $request->proveedores)
            ->pluck('id')
            ->toArray();
        
        $articulo->proveedores()->sync($proveedorIds);
        Log::debug($prefix . "Proveedores sincronizados: " . count($proveedorIds));
    } else {
        $articulo->proveedores()->sync([]);
        Log::debug($prefix . "Todos los proveedores desvinculados");
    }
}
```

#### Cambios en Respuestas (store, show, update, index)

**Antes:**
```php
return $this->success('Artículo encontrado', $articulo->toArray());
```

**Después:**
```php
// Transformar proveedores para mostrar solo identificadores externos
$articuloArray = $articulo->toArray();
$articuloArray['proveedores'] = $articulo->proveedores->map(function ($proveedor) {
    return [
        'identificador_externo' => $proveedor->identificador_externo,
        'nombre_comercial' => $proveedor->nombre_comercial,
        'calendario_siempre_abierto' => $proveedor->calendario_siempre_abierto,
    ];
})->toArray();

return $this->success('Artículo encontrado', $articuloArray);
```

#### Cambios en Anotaciones Swagger

**Antes:**
```php
@OA\Property(property="proveedores[]", type="array", @OA\Items(type="integer"), example={1, 2, 3})
```

**Después:**
```php
@OA\Property(property="proveedores[]", type="array", @OA\Items(type="string"), example={"PROV-001", "PROV-002", "PROV-003"})
```

---

### 2. **API_ARTICULOS.md**
**Ubicación:** `docs/API_ARTICULOS.md`

#### Cambios en Documentación de Parámetros

**Antes:**
- Tipo: `Array de enteros`
- Ejemplo: `[1, 2, 3]`

**Después:**
- Tipo: `Array de strings`
- Ejemplo: `["PROV-001", "PROV-002"]`

#### Cambios en Ejemplos cURL

**Antes:**
```bash
-F "proveedores[]=1" \
-F "proveedores[]=2"
```

**Después:**
```bash
-F "proveedores[]=PROV-001" \
-F "proveedores[]=PROV-002"
```

#### Cambios en Ejemplos de Respuesta

**Antes:**
```json
"proveedores": [
    {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "calendario_siempre_abierto": true,
        "created_at": "...",
        "updated_at": "...",
        "pivot": { ... }
    }
]
```

**Después:**
```json
"proveedores": [
    {
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "calendario_siempre_abierto": true
    }
]
```

#### Nueva Sección Agregada

```markdown
## Gestión de Proveedores

### Uso de Identificadores Externos

Los proveedores se especifican mediante su **identificador externo** (`identificador_externo`), no por su ID interno.

**IMPORTANTE:** El campo `proveedores[]` utiliza el método `sync()` de Eloquent:

- **Si se envía:** Reemplaza completamente los proveedores asociados con los identificadores externos proporcionados
- **Si NO se envía:** Mantiene los proveedores existentes
- **Si se envía vacío:** Elimina todas las asociaciones

Los identificadores deben ser los códigos externos (`identificador_externo`) de los proveedores, no sus IDs internos.
```

---

### 3. **ARTICULOS_PROVEEDORES.md**
**Ubicación:** `docs/ARTICULOS_PROVEEDORES.md`

Se actualizaron todos los ejemplos de uso:

- Tipo de dato cambiado de `Array de enteros` a `Array de strings`
- Ejemplos cURL actualizados con identificadores externos
- Tabla de comportamiento actualizada con ejemplos usando PROV-XXX
- Validaciones actualizadas
- Mensajes de error actualizados
- Casos de uso actualizados

**Ejemplo de cambio:**
```bash
# Antes
-F "proveedores[]=1"
-F "proveedores[]=2"

# Después
-F "proveedores[]=PROV-001"
-F "proveedores[]=PROV-002"
```

---

### 4. **Colección Postman**
**Ubicación:** `docs/postman/PRJ-002 eCommerce.postman_collection.json`

#### Endpoint "Crear artículo"

**Antes:**
```json
{
    "key": "proveedores[]",
    "value": "1",
    "type": "text",
    "description": "ID del proveedor 1"
}
```

**Después:**
```json
{
    "key": "proveedores[]",
    "value": "PROV-001",
    "type": "text",
    "description": "Identificador externo del proveedor 1"
}
```

#### Endpoint "Actualizar artículo"

**Antes:**
```json
{
    "key": "proveedores[]",
    "value": "1",
    "type": "text",
    "description": "ID del proveedor - Reemplaza todos los anteriores"
}
```

**Después:**
```json
{
    "key": "proveedores[]",
    "value": "PROV-001",
    "type": "text",
    "description": "Identificador externo del proveedor - Reemplaza todos los anteriores"
}
```

---

### 5. **TEST_PROVEEDORES_EXTERNOS.md** (NUEVO)
**Ubicación:** `docs/TEST_PROVEEDORES_EXTERNOS.md`

Nuevo documento con guía completa de testing que incluye:
- Script para crear proveedores de prueba
- 5 casos de prueba detallados
- Verificaciones en base de datos
- Checklist de validación
- Comparación antes/después

---

## Impacto de los Cambios

### ✅ Ventajas

1. **API más intuitiva:** Los usuarios no necesitan conocer los IDs internos de los proveedores
2. **Independencia de implementación:** Los IDs internos pueden cambiar sin afectar la API
3. **Mejor legibilidad:** Los identificadores externos son más descriptivos (PROV-001 vs 1)
4. **Respuestas simplificadas:** Menos información innecesaria en las respuestas
5. **Seguridad:** No se exponen IDs internos de la base de datos

### ⚠️ Consideraciones

1. **Cambio Breaking:** Cualquier cliente que esté usando IDs internos debe actualizarse
2. **Performance:** Se realiza una consulta adicional para convertir identificadores externos a IDs
3. **Validación:** Ahora se valida contra `identificador_externo` en lugar de `id`

---

## Migración para Clientes Existentes

### Si actualmente envías:
```bash
-F "proveedores[]=1"
-F "proveedores[]=2"
```

### Debes cambiar a:
```bash
-F "proveedores[]=PROV-001"
-F "proveedores[]=PROV-002"
```

### Script de Ayuda para Obtener Identificadores Externos

```sql
-- Ver correspondencia entre IDs internos e identificadores externos
SELECT id, identificador_externo, nombre_comercial
FROM proveedores
ORDER BY id;
```

---

## Testing

### Pasos para Probar los Cambios

1. **Crear proveedores de prueba:**
   ```bash
   # Ver TEST_PROVEEDORES_EXTERNOS.md para script completo
   ```

2. **Probar creación de artículo:**
   ```bash
   curl -X POST http://localhost:8000/api/articulos \
     -H "Authorization: Bearer {token}" \
     -F "identificador_externo=ART-TEST-001" \
     -F "nombre=Test" \
     -F "precio_base=100" \
     -F "tipo_iva=general" \
     -F "porcentaje_iva=21" \
     -F "proveedores[]=PROV-001"
   ```

3. **Verificar respuesta:**
   - Debe incluir proveedores sin IDs internos ni pivot
   - Solo `identificador_externo`, `nombre_comercial`, `calendario_siempre_abierto`

4. **Verificar base de datos:**
   ```sql
   SELECT * FROM articulo_proveedor WHERE articulo_id = 1;
   ```

---

## Retrocompatibilidad

**No hay retrocompatibilidad.** Este es un cambio breaking que requiere que todos los clientes se actualicen para usar identificadores externos.

### Alternativa para Mantener Retrocompatibilidad

Si se necesita retrocompatibilidad, se podría:

1. Aceptar ambos formatos (IDs y identificadores externos)
2. Detectar automáticamente el tipo
3. Procesar según corresponda

**No implementado actualmente** - se decidió por simplicidad usar solo identificadores externos.

---

## Documentación Actualizada

- ✅ ArticuloController.php - Código fuente
- ✅ API_ARTICULOS.md - Documentación principal del API
- ✅ ARTICULOS_PROVEEDORES.md - Guía de uso de proveedores
- ✅ Colección Postman - Ejemplos actualizados
- ✅ Swagger - Anotaciones actualizadas
- ✅ TEST_PROVEEDORES_EXTERNOS.md - Guía de testing (nuevo)

---

## Verificación Final

```bash
# Regenerar Swagger
php artisan l5-swagger:generate

# Verificar que no hay errores de compilación
# (Ya verificado - sin errores)
```

**Estado:** ✅ Completado exitosamente
