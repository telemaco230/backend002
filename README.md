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

## Referencias externas

### JWT

[Implement JWT Authentication in Laravel 12](https://medium.com/@aliboutaine/how-to-implement-jwt-authentication-in-laravel-12-1e2ae878d5dc)