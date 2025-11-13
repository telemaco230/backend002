# Guía de Uso - API de Proveedores

## Descripción

El API de Proveedores permite gestionar los proveedores del sistema eCommerce, incluyendo su calendario de disponibilidad y los artículos que distribuyen.

---

## Casos de Uso Comunes

### 1. Crear Proveedor con Disponibilidad 24/7

Para un proveedor que está siempre disponible:

```bash
curl -X POST http://localhost:8000/api/proveedores \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "identificador_externo": "PROV-001",
    "nombre_comercial": "Amazon 24/7",
    "calendario_siempre_abierto": true
  }'
```

**Resultado:**
- Proveedor creado con disponibilidad 24/7
- Los horarios específicos se ignoran
- `estaDisponible()` siempre retorna `true`

---

### 2. Crear Proveedor con Horario Semanal

Para un proveedor con horario de oficina (L-V 9:00-18:00, S 9:00-14:00):

```bash
curl -X POST http://localhost:8000/api/proveedores \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "identificador_externo": "PROV-002",
    "nombre_comercial": "Distribuidora ABC",
    "calendario_siempre_abierto": false,
    "horario_lunes_inicio": "09:00:00",
    "horario_lunes_fin": "18:00:00",
    "horario_martes_inicio": "09:00:00",
    "horario_martes_fin": "18:00:00",
    "horario_miercoles_inicio": "09:00:00",
    "horario_miercoles_fin": "18:00:00",
    "horario_jueves_inicio": "09:00:00",
    "horario_jueves_fin": "18:00:00",
    "horario_viernes_inicio": "09:00:00",
    "horario_viernes_fin": "18:00:00",
    "horario_sabado_inicio": "09:00:00",
    "horario_sabado_fin": "14:00:00"
  }'
```

**Nota:** Los días sin horario (domingo en este caso) quedan como `null`, indicando que el proveedor está cerrado.

---

### 3. Crear Proveedor con Artículos Asociados

```bash
curl -X POST http://localhost:8000/api/proveedores \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "identificador_externo": "PROV-003",
    "nombre_comercial": "Mayorista XYZ",
    "calendario_siempre_abierto": true,
    "articulos": ["ART-001", "ART-002", "ART-003"]
  }'
```

**Resultado:**
- Proveedor creado
- 3 artículos asociados mediante sus identificadores externos
- Relaciones creadas en la tabla pivot `articulo_proveedor`

---

### 4. Consultar Proveedor con Sus Artículos

```bash
curl -X GET http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {TOKEN}"
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Proveedor encontrado",
    "data": {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Amazon 24/7",
        "calendario_siempre_abierto": true,
        "articulos": [
            {
                "identificador_externo": "ART-001",
                "nombre": "Producto 1",
                "precio_base": "100.50"
            },
            {
                "identificador_externo": "ART-002",
                "nombre": "Producto 2",
                "precio_base": "75.00"
            }
        ]
    }
}
```

---

### 5. Actualizar Horario de un Proveedor

**Situación:** Cambiar el horario de cierre de viernes a 20:00

```bash
curl -X PUT http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "horario_viernes_fin": "20:00:00"
  }'
```

**Resultado:** Solo se actualiza el horario de cierre del viernes, el resto se mantiene igual.

---

### 6. Cambiar Artículos de un Proveedor

**Situación:** El proveedor tenía ART-001 y ART-002, ahora solo tiene ART-003 y ART-004

```bash
curl -X PUT http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "articulos": ["ART-003", "ART-004"]
  }'
```

**Resultado:**
- Se eliminan las asociaciones con ART-001 y ART-002
- Se crean asociaciones con ART-003 y ART-004

---

### 7. Verificar Disponibilidad

**Verificar si el proveedor está disponible el lunes a las 10:30:**

```bash
curl -X GET "http://localhost:8000/api/proveedores/1/disponibilidad?dia=lunes&hora=10:30:00" \
  -H "Authorization: Bearer {TOKEN}"
```

