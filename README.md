# Creación de proyecto

```cmd
composer create-project laravel/laravel 'PRJ- eCommerce'
```

# Control de código fuente

```cmd
git init .
git add .
git commit -m "Creación de proyecto"
git branch -m main
```

Al crear un proyecto en un sistema de control de versiones tipo GIT, el proveedor debe facilitar instrucciones como la que siguen:
```cmd
git remote add origin <dirección URL>
```

## Rutas

```
 GET|HEAD  / ........................................................................................................................................... 
            ⇂ web
  POST      api/auth/login ........................................................................................ api.auth.login › AuthController@login  
            ⇂ api
  POST      api/auth/logout ..................................................................................... api.auth.logout › AuthController@logout  
            ⇂ api
            ⇂ App\Http\Middleware\JwtMiddleware
  POST      api/auth/register ............................................................................... api.auth.register › AuthController@register  
            ⇂ api
  GET|HEAD  api/auth/user ......................................................................................... api.auth.get › AuthController@getUser  
            ⇂ api
            ⇂ App\Http\Middleware\JwtMiddleware
  PUT       api/auth/user ................................................................................... api.auth.update › AuthController@updateUser  
            ⇂ api
            ⇂ App\Http\Middleware\JwtMiddleware
  GET|HEAD  sanctum/csrf-cookie ....................................................... sanctum.csrf-cookie › Laravel\Sanctum › CsrfCookieController@show  
            ⇂ web
  GET|HEAD  storage/{path} ................................................................................................................ storage.local  
  GET|HEAD  up ..........................................................................................................................................  


```

## Swagger

```cmd
composer require "darkaonline/l5-swagger"
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
```

## Referencias externas

### JWT

[Implement JWT Authentication in Laravel 12](https://medium.com/@aliboutaine/how-to-implement-jwt-authentication-in-laravel-12-1e2ae878d5dc)



#

## Flujos de trabajo de la plataforma

### Venta a clientes anónimos

Paso 1 [gesfun]: informa a backend
  - Proveedores
  - Articulos

Paso 2 [cliente anónimo]:
  - Selecciona del catálogo filtrado en el paso anterior los artículos, de todo lo que esté en catálogo, que le interese

Paso 3 [backend]: 
  - Tramitar el pedido:
    - Confirmar el pedido
    - Confirmar la compra con pasarela de pago
    - Registrar las evidencias
    - Comunicar con GESFUN para enviar todos los detalles del pedido

### Venta a clientes de expediente_gesfun

Paso 1 [gesfun]: informa a backend
  - Proveedores
  - Articulos

Paso 2 [gesfun]: comunicar al backend las siguientes informaciones
  - Cliente + Fallecido
  - Lista de proveedores disponibles
  - Artículos: datos + archivos

Paso 3 [cliente]:
  - Selecciona del catálogo filtrado en el paso anterior los artículos que le interese

Paso 4 [backend]: 
  - Tramitar el pedido

### Portal de condolencias

