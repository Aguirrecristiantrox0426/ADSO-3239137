# RENT-TOOLS API

## Descripción

RENT-TOOLS es un sistema de gestión de alquiler de herramientas para construcción. Este repositorio contiene el backend encargado de administrar la información y las operaciones del sistema mediante una API REST.

El backend está desarrollado con PHP y Laravel.

## Objetivo

Proporcionar servicios para gestionar:

- Herramientas y cantidades disponibles.
- Clientes.
- Asesores.
- Alquileres.
- Devoluciones.
- Disponibilidad e inventario.

## Tecnologías

- PHP 8.3 o superior.
- Laravel 13.
- Composer.
- Base de datos relacional.
- Docker para el entorno de ejecución.

## Estructura del proyecto

```text
api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── public/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
├── artisan
├── composer.json
└── phpunit.xml
```