**Respuesta (disponible):**
```json
{
    "success": true,
    "message": "Disponibilidad verificada",
    "data": {
        "proveedor_id": 1,
        "identificador_externo": "PROV-002",
        "nombre_comercial": "Distribuidora ABC",
        "dia": "lunes",
        "hora": "10:30:00",
        "disponible": true
    }
}
```

**Verificar domingo (cerrado):**

```bash
curl -X GET "http://localhost:8000/api/proveedores/1/disponibilidad?dia=domingo&hora=10:30:00" \
  -H "Authorization: Bearer {TOKEN}"
```

**Respuesta (no disponible):**
```json
{
    "success": true,
    "message": "Disponibilidad verificada",
    "data": {
        "proveedor_id": 1,
        "identificador_externo": "PROV-002",
        "nombre_comercial": "Distribuidora ABC",
        "dia": "domingo",
        "hora": "10:30:00",
        "disponible": false
    }
}
```

---

### 8. Eliminar Todos los Artículos de un Proveedor

```bash
curl -X PUT http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "articulos": []
  }'
```

**Resultado:** Se eliminan todas las asociaciones con artículos.

---

### 9. Cambiar Proveedor a 24/7

**Situación:** Un proveedor que tenía horario ahora opera 24/7

```bash
curl -X PUT http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "calendario_siempre_abierto": true
  }'
```

**Resultado:** Los horarios específicos se ignoran, el proveedor está siempre disponible.

---

### 10. Listar Todos los Proveedores

```bash
curl -X GET http://localhost:8000/api/proveedores \
  -H "Authorization: Bearer {TOKEN}"
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Lista de proveedores",
    "data": {
        "proveedores": [
            {
                "id": 1,
                "identificador_externo": "PROV-001",
                "nombre_comercial": "Amazon 24/7",
                "calendario_siempre_abierto": true,
                "articulos": [...]
            },
            {
                "id": 2,
                "identificador_externo": "PROV-002",
                "nombre_comercial": "Distribuidora ABC",
                "calendario_siempre_abierto": false,
                "articulos": [...]
            }
        ]
    }
}
```

---

## Comportamiento del Campo `articulos`

### Sincronización (sync)

El campo `articulos[]` utiliza el método `sync()` de Eloquent:

1. **Si se envía:** Reemplaza completamente la lista de artículos
2. **Si NO se envía:** No modifica las relaciones existentes
3. **Si se envía vacío:** Elimina todas las asociaciones

### Ejemplos de Comportamiento

| Artículos Actuales | Campo Enviado | Resultado Final |
|-------------------|---------------|-----------------|
| [ART-001, ART-002] | `["ART-003", "ART-004"]` | [ART-003, ART-004] |
| [ART-001, ART-002] | `["ART-001", "ART-002", "ART-003"]` | [ART-001, ART-002, ART-003] |
| [ART-001, ART-002] | `[]` | [] |
| [ART-001, ART-002] | Campo no enviado | [ART-001, ART-002] |
| [] | `["ART-001"]` | [ART-001] |

---

## Validaciones de Horarios

### Formato de Hora

Todas las horas deben estar en formato `HH:MM:SS`:

✅ **Correcto:**
- `"09:00:00"`
- `"18:30:00"`
- `"23:59:59"`

❌ **Incorrecto:**
- `"9:00"` (sin segundos)
- `"09:00"` (sin segundos)
- `"9:0:0"` (sin ceros)

### Días de la Semana

Los días válidos para verificar disponibilidad:
- `lunes`
- `martes`
- `miercoles` (sin tilde)
- `jueves`
- `viernes`
- `sabado` (sin tilde)
- `domingo`

---

## Lógica de Disponibilidad

### Si `calendario_siempre_abierto` es `true`:

```
estaDisponible(cualquier_dia, cualquier_hora) → true
```

### Si `calendario_siempre_abierto` es `false`:

```
Si horario_dia_inicio es NULL → false
Si horario_dia_fin es NULL → false
Si hora >= horario_dia_inicio AND hora < horario_dia_fin → true
Sino → false
```

**Ejemplo:**

Proveedor con horario:
- Lunes: 09:00 - 18:00
- Domingo: NULL - NULL

