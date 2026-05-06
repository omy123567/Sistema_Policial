-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-05-2026 a las 14:41:07
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
-- Estructura de tabla para la tabla `alertas_config`
--

CREATE TABLE `alertas_config` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `dias_anticipacion` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alertas_config`
--

INSERT INTO `alertas_config` (`id`, `tipo`, `dias_anticipacion`, `activo`, `created_at`) VALUES
(1, 'licencias_vencer', 7, 1, '2026-04-21 15:12:12'),
(2, 'licencias_vencer', 15, 1, '2026-04-21 15:12:12'),
(3, 'licencias_vencer', 30, 1, '2026-04-21 15:12:12'),
(4, 'documentacion_vencida', 0, 1, '2026-04-21 15:12:12'),
(5, 'expedientes_vencer', 3, 1, '2026-04-21 15:12:12'),
(6, 'cumpleanos', 1, 1, '2026-04-21 15:12:12'),
(7, 'cumpleanos', 7, 1, '2026-04-21 15:12:12'),
(8, 'cumpleanos', 30, 1, '2026-04-21 15:12:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogos`
--

CREATE TABLE `catalogos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `valor` varchar(100) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `catalogos`
--

INSERT INTO `catalogos` (`id`, `tipo`, `valor`, `orden`, `activo`, `created_at`) VALUES
(31, 'tipos_licencia', 'Especial', 3, 1, '2026-04-21 15:12:12'),
(33, 'tipos_licencia', 'Maternidad/Paternidad', 5, 1, '2026-04-21 15:12:12'),
(53, 'tipos_licencia', 'Enfermedad', 2, 1, '2026-04-22 13:08:13'),
(69, 'licencia_categorias', 'B1', 1, 1, '2026-04-24 14:06:24'),
(70, 'licencia_categorias', 'B2', 2, 1, '2026-04-24 14:06:24'),
(71, 'licencia_categorias', 'B3', 3, 1, '2026-04-24 14:06:24'),
(72, 'licencia_categorias', 'C1', 4, 1, '2026-04-24 14:06:24'),
(73, 'licencia_categorias', 'C2', 5, 1, '2026-04-24 14:06:24'),
(74, 'licencia_categorias', 'C3', 6, 1, '2026-04-24 14:06:24'),
(75, 'licencia_categorias', 'D1', 7, 1, '2026-04-24 14:06:24'),
(76, 'licencia_categorias', 'D2', 8, 1, '2026-04-24 14:06:24'),
(77, 'licencia_categorias', 'E1', 9, 1, '2026-04-24 14:06:24'),
(78, 'licencia_categorias', 'E2', 10, 1, '2026-04-24 14:06:24'),
(79, 'licencia_categorias', 'G', 11, 1, '2026-04-24 14:06:24'),
(80, 'licencia_categorias', 'A1', 12, 1, '2026-04-24 14:06:24'),
(81, 'licencia_categorias', 'A2', 13, 1, '2026-04-24 14:06:24'),
(82, 'licencia_categorias', 'A3', 14, 1, '2026-04-24 14:06:24'),
(84, 'tipo_oficio', 'Oficio Judicial', 1, 1, '2026-04-28 12:29:30'),
(98, 'licencia_categorias', 'C', 6, 1, '2026-04-28 12:29:30'),
(99, 'licencia_categorias', 'D', 7, 1, '2026-04-28 12:29:30'),
(100, 'licencia_categorias', 'E', 8, 1, '2026-04-28 12:29:30'),
(109, 'jerarquias', 'Comisario General', 1, 1, '2026-04-30 12:44:29'),
(110, 'jerarquias', 'Comisario Mayor', 2, 1, '2026-04-30 12:44:29'),
(111, 'jerarquias', 'Comisario Inspector', 3, 1, '2026-04-30 12:44:29'),
(112, 'jerarquias', 'Comisario', 4, 1, '2026-04-30 12:44:29'),
(113, 'jerarquias', 'Subcomisario', 5, 1, '2026-04-30 12:44:29'),
(114, 'jerarquias', 'Oficial Principal', 6, 1, '2026-04-30 12:44:29'),
(115, 'jerarquias', 'Oficial Inspector', 7, 1, '2026-04-30 12:44:29'),
(116, 'jerarquias', 'Oficial Subinspector', 8, 1, '2026-04-30 12:44:29'),
(117, 'jerarquias', 'Oficial Ayudante', 9, 1, '2026-04-30 12:44:29'),
(118, 'jerarquias', 'Suboficial Mayor', 10, 1, '2026-04-30 12:44:29'),
(119, 'jerarquias', 'Suboficial Principal', 11, 1, '2026-04-30 12:44:29'),
(120, 'jerarquias', 'Sargento Ayudante', 12, 1, '2026-04-30 12:44:29'),
(121, 'jerarquias', 'Sargento 1°', 13, 1, '2026-04-30 12:44:29'),
(122, 'jerarquias', 'Sargento', 14, 1, '2026-04-30 12:44:29'),
(123, 'jerarquias', 'Cabo 1°', 15, 1, '2026-04-30 12:44:29'),
(124, 'jerarquias', 'Cabo', 16, 1, '2026-04-30 12:44:29'),
(125, 'jerarquias', 'Agente', 17, 1, '2026-04-30 12:44:29'),
(126, 'tipos_recargo', 'Seguridad CIudanada', 0, 1, '2026-04-30 12:45:00'),
(127, 'tipos_recargo', 'Operativo', 0, 1, '2026-04-30 12:45:08'),
(142, 'juzgados', 'J.I.C  Nro. 1- 1° Circunscripción', 0, 1, '2026-04-30 13:07:30'),
(143, 'juzgados', 'J.I.C  Nro. 2- 1° Circunscripción', 0, 1, '2026-04-30 13:07:37'),
(144, 'juzgados', 'J.I.C  Nro. 3- 1° Circunscripción', 0, 1, '2026-04-30 13:07:42'),
(145, 'juzgados', 'J.I.C  Nro. 4- 1° Circunscripción', 0, 1, '2026-04-30 13:07:48'),
(146, 'juzgados', 'J.I.C  Nro. 5- 1° Circunscripción', 0, 1, '2026-04-30 13:07:57'),
(147, 'juzgados', 'J.I.C  Nro. 6- 1° Circunscripción', 0, 1, '2026-04-30 13:08:07'),
(148, 'juzgados', 'Excelenticima Camara Primera en lo Criminal', 0, 1, '2026-04-30 13:08:25'),
(149, 'juzgados', 'Excelenticima Camara Segunda en lo Criminal', 0, 1, '2026-04-30 13:08:39'),
(150, 'juzgados', 'Federal Nº 1- 1° Circunscripción', 0, 1, '2026-04-30 13:09:01'),
(151, 'juzgados', 'Federal Nº 2- 1° Circunscripción', 0, 1, '2026-04-30 13:09:09'),
(152, 'juzgados', 'J.I.C  Nro. 1- 2° Circunscripción', 0, 1, '2026-04-30 13:09:44'),
(153, 'juzgados', 'J.I.C  Nro. 2- 2° Circunscripción', 0, 1, '2026-04-30 13:09:50'),
(155, 'tipos_requerimiento', 'Resguardo de Evidencia', 0, 1, '2026-04-30 13:10:58'),
(157, 'unidades_regionales', 'Dirección General de Policía Científica', 1, 1, '2026-05-01 02:40:33'),
(164, 'subordinados', 'Delegacion El Colorado', 1, 1, '2026-05-01 02:40:33'),
(165, 'subordinados', 'Delegacion Criminalística C-5', 2, 1, '2026-05-01 02:40:33'),
(166, 'subordinados', 'Delegacion Pirané', 3, 1, '2026-05-01 02:40:33'),
(167, 'subordinados', 'Direccion de Policia Cientifica', 4, 1, '2026-05-01 02:40:33'),
(168, 'subordinados', 'Deleg Clorinda', 5, 1, '2026-05-01 02:40:33'),
(169, 'subordinados', 'Delegacion Laguna Blanca', 6, 1, '2026-05-01 02:40:33'),
(170, 'subordinados', 'Delegacion Las Lomitas', 7, 1, '2026-05-01 02:40:33'),
(171, 'subordinados', 'Delegacion Ing Juarez', 8, 1, '2026-05-01 02:40:33'),
(172, 'subordinados', 'Delegacion Güemes', 9, 1, '2026-05-01 02:40:33'),
(173, 'subordinados', 'Deleg Ibarreta', 10, 1, '2026-05-01 02:40:33'),
(174, 'subordinados', 'Delegacion Nueva Formosa', 11, 1, '2026-05-01 02:40:33'),
(175, 'subordinados', 'Division Investigación Ciberdelitos', 12, 1, '2026-05-01 02:40:33'),
(176, 'subordinados', 'Division Informatica Forense', 13, 1, '2026-05-01 02:40:33'),
(177, 'subordinados', 'Sección Criminalística', 14, 1, '2026-05-01 02:40:33'),
(178, 'subordinados', 'Sección Balística', 15, 1, '2026-05-01 02:40:33'),
(179, 'subordinados', 'Sección Documentología', 16, 1, '2026-05-01 02:40:33'),
(180, 'subordinados', 'Sección Química Legal', 17, 1, '2026-05-01 02:40:33'),
(181, 'subordinados', 'Sección Informática Forense', 18, 1, '2026-05-01 02:40:33'),
(183, 'obras_sociales', 'A.M.P.', 0, 1, '2026-05-06 11:29:52'),
(184, 'tipos_requerimiento', 'Descarga de Filmaciones', 0, 1, '2026-05-06 11:31:04'),
(185, 'tipos_requerimiento', 'Escalamiento de Datos', 0, 1, '2026-05-06 11:31:09'),
(186, 'tipos_requerimiento', 'Informe Técnico', 0, 1, '2026-05-06 11:31:21'),
(187, 'tipos_licencia', 'Anual', 0, 1, '2026-05-06 11:32:24'),
(188, 'tipos_licencia', 'Invernal', 0, 1, '2026-05-06 11:32:28'),
(189, 'tipos_recargo', 'Cancha', 0, 1, '2026-05-06 11:32:54'),
(190, 'tipo_oficio', 'Oficio Vario', 0, 1, '2026-05-06 11:33:25'),
(191, 'tipo_oficio', 'Nota V', 0, 1, '2026-05-06 11:33:29'),
(192, 'tipo_oficio', 'Memorandum', 0, 1, '2026-05-06 11:33:35'),
(193, 'tipos_recargo', 'Recargo Guardia', 0, 1, '2026-05-06 12:05:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo_url` varchar(500) DEFAULT NULL,
  `logo_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `tipo`, `created_at`, `updated_at`, `logo_url`, `logo_updated_at`) VALUES
(1, 'nombre_sistema', 'Sistema de Gestión ', 'text', '2026-04-21 15:12:12', '2026-04-28 22:14:52', NULL, NULL),
(2, 'logo_sistema', '', 'image', '2026-04-21 15:12:12', '2026-04-21 15:12:12', NULL, NULL),
(3, 'widgets_dashboard', '[\"stats\",\"jerarquias\",\"recargos\",\"expedientes\",\"licencias\",\"licencias_vencer\"]', 'json', '2026-04-21 15:12:12', '2026-04-21 15:12:12', NULL, NULL),
(4, 'logo_url', 'uploads/logo/logo_1777417287_69f13c47795dc.png', 'image', '2026-04-28 22:04:54', '2026-04-28 23:01:27', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dashboard_config`
--

