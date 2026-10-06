
# MetalValue

Una aplicación web desarrollada con **Laravel** diseñada para la gestión, cálculo y monitoreo de precios y valores de metales en tiempo real.

---

## Características Principales

- **Cálculo en Tiempo Real:** Interfaz intuitiva para procesar y calcular valores actualizados.
- **Gestión de Datos:** Sistema seguro para el registro y consulta de información.

---

## Tecnologías Utilizadas

- **Backend:** PHP (8.2+) / Laravel (11.x / 12.x)
- **Base de Datos:** PostgreSQL
- **Testing:** PHPUnit
- **Gestor de Dependencias:** Composer

---

## Requisitos Previos

Asegúrate de contar con los siguientes componentes instalados en tu entorno de desarrollo:

- **PHP** `>= 8.2` (se recomienda PHP 8.3+)
- **Composer**
- **Servidor de Base de Datos** (XAMPP, MySQL, Docker, etc.)

---

## Instalación y Configuración Local

Sigue estos pasos para clonar y ejecutar el proyecto localmente:

1. **Clonar el repositorio:**
   ```bash
   git clone (https://github.com/selmtz/GestorDeActivos)
   cd metalvalue
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan serve
