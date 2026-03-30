# Fundación Nuestra Esperanza — Backend (Laravel 11)

Base del backend con **Autenticación + Roles** lista, sobre la cual construiremos módulos (Donaciones, Campañas, CMS, Voluntariado, etc.).

## Requisitos
- PHP 8.2+ (recomendado 8.3)
- Composer 2.x
- MySQL/MariaDB
- Node (solo si vas a servir assets; opcional)

## Setup local
```bash
cp .env.example .env
php artisan key:generate

# Configura DB en .env, crea tu BD y luego:
composer install
php artisan migrate
php artisan db:seed

php artisan serve
# http://127.0.0.1:8000
