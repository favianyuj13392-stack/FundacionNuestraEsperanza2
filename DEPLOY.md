# PLAN DE DEPLOY - VPS Hostinger KVM2

**Proyecto:** Fundación (Laravel 12 + React + MySQL + Pasarela QR BNB)  
**Objetivo:** Configurar el VPS para correr múltiples proyectos de forma aislada y segura, comenzando por la aplicación de la Fundación.

**Fecha:** Abril 2026  
**Servidor:** Hostinger VPS KVM2 (2 vCPU - 8 GB RAM - 100 GB NVMe)

## 1. Objetivos del Deploy

- Dockerizar la aplicación actual de la Fundación (que aún no está dockerizada).
- Establecer una arquitectura que permita correr **múltiples proyectos** de forma aislada.
- Mantener buen rendimiento a pesar de tener solo 2 vCPU y 8 GB de RAM.
- Implementar buenas prácticas de seguridad y mantenibilidad.
- Facilitar futuros proyectos en diferentes tecnologías (FastAPI, PostgreSQL, Node.js, etc.).

## 2. Estrategia de Aislamiento Elegida

**Tecnología principal:** Docker + Docker Compose  
**Razones:**
- Bajo overhead de recursos (ideal para VPS de 2 vCPU / 8 GB).
- Excelente soporte para stacks heterogéneos.
- Fácil de mantener y migrar.
- Aislamiento suficiente para este tamaño de servidor.

**No se recomienda:**
- Máquinas virtuales (KVM anidado)
- LXC/LXD (overkill y más complejo)
- División manual fuerte de recursos

## 3. Arquitectura General

- **Host:** Ubuntu 24.04 LTS
- **Reverse Proxy Central:** Traefik (recomendado) o Nginx Proxy Manager
- **Contenedores por proyecto:** Cada proyecto tendrá su propio `docker-compose.yml`
- **Redes Docker:** Una red por proyecto para mayor aislamiento
- **Bases de datos:** 
  - MySQL para la Fundación
  - PostgreSQL para proyectos futuros (pueden coexistir)
- **Dominios:** Subdominios por proyecto (`fundacion.tudominio.com`, `api.proyecto2.com`, etc.)

## 4. Estructura de Directorios en el Servidor

```bash
/home/deploy/
├── projects/
│   ├── fundacion-bnb/              # ← Proyecto principal
│   │   ├── backend/
│   │   ├── frontend/
│   │   ├── docker-compose.yml
│   │   ├── .env
│   │   └── nginx/                  # (opcional)
│   ├── proyecto2-fastapi/
│   └── proyecto3-...
├── proxy/                          # Traefik o Nginx Proxy Manager
├── scripts/
│   ├── backup.sh
│   ├── update-all.sh
│   └── monitor.sh
├── logs/
└── data/                           # Volúmenes persistentes (opcional)
```

## 5. Pasos de Deploy (Secuencia Recomendada)

### Fase 1: Preparación del Servidor
1. Actualizar sistema e instalar herramientas básicas
2. Crear usuario `deploy` (no-root) y agregarlo al grupo docker
3. Instalar Docker + Docker Compose
4. Configurar Firewall (UFW)
5. Configurar SSH (solo llaves + Fail2Ban)
6. Instalar herramientas útiles (`htop`, `curl`, `git`, `jq`, etc.)

### Fase 2: Configuración del Reverse Proxy
1. Desplegar Traefik (o Nginx Proxy Manager)
2. Configurar Let's Encrypt
3. Verificar acceso vía subdominios

### Fase 3: Dockerizar la Aplicación de la Fundación
1. Crear carpeta `projects/fundacion-bnb`
2. Crear `docker-compose.yml` con los siguientes servicios:
   - `app` → PHP 8.3 + Laravel 12 (FPM)
   - `nginx` → Servidor web
   - `db` → MySQL 8.0
   - `redis` → (recomendado para queues y cache)
   - `frontend` → React (build estático servido por Nginx)
3. Migrar la base de datos actual al contenedor
4. Configurar variables de entorno (.env)
5. Añadir labels de Traefik para routing automático

### Fase 4: Optimizaciones y Seguridad
- Establecer límites de recursos (`mem_limit`, `cpus`) en cada servicio
- Configurar redes Docker separadas
- Crear usuarios de base de datos con permisos mínimos
- Configurar backups automáticos
- Implementar monitoreo básico (`docker stats`, Portainer opcional)

### Fase 5: Preparación para Proyectos Futuros
- Documentar plantilla de `docker-compose.yml` base
- Definir estándares para nuevos proyectos (FastAPI, etc.)

## 6. Requisitos Técnicos por Servicio

**Fundación (Laravel + React):**
- PHP 8.3 o superior
- MySQL 8.0
- Node.js (para build de React)
- Redis (opcional pero recomendado)
- Laravel Horizon / Queues (si aplica)

**Proyectos Futuros:**
- Soporte para Python/FastAPI + PostgreSQL
- Soporte para Node.js / Next.js
- Flexibilidad para diferentes versiones

## 7. Buenas Prácticas Obligatorias

- Nunca correr contenedores como `root`
- Usar volúmenes nombrados o bind mounts controlados
- No exponer puertos de bases de datos al exterior (`127.0.0.1` o redes internas)
- Mantener `.env` fuera del control de versiones
- Hacer backups diarios de bases de datos y volúmenes
- Actualizar imágenes Docker regularmente

## 8. Comandos Útiles

```bash
# Ver estado general
docker compose ls
docker stats
docker logs <container>

# Reiniciar solo un proyecto
cd /home/deploy/projects/fundacion-bnb && docker compose restart

# Ver logs en tiempo real
docker compose logs -f app
```

