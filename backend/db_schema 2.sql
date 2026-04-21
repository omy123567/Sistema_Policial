-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-04-2026 a las 00:32:13
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
(1, 'jerarquias', 'Oficial Principal', 1, 1, '2026-04-21 15:12:12'),
(2, 'jerarquias', 'Oficial Inspector', 2, 1, '2026-04-21 15:12:12'),
(3, 'jerarquias', 'Oficial Subinspector', 3, 1, '2026-04-21 15:12:12'),
(4, 'jerarquias', 'Oficial Ayudante', 4, 1, '2026-04-21 15:12:12'),
(5, 'jerarquias', 'Suboficial Principal', 5, 1, '2026-04-21 15:12:12'),
(6, 'jerarquias', 'Suboficial Mayor', 6, 1, '2026-04-21 15:12:12'),
(7, 'jerarquias', 'Suboficial', 7, 1, '2026-04-21 15:12:12'),
(8, 'jerarquias', 'Cabo Principal', 8, 1, '2026-04-21 15:12:12'),
(9, 'jerarquias', 'Cabo', 9, 1, '2026-04-21 15:12:12'),
(10, 'jerarquias', 'Agente', 10, 1, '2026-04-21 15:12:12'),
(11, 'obras_sociales', 'OSDE', 1, 1, '2026-04-21 15:12:12'),
(12, 'obras_sociales', 'Medife', 2, 1, '2026-04-21 15:12:12'),
(13, 'obras_sociales', 'Swiss Medical', 3, 1, '2026-04-21 15:12:12'),
(14, 'obras_sociales', 'PAMI', 4, 1, '2026-04-21 15:12:12'),
(15, 'obras_sociales', 'IOSFA', 5, 1, '2026-04-21 15:12:12'),
(16, 'tipos_recargo', 'Llegada tarde', 1, 1, '2026-04-21 15:12:12'),
(17, 'tipos_recargo', 'Falta injustificada', 2, 1, '2026-04-21 15:12:12'),
(18, 'tipos_recargo', 'Incumplimiento de deberes', 3, 1, '2026-04-21 15:12:12'),
(19, 'tipos_recargo', 'Mal desempeño', 4, 1, '2026-04-21 15:12:12'),
(20, 'tipos_recargo', 'Falta de respeto', 5, 1, '2026-04-21 15:12:12'),
(21, 'tipos_oficio', 'Oficio Judicial', 1, 1, '2026-04-21 15:12:12'),
(22, 'tipos_oficio', 'Oficio Fiscal', 2, 1, '2026-04-21 15:12:12'),
(23, 'tipos_oficio', 'Oficio Administrativo', 3, 1, '2026-04-21 15:12:12'),
(24, 'tipos_oficio', 'Oficio Policial', 4, 1, '2026-04-21 15:12:12'),
(25, 'tipos_requerimiento', 'Informe técnico', 1, 1, '2026-04-21 15:12:12'),
(26, 'tipos_requerimiento', 'Peritaje', 2, 1, '2026-04-21 15:12:12'),
(27, 'tipos_requerimiento', 'Relevamiento', 3, 1, '2026-04-21 15:12:12'),
(28, 'tipos_requerimiento', 'Investigación', 4, 1, '2026-04-21 15:12:12'),
(29, 'tipos_licencia', 'Ordinaria', 1, 1, '2026-04-21 15:12:12'),
(30, 'tipos_licencia', 'Por enfermedad', 2, 1, '2026-04-21 15:12:12'),
(31, 'tipos_licencia', 'Especial', 3, 1, '2026-04-21 15:12:12'),
(32, 'tipos_licencia', 'Por estudio', 4, 1, '2026-04-21 15:12:12'),
(33, 'tipos_licencia', 'Maternidad/Paternidad', 5, 1, '2026-04-21 15:12:12'),
(34, 'oficinas', 'Comisaría 1ra', 1, 1, '2026-04-21 15:12:12'),
(35, 'oficinas', 'Comisaría 2da', 2, 1, '2026-04-21 15:12:12'),
(36, 'oficinas', 'Departamento de Investigaciones', 3, 1, '2026-04-21 15:12:12'),
(37, 'oficinas', 'Oficina de Tránsito', 4, 1, '2026-04-21 15:12:12'),
(38, 'oficinas', 'División de Operaciones', 5, 1, '2026-04-21 15:12:12'),
(39, 'dependencias', 'Ministerio de Seguridad', 1, 1, '2026-04-21 15:12:12'),
(40, 'dependencias', 'Jefatura de Policía', 2, 1, '2026-04-21 15:12:12'),
(41, 'dependencias', 'Tribunales', 3, 1, '2026-04-21 15:12:12'),
(42, 'juzgados', 'Juzgado Federal N°1', 1, 1, '2026-04-21 15:12:12'),
(43, 'juzgados', 'Juzgado Federal N°2', 2, 1, '2026-04-21 15:12:12'),
(44, 'juzgados', 'Juzgado de Garantías N°1', 3, 1, '2026-04-21 15:12:12'),
(45, 'juzgados', 'Juzgado de Garantías N°2', 4, 1, '2026-04-21 15:12:12'),
(46, 'juzgados', 'Juzgado de Instrucción N°1', 5, 1, '2026-04-21 15:12:12');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `tipo`, `created_at`, `updated_at`) VALUES
(1, 'nombre_sistema', 'Sistema de Gestión Policial', 'text', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(2, 'logo_sistema', '', 'image', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(3, 'widgets_dashboard', '[\"stats\",\"jerarquias\",\"recargos\",\"expedientes\",\"licencias\",\"licencias_vencer\"]', 'json', '2026-04-21 15:12:12', '2026-04-21 15:12:12');

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
(1, 1, '[\"stats\", \"jerarquias\", \"recargos\", \"expedientes\", \"licencias\", \"tipos_requerimiento\", \"juzgados\", \"cumpleanos\"]', '2026-04-21 22:28:02', '2026-04-21 22:28:02');

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
(2, 'Delegación La Plata', 'delegacion', 1, 'DEL-LP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(3, 'Delegación Mar del Plata', 'delegacion', 1, 'DEL-MDP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(4, 'Delegación Bahía Blanca', 'delegacion', 1, 'DEL-BB-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(5, 'Sección Criminalística', 'seccion', 2, 'SEC-CRIM-LP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(6, 'Sección Balística', 'seccion', 2, 'SEC-BAL-LP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
(7, 'Sección Documentología', 'seccion', 2, 'SEC-DOC-LP-001', NULL, NULL, NULL, 1, '2026-04-21 15:12:12'),
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
-- Estructura de tabla para la tabla `expedientes`
--

CREATE TABLE `expedientes` (
  `id` int(11) NOT NULL,
  `nro_expediente` varchar(50) NOT NULL,
  `expediente_origen` varchar(100) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `tipo_oficio` varchar(100) DEFAULT NULL,
  `juzgado_origen` varchar(200) DEFAULT NULL,
  `dependencia` varchar(100) DEFAULT NULL,
  `dependencia_id` int(11) DEFAULT NULL,
  `tipo_requerimiento` varchar(100) DEFAULT NULL,
  `resumen` text DEFAULT NULL,
  `nro_informe_tecnico` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `expedientes`
--

INSERT INTO `expedientes` (`id`, `nro_expediente`, `expediente_origen`, `fecha`, `fecha_vencimiento`, `tipo_oficio`, `juzgado_origen`, `dependencia`, `dependencia_id`, `tipo_requerimiento`, `resumen`, `nro_informe_tecnico`, `estado`, `created_by`, `created_at`) VALUES
(1, 'EXP-001', NULL, '2024-01-10', NULL, 'Oficio Judicial', 'Juzgado Federal N°1', 'Ministerio de Seguridad', 1, 'Informe técnico', 'Solicitan informe pericial sobre el caso de homicidio', NULL, 'Activo', NULL, '2026-04-21 15:12:12'),
(2, 'EXP-002', NULL, '2024-01-20', NULL, 'Oficio Fiscal', 'Fiscalía N°2', 'Jefatura de Policía', 1, 'Investigación', 'Requieren colaboración para investigación de robo', NULL, 'Pendiente', NULL, '2026-04-21 15:12:12'),
(3, 'EXP-003', NULL, '2024-02-05', NULL, 'Oficio Administrativo', 'Juzgado de Garantías', 'Tribunales', 1, 'Relevamiento', 'Solicitan relevamiento de información de antecedentes', NULL, 'Activo', NULL, '2026-04-21 15:12:12'),
(4, 'EXP-004', NULL, '2024-02-15', NULL, 'Oficio Judicial', 'Juzgado Federal N°2', 'Ministerio de Seguridad', 1, 'Peritaje', 'Solicitan peritaje balístico', NULL, 'Resuelto', NULL, '2026-04-21 15:12:12'),
(5, 'EXP-005', NULL, '2024-03-01', NULL, 'Oficio Fiscal', 'Fiscalía N°1', 'Jefatura de Policía', 1, 'Informe técnico', 'Requieren informe de antecedentes penales', NULL, 'Activo', NULL, '2026-04-21 15:12:12');

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
(1, '2024-01-01', 'Año Nuevo', 'Nacional', '2026-04-21 15:12:12'),
(2, '2024-02-12', 'Carnaval', 'Nacional', '2026-04-21 15:12:12'),
(3, '2024-02-13', 'Carnaval', 'Nacional', '2026-04-21 15:12:12'),
(4, '2024-03-24', 'Día de la Memoria', 'Nacional', '2026-04-21 15:12:12'),
(5, '2024-04-02', 'Día del Veterano', 'Nacional', '2026-04-21 15:12:12'),
(6, '2024-05-01', 'Día del Trabajador', 'Nacional', '2026-04-21 15:12:12'),
(7, '2024-05-25', 'Revolución de Mayo', 'Nacional', '2026-04-21 15:12:12'),
(8, '2024-06-20', 'Bandera', 'Nacional', '2026-04-21 15:12:12'),
(9, '2024-07-09', 'Independencia', 'Nacional', '2026-04-21 15:12:12'),
(10, '2024-12-08', 'Inmaculada Concepción', 'Nacional', '2026-04-21 15:12:12'),
(11, '2024-12-25', 'Navidad', 'Nacional', '2026-04-21 15:12:12'),
(12, '2025-01-01', 'Año Nuevo', 'Nacional', '2026-04-21 15:12:12'),
(13, '2025-03-03', 'Carnaval', 'Nacional', '2026-04-21 15:12:12'),
(14, '2025-03-04', 'Carnaval', 'Nacional', '2026-04-21 15:12:12'),
(15, '2025-03-24', 'Día de la Memoria', 'Nacional', '2026-04-21 15:12:12'),
(16, '2025-04-02', 'Día del Veterano', 'Nacional', '2026-04-21 15:12:12'),
(17, '2025-05-01', 'Día del Trabajador', 'Nacional', '2026-04-21 15:12:12'),
(18, '2025-05-25', 'Revolución de Mayo', 'Nacional', '2026-04-21 15:12:12'),
(19, '2025-06-20', 'Bandera', 'Nacional', '2026-04-21 15:12:12'),
(20, '2025-07-09', 'Independencia', 'Nacional', '2026-04-21 15:12:12'),
(21, '2025-12-08', 'Inmaculada Concepción', 'Nacional', '2026-04-21 15:12:12'),
(22, '2025-12-25', 'Navidad', 'Nacional', '2026-04-21 15:12:12');

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

--
-- Volcado de datos para la tabla `licencias`
--

INSERT INTO `licencias` (`id`, `agente_id`, `dependencia_id`, `tipo_licencia`, `estado`, `fecha_inicio`, `dias_habiles`, `dias_viaje`, `contar_fines_semana`, `fecha_fin`, `observaciones`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Ordinaria', 'Aprobada', '2024-02-01', 10, 0, 0, '2024-02-15', 'Vacaciones anuales', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(2, 2, 2, 'Por enfermedad', 'Pendiente', '2024-01-25', 8, 0, 0, '2024-02-05', 'Licencia médica por reposo', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(3, 3, 5, 'Especial', 'Aprobada', '2024-02-10', 5, 0, 0, '2024-02-17', 'Trámites personales', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(4, 4, 2, 'Ordinaria', 'Pendiente', '2024-03-01', 15, 0, 0, '2024-03-16', 'Vacaciones', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(5, 5, 3, 'Por enfermedad', 'Aprobada', '2024-02-20', 3, 0, 0, '2024-02-23', 'Reposo médico por 3 días', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12');

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
  `fecha_nacimiento` date DEFAULT NULL,
  `tiene_arma` tinyint(1) DEFAULT 0,
  `arma_marca` varchar(50) DEFAULT NULL,
  `arma_modelo` varchar(50) DEFAULT NULL,
  `arma_serie` varchar(50) DEFAULT NULL,
  `sin_arma_justificacion` text DEFAULT NULL,
  `nro_credencial` varchar(50) DEFAULT NULL,
  `nro_licencia_conducir` varchar(50) DEFAULT NULL,
  `fecha_vencimiento_licencia` date DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `nro_afiliado` varchar(50) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `legajo`, `jerarquia`, `apellido`, `nombre`, `dni`, `sexo`, `oficina`, `dependencia_id`, `fecha_nacimiento`, `tiene_arma`, `arma_marca`, `arma_modelo`, `arma_serie`, `sin_arma_justificacion`, `nro_credencial`, `nro_licencia_conducir`, `fecha_vencimiento_licencia`, `obra_social`, `nro_afiliado`, `telefono`, `email`, `direccion`, `estado`, `created_at`, `updated_at`) VALUES
(1, '001', 'Oficial Principal', 'García', 'Juan', '40123456', 'Masculino', 'Comisaría 1ra', 2, '1980-05-15', 1, NULL, NULL, NULL, NULL, 'CRED-001', 'LIC-001', '2025-12-31', 'OSDE', 'AF-001', '221-1234567', 'juan.garcia@policia.gob.ar', 'Calle 1 N° 123, La Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(2, '002', 'Suboficial', 'Rodríguez', 'María', '40876543', 'Femenino', 'Comisaría 2da', 2, '1985-08-20', 0, NULL, NULL, NULL, NULL, 'CRED-002', 'LIC-002', '2024-06-30', 'Medife', 'AF-002', '221-7654321', 'maria.rodriguez@policia.gob.ar', 'Calle 2 N° 456, La Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(3, '003', 'Agente', 'López', 'Carlos', '40112233', 'Masculino', 'Investigaciones', 5, '1990-03-10', 1, NULL, NULL, NULL, NULL, 'CRED-003', 'LIC-003', '2025-03-15', 'Swiss Medical', 'AF-003', '221-1122334', 'carlos.lopez@policia.gob.ar', 'Calle 3 N° 789, La Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(4, '004', 'Cabo', 'Martínez', 'Ana', '40556677', 'Femenino', 'Comisaría 1ra', 2, '1995-07-22', 0, NULL, NULL, NULL, NULL, 'CRED-004', 'LIC-004', '2026-01-20', 'PAMI', 'AF-004', '221-5566778', 'ana.martinez@policia.gob.ar', 'Calle 4 N° 101, La Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(5, '005', 'Oficial Inspector', 'Pérez', 'Luis', '40998877', 'Masculino', 'Tránsito', 3, '1988-11-30', 1, NULL, NULL, NULL, NULL, 'CRED-005', 'LIC-005', '2024-11-01', 'IOSFA', 'AF-005', '221-9988776', 'luis.perez@policia.gob.ar', 'Calle 5 N° 202, Mar del Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(6, '006', 'Oficial Principal', 'Gómez', 'Laura', '40334455', 'Femenino', 'Comisaría 2da', 3, '1982-03-25', 0, NULL, NULL, NULL, NULL, 'CRED-006', 'LIC-006', '2026-03-25', 'OSDE', 'AF-006', '221-3344556', 'laura.gomez@policia.gob.ar', 'Calle 6 N° 303, Mar del Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(7, '007', 'Suboficial', 'Fernández', 'Diego', '40667788', 'Masculino', 'Investigaciones', 5, '1992-12-10', 1, NULL, NULL, NULL, NULL, 'CRED-007', 'LIC-007', '2025-12-10', 'Medife', 'AF-007', '221-6677889', 'diego.fernandez@policia.gob.ar', 'Calle 7 N° 404, La Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(8, '008', 'Agente', 'Sánchez', 'Lucía', '40223344', 'Femenino', 'Tránsito', 3, '1998-06-05', 0, NULL, NULL, NULL, NULL, 'CRED-008', 'LIC-008', '2026-06-05', 'Swiss Medical', 'AF-008', '221-2233445', 'lucia.sanchez@policia.gob.ar', 'Calle 8 N° 505, Mar del Plata', 'Activo', '2026-04-21 15:12:12', '2026-04-21 15:12:12');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recargos`
--

INSERT INTO `recargos` (`id`, `fecha`, `hora`, `tipo_recargo`, `oficina`, `dependencia_id`, `personal_id`, `observaciones`, `estado`, `created_by`, `created_at`) VALUES
(1, '2024-01-15', '08:30:00', 'Llegada tarde', 'Comisaría 1ra', 2, 1, 'Llegó 15 minutos tarde', 'Resuelto', NULL, '2026-04-21 15:12:12'),
(2, '2024-02-01', '10:00:00', 'Falta injustificada', 'Comisaría 2da', 2, 2, 'No se presentó a trabajar', 'Pendiente', NULL, '2026-04-21 15:12:12'),
(3, '2024-02-10', '14:15:00', 'Incumplimiento de deberes', 'Investigaciones', 5, 3, 'No completó informe requerido', 'Pendiente', NULL, '2026-04-21 15:12:12'),
(4, '2024-03-05', '09:00:00', 'Mal desempeño', 'Tránsito', 3, 5, 'No respetó procedimientos establecidos', 'Resuelto', NULL, '2026-04-21 15:12:12'),
(5, '2024-03-15', '11:30:00', 'Llegada tarde', 'Comisaría 1ra', 2, 4, 'Llegó 30 minutos tarde', 'Pendiente', NULL, '2026-04-21 15:12:12');

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
(2, 'Supervisor La Plata', 'supervisor.lp', 'supervisor.lp@policia.gob.ar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 2, 0, 'delegacion', 'Activo', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12'),
(3, 'Jefe Criminalística LP', 'jefe.crim.lp', 'jefe.crim.lp@policia.gob.ar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 5, 0, 'solo_propio', 'Activo', NULL, '2026-04-21 15:12:12', '2026-04-21 15:12:12');

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
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agente_id` (`agente_id`),
  ADD KEY `dependencia_id` (`dependencia_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`);

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
  ADD KEY `idx_apellido_nombre` (`apellido`,`nombre`);

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
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_personal` (`personal_id`),
  ADD KEY `idx_estado` (`estado`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `dashboard_config`
--
ALTER TABLE `dashboard_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `personal_documentos`
--
ALTER TABLE `personal_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recargos`
--
ALTER TABLE `recargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Filtros para la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD CONSTRAINT `expedientes_ibfk_1` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expedientes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD CONSTRAINT `licencias_ibfk_1` FOREIGN KEY (`agente_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `licencias_ibfk_2` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `licencias_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `personal_documentos`
--
ALTER TABLE `personal_documentos`
  ADD CONSTRAINT `personal_documentos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recargos`
--
ALTER TABLE `recargos`
  ADD CONSTRAINT `recargos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recargos_ibfk_2` FOREIGN KEY (`dependencia_id`) REFERENCES `dependencias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recargos_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

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
