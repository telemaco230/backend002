# API de Artículos

Este documento describe el uso del API RESTful para la gestión de artículos en el sistema eCommerce.

## Endpoints

Todas las rutas requieren autenticación mediante token JWT en el header:
```
Authorization: Bearer {token}
```

### 1. Listar todos los artículos

**GET** `/api/articulos`

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Lista de artículos",
    "data": {
        "articulos": [
            {
                "id": 1,
                "identificador_externo": "ART-001",
                "nombre": "Producto ejemplo",
                "descripcion": "Descripción del producto",
                "precio_base": "100.50",
                "tipo_iva": "general",
                "porcentaje_iva": "21.00",
                "created_at": "2025-11-13T16:38:13.000000Z",
                "updated_at": "2025-11-13T16:38:13.000000Z",
                "imagenes": [],
                "documentos": []
            }
        ]
    }
}
```

### 2. Crear un nuevo artículo

**POST** `/api/articulos`

**Content-Type:** `multipart/form-data`

**Parámetros:**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| identificador_externo | string | Sí | Identificador único externo del artículo |
| nombre | string | Sí | Nombre del artículo |
| descripcion | string | No | Descripción detallada |
| precio_base | decimal | Sí | Precio base sin IVA |
| tipo_iva | string | Sí | Tipo de IVA: general, reducido, superreducido, exento |
| porcentaje_iva | decimal | Sí | Porcentaje de IVA (0-100) |
| imagenes[] | file[] | No | Array de archivos de imagen (jpeg, jpg, png, gif, webp, max 5MB) |
| documentos[] | file[] | No | Array de archivos de documentos (pdf, doc, docx, xls, xlsx, txt, max 10MB) |
| `proveedores[]` | Array de strings | No | IDs externos de proveedores a sincronizar | `["PROV-003"]` |"PROV-001", "PROV-002"]` |

**Ejemplo de solicitud con cURL:**
```bash
curl -X POST http://localhost:8000/api/articulos \
  -H "Authorization: Bearer {token}" \
  -F "identificador_externo=ART-001" \
  -F "nombre=Producto de ejemplo" \
  -F "descripcion=Descripción detallada" \
  -F "precio_base=100.50" \
  -F "tipo_iva=general" \
  -F "porcentaje_iva=21.00" \
  -F "proveedores[]=PROV-001" \
  -F "proveedores[]=PROV-002" \
  -F "imagenes[]=@/path/to/image1.jpg" \
  -F "imagenes[]=@/path/to/image2.jpg" \
  -F "documentos[]=@/path/to/doc1.pdf"
```

**Respuesta exitosa (201):**
```json
{
    "success": true,
    "message": "Artículo creado exitosamente",
    "data": {
        "id": 1,
        "identificador_externo": "ART-001",
        "nombre": "Producto de ejemplo",
        "descripcion": "Descripción detallada",
        "precio_base": "100.50",
        "tipo_iva": "general",
        "porcentaje_iva": "21.00",
        "created_at": "2025-11-13T16:38:13.000000Z",
        "updated_at": "2025-11-13T16:38:13.000000Z",
        "imagenes": [
            {
                "id": 1,
                "articulo_id": 1,
                "nombre_archivo": "image1.jpg",
                "ruta": "public/articulos/imagenes/1/1731513493_0_abc123.jpg",
                "tipo_mime": "image/jpeg",
                "tamanio": 245678,
                "orden": 0,
                "created_at": "2025-11-13T16:38:13.000000Z",
                "updated_at": "2025-11-13T16:38:13.000000Z"
            }
        ],
        "documentos": [
            {
                "id": 1,
                "articulo_id": 1,
                "nombre_archivo": "doc1.pdf",
                "ruta": "public/articulos/documentos/1/1731513493_0_def456.pdf",
                "tipo_mime": "application/pdf",
                "tamanio": 512345,
                "descripcion": null,
                "created_at": "2025-11-13T16:38:13.000000Z",
                "updated_at": "2025-11-13T16:38:13.000000Z"
            }
        ],
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

### 3. Consultar un artículo específico

**GET** `/api/articulos/{id}`

**Parámetros de URL:**
- `id` (integer): ID del artículo

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Artículo encontrado",
    "data": {
        "id": 1,
        "identificador_externo": "ART-001",
        "nombre": "Producto de ejemplo",
        "descripcion": "Descripción detallada",
        "precio_base": "100.50",
        "tipo_iva": "general",
        "porcentaje_iva": "21.00",
        "created_at": "2025-11-13T16:38:13.000000Z",
        "updated_at": "2025-11-13T16:38:13.000000Z",
        "imagenes": [...],
        "documentos": [...],
        "proveedores": [...]
    }
}
```

**Respuesta de error (404):**
```json
{
    "success": false,
    "message": "Artículo no encontrado"
}
```

### 4. Actualizar un artículo

**POST** `/api/articulos/{id}`

**Content-Type:** `multipart/form-data`

**Nota:** Se utiliza POST en lugar de PUT para soportar multipart/form-data. Incluir `_method=PUT` en el body.

**Parámetros:**

