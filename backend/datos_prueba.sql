SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. LIMPIEZA PREVIA (Opcional, para asegurar que insertamos en limpio)
-- -----------------------------------------------------------------------------
TRUNCATE TABLE certificates;
TRUNCATE TABLE donations;
TRUNCATE TABLE donors;
TRUNCATE TABLE role_user;
TRUNCATE TABLE roles;
TRUNCATE TABLE users;
TRUNCATE TABLE currencies;
-- TRUNCATE TABLE programs; -- No había datos en el dump original
-- TRUNCATE TABLE campaigns; -- No había datos vinculados en el dump original

-- -----------------------------------------------------------------------------
-- 2. MIGRACIÓN DE USUARIOS (personas -> users)
-- -----------------------------------------------------------------------------
INSERT INTO users (id, name, last_name, email, password, ci, is_active, created_at, updated_at) VALUES
(1, 'Super', 'Admin', 'admin@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '1234567', 1, '2025-10-18 07:43:56', '2025-10-21 17:34:02'),
(2, 'Editor', 'Test', 'editor@fundacion.org', '$2y$12$W9yH5H5D1xX5G0cT0kL0L.Q.7u2q8f3s0c0c0c0c0c0c0c0c0c0c', '7654321', 1, '2025-10-18 07:43:56', '2025-10-18 07:43:56'),
(3, 'Tesorero', 'Test', 'panel.test@fundacion.org', '$2y$12$W9yH5H5D1xX5G0cT0kL0L.Q.7u2q8f3s0c0c0c0c0c0c0c0c0c0c', '9876543', 1, '2025-10-18 07:43:56', '2025-10-18 07:43:56'),
(4, 'Donante', 'Prueba', 'donante.prueba@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '11111111', 1, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
(5, 'User', 'Deleted', 'user.deleted@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '22222222', 0, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
(6, 'Otro', 'Usuario', 'otro.usuario@fundacion.org', '$2y$12$Bi6ZA1JWiTskN/Yl757.8.H356z3L9bF.Wk80gP9qPqP5P/w.yP.w', '33333333', 1, '2025-11-25 22:42:28', '2025-11-25 22:42:28');

-- -----------------------------------------------------------------------------
-- 3. MIGRACIÓN DE ROLES (roles -> roles)
-- -----------------------------------------------------------------------------
INSERT INTO roles (id, name, description, created_at, updated_at) VALUES
(1, 'admin', 'Acceso total', '2025-10-18 07:43:57', '2025-10-18 07:43:57'),
(2, 'editor', 'CMS y campañas', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(3, 'tesorero', 'Finanzas y donaciones', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(4, 'viewer', 'Solo lectura', '2025-10-18 07:43:58', '2025-10-18 07:43:58'),
(5, 'donante', 'Usuario con capacidad de ver historial', '2025-11-12 20:18:21', '2025-11-12 20:18:21');

-- -----------------------------------------------------------------------------
-- 4. ASIGNACIÓN DE ROLES (person_roles -> role_user)
-- -----------------------------------------------------------------------------
INSERT INTO role_user (user_id, role_id) VALUES
(1, 1), -- Super Admin -> admin
(2, 2), -- Editor -> editor
(3, 3), -- Tesorero -> tesorero
(6, 4), -- Otro -> viewer
(4, 5); -- Donante -> donante

-- -----------------------------------------------------------------------------
-- 5. CATÁLOGOS: MONEDAS (currency_types -> currencies)
-- -----------------------------------------------------------------------------
INSERT INTO currencies (id, name, iso_code, symbol) VALUES
(1, 'Dólar Estadounidense', 'USD', '$');

-- -----------------------------------------------------------------------------
-- 6. MIGRACIÓN DE DONANTES (donors -> donors)
-- -----------------------------------------------------------------------------
-- Mapeo: person_id -> user_id, name -> first_name, last_name -> last_name
INSERT INTO donors (id, user_id, first_name, last_name, email, phone, marketing_opt_in, created_at, updated_at) VALUES
(1, NULL, NULL, NULL, 'favian.prueba@correo.com', '77712345', 0, '2025-11-12 14:06:25', NULL),
(2, 3, 'Usuario', 'Panel', 'panel.test@fundacion.org', '777-DONA-TEST', 0, '2025-11-12 20:18:21', '2025-11-12 20:18:21'),
(3, NULL, 'Donante', 'Masivo', 'donante.masivo@prueba.com', '99988877', 0, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(4, 4, 'Donante', 'Prueba', 'donante.prueba@fundacion.org', '77777777', 0, '2025-11-25 22:42:28', '2025-11-25 22:42:28');

-- -----------------------------------------------------------------------------
-- 7. MIGRACIÓN DE DONACIONES (donations -> donations)
-- -----------------------------------------------------------------------------
-- Nota: 'campaign_id' era NULL en todos los registros del dump original
INSERT INTO donations (id, date, campaign_id, donor_id, currency_id, amount, status, provider, provider_payment_id, is_recurring, is_anonymous, fee, net_amount, created_at, updated_at) VALUES
(1, '2025-11-12 19:10:36', NULL, 1, 1, 250.00, 'succeeded', 'test', 'test_6914a36c9a222', 0, 0, NULL, NULL, '2025-11-12 19:10:36', '2025-11-12 19:10:36'),
(2, '2025-11-12 20:18:21', NULL, 2, 1, 300.00, 'succeeded', 'test_auth', 'auth_test_6914b34dd9cf5', 0, 0, NULL, NULL, '2025-11-12 20:18:21', '2025-11-12 20:18:21'),
(3, '2025-11-12 16:28:30', NULL, 2, 1, 500.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_ca9ada46-c0ad-11f0-9097-30e37a1a6bf6', 0, 0, 15.00, 485.00, '2025-11-13 16:28:30', '2025-11-13 16:28:30'),
(4, '2025-11-03 16:28:30', NULL, 2, 1, 150.75, 'succeeded', 'SQL_TEST', 'SQL_TEST_caa2e1fa-c0ad-11f0-9097-30e37a1a6bf6', 0, 0, 10.00, 140.75, '2025-11-13 16:28:30', '2025-11-13 16:28:30'),
(5, '2025-10-14 16:28:31', NULL, 2, 1, 1200.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_caa919b0-c0ad-11f0-9097-30e37a1a6bf6', 0, 1, 50.00, 1150.00, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
(6, '2025-11-08 16:28:31', NULL, 2, 1, 200.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_caaf37d8-c0ad-11f0-9097-30e37a1a6bf6', 1, 0, 10.00, 190.00, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
(7, '2025-10-24 16:28:31', NULL, 2, 1, 100.00, 'succeeded', 'SQL_TEST', 'SQL_TEST_cab53e44-c0ad-11f0-9097-30e37a1a6bf6', 0, 0, 5.00, 95.00, '2025-11-13 16:28:31', '2025-11-13 16:28:31'),
(8, '2025-11-13 22:33:48', NULL, 1, 1, 250.00, 'succeeded', 'test', 'test_6916248ce4262', 0, 0, NULL, NULL, '2025-11-13 22:33:48', '2025-11-13 22:33:48'),
(9, '2025-11-13 23:30:57', NULL, 3, 1, 351.00, 'succeeded', 'test_masivo', 'mass_691631f1717f9', 0, 0, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(10, '2025-11-13 23:30:57', NULL, 3, 1, 352.00, 'succeeded', 'test_masivo', 'mass_691631f17a67d', 0, 0, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(11, '2025-11-13 23:30:57', NULL, 3, 1, 353.00, 'succeeded', 'test_masivo', 'mass_691631f17e9f4', 0, 0, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(12, '2025-11-13 23:30:57', NULL, 3, 1, 354.00, 'succeeded', 'test_masivo', 'mass_691631f182316', 0, 0, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(13, '2025-11-13 23:30:57', NULL, 3, 1, 355.00, 'succeeded', 'test_masivo', 'mass_691631f1840cc', 0, 0, NULL, NULL, '2025-11-13 23:30:57', '2025-11-13 23:30:57'),
(14, '2025-11-13 23:40:26', NULL, 2, 1, 351.00, 'succeeded', 'test_masivo', 'mass_6916342acf97e', 0, 0, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
(15, '2025-11-13 23:40:26', NULL, 2, 1, 352.00, 'succeeded', 'test_masivo', 'mass_6916342ad8073', 0, 0, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
(16, '2025-11-13 23:40:26', NULL, 2, 1, 353.00, 'succeeded', 'test_masivo', 'mass_6916342ad9736', 0, 0, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
(17, '2025-11-13 23:40:26', NULL, 2, 1, 354.00, 'succeeded', 'test_masivo', 'mass_6916342adb464', 0, 0, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
(18, '2025-11-13 23:40:26', NULL, 2, 1, 355.00, 'succeeded', 'test_masivo', 'mass_6916342add19f', 0, 0, NULL, NULL, '2025-11-13 23:40:26', '2025-11-13 23:40:26'),
(19, '2025-11-24 22:42:28', NULL, 4, 1, 100.00, 'succeeded', 'seed_script', 'seed_6925f894e011e', 0, 0, NULL, NULL, '2025-11-25 22:42:28', '2025-11-25 22:42:28'),
(20, '2025-11-23 22:42:32', NULL, 4, 1, 200.00, 'succeeded', 'seed_script', 'seed_6925f89842239', 0, 0, NULL, NULL, '2025-11-25 22:42:32', '2025-11-25 22:42:32'),
(21, '2025-11-22 22:42:32', NULL, 4, 1, 300.00, 'succeeded', 'seed_script', 'seed_6925f89844bf7', 0, 0, NULL, NULL, '2025-11-25 22:42:32', '2025-11-25 22:42:32'),
(22, '2025-11-24 22:57:29', NULL, 4, 1, 100.00, 'succeeded', 'seed_script', 'seed_6925fc19cf351', 0, 0, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29'),
(23, '2025-11-23 22:57:29', NULL, 4, 1, 200.00, 'succeeded', 'seed_script', 'seed_6925fc19ddfec', 0, 0, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29'),
(24, '2025-11-22 22:57:29', NULL, 4, 1, 300.00, 'succeeded', 'seed_script', 'seed_6925fc19dfdf2', 0, 0, NULL, NULL, '2025-11-25 22:57:29', '2025-11-25 22:57:29');

-- -----------------------------------------------------------------------------
-- 8. MIGRACIÓN DE CERTIFICADOS (certificates -> certificates)
-- -----------------------------------------------------------------------------
INSERT INTO certificates (id, donation_id, folio, pdf_url, issued_on) VALUES
(1, 1, 'c0b80ac9-62f8-4ab7-a9b1-51f355bf26fc', 'certificados/donacion-c0b80ac9-62f8-4ab7-a9b1-51f355bf26fc.pdf', '2025-11-12 15:10:43'),
(2, 2, '21e819ac-d6d4-4756-9129-42dd637a3fca', 'certificados/donacion-21e819ac-d6d4-4756-9129-42dd637a3fca.pdf', '2025-11-12 16:18:22'),
(3, 3, 'cabbe2e2-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-1.pdf', '2025-11-13 16:28:31'),
(4, 4, 'cabbfdb2-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-2.pdf', '2025-11-04 16:28:31'),
(5, 5, 'cabbff69-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-3.pdf', '2025-10-15 16:28:31'),
(6, 6, 'cabc0018-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-4.pdf', '2025-11-09 16:28:31'),
(7, 7, 'cabc00a8-c0ad-11f0-9097-30e37a1a6bf6', 'certificados/prueba-5.pdf', '2025-10-25 16:28:31'),
(8, 8, '33145b4d-4190-4386-9ea6-346b4cdfca17', 'certificados/donacion-33145b4d-4190-4386-9ea6-346b4cdfca17.pdf', '2025-11-13 22:33:57'),
(9, 9, '80f17cf6-53b5-4b2a-b480-7cf5a1fa2832', 'certificados/donacion-80f17cf6-53b5-4b2a-b480-7cf5a1fa2832.pdf', '2025-11-13 23:30:59'),
(10, 10, '4892abee-0b3b-4d4f-b7e8-ef442e1e8bf9', 'certificados/donacion-4892abee-0b3b-4d4f-b7e8-ef442e1e8bf9.pdf', '2025-11-13 23:30:59'),
(11, 11, 'e16771a7-551b-4cc6-8b0a-a44dad4ff7c7', 'certificados/donacion-e16771a7-551b-4cc6-8b0a-a44dad4ff7c7.pdf', '2025-11-13 23:30:59'),
(12, 12, '21c5162c-3773-412b-8c3d-143008b8b385', 'certificados/donacion-21c5162c-3773-412b-8c3d-143008b8b385.pdf', '2025-11-13 23:30:59'),
(13, 13, '32f8a4ed-14f4-4008-8a7e-3778ef9a6491', 'certificados/donacion-32f8a4ed-14f4-4008-8a7e-3778ef9a6491.pdf', '2025-11-13 23:30:59'),
(14, 14, '962ee6b5-330c-4c8c-9615-092154e0acb5', 'certificados/donacion-962ee6b5-330c-4c8c-9615-092154e0acb5.pdf', '2025-11-13 23:40:29'),
(15, 15, 'af59b27a-499c-4df2-a3f4-acf29f30afd7', 'certificados/donacion-af59b27a-499c-4df2-a3f4-acf29f30afd7.pdf', '2025-11-13 23:40:29'),
(16, 16, '2b641e1d-6c1a-4278-976b-b0a2d42c34c3', 'certificados/donacion-2b641e1d-6c1a-4278-976b-b0a2d42c34c3.pdf', '2025-11-13 23:40:29'),
(17, 17, '9ace0c3c-2aac-42f2-8c74-8567215e6bed', 'certificados/donacion-9ace0c3c-2aac-42f2-8c74-8567215e6bed.pdf', '2025-11-13 23:40:29'),
(18, 18, '4564f03b-244e-4183-8616-ed5ba14d91c2', 'certificados/donacion-4564f03b-244e-4183-8616-ed5ba14d91c2.pdf', '2025-11-13 23:40:29'),
(19, 19, '30de7df6-9f3c-4126-8cc9-5dd31c9a3ffd', 'certificados/donacion-30de7df6-9f3c-4126-8cc9-5dd31c9a3ffd.pdf', '2025-11-25 23:08:20'),
(20, 20, '4a08764e-3a85-4c4b-b811-6de3958be786', 'certificados/donacion-4a08764e-3a85-4c4b-b811-6de3958be786.pdf', '2025-11-25 23:08:21'),
(21, 21, '941b76ef-463e-4ef9-abee-41c6df80be53', 'certificados/donacion-941b76ef-463e-4ef9-abee-41c6df80be53.pdf', '2025-11-25 23:08:21'),
(22, 22, '93798775-c1af-4dfc-a3e4-afebcd4e9a62', 'certificados/donacion-93798775-c1af-4dfc-a3e4-afebcd4e9a62.pdf', '2025-11-25 23:08:21'),
(23, 23, '57a10bc2-15d3-4bc7-8d60-9b5204dd5e5e', 'certificados/donacion-57a10bc2-15d3-4bc7-8d60-9b5204dd5e5e.pdf', '2025-11-25 23:08:22'),
(24, 24, 'b9820f46-c29b-4459-9342-eace016fadf8', 'certificados/donacion-b9820f46-c29b-4459-9342-eace016fadf8.pdf', '2025-11-25 23:08:22');

SET FOREIGN_KEY_CHECKS = 1;