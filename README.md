<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Instalacion
Ejecute los comandos requeridos para la instalacion de una aplicacion en 
[Laravel 10](https://laravel.com/docs)

- composer install 
- php artisan migrate

Para contar con Juegos de prueba para la ejucion del proceso de ranking
ejecute 

- php artisan migrate:rollback
- php artisan migrate --seed

## Visual Studio Code

Cuenta con el archivo Game.rest si desea ejecutar las pruebas de desde este editor de texto.

Instale la extencion REST Client (humao.rest-client)

## Uso del Api

Por ser un Api que que ejecuta usuarios anonimos y con el objetivo de mantener la integridad de los datos se impemento 
un sistema basico de autenticacion.

- El consumidor del api debera enviar en cada peticion una cabecera X-API-KEY con el valor correspondiente a la misama.

- El consumidor debera guardar el valor proporcionado por la propiedad auth_key de cada jugo que cree (actua como identificador y contraparte del ID).



### Documentacion del API

- **[Swagger](http://localhost/api/documentation#/Game)**