Todos los parámetros son opcionales. Solo se actualizarán los campos proporcionados.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| _method | string | Debe ser "PUT" |
| identificador_externo | string | Identificador único externo |
| nombre | string | Nombre del artículo |
| descripcion | string | Descripción detallada |
| precio_base | decimal | Precio base sin IVA |
| tipo_iva | string | Tipo de IVA |
| porcentaje_iva | decimal | Porcentaje de IVA |
| imagenes[] | file[] | **REEMPLAZA** todas las imágenes anteriores |
| documentos[] | file[] | **REEMPLAZA** todos los documentos anteriores |
| proveedores[] | integer[] | **REEMPLAZA** todos los proveedores anteriores (sync) |

**IMPORTANTE:** Si se envían imágenes o documentos en la actualización, **todos los archivos anteriores serán eliminados** y reemplazados por los nuevos.

**IMPORTANTE:** Si se envía el campo `proveedores` (aunque sea un array vacío), **se reemplazará completamente** la lista de proveedores asociados al artículo. Si no se envía el campo `proveedores`, la relación actual se mantendrá sin cambios.

**Ejemplo de solicitud con cURL:**
```bash
curl -X POST http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "nombre=Producto actualizado" \
  -F "precio_base=150.75" \
  -F "proveedores[]=PROV-003" \
  -F "proveedores[]=PROV-005" \
  -F "imagenes[]=@/path/to/new_image.jpg"
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Artículo actualizado exitosamente",
    "data": {
        "id": 1,
        "identificador_externo": "ART-001",
        "nombre": "Producto actualizado",
        "descripcion": "Descripción detallada",
        "precio_base": "150.75",
        "tipo_iva": "general",
        "porcentaje_iva": "21.00",
        "created_at": "2025-11-13T16:38:13.000000Z",
        "updated_at": "2025-11-13T17:45:20.000000Z",
        "imagenes": [...],
        "documentos": [...],
        "proveedores": [...]
    }
}
```

### 5. Eliminar un artículo

**DELETE** `/api/articulos/{id}`

**Parámetros de URL:**
- `id` (integer): ID del artículo

Esta operación:
- Elimina el registro del artículo de la base de datos
- Elimina todos los registros de imágenes asociadas
- Elimina todos los registros de documentos asociados
- Elimina físicamente todos los archivos (imágenes y documentos) del almacenamiento
- Elimina los directorios vacíos

**Ejemplo de solicitud con cURL:**
```bash
curl -X DELETE http://localhost:8000/api/articulos/1 \
  -H "Authorization: Bearer {token}"
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Artículo eliminado exitosamente"
}
```

**Respuesta de error (404):**
```json
{
    "success": false,
    "message": "Artículo no encontrado"
}
```

## Estructura de Archivos

Los archivos se almacenan en el sistema de archivos con la siguiente estructura:

```
storage/app/public/articulos/
├── imagenes/
│   └── {articulo_id}/
│       ├── {timestamp}_{index}_{unique_id}.{extension}
│       └── ...
└── documentos/
    └── {articulo_id}/
        ├── {timestamp}_{index}_{unique_id}.{extension}
        └── ...
```

Los archivos son accesibles públicamente a través del enlace simbólico en `public/storage`.

## Tipos de IVA

El campo `tipo_iva` acepta los siguientes valores:

- `general`: IVA general (21%)
- `reducido`: IVA reducido (10%)
- `superreducido`: IVA superreducido (4%)
- `exento`: Exento de IVA (0%)

## Formatos de Archivo Permitidos

### Imágenes
- Formatos: JPEG, JPG, PNG, GIF, WEBP
- Tamaño máximo: 5 MB por archivo

### Documentos
- Formatos: PDF, DOC, DOCX, XLS, XLSX, TXT
- Tamaño máximo: 10 MB por archivo

## Códigos de Estado HTTP

- `200 OK`: Operación exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Error en la validación de datos
- `404 Not Found`: Recurso no encontrado
- `500 Internal Server Error`: Error del servidor

## Documentación Swagger

La documentación interactiva completa del API está disponible en:

```
http://localhost:8000/api/documentation
```

## Colección Postman

Se incluye una colección de Postman actualizada en:

```
docs/postman/PRJ-002 eCommerce.postman_collection.json
```

Importa esta colección en Postman para probar todos los endpoints fácilmente.

---

## Gestión de Proveedores

### Uso de Identificadores Externos

Los proveedores se especifican mediante su **identificador externo** (`identificador_externo`), no por su ID interno.

**IMPORTANTE:** El campo `proveedores[]` utiliza el método `sync()` de Eloquent:

- **Si se envía:** Reemplaza completamente los proveedores asociados con los identificadores externos proporcionados
- **Si NO se envía:** Mantiene los proveedores existentes
- **Si se envía vacío:** Elimina todas las asociaciones

Los identificadores deben ser los códigos externos (`identificador_externo`) de los proveedores, no sus IDs internos.

**Ejemplo:**
```bash
# Asociar proveedores PROV-001 y PROV-002 al artículo
-F "proveedores[]=PROV-001" \
-F "proveedores[]=PROV-002"
```

### Formato de Respuesta de Proveedores

Los proveedores en la respuesta incluyen solo información básica:

```json
"proveedores": [
    {
        "identificador_externo": "PROV-001",
        "nombre_comercial": "Distribuidora ABC",
        "calendario_siempre_abierto": true
    }
]
```

**Nota:** No se incluyen IDs internos ni información de la tabla pivot en las respuestas.
