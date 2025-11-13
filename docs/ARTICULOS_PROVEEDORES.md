# Gestión de Proveedores en Artículos - Guía de Uso

## Descripción

Los artículos ahora pueden tener asociados múltiples proveedores. Esta relación muchos-a-muchos permite definir qué proveedores disponen de cada artículo.

## Cambios en el API de Artículos

### Campo Adicional: `proveedores[]`

Se ha agregado el campo `proveedores[]` a los endpoints de creación y actualización de artículos.

- **Tipo:** Array de strings
- **Descripción:** Identificadores externos de los proveedores que disponen del artículo
- **Validación:** Cada identificador externo debe existir en la tabla `proveedores`
- **Comportamiento:** Sincronización (reemplazo completo)

---

## Ejemplos de Uso

### 1. Crear Artículo SIN Proveedores

```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}" \
  -F "identificador_externo=ART-100" \
  -F "nombre=Producto Sin Proveedores" \
  -F "descripcion=Este producto no tiene proveedores asociados" \
  -F "precio_base=50.00" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00"
```

**Resultado:** Artículo creado sin proveedores asociados.

---

### 2. Crear Artículo CON Proveedores

```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}" \
  -F "identificador_externo=ART-101" \
  -F "nombre=Producto con Proveedores" \
  -F "descripcion=Este producto está disponible en varios proveedores" \
  -F "precio_base=75.00" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00" \
  -F "proveedores[]=PROV-001" \
  -F "proveedores[]=PROV-002" \
  -F "proveedores[]=PROV-003"
```

**Resultado:** Artículo creado y asociado a los proveedores con identificadores externos PROV-001, PROV-002 y PROV-003.

---

### 3. Consultar Artículo con Proveedores

```bash
curl -X GET http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}"
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Artículo encontrado",
    "data": {
        "id": 1,
        "identificador_externo": "ART-101",
        "nombre": "Producto con Proveedores",
        "precio_base": "75.00",
        "tipo_iva": "general",
        "porcentaje_iva": "21.00",
        "proveedores": [
            {
                "identificador_externo": "PROV-001",
                "nombre_comercial": "Distribuidora ABC",
                "calendario_siempre_abierto": true
            },
            {
                "identificador_externo": "PROV-002",
                "nombre_comercial": "Tienda XYZ",
                "calendario_siempre_abierto": false
            }
        ]
    }
}
```

---

### 4. Actualizar Artículo - Agregar Proveedores

**Situación:** El artículo tiene los proveedores PROV-001, PROV-002, PROV-003. Queremos cambiarlo a PROV-004, PROV-005.

```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "proveedores[]=PROV-004" \
  -F "proveedores[]=PROV-005"
```

**Resultado:** 
- Se eliminan las asociaciones con proveedores PROV-001, PROV-002, PROV-003
- Se crean asociaciones con proveedores PROV-004, PROV-005

---

### 5. Actualizar Artículo - Eliminar Todos los Proveedores

```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "proveedores[]="
```

**O usando un array vacío en JSON:**
```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: multipart/form-data" \
  -F "_method=PUT" \
  -F "proveedores="
```

**Resultado:** Se eliminan todas las asociaciones con proveedores.

---

### 6. Actualizar Artículo - SIN Modificar Proveedores

**Situación:** Quiero actualizar el precio pero mantener los proveedores actuales.

```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "precio_base=99.99"
```

**IMPORTANTE:** No incluir el campo `proveedores` en la petición.

**Resultado:** Solo se actualiza el precio, los proveedores se mantienen igual.

---

### 7. Listar Todos los Artículos con Proveedores

```bash
curl -X GET http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}"
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Lista de artículos",
    "data": {
        "articulos": [
            {
                "id": 1,
                "nombre": "Artículo 1",
                "proveedores": [
                    {
                        "identificador_externo": "PROV-001",
                        "nombre_comercial": "Proveedor A",
                        "calendario_siempre_abierto": true
                    }
                ]
            },
            {
                "id": 2,
                "nombre": "Artículo 2",
                "proveedores": []
            }
        ]
    }
}
```

---

## Comportamiento del Campo `proveedores`

### ✅ Sincronización (sync)

El campo `proveedores[]` utiliza el método `sync()` de Eloquent, lo que significa:

1. **Si se envía:** Reemplaza completamente la lista de proveedores
2. **Si NO se envía:** No modifica las relaciones existentes
3. **Si se envía vacío:** Elimina todas las asociaciones

### Ejemplos de Comportamiento

