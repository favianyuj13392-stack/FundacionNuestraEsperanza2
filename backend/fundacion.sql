personasprueba_finalprueba_final-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2025 a las 03:53:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fundacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id_audit` bigint(20) UNSIGNED NOT NULL,
  `entidad` varchar(60) NOT NULL,
  `entidad_id` varchar(60) NOT NULL,
  `accion` enum('insert','update','delete') NOT NULL,
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `cambios` longtext DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campanias`
--

CREATE TABLE `campanias` (
  `id_campania` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `descripcion` varchar(1024) DEFAULT NULL,
  `id_tipo_moneda` int(10) UNSIGNED NOT NULL,
  `objetivo_monetario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado_campania` enum('borrador','activa','finalizada','oculta') NOT NULL DEFAULT 'activa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificados`
--

CREATE TABLE `certificados` (
  `id_certificado` bigint(20) UNSIGNED NOT NULL,
  `id_donacion` bigint(20) UNSIGNED NOT NULL,
  `folio` varchar(50) NOT NULL,
  `pdf_url` varchar(255) NOT NULL,
  `emitido_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id_contacto` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidades`
--

CREATE TABLE `disponibilidades` (
  `id_disponibilidad` tinyint(3) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `etiqueta` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones`
--

CREATE TABLE `donaciones` (
  `id_donacion` bigint(20) UNSIGNED NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_campania` int(10) UNSIGNED DEFAULT NULL,
  `id_donante` bigint(20) UNSIGNED DEFAULT NULL,
  `id_qr` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tipo_moneda` int(10) UNSIGNED NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `estado` enum('pending','succeeded','failed','refunded','canceled') NOT NULL DEFAULT 'pending',
  `proveedor` varchar(40) NOT NULL,
  `id_pago_proveedor` varchar(191) DEFAULT NULL,
  `id_suscripcion_proveedor` varchar(191) DEFAULT NULL,
  `es_recurrente` tinyint(1) NOT NULL DEFAULT 0,
  `es_anonima` tinyint(1) NOT NULL DEFAULT 0,
  `tarifa` decimal(12,2) DEFAULT NULL,
  `neto` decimal(12,2) DEFAULT NULL,
  `canal` varchar(40) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones_en_especie`
--

CREATE TABLE `donaciones_en_especie` (
  `id_especie` bigint(20) UNSIGNED NOT NULL,
  `id_donante` bigint(20) UNSIGNED DEFAULT NULL,
  `id_campania` int(10) UNSIGNED DEFAULT NULL,
  `item` varchar(150) NOT NULL,
  `unidad` varchar(30) DEFAULT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `valor_estimado` decimal(12,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones_suscripciones`
--

CREATE TABLE `donaciones_suscripciones` (
  `id_suscripcion` bigint(20) UNSIGNED NOT NULL,
  `id_donante` bigint(20) UNSIGNED NOT NULL,
  `id_campania` int(10) UNSIGNED DEFAULT NULL,
  `proveedor` varchar(40) NOT NULL,
  `id_suscripcion_proveedor` varchar(191) NOT NULL,
  `intervalo` enum('mensual','trimestral','anual') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `id_tipo_moneda` int(10) UNSIGNED NOT NULL,
  `estado` enum('active','paused','canceled') NOT NULL DEFAULT 'active',
  `fecha_inicio` date NOT NULL,
  `fecha_cancelacion` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donantes`
--

CREATE TABLE `donantes` (
  `id_donante` bigint(20) UNSIGNED NOT NULL,
  `persona_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `pais` char(2) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `marketing_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_campania` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `media_assets`
--

CREATE TABLE `media_assets` (
  `id_media` int(10) UNSIGNED NOT NULL,
  `tipo` enum('imagen','video','documento','otro') NOT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `url` varchar(512) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `creado_por` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_18_063625_create_personal_access_tokens_table', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(10) UNSIGNED NOT NULL,
  `id_persona` int(10) UNSIGNED DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages`
--

CREATE TABLE `pages` (
  `id_page` int(10) UNSIGNED NOT NULL,
  `slug` varchar(160) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` longtext DEFAULT NULL,
  `estado` enum('borrador','publicado','archivado') NOT NULL DEFAULT 'borrador',
  `publicado_en` timestamp NULL DEFAULT NULL,
  `autor_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participacion`
--

CREATE TABLE `participacion` (
  `id_participacion` int(10) UNSIGNED NOT NULL,
  `id_evento` int(10) UNSIGNED DEFAULT NULL,
  `id_voluntario` int(10) UNSIGNED DEFAULT NULL,
  `id_programa` int(10) UNSIGNED DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Persona', 1, 'api', '59a35ca87689e762920fb718b071001a4eb0026614be3ebd5350f03efa3d9cac', '[\"*\"]', NULL, NULL, '2025-10-18 10:38:48', '2025-10-18 10:38:48'),
(2, 'App\\Models\\Persona', 1, 'api', 'be98041ad7d2362ea67dbf80f909c0346fa1d0b7e42022293ad33d10bae176d2', '[\"*\"]', NULL, NULL, '2025-10-19 01:21:25', '2025-10-19 01:21:25'),
(3, 'App\\Models\\Persona', 1, 'api', '56802af4fa9f8b7d1090be4a3bcf80720633836820dc7c25e4eb5015c7f6b7c0', '[\"*\"]', NULL, NULL, '2025-10-21 08:05:36', '2025-10-21 08:05:36'),
(4, 'App\\Models\\Persona', 1, 'api', 'd1aa0d8b237317aa2829d18a892c8e466d528c6c450e6fe3552d1a69348df8ed', '[\"*\"]', NULL, NULL, '2025-10-23 06:43:24', '2025-10-23 06:43:24'),
(5, 'App\\Models\\Persona', 1, 'api', '8a8da340ba91076e495191b819a4586bc90518fe05f3fca5388eeb29619b8f34', '[\"*\"]', NULL, NULL, '2025-10-23 07:04:17', '2025-10-23 07:04:17'),
(6, 'App\\Models\\Persona', 2, 'api', '4d606445463cf43be6726f85c4ac6e27ca8d99c16342bf23277064a7c5e0ea1f', '[\"*\"]', NULL, NULL, '2025-10-23 07:04:52', '2025-10-23 07:04:52'),
(7, 'App\\Models\\Persona', 1, 'api', '0ab1fc44026ddeb33bd4ad0afc163f256d757b89ef1b26606fcffcc7d2331917', '[\"*\"]', NULL, NULL, '2025-11-12 06:42:12', '2025-11-12 06:42:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id_persona` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULLpersonaspersonaspersonas,
  `correo_electronico` varchar(150) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `ci` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id_persona`, `nombre`, `apellido_paterno`, `apellido_materno`, `correo_electronico`, `contrasenia`, `ci`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Super', 'Admin', NULL, 'admin@fundacion.org', '$2y$12$hunSFmJecxxjLHinlulk9.yelR/KqovArLbX3YHziH2NqiDEXVRKK', NULL, 1, '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(2, 'Juan', 'Perez', NULL, 'juan.perez@ejemplo.com', '$2y$12$NMee0XiBw9sVgYCdJAjlUOEXssbFNP90m7Z7XndvH06OMM20vyA4a', '1234567 LP', 1, '2025-10-23 07:04:52', '2025-10-23 07:04:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas_testimonios`
--

CREATE TABLE `personas_testimonios` (
  `id_pers_test` int(10) UNSIGNED NOT NULL,
  `id_testimonio` int(10) UNSIGNED DEFAULT NULL,
  `id_persona` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona_rol`
--

CREATE TABLE `persona_rol` (
  `id_persona` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `asignado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `persona_rol`
--

INSERT INTO `persona_rol` (`id_persona`, `id_rol`, `asignado_en`) VALUES
(1, 1, '2025-10-18 03:43:58'),
(2, 4, '2025-10-23 03:04:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `posts`
--

CREATE TABLE `posts` (
  `id_post` int(10) UNSIGNED NOT NULL,
  `slug` varchar(160) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `resumen` varchar(500) DEFAULT NULL,
  `contenido` longtext DEFAULT NULL,
  `estadojobs` enum('borrador','publicado','archivado') NOT NULL DEFAULT 'borrador',
  `publicado_en` timestamp NULL DEFAULT NULL,
  `autor_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post_media`
--

CREATE TABLE `post_media` (
  `id_post` int(10) UNSIGNED NOT NULL,
  `id_media` int(10) UNSIGNED NOT NULL,
  `posicion` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post_tags`
--

CREATE TABLE `post_tags` (
  `id_post` int(10) UNSIGNED NOT NULL,
  `id_tag` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas`
--

CREATE TABLE `programas` (
  `id_programa` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qrs`
--

CREATE TABLE `qrs` (
  `id_qr` bigint(20) UNSIGNED NOT NULL,
  `id_campania` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(512) NOT NULL,
  `etiqueta` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Acceso total', '2025-10-18 07:43:57', '2025-10-18 07:43:57'),
(2, 'editor', 'CMS y campañas', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(3, 'tesorero', 'Finanzas y donaciones', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(4, 'viewer', 'Solo lectura', '2025-10-18 07:43:58', '2025-10-18 07:43:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `id_tag` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `slug` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `testimonios`
--

CREATE TABLE `testimonios` (
  `id_testimonio` int(10) UNSIGNED NOT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_monedas`
--

CREATE TABLE `tipos_monedas` (
  `id_tipo_moneda` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(3) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `simbolo` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id_video` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `link` varchar(512) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos_testimonios`
--

CREATE TABLE `videos_testimonios` (
  `id_vt` int(10) UNSIGNED NOT NULL,
  `id_testimonio` int(10) UNSIGNED DEFAULT NULL,
  `id_video` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `voluntarios`
--

CREATE TABLE `voluntarios` (
  `id_voluntario` int(10) UNSIGNED NOT NULL,
  `id_persona` int(10) UNSIGNED DEFAULT NULL,
  `fecha_de_incorporacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_disponibilidad` tinyint(3) UNSIGNED DEFAULT NULL,
  `habilidades` varchar(255) DEFAULT NULL,
  `estado` enum('nuevo','contactado','aceptado','rechazado') NOT NULL DEFAULT 'nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_campanias_progreso`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_campanias_progreso` (
`id_campania` int(10) unsigned
,`nombre` varchar(100)
,`slug` varchar(120)
,`objetivo_monetario` decimal(12,2)
,`id_tipo_moneda` int(10) unsigned
,`recaudado` decimal(34,2)
,`avance` decimal(40,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_historial_donante`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_historial_donante` (
`id_donacion` bigint(20) unsigned
,`id_donante` bigint(20) unsigned
,`email` varchar(255)
,`fecha` timestamp
,`monto` decimal(12,2)
,`estado` enum('pending','succeeded','failed','refunded','canceled')
,`proveedor` varchar(40)
,`id_pago_proveedor` varchar(191)
,`id_campania` int(10) unsigned
,`campania` varchar(100)
,`es_anonima` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_resumen_diario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_resumen_diario` (
`fecha` date
,`cantidad` bigint(21)
,`monto_exitoso` decimal(34,2)
,`fallidas` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_suscripciones_activas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_suscripciones_activas` (
`id_suscripcion` bigint(20) unsigned
,`id_donante` bigint(20) unsigned
,`id_campania` int(10) unsigned
,`proveedor` varchar(40)
,`id_suscripcion_proveedor` varchar(191)
,`intervalo` enum('mensual','trimestral','anual')
,`monto` decimal(12,2)
,`id_tipo_moneda` int(10) unsigned
,`estado` enum('active','paused','canceled')
,`fecha_inicio` date
,`fecha_cancelacion` date
,`created_at` timestamp
,`updated_at` timestamp
,`email` varchar(255)
,`campania` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `webhooks`
--

CREATE TABLE `webhooks` (
  `id_webhook` bigint(20) UNSIGNED NOT NULL,
  `proveedor` varchar(40) NOT NULL,
  `id_evento_externo` varchar(191) NOT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `firma` varchar(191) DEFAULT NULL,
  `payload` longtext NOT NULL,
  `recibido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `procesado_en` timestamp NULL DEFAULT NULL,
  `estado_proceso` enum('pending','processed','failed','ignored') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_campanias_progreso`
--
DROP TABLE IF EXISTS `vw_campanias_progreso`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_campanias_progreso`  AS SELECT `c`.`id_campania` AS `id_campania`, `c`.`nombre` AS `nombre`, `c`.`slug` AS `slug`, `c`.`objetivo_monetario` AS `objetivo_monetario`, `c`.`id_tipo_moneda` AS `id_tipo_moneda`, coalesce(sum(case when `d`.`estado` = 'succeeded' then `d`.`monto` else 0 end),0) AS `recaudado`, coalesce(sum(case when `d`.`estado` = 'succeeded' then `d`.`monto` else 0 end),0) / nullif(`c`.`objetivo_monetario`,0) AS `avance` FROM (`campanias` `c` left join `donaciones` `d` on(`d`.`id_campania` = `c`.`id_campania`)) GROUP BY `c`.`id_campania` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_historial_donante`
--
DROP TABLE IF EXISTS `vw_historial_donante`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historial_donante`  AS SELECT `d`.`id_donacion` AS `id_donacion`, `d`.`id_donante` AS `id_donante`, `dn`.`email` AS `email`, `d`.`fecha` AS `fecha`, `d`.`monto` AS `monto`, `d`.`estado` AS `estado`, `d`.`proveedor` AS `proveedor`, `d`.`id_pago_proveedor` AS `id_pago_proveedor`, `d`.`id_campania` AS `id_campania`, `c`.`nombre` AS `campania`, `d`.`es_anonima` AS `es_anonima` FROM ((`donaciones` `d` left join `donantes` `dn` on(`dn`.`id_donante` = `d`.`id_donante`)) left join `campanias` `c` on(`c`.`id_campania` = `d`.`id_campania`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_resumen_diario`
--
DROP TABLE IF EXISTS `vw_resumen_diario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_resumen_diario`  AS SELECT cast(`d`.`fecha` as date) AS `fecha`, count(0) AS `cantidad`, sum(case when `d`.`estado` = 'succeeded' then `d`.`monto` else 0 end) AS `monto_exitoso`, sum(case when `d`.`estado` = 'failed' then 1 else 0 end) AS `fallidas` FROM `donaciones` AS `d` GROUP BY cast(`d`.`fecha` as date) ORDER BY cast(`d`.`fecha` as date) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_suscripciones_activas`
--
DROP TABLE IF EXISTS `vw_suscripciones_activas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_suscripciones_activas`  AS SELECT `s`.`id_suscripcion` AS `id_suscripcion`, `s`.`id_donante` AS `id_donante`, `s`.`id_campania` AS `id_campania`, `s`.`proveedor` AS `proveedor`, `s`.`id_suscripcion_proveedor` AS `id_suscripcion_proveedor`, `s`.`intervalo` AS `intervalo`, `s`.`monto` AS `monto`, `s`.`id_tipo_moneda` AS `id_tipo_moneda`, `s`.`estado` AS `estado`, `s`.`fecha_inicio` AS `fecha_inicio`, `s`.`fecha_cancelacion` AS `fecha_cancelacion`, `s`.`created_at` AS `created_at`, `s`.`updated_at` AS `updated_at`, `dn`.`email` AS `email`, `c`.`nombre` AS `campania` FROM ((`donaciones_suscripciones` `s` left join `donantes` `dn` on(`dn`.`id_donante` = `s`.`id_donante`)) left join `campanias` `c` on(`c`.`id_campania` = `s`.`id_campania`)) WHERE `s`.`estado` = 'active' ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id_audit`),
  ADD KEY `idx_audit_entidad` (`entidad`,`entidad_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `campanias`
--
ALTER TABLE `campanias`
  ADD PRIMARY KEY (`id_campania`),
  ADD UNIQUE KEY `uq_campania_slug` (`slug`),
  ADD KEY `idx_campania_estado` (`estado_campania`),
  ADD KEY `fk_campania_moneda` (`id_tipo_moneda`);

--
-- Indices de la tabla `certificados`
--
ALTER TABLE `certificados`
  ADD PRIMARY KEY (`id_certificado`),
  ADD UNIQUE KEY `uq_cert_folio` (`folio`),
  ADD UNIQUE KEY `uq_cert_donacion` (`id_donacion`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id_contacto`);

--
-- Indices de la tabla `disponibilidades`
--
ALTER TABLE `disponibilidades`
  ADD PRIMARY KEY (`id_disponibilidad`),
  ADD UNIQUE KEY `uq_disponibilidad_codigo` (`codigo`);

--
-- Indices de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD PRIMARY KEY (`id_donacion`),
  ADD UNIQUE KEY `uq_pago_proveedor` (`proveedor`,`id_pago_proveedor`),
  ADD KEY `idx_donaciones_campania_estado_fecha` (`id_campania`,`estado`,`fecha`),
  ADD KEY `idx_donaciones_donante_fecha` (`id_donante`,`fecha`),
  ADD KEY `fk_donacion_moneda` (`id_tipo_moneda`),
  ADD KEY `fk_donacion_qr` (`id_qr`);

--
-- Indices de la tabla `donaciones_en_especie`
--
ALTER TABLE `donaciones_en_especie`
  ADD PRIMARY KEY (`id_especie`),
  ADD KEY `idx_especie_campania` (`id_campania`),
  ADD KEY `fk_especie_donante` (`id_donante`);

--
-- Indices de la tabla `donaciones_suscripciones`
--
ALTER TABLE `donaciones_suscripciones`
  ADD PRIMARY KEY (`id_suscripcion`),
  ADD UNIQUE KEY `uq_suscripcion_proveedor` (`proveedor`,`id_suscripcion_proveedor`),
  ADD KEY `idx_susc_donante` (`id_donante`),
  ADD KEY `fk_susc_campania` (`id_campania`),
  ADD KEY `fk_susc_moneda` (`id_tipo_moneda`);

--
-- Indices de la tabla `donantes`
--
ALTER TABLE `donantes`
  ADD PRIMARY KEY (`id_donante`),
  ADD KEY `idx_donantes_email` (`email`),
  ADD KEY `fk_donante_persona` (`persona_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `idx_evento_campania` (`id_campania`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `media_assets`
--
ALTER TABLE `media_assets`
  ADD PRIMARY KEY (`id_media`),
  ADD KEY `idx_media_tipo` (`tipo`),
  ADD KEY `fk_media_autor` (`creado_por`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_notif_persona` (`id_persona`);

--
-- Indices de la tabla `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id_page`),
  ADD UNIQUE KEY `uq_pages_slug` (`slug`),
  ADD KEY `fk_pages_autor` (`autor_id`);

--
-- Indices de la tabla `participacion`
--
ALTER TABLE `participacion`
  ADD PRIMARY KEY (`id_participacion`),
  ADD KEY `idx_part_evento` (`id_evento`),
  ADD KEY `idx_part_voluntario` (`id_voluntario`),
  ADD KEY `idx_part_programa` (`id_programa`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `uq_personas_email` (`correo_electronico`);

--
-- Indices de la tabla `personas_testimonios`
--
ALTER TABLE `personas_testimonios`
  ADD PRIMARY KEY (`id_pers_test`),
  ADD KEY `idx_pt_testimonio` (`id_testimonio`),
  ADD KEY `idx_pt_persona` (`id_persona`);

--
-- Indices de la tabla `persona_rol`
--
ALTER TABLE `persona_rol`
  ADD PRIMARY KEY (`id_persona`,`id_rol`),
  ADD KEY `fk_pr_rol` (`id_rol`);

--
-- Indices de la tabla `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id_post`),
  ADD UNIQUE KEY `uq_posts_slug` (`slug`),
  ADD KEY `idx_posts_estado_publicado` (`estado`,`publicado_en`),
  ADD KEY `fk_posts_autor` (`autor_id`);

--
-- Indices de la tabla `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id_post`,`id_media`),
  ADD KEY `fk_pm_media` (`id_media`);

--
-- Indices de la tabla `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id_post`,`id_tag`),
  ADD KEY `fk_pt_tag` (`id_tag`);

--
-- Indices de la tabla `programas`
--
ALTER TABLE `programas`
  ADD PRIMARY KEY (`id_programa`);

--
-- Indices de la tabla `qrs`
--
ALTER TABLE `qrs`
  ADD PRIMARY KEY (`id_qr`),
  ADD KEY `idx_qr_campania` (`id_campania`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id_tag`),
  ADD UNIQUE KEY `uq_tags_slug` (`slug`),
  ADD UNIQUE KEY `uq_tags_nombre` (`nombre`);

--
-- Indices de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  ADD PRIMARY KEY (`id_testimonio`);

--
-- Indices de la tabla `tipos_monedas`
--
ALTER TABLE `tipos_monedas`
  ADD PRIMARY KEY (`id_tipo_moneda`),
  ADD UNIQUE KEY `uq_monedas_codigo` (`codigo`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id_video`);

--
-- Indices de la tabla `videos_testimonios`
--
ALTER TABLE `videos_testimonios`
  ADD PRIMARY KEY (`id_vt`),
  ADD KEY `idx_vt_testimonio` (`id_testimonio`),
  ADD KEY `idx_vt_video` (`id_video`);

--
-- Indices de la tabla `voluntarios`
--
ALTER TABLE `voluntarios`
  ADD PRIMARY KEY (`id_voluntario`),
  ADD UNIQUE KEY `uq_vol_persona` (`id_persona`),
  ADD KEY `fk_vol_dispo` (`id_disponibilidad`);

--
-- Indices de la tabla `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id_webhook`),
  ADD UNIQUE KEY `uq_webhook_externo` (`proveedor`,`id_evento_externo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id_audit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `campanias`
--
ALTER TABLE `campanias`
  MODIFY `id_campania` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certificados`
--
ALTER TABLE `certificados`
  MODIFY `id_certificado` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_contacto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `disponibilidades`
--
ALTER TABLE `disponibilidades`
  MODIFY `id_disponibilidad` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  MODIFY `id_donacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `donaciones_en_especie`
--
ALTER TABLE `donaciones_en_especie`
  MODIFY `id_especie` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `donaciones_suscripciones`
--
ALTER TABLE `donaciones_suscripciones`
  MODIFY `id_suscripcion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `donantes`
--
ALTER TABLE `donantes`
  MODIFY `id_donante` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_evento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `media_assets`
--
ALTER TABLE `media_assets`
  MODIFY `id_media` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pages`
--
ALTER TABLE `pages`
  MODIFY `id_page` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `participacion`
--
ALTER TABLE `participacion`
  MODIFY `id_participacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `personas_testimonios`
--
ALTER TABLE `personas_testimonios`
  MODIFY `id_pers_test` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `posts`
--
ALTER TABLE `posts`
  MODIFY `id_post` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `programas`
--
ALTER TABLE `programas`
  MODIFY `id_programa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `qrs`
--
ALTER TABLE `qrs`
  MODIFY `id_qr` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `id_tag` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `testimonios`
--
ALTER TABLE `testimonios`
  MODIFY `id_testimonio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_monedas`
--
ALTER TABLE `tipos_monedas`
  MODIFY `id_tipo_moneda` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id_video` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `videos_testimonios`
--
ALTER TABLE `videos_testimonios`
  MODIFY `id_vt` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `voluntarios`
--
ALTER TABLE `voluntarios`
  MODIFY `id_voluntario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id_webhook` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `campanias`
--
ALTER TABLE `campanias`
  ADD CONSTRAINT `fk_campania_moneda` FOREIGN KEY (`id_tipo_moneda`) REFERENCES `tipos_monedas` (`id_tipo_moneda`);

--
-- Filtros para la tabla `certificados`
--
ALTER TABLE `certificados`
  ADD CONSTRAINT `fk_cert_donacion` FOREIGN KEY (`id_donacion`) REFERENCES `donaciones` (`id_donacion`);

--
-- Filtros para la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD CONSTRAINT `fk_donacion_campania` FOREIGN KEY (`id_campania`) REFERENCES `campanias` (`id_campania`),
  ADD CONSTRAINT `fk_donacion_donante` FOREIGN KEY (`id_donante`) REFERENCES `donantes` (`id_donante`),
  ADD CONSTRAINT `fk_donacion_moneda` FOREIGN KEY (`id_tipo_moneda`) REFERENCES `tipos_monedas` (`id_tipo_moneda`),
  ADD CONSTRAINT `fk_donacion_qr` FOREIGN KEY (`id_qr`) REFERENCES `qrs` (`id_qr`);

--
-- Filtros para la tabla `donaciones_en_especie`
--
ALTER TABLE `donaciones_en_especie`
  ADD CONSTRAINT `fk_especie_campania` FOREIGN KEY (`id_campania`) REFERENCES `campanias` (`id_campania`),
  ADD CONSTRAINT `fk_especie_donante` FOREIGN KEY (`id_donante`) REFERENCES `donantes` (`id_donante`);

--
-- Filtros para la tabla `donaciones_suscripciones`
--
ALTER TABLE `donaciones_suscripciones`
  ADD CONSTRAINT `fk_susc_campania` FOREIGN KEY (`id_campania`) REFERENCES `campanias` (`id_campania`),
  ADD CONSTRAINT `fk_susc_donante` FOREIGN KEY (`id_donante`) REFERENCES `donantes` (`id_donante`),
  ADD CONSTRAINT `fk_susc_moneda` FOREIGN KEY (`id_tipo_moneda`) REFERENCES `tipos_monedas` (`id_tipo_moneda`);

--
-- Filtros para la tabla `donantes`
--
ALTER TABLE `donantes`
  ADD CONSTRAINT `fk_donante_persona` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`);

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_campania` FOREIGN KEY (`id_campania`) REFERENCES `campanias` (`id_campania`);

--
-- Filtros para la tabla `media_assets`
--
ALTER TABLE `media_assets`
  ADD CONSTRAINT `fk_media_autor` FOREIGN KEY (`creado_por`) REFERENCES `personas` (`id_persona`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`);

--
-- Filtros para la tabla `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `fk_pages_autor` FOREIGN KEY (`autor_id`) REFERENCES `personas` (`id_persona`);

--
-- Filtros para la tabla `participacion`
--
ALTER TABLE `participacion`
  ADD CONSTRAINT `fk_part_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`),
  ADD CONSTRAINT `fk_part_programa` FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id_programa`),
  ADD CONSTRAINT `fk_part_voluntario` FOREIGN KEY (`id_voluntario`) REFERENCES `voluntarios` (`id_voluntario`);

--
-- Filtros para la tabla `personas_testimonios`
--
ALTER TABLE `personas_testimonios`
  ADD CONSTRAINT `fk_pt_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`),
  ADD CONSTRAINT `fk_pt_testimonio` FOREIGN KEY (`id_testimonio`) REFERENCES `testimonios` (`id_testimonio`);

--
-- Filtros para la tabla `persona_rol`
--
ALTER TABLE `persona_rol`
  ADD CONSTRAINT `fk_pr_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pr_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE;

--
-- Filtros para la tabla `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_autor` FOREIGN KEY (`autor_id`) REFERENCES `personas` (`id_persona`);

--
-- Filtros para la tabla `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `fk_pm_media` FOREIGN KEY (`id_media`) REFERENCES `media_assets` (`id_media`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pm_post` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON DELETE CASCADE;

--
-- Filtros para la tabla `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `fk_pt_post` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pt_tag` FOREIGN KEY (`id_tag`) REFERENCES `tags` (`id_tag`) ON DELETE CASCADE;

--
-- Filtros para la tabla `qrs`
--
ALTER TABLE `qrs`
  ADD CONSTRAINT `fk_qr_campania` FOREIGN KEY (`id_campania`) REFERENCES `campanias` (`id_campania`);

--
-- Filtros para la tabla `videos_testimonios`
--
ALTER TABLE `videos_testimonios`
  ADD CONSTRAINT `fk_vt_testimonio` FOREIGN KEY (`id_testimonio`) REFERENCES `testimonios` (`id_testimonio`),
  ADD CONSTRAINT `fk_vt_video` FOREIGN KEY (`id_video`) REFERENCES `videos` (`id_video`);

--
-- Filtros para la tabla `voluntarios`
--
ALTER TABLE `voluntarios`
  ADD CONSTRAINT `fk_vol_dispo` FOREIGN KEY (`id_disponibilidad`) REFERENCES `disponibilidades` (`id_disponibilidad`),
  ADD CONSTRAINT `fk_vol_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