CREATE TABLE `dashboard_config` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `widgets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widgets`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dashboard_config`
--

INSERT INTO `dashboard_config` (`id`, `usuario_id`, `widgets`, `created_at`, `updated_at`) VALUES
(1, 1, '[\"stats\",\"jerarquias\",\"recargos\",\"expedientes\",\"licencias\",\"tipos_requerimiento\",\"juzgados\",\"cumpleanos\"]', '2026-04-21 22:28:02', '2026-04-23 14:04:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependencias`
--

CREATE TABLE `dependencias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nivel` enum('central','delegacion','seccion') DEFAULT 'seccion',
  `padre_id` int(11) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dependencias`
--

INSERT INTO `dependencias` (`id`, `nombre`, `nivel`, `padre_id`, `codigo`, `direccion`, `telefono`, `email`, `activo`, `created_at`) VALUES
(1, 'Dirección General de Policía Científica', 'central', NULL, 'DGPC-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(3, 'Delegación Mar del Plata', 'delegacion', 1, 'DEL-MDP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(4, 'Delegación Bahía Blanca', 'delegacion', 1, 'DEL-BB-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(8, 'Sección Química Legal', 'seccion', 3, 'SEC-QUIM-MDP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(9, 'Sección Balística', 'seccion', 3, 'SEC-BAL-MDP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(10, 'Sección Criminalística', 'seccion', 4, 'SEC-CRIM-BB-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `elevaciones`
--

CREATE TABLE `elevaciones` (
  `id` int(11) NOT NULL,
  `expediente_id` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `recibido_por` varchar(100) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `imagen_ruta` varchar(500) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipamiento_armas`