| Proveedores Actuales | Campo Enviado | Resultado Final |
|---------------------|---------------|-----------------|
| [PROV-001, PROV-002, PROV-003] | `proveedores[]=PROV-004&proveedores[]=PROV-005` | [PROV-004, PROV-005] |
| [PROV-001, PROV-002, PROV-003] | `proveedores[]=PROV-001&proveedores[]=PROV-002&proveedores[]=PROV-003&proveedores[]=PROV-004` | [PROV-001, PROV-002, PROV-003, PROV-004] |
| [PROV-001, PROV-002, PROV-003] | `proveedores[]=` (vacío) | [] |
| [PROV-001, PROV-002, PROV-003] | Campo no enviado | [PROV-001, PROV-002, PROV-003] |
| [] | `proveedores[]=PROV-001` | [PROV-001] |

---

## Validaciones

### Crear Artículo

```php
'proveedores' => 'nullable|array',
'proveedores.*' => 'nullable|string|exists:proveedores,identificador_externo',
```

- Los proveedores son **opcionales**
- Si se envían, deben ser un **array de strings**
- Cada identificador externo debe **existir** en la tabla `proveedores`

### Actualizar Artículo

Mismas validaciones que en crear.

---

## Errores Comunes

### 1. Proveedor No Existe

**Request:**
```bash
-F "proveedores[]=PROV-999"
```

**Respuesta (400):**
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

### 2. Tipo de Dato Incorrecto

**Request:**
```bash
-F "proveedores[]=123"
```

**Nota:** Aunque 123 es un número, se acepta como string. El error solo ocurre si el identificador no existe en la tabla.

---

## Casos de Uso Prácticos

### Caso 1: Producto Exclusivo de un Proveedor

```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}" \
  -F "identificador_externo=ART-EXCLUSIVE" \
  -F "nombre=Producto Exclusivo" \
  -F "precio_base=199.99" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00" \
  -F "proveedores[]=PROV-005"
```

---

### Caso 2: Producto Disponible en Múltiples Proveedores

```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}" \
  -F "identificador_externo=ART-MULTI" \
  -F "nombre=Producto Multi-Proveedor" \
  -F "precio_base=49.99" \
  -F "tipo_iva=reducido" \
  -F "porcentaje_iva=10.00" \
  -F "proveedores[]=PROV-001" \
  -F "proveedores[]=PROV-002" \
  -F "proveedores[]=PROV-003" \
  -F "proveedores[]=PROV-004" \
  -F "proveedores[]=PROV-005"
```

---

### Caso 3: Cambiar Proveedor de un Artículo

**Situación:** El artículo actualmente está con el proveedor 3, pero queremos cambiarlo al proveedor 7.

```bash
curl -X POST http://localhost:8000/api/articulos/10 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "proveedores[]=7"
```

---

### 6. Actualizar Artículo - Agregar Proveedor Adicional (Mantener Existentes + Agregar)

**Situación:** El artículo tiene proveedores [PROV-001, PROV-002], queremos agregar el PROV-003.

**IMPORTANTE:** Debes enviar TODOS los identificadores externos (los existentes + los nuevos).

```bash
curl -X POST http://localhost:8000/api/articulos/10 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "proveedores[]=PROV-001" \
  -F "proveedores[]=PROV-002" \
  -F "proveedores[]=PROV-003"
```

---

## Notas Importantes

1. **Sincronización Completa:** El campo `proveedores` siempre reemplaza completamente la lista. No hay "agregar" o "quitar" individual.

2. **Opcional en Creación:** No es obligatorio especificar proveedores al crear un artículo.

3. **Opcional en Actualización:** Si no se incluye el campo `proveedores` en la actualización, las relaciones actuales no se modifican.

4. **Array Vacío vs No Enviado:**
   - Array vacío `[]`: Elimina todas las relaciones
   - No enviado: Mantiene las relaciones actuales

5. **IDs Duplicados:** Si envías el mismo identificador externo múltiples veces, se guardará solo una vez (no hay duplicados).

6. **Timestamps en Pivot:** La tabla pivot `articulo_proveedor` incluye timestamps automáticos.

7. **Identificadores Externos:** Siempre usa `identificador_externo` de los proveedores, NO los IDs internos.

8. **Respuestas Simplificadas:** Las respuestas del API solo incluyen `identificador_externo`, `nombre_comercial` y `calendario_siempre_abierto` de cada proveedor (sin IDs internos ni información de pivot).

---

## Testing con Postman

La colección de Postman ha sido actualizada con ejemplos de uso del campo `proveedores[]`:

- **Crear artículo:** Incluye campos para proveedores (por defecto con ejemplo de identificadores PROV-001 y PROV-002)
- **Actualizar artículo:** Incluye campo para sincronizar proveedores

Archivo: `docs/postman/PRJ-002 eCommerce.postman_collection.json`