## 9. Próximos Pasos (Checklist)

- [ ] Preparar servidor base
- [ ] Instalar y configurar Docker
- [ ] Desplegar Reverse Proxy (Traefik)
- [ ] Dockerizar backend Laravel
- [ ] Dockerizar frontend React
- [ ] Configurar base de datos y migraciones
- [ ] Configurar routing con Traefik + SSL
- [ ] Probar pasarela de pagos QR BNB en entorno Docker
- [ ] Configurar backups
- [ ] Documentar credenciales y accesos

---

**Nota importante para la IA asistente:**

Este servidor tiene recursos limitados (2 vCPU / 8 GB RAM).  
Siempre aplicar límites de recursos en los `docker-compose.yml` y priorizar el proyecto de la Fundación.  
Evitar correr más de 2-3 proyectos simultáneos sin monitorear el consumo de RAM y CPU.

## 10. Configuración de SSL y Reverse Proxy (Traefik)

### 10.1 ¿Por qué Traefik?
- Gestiona **todos los certificados SSL** automáticamente con **Let's Encrypt**.
- Soporta múltiples proyectos (Fundación, Ecommerce, APIs, etc.) en el mismo servidor.
- Bajo consumo de recursos (ideal para VPS de 2 vCPU / 8 GB).
- Configuración declarativa mediante **labels** en cada `docker-compose.yml`.
- Auto-descubre nuevos servicios cuando levantas un nuevo proyecto.

### 10.2 Cómo funcionan los certificados SSL con múltiples proyectos

- Un solo contenedor de **Traefik** escucha en los puertos **80** y **443**.
- Traefik detecta el dominio que llega (`fundacion.tudominio.com`, `tienda.tudominio.com`, etc.).
- Solicita y renueva automáticamente los certificados de Let's Encrypt.
- Cada proyecto puede tener su propio dominio o subdominio con su certificado independiente.
- **No** es necesario instalar certificados dentro de cada aplicación (Laravel, FastAPI, React, etc.).

**Importante para donaciones y ecommerce:**
- La pasarela QR del BNB y la pasarela del ecommerce funcionarán sin conflicto siempre que apunten a URLs diferentes.
- Todo el tráfico irá encriptado (HTTPS).

### 10.3 Estructura recomendada

```bash
/home/deploy/
├── proxy/                    # ← Aquí va Traefik
│   ├── docker-compose.yml
│   ├── acme.json             # ← Archivo de certificados (permisos 600)
│   └── traefik.yml           # (opcional para config avanzada)
├── projects/
│   ├── fundacion-bnb/
│   └── ecommerce-tienda/
```

### 10.4 docker-compose.yml de Traefik (Recomendado 2026)

Crea la carpeta `/home/deploy/proxy/` y dentro este archivo `docker-compose.yml`:

```yaml
version: "3.9"

services:
  traefik:
    image: traefik:v3.4
    container_name: traefik
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    ports:
      - "80:80"      # HTTP (para challenge)
      - "443:443"    # HTTPS
      # - "8080:8080" # Dashboard (desactívalo en producción o protégelo)
    networks:
      - proxy
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./acme.json:/letsencrypt/acme.json
      - ./traefik.yml:/traefik.yml:ro   # opcional
    command:
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--providers.docker.network=proxy"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--entrypoints.web.http.redirections.entryPoint.to=websecure"
      - "--entrypoints.web.http.redirections.entryPoint.scheme=https"
      - "--certificatesresolvers.letsencrypt.acme.email=tuemail@dominio.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge=true"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
      # Opcional: Activar staging primero para pruebas
      # - "--certificatesresolvers.letsencrypt.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory"

networks:
  proxy:
    name: proxy
    external: true
```

**Pasos previos obligatorios:**
1. Crea el archivo `acme.json`:
   ```bash
   touch acme.json
   chmod 600 acme.json
   ```
2. Crea la red externa:
   ```bash
   docker network create proxy
   ```

### 10.5 Cómo conectar un proyecto a Traefik (Ejemplo Fundación)

Dentro de `projects/fundacion-bnb/docker-compose.yml` agrega estos **labels** en el servicio que expone el puerto (normalmente nginx o el frontend):

```yaml
services:
  nginx:   # o "app" si usas Laravel Octane
    image: ...
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.fundacion.rule=Host(`fundacion.tudominio.com`)"
      - "traefik.http.routers.fundacion.entrypoints=websecure"
      - "traefik.http.routers.fundacion.tls=true"
      - "traefik.http.routers.fundacion.tls.certresolver=letsencrypt"
      - "traefik.http.services.fundacion.loadbalancer.server.port=80"   # puerto interno del contenedor
      - "traefik.http.routers.fundacion.middlewares=security-headers@file"  # opcional
    networks:
      - proxy      # ← importante

networks:
  proxy:
    external: true
```

### 10.6 Para el futuro Ecommerce

Usa exactamente el mismo patrón:

```yaml
- "traefik.http.routers.ecommerce.rule=Host(`tienda.tudominio.com`)"
- "traefik.http.routers.ecommerce.tls.certresolver=letsencrypt"
```

Cada uno tendrá su propio certificado SSL independiente.

### 10.7 Recomendaciones de Seguridad

- Usa primero el **servidor de staging** de Let's Encrypt mientras pruebas.
- Agrega middlewares de seguridad (rate limit, security headers, compress).
- Protege el dashboard de Traefik (no lo expongas públicamente).
- Nunca expongas puertos de bases de datos (MySQL/PostgreSQL) al exterior.
- Mantén actualizado Traefik y las imágenes de tus proyectos.