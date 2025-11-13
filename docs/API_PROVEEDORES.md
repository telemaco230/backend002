# API de Proveedores

Este documento describe el uso del API RESTful para la gestión de proveedores en el sistema eCommerce.

## Endpoints

Todas las rutas requieren autenticación mediante token JWT en el header:
```
Authorization: Bearer {token}
```

### 1. Listar todos los proveedores

**GET** `/api/proveedores`

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Lista de proveedores",
    "data": {
        "proveedores": [
            {
                "id": 1,
                "identificador_externo": "PROV-001",
                "nombre_comercial": "Distribuidora ABC",
                "calendario_siempre_abierto": true,
                "horario_lunes_inicio": null,
                "horario_lunes_fin": null,
                "created_at": "2025-11-13T16:00:00.000000Z",
                "updated_at": "2025-11-13T16:00:00.000000Z",
                "articulos": [
                    {
                        "identificador_externo": "ART-001",
                        "nombre": "Producto 1",
                        "precio_base": "100.50"
                    }
                ]
            }
        ]
    }
}
```

---

### 2. Crear un nuevo proveedor

**POST** `/api/proveedores`

**Content-Type:** `application/json`

**Parámetros:**

| Campo | Tipo | Requerido | Descripción | Ejemplo |
|-------|------|-----------|-------------|---------|
| `identificador_externo` | string | Sí | Identificador único externo del proveedor | `"PROV-001"` |
| `nombre_comercial` | string | Sí | Nombre comercial del proveedor | `"Distribuidora ABC"` |
| `calendario_siempre_abierto` | boolean | Sí | Indica si el proveedor está disponible 24/7 | `true` |
| `horario_lunes_inicio` | string (time) | No | Hora de apertura los lunes (HH:MM:SS) | `"09:00:00"` |
| `horario_lunes_fin` | string (time) | No | Hora de cierre los lunes (HH:MM:SS) | `"18:00:00"` |
| `horario_martes_inicio` | string (time) | No | Hora de apertura los martes | `"09:00:00"` |
| `horario_martes_fin` | string (time) | No | Hora de cierre los martes | `"18:00:00"` |
| `horario_miercoles_inicio` | string (time) | No | Hora de apertura los miércoles | `"09:00:00"` |
| `horario_miercoles_fin` | string (time) | No | Hora de cierre los miércoles | `"18:00:00"` |
| `horario_jueves_inicio` | string (time) | No | Hora de apertura los jueves | `"09:00:00"` |
| `horario_jueves_fin` | string (time) | No | Hora de cierre los jueves | `"18:00:00"` |
| `horario_viernes_inicio` | string (time) | No | Hora de apertura los viernes | `"09:00:00"` |
| `horario_viernes_fin` | string (time) | No | Hora de cierre los viernes | `"18:00:00"` |
| `horario_sabado_inicio` | string (time) | No | Hora de apertura los sábados | `"09:00:00"` |
| `horario_sabado_fin` | string (time) | No | Hora de cierre los sábados | `"14:00:00"` |
| `horario_domingo_inicio` | string (time) | No | Hora de apertura los domingos | `null` |
| `horario_domingo_fin` | string (time) | No | Hora de cierre los domingos | `null` |
| `articulos[]` | Array de strings | No | IDs externos de artículos | `["ART-001", "ART-002"]` |

**Ejemplo de solicitud con cURL:**
```bash
curl -X POST http://localhost:8000/api/proveedores \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "identificador_externo": "PROV-001",
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
    "horario_sabado_fin": "14:00:00",
    "articulos": ["ART-001", "ART-002"]
  }'