```
estaDisponible("lunes", "10:30:00") → true
estaDisponible("lunes", "08:30:00") → false
estaDisponible("lunes", "18:00:00") → false (18:00 ya es fuera del horario)
estaDisponible("domingo", "10:30:00") → false (sin horario)
```

---

## Integración con Artículos

### Relación Bidireccional

Cuando asocias artículos a un proveedor:

**Desde el lado del Proveedor:**
```bash
# Asociar ART-001 al PROV-001
curl -X POST http://localhost:8000/api/proveedores \
  -d '{"identificador_externo": "PROV-001", ..., "articulos": ["ART-001"]}'
```

**Desde el lado del Artículo:**
```bash
# También asociar PROV-001 al ART-001
curl -X POST http://localhost:8000/api/articulos \
  -F "identificador_externo=ART-001" \
  -F "proveedores[]=PROV-001"
```

Ambas operaciones crean la misma relación en la tabla pivot `articulo_proveedor`.

---

## Errores Comunes

### 1. Artículo no existe

**Request:**
```json
{
    "articulos": ["ART-999"]
}
```

**Error:**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "articulos.0": ["The selected articulos.0 is invalid."]
        }
    }
}
```

---

### 2. Formato de hora incorrecto

**Request:**
```json
{
    "horario_lunes_inicio": "9:00"
}
```

**Error:**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "horario_lunes_inicio": ["The horario lunes inicio does not match the format H:i:s."]
        }
    }
}
```

---

### 3. Día inválido en verificación de disponibilidad

**Request:**
```
?dia=monday&hora=10:00:00
```

**Error:**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "dia": ["The selected dia is invalid."]
        }
    }
}
```

---

## Tips y Mejores Prácticas

1. **Identificadores Externos Descriptivos:**
   - ✅ `PROV-AMAZON-ES`
   - ✅ `PROV-001-DISTRIB-ABC`
   - ❌ `1`, `2`, `3`

2. **Calendario Siempre Abierto:**
   - Úsalo solo para proveedores verdaderamente 24/7
   - Si tiene horario, especifica todos los días

3. **Horarios Completos:**
   - Si un día no tiene horario, déjalo como `null`
   - No uses `"00:00:00"` para indicar "cerrado"

4. **Sincronización de Artículos:**
   - Si quieres agregar un artículo sin quitar los existentes, debes enviar TODOS los identificadores
   - No hay operación "agregar solo uno"

5. **Verificación de Disponibilidad:**
   - Usa este endpoint antes de procesar pedidos
   - Ten en cuenta que la hora `18:00:00` ya NO está dentro del horario `09:00:00 - 18:00:00`

---

## Ejemplos de Integración

### Flujo: Crear Proveedor y Asociar Artículos Existentes

```bash
# 1. Crear artículos primero
curl -X POST http://localhost:8000/api/articulos \
  -F "identificador_externo=ART-001" \
  -F "nombre=Producto 1" \
  -F "precio_base=100"

curl -X POST http://localhost:8000/api/articulos \
  -F "identificador_externo=ART-002" \
  -F "nombre=Producto 2" \
  -F "precio_base=200"

# 2. Crear proveedor con los artículos
curl -X POST http://localhost:8000/api/proveedores \
  -H "Content-Type: application/json" \
  -d '{
    "identificador_externo": "PROV-001",
    "nombre_comercial": "Mi Proveedor",
    "calendario_siempre_abierto": true,
    "articulos": ["ART-001", "ART-002"]
  }'
```

---

### Flujo: Verificar Disponibilidad Antes de Pedido

```bash
# 1. Obtener ID del proveedor
PROVEEDOR_ID=1

# 2. Verificar disponibilidad
curl -X GET "http://localhost:8000/api/proveedores/${PROVEEDOR_ID}/disponibilidad?dia=lunes&hora=14:30:00" \
  -H "Authorization: Bearer {TOKEN}"

# 3. Si disponible=true, procesar el pedido
# Si disponible=false, mostrar mensaje al usuario
```

---

## Testing

Ver `docs/TEST_PROVEEDORES_EXTERNOS.md` para guía completa de testing del API.
