# Reporte de Actualización: Módulo de Campañas y Transparencia

## 1. Resumen de Cambios
Se reestructurará el módulo de transparencia y gestión de campañas para optimizar el rendimiento del servidor y centralizar la lógica de negocio. Se eliminan los procesos asíncronos y se adopta una estrategia de *Cache-aside* para los cálculos estadísticos.

## 2. Actualización de Requerimientos Funcionales (RF)

### RF-09 | Módulo de Transparencia (Modificado)
*   **Descripción:** Dashboard público con estadísticas de recaudación consolidadas.
*   **Cambio:** Se elimina el cálculo automático tras confirmar donaciones (post-payment). 
*   **Nueva Lógica:** El sistema calculará el total recaudado vs. meta mediante una estrategia de **Cache-aside** bajo demanda. La consulta se ejecutará solo cuando se acceda al dashboard público y se almacenará en caché (TTL: 30 min) para evitar consultas repetitivas a la base de datos transaccional.

### RF-11 | Integridad de Datos Financieros (Aclarado)
*   **Descripción:** El sistema debe garantizar que los reportes de transparencia sean de solo lectura para asegurar la veracidad del informe.
*   **Implementación:** La capa de servicios utilizada para el Dashboard público debe ser estrictamente *Read-only*. No debe haber lógica de escritura o mutación de estados en los servicios de visualización.

### RF-12 | Gestión y Visualización de Campañas (Nuevo)
*   **Descripción:** El sistema permitirá al Administrador gestionar el ciclo de vida completo de las campañas y su visualización en el frontend.
*   **CRUD (Filament):** Crear recurso para `Campaign` (meta financiera, fechas, descripción, imagen destacada, status).
*   **Frontend Público:** Desarrollar grid de campañas con `status = 'active'`.
*   **Lógica de Selección:** 
    *   Si existe `campaign_id` en la petición (ej. vía link), pre-seleccionar en el formulario de donación.
    *   Si no existe, asignar por defecto a "Fondo General".
*   **Visualización:** Cada tarjeta de campaña mostrará una barra de progreso calculada mediante el patrón definido en RF-09.

## 3. Requerimientos Eliminados
*   **RNF-04 (Sincronización de Pagos):** Queda eliminado. El sistema ya no está obligado a reflejar donaciones en 5 segundos. Se prioriza la estabilidad del servidor sobre la actualización en tiempo real estricta.

## 4. Instrucciones de Verificación para IA CLI
Antes de proceder con la codificación, por favor verifica lo siguiente en el codebase existente:

1.  **Estructura de Base de Datos:**
    *   Verifica que la tabla `campaigns` cuente con: `id`, `name`, `slug`, `type`, `description`, `currency_id`, `monetary_goal`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`.
    *   Confirma la existencia del campo `image_path` o `image_url` en `campaigns`. Si no existe, genera la migración.
    *   Verifica que `donations` y `donation_subscriptions` incluyan la relación `campaign_id`.
2.  **Dependencias:**
    *   Asegurar que el modelo `Campaign` esté correctamente mapeado en Filament.
3.  **Ejecución:**
    *   Tras verificar la estructura, proceder con la implementación del servicio de cálculo bajo demanda (Cache-aside) para las estadísticas y la vista de detalle de campañas.
# ESPECIFICACIÓN: NUEVO MÓDULO DE CAMPAÑAS Y AJUSTE DE TRANSPARENCIA

## 1. Objetivo
Implementar el módulo de Campañas (RF-12) y optimizar el Módulo de Transparencia (RF-09, RF-11) usando un patrón de caché, eliminando el RNF-04.

## 2. Definición Técnica del Cambio
*   **RF-09 (Transparencia):** Cambiar de cálculo automático (eventos/jobs) a cálculo bajo demanda (*Cache-aside*).
*   **RF-11 (Integridad):** Asegurar que las consultas de transparencia sean solo lectura.
*   **RF-12 (Campañas):** CRUD en Filament para gestionar campañas con: nombre, slug, meta, fechas y estatus.

## 3. Regla de Oro
Cualquier cambio debe ser por **extensión**. No modificar la lógica de los controladores de donación existentes, a menos que sea para inyectar el parámetro `campaign_id`.