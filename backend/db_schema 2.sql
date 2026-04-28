-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-04-2026 a las 16:43:36
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
-- Base de datos: `sistema_policial`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expedientes`
--

CREATE TABLE `expedientes` (
  `id` int(11) NOT NULL,
  `nro_expediente` varchar(50) NOT NULL,
  `anio` varchar(4) DEFAULT NULL,
  `expediente_origen` varchar(100) DEFAULT NULL,
  `anio_origen` varchar(4) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `tipo_oficio` varchar(100) DEFAULT NULL,
  `juzgado_origen` varchar(200) DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `dependencia` varchar(100) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `tipo_requerimiento` varchar(100) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `nro_informe_tecnico` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `expedientes`
--

INSERT INTO `expedientes` (`id`, `nro_expediente`, `anio`, `expediente_origen`, `anio_origen`, `fecha`, `fecha_vencimiento`, `tipo_oficio`, `juzgado_origen`, `responsable_id`, `dependencia`, `dependencia_id`, `tipo_requerimiento`, `resumen`, `observaciones`, `nro_informe_tecnico`, `estado`, `created_by`, `created_at`) VALUES
(1, 'EXP-001', NULL, NULL, NULL, '2026-04-19', NULL, 'Oficio Judicial', 'Juzgado Federal N°1', NULL, 'Ministerio de Seguridad', 1, 'Informe técnico', 'Solicitan informe pericial sobre el caso de homicidio', NULL, NULL, 'Elevado', NULL, '2026-04-21 15:12:12'),
(3, 'EXP-003', '2026', '', '', '2026-04-12', NULL, 'Oficio Administrativo', 'Juzgado de Garantías N° 2', NULL, 'Tribunales', 1, 'Informe Pericial', 'Solicitan relevamiento de información de antecedentes', '', '', '', NULL, '2026-04-21 15:12:12'),
(4, 'EXP-004', NULL, NULL, NULL, '2026-04-09', NULL, 'Oficio Judicial', 'Juzgado Federal N°2', NULL, 'Ministerio de Seguridad', 1, 'Peritaje', 'Solicitan peritaje balístico', NULL, NULL, 'Resuelto', NULL, '2026-04-21 15:12:12'),
(5, 'EXP-005', NULL, NULL, NULL, '2026-04-04', NULL, 'Oficio Fiscal', 'Juzgado Federal N°1', NULL, 'Jefatura de Policía', 1, 'Informe técnico', 'Requieren informe de antecedentes penales', NULL, NULL, 'Activo', NULL, '2026-04-21 15:12:12'),
(12, '12346', '2024', '9877', '2023', '2024-01-20', NULL, 'Oficio Fiscal', 'Juzgado Civil N° 3', NULL, NULL, NULL, 'Informe Pericial', NULL, NULL, NULL, 'En trámite', NULL, '2026-04-28 13:08:19'),
(13, '12347', '2024', '9878', '2023', '2024-02-01', NULL, 'Mandamiento', 'Juzgado de Garantías', NULL, NULL, NULL, 'Informe Social', NULL, NULL, NULL, 'Resuelto', NULL, '2026-04-28 13:08:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nro_expediente` (`nro_expediente`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_nro_expediente` (`nro_expediente`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_tipo_oficio` (`tipo_oficio`),
  ADD KEY `idx_juzgado_origen` (`juzgado_origen`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_expedientes_nro` (`nro_expediente`),
  ADD KEY `idx_expedientes_estado` (`estado`),
  ADD KEY `idx_expedientes_fecha` (`fecha`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD CONSTRAINT `expedientes_ibfk_1` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expedientes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