--

CREATE TABLE `equipamiento_armas` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT 'Arma de fuego',
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `serie` varchar(100) NOT NULL,
  `calibre` varchar(20) DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `estado` enum('Asignada','En mantenimiento','Dado de baja') DEFAULT 'Asignada',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipamiento_chalecos`
--

CREATE TABLE `equipamiento_chalecos` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `talla` varchar(10) DEFAULT NULL,
  `nivel_proteccion` varchar(50) DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('Activo','Vencido','En mantenimiento') DEFAULT 'Activo',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expediente_documentos`
--

CREATE TABLE `expediente_documentos` (
  `id` int(11) NOT NULL,
  `expediente_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo_ruta` varchar(500) NOT NULL,
  `tipo_archivo` varchar(20) DEFAULT NULL,
  `tamano` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expediente_elevaciones`
--

CREATE TABLE `expediente_elevaciones` (
  `id` int(11) NOT NULL,
  `expediente_id` int(11) NOT NULL,
  `fecha_elevacion` date NOT NULL,
  `hora_elevacion` time DEFAULT NULL,
  `persona_recibio` varchar(200) DEFAULT NULL,
  `instancia_destino` varchar(200) DEFAULT NULL,
  `recibo_ruta` varchar(500) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exportaciones_programadas`
--

CREATE TABLE `exportaciones_programadas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `formato` enum('csv','excel','pdf') DEFAULT 'csv',
  `frecuencia` enum('diaria','semanal','mensual') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ultima_ejecucion` datetime DEFAULT NULL,
  `proxima_ejecucion` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE `feriados` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `tipo` enum('Nacional','Provincial','Municipal') DEFAULT 'Nacional',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `feriados`
--

INSERT INTO `feriados` (`id`, `fecha`, `motivo`, `tipo`, `created_at`) VALUES
(1, '2026-01-01', 'Año Nuevo', 'Nacional', '2026-05-06 11:28:15'),
(2, '2026-02-16', 'Carnaval', 'Nacional', '2026-05-06 11:28:15'),
(3, '2026-02-17', 'Carnaval', 'Nacional', '2026-05-06 11:28:15'),
(4, '2026-03-24', 'Día de la Memoria por la Verdad y la Justicia', 'Nacional', '2026-05-06 11:28:15'),
(5, '2026-04-02', 'Día del Veterano y de los Caídos en Malvinas', 'Nacional', '2026-05-06 11:28:15'),
(6, '2026-04-03', 'Viernes Santo', 'Nacional', '2026-05-06 11:28:15'),
(7, '2026-05-01', 'Día del Trabajador', 'Nacional', '2026-05-06 11:28:15'),
(8, '2026-05-25', 'Revolución de Mayo', 'Nacional', '2026-05-06 11:28:15'),
(9, '2026-06-15', 'Paso a la Inmortalidad de Güemes (trasladado)', 'Nacional', '2026-05-06 11:28:15'),
(10, '2026-06-20', 'Paso a la Inmortalidad de Belgrano', 'Nacional', '2026-05-06 11:28:15'),
(11, '2026-07-09', 'Día de la Independencia', 'Nacional', '2026-05-06 11:28:15'),
(12, '2026-08-17', 'Paso a la Inmortalidad de San Martín', 'Nacional', '2026-05-06 11:28:15'),
(13, '2026-10-12', 'Día del Respeto a la Diversidad Cultural', 'Nacional', '2026-05-06 11:28:15'),
(14, '2026-11-23', 'Día de la Soberanía Nacional (trasladado)', 'Nacional', '2026-05-06 11:28:15'),
(15, '2026-12-08', 'Inmaculada Concepción de María', 'Nacional', '2026-05-06 11:28:15'),
(16, '2026-12-25', 'Navidad', 'Nacional', '2026-05-06 11:28:15'),
(17, '2026-04-08', 'Fundación de Formosa', 'Provincial', '2026-05-06 11:28:15'),
(18, '2026-06-28', 'Provincialización de Formosa', 'Provincial', '2026-05-06 11:28:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `importaciones_programadas`
--

CREATE TABLE `importaciones_programadas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('personal','recargos','expedientes','licencias') NOT NULL,
  `frecuencia` enum('diaria','semanal','mensual') NOT NULL,
  `ultima_ejecucion` datetime DEFAULT NULL,
  `proxima_ejecucion` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `juzgados`
--

CREATE TABLE `juzgados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `direccion` varchar(300) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `juzgados`
--

INSERT INTO `juzgados` (`id`, `nombre`, `direccion`, `telefono`, `email`, `contacto`, `activo`, `created_at`) VALUES
(1, 'Juzgado de Instrucción y Correccional Nro. 1- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(2, 'Juzgado de Instrucción y Correccional Nro. 2- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(3, 'Juzgado de Instrucción y Correccional Nro. 3- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(4, 'Juzgado de Instrucción y Correccional Nro. 4- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(5, 'Juzgado de Instrucción y Correccional Nro. 5- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(6, 'Juzgado de Instrucción y Correccional Nro. 6- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(7, 'Excelenticima Camara Primera en lo Criminal- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(8, 'Excelentisima Camara Segunda en lo Criminal- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(9, 'Excelentísimo Tribunal de Casacion- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(10, 'Excelentisimo Tribunal de Familia- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(11, 'Federal Nº 1- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(12, 'Federal Nº 2- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(13, 'Fiscalia Nº 1- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(14, 'Fiscalia Nº2- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(15, 'Juzgado Civil y Comercial del Trabajo y Menores Nº7 - El Colorado', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(16, 'Juzgado de 1° Instancia en lo Civil y Comercial N°1- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(17, 'Juzgado de 1° Instancia en lo Civil y Comercial N°2- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(18, 'Juzgado de 1° Instancia en lo Civil y Comercial N°3- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(19, 'Juzgado de 1° Instancia en lo Civil y Comercial N°4- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(20, 'Juzgado de 1° Instancia en lo Civil y Comercial N°5- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(21, 'Juzgado de 1° Instancia en lo Civil y Comercial N°6- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(22, 'Juzgado de Ejecución Penal- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(23, 'Juzgado de Instruccion y Correccional Contra el Narcocrimen', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(24, 'Juzgado de Menores- 1° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(25, 'Juzgado de Paz de Menor cuantia El Colorado', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(26, 'Juzgado de Paz de Menor Cuantia - Fontana', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(27, 'Juzgado de Paz de Menor Cuantia Herradura - Formosa', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(28, 'Juzgado de Paz de Menor Cuantia Ibarreta', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(29, 'Juzgado de Paz de Menor Cuantía N°1', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(30, 'Juzgado de Paz de Menor Cuantía N°2', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(31, 'Juzgado de Paz de Menor Cuantía N°3', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(32, 'Juzgado de Paz de Menor Cuantía N°4', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(33, 'Juzgado de Paz de Menor Cuantia - Palo Santo', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(34, 'Juzgado de Paz de Menor Cuantia- Villa Gral. Guemes', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(35, 'Juzgado de Paz de Menor Cuatia Laishi', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(36, 'Juzgado de Paz Pirane', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(37, 'Juzgado Federal Civil y Comercial', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(38, 'Juzgado Penal N° 2', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(39, 'Oficina de Gestión de Audiencia-OGA', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(40, 'Oficina de Violencia Familiar', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(41, 'Otros Juzgados', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(42, 'Secretaria Unica Narcocrimen', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(43, 'Tribunal Oral Criminal Federal', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(44, 'Unidad Fiscal', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(45, 'Juez de Instrucción y Correccional Nro. 1- 2° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(46, 'Juez de Instrucción y Correccional Nro. 2- 2° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(47, 'Juzgado de Paz de Menor Cuantia Clorinda', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(48, 'Juzgado de Paz de Menor Cuantia El Espinillo', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(49, 'Juzgado de Paz de Menor Cuantia G. Belgrano', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(50, 'Juzgado de Paz de Menor Cuantia General Guemes', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(51, 'Juzgado de Paz de Menor Cuantia Laguna Blanca', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(52, 'Juzgado de Paz de Menor Cuantia N° 1', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(53, 'Juzgado de Paz de Menor Cuantia N° 2', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(54, 'Juzgado de Paz de Menor Cuantia Riacho He He', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(55, 'Juzgado Menor Cuantia Palma Sola - Formosa', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(56, 'Juzgado Menores Clorinda', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(57, 'Oficina de Gestión de Audiencia (OGA) Clorinda', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(58, 'Juez de Instrucción y Correccional Las Lomitas- 3° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(59, 'Juzgado 1ra. Instancia Civil, Comercial, del trabajo y de Menores- 3° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(60, 'Juzgado de Paz de Menor Cuantia Las Lomitas- 3° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(61, 'Juzgado de Paz Ingeniero Juarez- 3° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(62, 'Juzgado Menores- 3° Circunscripción', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(63, 'Juzgado Paz Menor Cuantia Comandante Fontana', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(64, 'Juzgado Paz Menor Cuantia Estanislao del Campo', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13'),
(65, 'Juzgado Paz Menor Cuantia Pozo del Tigre', NULL, NULL, NULL, NULL, 1, '2026-04-30 13:04:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id` int(11) NOT NULL,
  `agente_id` int(11) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `tipo_licencia` varchar(100) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada','En Curso','Finalizada') DEFAULT 'Pendiente',
  `fecha_inicio` date NOT NULL,
  `dias_habiles` int(11) DEFAULT 0,
  `dias_viaje` int(11) DEFAULT 0,
  `contar_fines_semana` tinyint(1) DEFAULT 0,
  `fecha_fin` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `tipo` enum('info','success','warning','danger') DEFAULT 'info',
  `leida` tinyint(1) DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `modulo`, `accion`, `descripcion`) VALUES
(1, 'dashboard', 'ver', 'Ver dashboard'),
(2, 'personal', 'ver', 'Ver listado de personal'),
(3, 'personal', 'crear', 'Crear nuevo personal'),
(4, 'personal', 'editar', 'Editar personal existente'),
(5, 'personal', 'eliminar', 'Eliminar personal'),
(6, 'recargos', 'ver', 'Ver listado de recargos'),
(7, 'recargos', 'crear', 'Crear nuevo recargo'),
(8, 'recargos', 'editar', 'Editar recargo existente'),
(9, 'recargos', 'eliminar', 'Eliminar recargo'),
(10, 'expedientes', 'ver', 'Ver listado de expedientes'),
(11, 'expedientes', 'crear', 'Crear nuevo expediente'),
(12, 'expedientes', 'editar', 'Editar expediente existente'),
(13, 'expedientes', 'eliminar', 'Eliminar expediente'),
(14, 'licencias', 'ver', 'Ver listado de licencias'),
(15, 'licencias', 'crear', 'Crear nueva licencia'),
(16, 'licencias', 'editar', 'Editar licencia existente'),
(17, 'licencias', 'eliminar', 'Eliminar licencia'),
(18, 'usuarios', 'ver', 'Ver listado de usuarios'),
(19, 'usuarios', 'crear', 'Crear nuevo usuario'),
(20, 'usuarios', 'editar', 'Editar usuario existente'),
(21, 'usuarios', 'eliminar', 'Eliminar usuario'),
(22, 'configuracion', 'ver', 'Ver configuración'),
(23, 'configuracion', 'editar', 'Editar configuración'),
(24, 'reportes', 'ver', 'Ver reportes'),
(25, 'reportes', 'exportar', 'Exportar reportes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `legajo` varchar(20) NOT NULL,
  `jerarquia` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `sexo` enum('Masculino','Femenino','Otro') DEFAULT NULL,
  `oficina` varchar(100) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `unidad_regional_id` int(11) DEFAULT NULL,
  `subordinado` varchar(100) DEFAULT NULL,
  `seccion_guardia_id` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `tiene_arma` tinyint(1) DEFAULT 0,
  `arma_marca` varchar(50) DEFAULT NULL,
  `arma_modelo` varchar(50) DEFAULT NULL,
  `arma_serie` varchar(50) DEFAULT NULL,
  `chaleco_numero` varchar(50) DEFAULT NULL,
  `sin_arma_motivo` text DEFAULT NULL,
  `sin_arma_justificacion` text DEFAULT NULL,
  `nro_credencial` varchar(50) DEFAULT NULL,
  `nro_licencia_conducir` varchar(50) DEFAULT NULL,
  `licencia_categoria` varchar(20) DEFAULT NULL,
  `es_chofer` tinyint(1) DEFAULT 0,
  `fecha_vencimiento_licencia` date DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `nro_afiliado` varchar(50) DEFAULT NULL,
  `obra_social_numero` varchar(50) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_documentos`
--

CREATE TABLE `personal_documentos` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo_ruta` varchar(500) DEFAULT NULL,
  `tipo_archivo` varchar(50) DEFAULT NULL,
  `tamano` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recargos`
--

CREATE TABLE `recargos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `tipo_recargo` varchar(100) DEFAULT NULL,
  `oficina` varchar(100) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `personal_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Pendiente',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seccion_guardia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_guardados`
--

CREATE TABLE `reportes_guardados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `columnas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`columnas`)),
  `filtros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filtros`)),
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `nivel`, `created_at`) VALUES
(1, 'Administrador Central', 'Acceso total a todo el sistema', 100, '2026-04-21 15:12:12'),
(2, 'Supervisor Delegación', 'Acceso a su delegación y secciones hijas', 50, '2026-04-21 15:12:12'),
(3, 'Jefe Sección', 'Acceso solo a su sección', 30, '2026-04-21 15:12:12'),
(4, 'Operador', 'Acceso básico solo a su área', 10, '2026-04-21 15:12:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_permisos`
--

CREATE TABLE `roles_permisos` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_permisos`
--

INSERT INTO `roles_permisos` (`rol_id`, `permiso_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 6),
(2, 7),
(2, 8),
(2, 10),
(2, 11),
(2, 12),
(2, 14),
(2, 15),
(2, 16),
(2, 24),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 6),
(3, 7),
(3, 8),
(3, 10),
(3, 11),
(3, 12),
(3, 14),
(3, 15),
(3, 16),
(4, 1),
(4, 2),
(4, 6),
(4, 10),
(4, 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones_guardia`
--

CREATE TABLE `secciones_guardia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secciones_guardia`
--

INSERT INTO `secciones_guardia` (`id`, `nombre`, `descripcion`, `orden`, `activo`, `created_at`) VALUES
(2, '2° Grupo', 'Segundo grupo de guardia', 2, 1, '2026-04-24 22:59:36'),
(3, '3° Grupo', 'Tercer grupo de guardia', 3, 1, '2026-04-24 22:59:36'),
(5, '1° Grupo', 'Primer grupo de guardia', 1, 1, '2026-04-24 23:37:58'),
(8, 'Administrativo', 'Personal administrativo', 4, 1, '2026-04-24 23:37:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_table_config`
--

CREATE TABLE `user_table_config` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `columnas` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_table_config`
--

INSERT INTO `user_table_config` (`id`, `usuario_id`, `tabla`, `columnas`, `created_at`, `updated_at`) VALUES
(2, 1, 'expedientes', '[\"nro_expediente\",\"expediente_origen\",\"fecha\",\"tipo_oficio\",\"juzgado_origen\",\"tipo_requerimiento\",\"estado\"]', '2026-04-23 22:03:14', '2026-04-23 22:19:54'),
(11, 1, 'recargos', '[\"fecha\",\"hora\",\"tipo_recargo\",\"oficina\",\"personal\",\"estado\",\"observaciones\"]', '2026-04-24 14:22:32', '2026-04-24 14:22:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `puede_ver_todas` tinyint(1) DEFAULT 0,
  `nivel_acceso` enum('solo_propio','delegacion','todas') DEFAULT 'solo_propio',
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permisos`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `username`, `email`, `password`, `rol_id`, `dependencia_id`, `puede_ver_todas`, `nivel_acceso`, `estado`, `permisos`, `created_at`, `updated_at`) VALUES
(1, 'Administrador Central', 'admin', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 1, 'todas', 'Activo', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(3, 'Jefe Criminalística LP', 'jefe.crim.lp', 'jefe.crim.lp@policia.gob.ar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, NULL, 0, 'solo_propio', 'Activo', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_config`
--
ALTER TABLE `alertas_config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `catalogos`
--
ALTER TABLE `catalogos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tipo_valor` (`tipo`,`valor`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `dashboard_config`
--
ALTER TABLE `dashboard_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`);

--
-- Indices de la tabla `dependencias`
--
ALTER TABLE `dependencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_padre` (`padre_id`);

--
-- Indices de la tabla `elevaciones`
--
ALTER TABLE `elevaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expediente` (`expediente_id`);

--
-- Indices de la tabla `equipamiento_armas`
--
ALTER TABLE `equipamiento_armas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `idx_armas_personal` (`personal_id`);

--
-- Indices de la tabla `equipamiento_chalecos`
--
ALTER TABLE `equipamiento_chalecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `idx_vencimiento` (`fecha_vencimiento`),
  ADD KEY `idx_chalecos_personal` (`personal_id`),
  ADD KEY `idx_chalecos_vencimiento` (`fecha_vencimiento`);

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
-- Indices de la tabla `expediente_documentos`
--
ALTER TABLE `expediente_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exp_documentos_exp` (`expediente_id`);

--
-- Indices de la tabla `expediente_elevaciones`
--
ALTER TABLE `expediente_elevaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exp_elevaciones_exp` (`expediente_id`),
  ADD KEY `idx_exp_elevaciones_fecha` (`fecha_elevacion`);

--
-- Indices de la tabla `exportaciones_programadas`
--
ALTER TABLE `exportaciones_programadas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `importaciones_programadas`
--
ALTER TABLE `importaciones_programadas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `juzgados`
--
ALTER TABLE `juzgados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nombre` (`nombre`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agente_id` (`agente_id`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_usuario` (`usuario_id`),
  ADD KEY `idx_logs_created` (`created_at`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`usuario_id`,`leida`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permiso` (`modulo`,`accion`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legajo` (`legajo`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `idx_legajo` (`legajo`),
  ADD KEY `idx_dni` (`dni`),
  ADD KEY `idx_apellido_nombre` (`apellido`,`nombre`),
  ADD KEY `seccion_guardia_id` (`seccion_guardia_id`);

--
-- Indices de la tabla `personal_documentos`
--
ALTER TABLE `personal_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- Indices de la tabla `recargos`
--
ALTER TABLE `recargos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_seccion` (`seccion_guardia_id`);

--
-- Indices de la tabla `reportes_guardados`
--
ALTER TABLE `reportes_guardados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`),
  ADD KEY `permiso_id` (`permiso_id`);

--
-- Indices de la tabla `secciones_guardia`
--
ALTER TABLE `secciones_guardia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_table_config`
--
ALTER TABLE `user_table_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_tabla` (`usuario_id`,`tabla`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_config`
--
ALTER TABLE `alertas_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `catalogos`
--
ALTER TABLE `catalogos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `dashboard_config`
--
ALTER TABLE `dashboard_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `dependencias`
--
ALTER TABLE `dependencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `elevaciones`
--
ALTER TABLE `elevaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipamiento_armas`
--
ALTER TABLE `equipamiento_armas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `equipamiento_chalecos`
--
ALTER TABLE `equipamiento_chalecos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `expediente_documentos`
--
ALTER TABLE `expediente_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `expediente_elevaciones`
--
ALTER TABLE `expediente_elevaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `exportaciones_programadas`
--
ALTER TABLE `exportaciones_programadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `importaciones_programadas`
--
ALTER TABLE `importaciones_programadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `juzgados`
--
ALTER TABLE `juzgados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_documentos`
--
ALTER TABLE `personal_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recargos`
--
ALTER TABLE `recargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_guardados`
--
ALTER TABLE `reportes_guardados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `secciones_guardia`
--
ALTER TABLE `secciones_guardia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user_table_config`
--
ALTER TABLE `user_table_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `dashboard_config`
--
ALTER TABLE `dashboard_config`
  ADD CONSTRAINT `dashboard_config_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dependencias`
--
ALTER TABLE `dependencias`
  ADD CONSTRAINT `dependencias_ibfk_1` FOREIGN KEY (`padre_id`) REFERENCES `dependencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `elevaciones`
--
ALTER TABLE `elevaciones`
  ADD CONSTRAINT `elevaciones_ibfk_1` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `equipamiento_armas`
--
ALTER TABLE `equipamiento_armas`
  ADD CONSTRAINT `equipamiento_armas_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `equipamiento_chalecos`
--
ALTER TABLE `equipamiento_chalecos`
  ADD CONSTRAINT `equipamiento_chalecos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD CONSTRAINT `expedientes_ibfk_1` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expedientes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `expediente_documentos`
--
ALTER TABLE `expediente_documentos`
  ADD CONSTRAINT `expediente_documentos_ibfk_1` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `expediente_elevaciones`
--
ALTER TABLE `expediente_elevaciones`
  ADD CONSTRAINT `expediente_elevaciones_ibfk_1` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personal_ibfk_2` FOREIGN KEY (`seccion_guardia_id`) REFERENCES `secciones_guardia` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `personal_documentos`
--
ALTER TABLE `personal_documentos`
  ADD CONSTRAINT `personal_documentos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_guardados`
--
ALTER TABLE `reportes_guardados`
  ADD CONSTRAINT `reportes_guardados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `roles_permisos`
--
ALTER TABLE `roles_permisos`
  ADD CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