```

**Respuesta exitosa (201):**
```json
{
    "success": true,
    "message": "Proveedor creado exitosamente",
    "data": {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "calendario_siempre_abierto": false,
        "horario_lunes_inicio": "09:00:00",
        "horario_lunes_fin": "18:00:00",
        "created_at": "2025-11-13T16:00:00.000000Z",
        "updated_at": "2025-11-13T16:00:00.000000Z",
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

### 3. Consultar un proveedor específico

**GET** `/api/proveedores/{id}`

**Parámetros de URL:**
- `id` (integer): ID del proveedor

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Proveedor encontrado",
    "data": {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "calendario_siempre_abierto": false,
        "horario_lunes_inicio": "09:00:00",
        "horario_lunes_fin": "18:00:00",
        "created_at": "2025-11-13T16:00:00.000000Z",
        "updated_at": "2025-11-13T16:00:00.000000Z",
        "articulos": [...]
    }
}
```

**Respuesta de error (404):**
```json
{
    "success": false,
    "message": "Proveedor no encontrado"
}
```

---

### 4. Actualizar un proveedor

**PUT** `/api/proveedores/{id}`

**Content-Type:** `application/json`

**Parámetros:**

Todos los parámetros son opcionales. Solo se actualizarán los campos proporcionados.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `identificador_externo` | string | Identificador único externo |
| `nombre_comercial` | string | Nombre comercial |
| `calendario_siempre_abierto` | boolean | Disponible 24/7 |
| `horario_*_inicio` | string (time) | Horarios de apertura |
| `horario_*_fin` | string (time) | Horarios de cierre |
| `articulos[]` | Array de strings | IDs externos de artículos a sincronizar |

**Ejemplo de solicitud con cURL:**
```bash
curl -X PUT http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre_comercial": "Distribuidora ABC S.L.",
    "horario_lunes_inicio": "08:00:00",
    "horario_lunes_fin": "20:00:00",
    "articulos": ["ART-001", "ART-003"]
  }'
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Proveedor actualizado exitosamente",
    "data": {
        "id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC S.L.",
        "horario_lunes_inicio": "08:00:00",
        "horario_lunes_fin": "20:00:00",
        "articulos": [
            {
                "identificador_externo": "ART-001",
                "nombre": "Producto 1",
                "precio_base": "100.50"
            },
            {
                "identificador_externo": "ART-003",
                "nombre": "Producto 3",
                "precio_base": "50.00"
            }
        ]
    }
}
```

---

### 5. Eliminar un proveedor

**DELETE** `/api/proveedores/{id}`

**Parámetros de URL:**
- `id` (integer): ID del proveedor

Esta operación:
- Elimina el registro del proveedor de la base de datos
- Elimina todas las relaciones con artículos en la tabla pivot

**Ejemplo de solicitud con cURL:**
```bash
curl -X DELETE http://localhost:8000/api/proveedores/1 \
  -H "Authorization: Bearer {token}"
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Proveedor eliminado exitosamente"
}
```

**Respuesta de error (404):**
```json
{
    "success": false,
    "message": "Proveedor no encontrado"
}
```

---

### 6. Verificar disponibilidad de un proveedor

**GET** `/api/proveedores/{id}/disponibilidad?dia={dia}&hora={hora}`

**Parámetros de URL:**
- `id` (integer): ID del proveedor

**Parámetros de Query:**
- `dia` (string): Día de la semana: `lunes`, `martes`, `miercoles`, `jueves`, `viernes`, `sabado`, `domingo`
- `hora` (string): Hora en formato `HH:MM:SS`

**Ejemplo de solicitud con cURL:**
```bash
curl -X GET "http://localhost:8000/api/proveedores/1/disponibilidad?dia=lunes&hora=10:30:00" \
  -H "Authorization: Bearer {token}"
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Disponibilidad verificada",
    "data": {
        "proveedor_id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "dia": "lunes",
        "hora": "10:30:00",
        "disponible": true
    }
}
```

**Respuesta con proveedor no disponible:**
```json
{
    "success": true,
    "message": "Disponibilidad verificada",
    "data": {
        "proveedor_id": 1,
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "dia": "domingo",
        "hora": "10:30:00",
        "disponible": false
    }
}
```

---

## Gestión de Artículos Asociados

### Uso de Identificadores Externos

Los artículos se especifican mediante su **identificador externo** (`identificador_externo`), no por su ID interno.

**IMPORTANTE:** El campo `articulos[]` utiliza el método `sync()` de Eloquent:

- **Si se envía:** Reemplaza completamente los artículos asociados con los identificadores externos proporcionados
- **Si NO se envía:** Mantiene los artículos existentes
- **Si se envía vacío:** Elimina todas las asociaciones

Los identificadores deben ser los códigos externos (`identificador_externo`) de los artículos, no sus IDs internos.

**Ejemplo:**
```json
{
    "articulos": ["ART-001", "ART-002", "ART-003"]
}
```

### Formato de Respuesta de Artículos

Los artículos en la respuesta incluyen solo información básica:

```json
"articulos": [
    {
        "identificador_externo": "ART-001",
        "nombre": "Producto 1",
        "precio_base": "100.50"
    }
]
```

**Nota:** No se incluyen IDs internos ni información de la tabla pivot en las respuestas.

---

## Calendario y Horarios

### Calendario Siempre Abierto

Si `calendario_siempre_abierto` es `true`:
- El proveedor está disponible 24/7
- Los horarios específicos de cada día son ignorados
- El método `estaDisponible()` siempre retorna `true`

### Calendario con Horarios

Si `calendario_siempre_abierto` es `false`:
- Los horarios de cada día deben especificarse
- Si un día no tiene horario (ambos campos `null`), el proveedor NO está disponible ese día
- El método `estaDisponible()` verifica la hora contra el horario del día especificado

**Ejemplo de proveedor con horario:**
```json
{
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
    "horario_sabado_fin": "14:00:00",
    "horario_domingo_inicio": null,
    "horario_domingo_fin": null
}
```

En este ejemplo:
- Lunes a Viernes: 09:00 - 18:00
- Sábado: 09:00 - 14:00
- Domingo: CERRADO

---

## Códigos de Estado HTTP

- `200 OK`: Operación exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Error en la validación de datos
- `404 Not Found`: Recurso no encontrado
- `500 Internal Server Error`: Error del servidor

---

## Validaciones

### Crear Proveedor

```php
'identificador_externo' => 'required|string|unique:proveedores,identificador_externo',
'nombre_comercial' => 'required|string|max:255',
'calendario_siempre_abierto' => 'required|boolean',
'horario_*_inicio' => 'nullable|date_format:H:i:s',
'horario_*_fin' => 'nullable|date_format:H:i:s',
'articulos' => 'nullable|array',
'articulos.*' => 'nullable|string|exists:articulos,identificador_externo',
```

### Actualizar Proveedor

Mismas validaciones que en crear, excepto que todos los campos son opcionales (`sometimes`).

---

## Errores Comunes

### 1. Artículo No Existe

**Request:**
```json
{
    "articulos": ["ART-999"]
}
```

**Respuesta (400):**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "articulos.0": [
                "The selected articulos.0 is invalid."
            ]
        }
    }
}
```

### 2. Formato de Hora Incorrecto

**Request:**
```json
{
    "horario_lunes_inicio": "9:00"
}
```

**Respuesta (400):**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "horario_lunes_inicio": [
                "The horario lunes inicio does not match the format H:i:s."
            ]
        }
    }
}
```

### 3. Identificador Externo Duplicado

**Request:**
```json
{
    "identificador_externo": "PROV-001"
}
```

**Respuesta (400):**
```json
{
    "success": false,
    "message": "Error en la validación de datos",
    "data": {
        "errors": {
            "identificador_externo": [
                "The identificador externo has already been taken."
            ]
        }
    }
}
```

---

## Documentación Swagger

La documentación interactiva completa del API está disponible en:

```
http://localhost:8000/api/documentation
```

---

## Colección Postman

Se incluye una colección de Postman actualizada en:

```
docs/postman/PRJ-002 eCommerce.postman_collection.json
```

Importa esta colección en Postman para probar todos los endpoints fácilmente.
