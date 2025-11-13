# Modelado de datos

## Maestros

### Proveedor

Campos:
- gesfun_id: contador`<numérico>`, 5 dígitos
- horario:
    - **A definir**
- artículos: colección de identificadores

### Artículo

Campos:
- id: contador *string*,
- nombre
- descripción: texto a mostrar en la Web
- colección archivos
    - imágenes de galería
    - documentación técnica
- Cotización:
    - Precio base
    - IVA
    - Tipo de IVA    

### Relación Artículo-Proveedor

Qué proveedor / Qué artículo 

### Cliente

Campos:
    - id: contador *string*
    - Nombre y apellidos

### Pedido

Campos:   
    *A definir*



# ------------------------------------------------------------------
# Modelos de datos por definir

Modelos de datos a definir:
- Proveedor
  - Datos específicos
  - Calendario de disponibilidad
- Cliente
  - Datos específicos
    - Dirección de facturación
    - Calendario de disponibilidad:
      - Por defecto abierto
      - Para los de expediente, limitados por el horario informado por cliente
- Artículo
  - Datos específicos
  - Archivos asociados:
    - Categoría: imagen, ficha técnica, ...
  - Cotización
- Relación Proveedor-Artículo
  - Calendario de disponibilidad
- Pedido
  - Cabecera de pedido
    - Cliente
      - Datos propios
      - Dirección de emisión de factura
    - Confirmación de pago
    - Resumen financiero
    - Archivos asociados:
      - Pasarela de pago
      - Datos técnicos
      - Documento de confirmación por parte de la pasarela de pago
    - Colección estados
  - Líneas de pedido
    - Artículo
    - Cantidad
    - Precio
    - Observaciones
    - Condiciones de entrega
      - Dirección física
      - Horario

## Puntos de API

### Backend

Objetivo: administración de maestros dirigida por GESFUN
Administración de proveedores
Administración de artículos
Consulta de pedidos
Administración de clientes

### Gesfun

Recepción de pedidos