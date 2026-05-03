# Contribuir

## Flujo de ramas
- `main`: producción
- `dev`: integración
- `feature/<modulo>`: rama por módulo/feature

## Requisitos por PR
- Migraciones incluidas (sin SQL suelto)
- Seeders cuando aplique (y referenciados en DatabaseSeeder)
- Endpoints documentados (Swagger o README del módulo)
- Tests básicos (al menos de endpoints críticos)
- Notas de despliegue: ¿requiere cron/queue?, ¿nuevas variables .env?

## Estilo
- Controladores finos: validación en Form Requests, reglas de negocio en Services
- Contratos para adaptadores externos (`Contracts/*` + `Gateways/*`)
- Nombres consistentes con el esquema v2
- Evitar lógica en migraciones; usar seeders para data

## Local
- Servir siempre desde `/public` (apache vhost o `php artisan serve`)
- PHP 8.2+ (ideal 8.3)
