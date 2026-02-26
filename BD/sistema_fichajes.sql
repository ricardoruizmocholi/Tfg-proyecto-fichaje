-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-02-2026 a las 12:10:34
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
-- Base de datos: `sistema_fichajes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_solicitud_horario`
--

CREATE TABLE `detalle_solicitud_horario` (
  `id_detalle` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `orden_dia` tinyint(4) DEFAULT 1,
  `tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO') DEFAULT 'TRABAJO',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `horas_totales` decimal(4,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_solicitud_horario`
--

INSERT INTO `detalle_solicitud_horario` (`id_detalle`, `id_solicitud`, `fecha`, `orden_dia`, `tipo_jornada`, `hora_inicio`, `hora_fin`, `horas_totales`, `observaciones`) VALUES
(1, 1, '2026-01-01', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(2, 1, '2026-01-02', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(3, 1, '2026-01-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(4, 2, '2026-02-02', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(5, 2, '2026-02-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(6, 2, '2026-02-04', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(7, 2, '2026-02-05', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(8, 2, '2026-02-06', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(99, 9, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(100, 9, '2026-02-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(101, 9, '2026-02-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(102, 9, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(103, 9, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(104, 9, '2026-02-16', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(105, 9, '2026-02-17', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(106, 9, '2026-02-18', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(107, 9, '2026-02-19', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(108, 9, '2026-02-20', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(109, 9, '2026-02-23', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(110, 9, '2026-02-24', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(111, 9, '2026-02-25', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(112, 9, '2026-02-26', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(113, 9, '2026-02-27', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(114, 10, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(115, 10, '2026-02-12', 1, 'MEDICO', '09:00:00', '10:00:00', 1.00, ''),
(116, 10, '2026-02-02', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(117, 10, '2026-02-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(118, 10, '2026-02-05', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(119, 10, '2026-02-06', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(120, 10, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(121, 10, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(122, 10, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(123, 10, '2026-02-16', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(124, 10, '2026-02-17', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(125, 10, '2026-02-18', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(126, 10, '2026-02-19', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(127, 10, '2026-02-20', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(128, 10, '2026-02-11', 1, 'FESTIVO', NULL, NULL, NULL, ''),
(129, 10, '2026-02-10', 1, 'LIBRE', NULL, NULL, NULL, ''),
(130, 10, '2026-02-13', 1, 'MEDICO', '09:00:00', '10:00:00', 1.00, ''),
(131, 11, '2026-02-05', 1, 'FESTIVO', NULL, NULL, NULL, ''),
(132, 12, '2026-02-23', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(133, 12, '2026-02-24', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(134, 12, '2026-02-25', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(135, 12, '2026-02-26', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(136, 12, '2026-02-27', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(137, 13, '2026-02-05', 1, 'FESTIVO', NULL, NULL, NULL, ''),
(138, 14, '2026-02-05', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(139, 15, '2026-03-02', 1, 'LIBRE', '09:00:00', '17:00:00', NULL, 'Copiado del mes anterior'),
(140, 15, '2026-03-04', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(141, 15, '2026-03-06', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(142, 15, '2026-03-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(143, 15, '2026-03-10', 1, 'LIBRE', NULL, NULL, NULL, 'Copiado del mes anterior'),
(144, 15, '2026-03-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(145, 15, '2026-03-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(146, 15, '2026-03-23', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(147, 15, '2026-03-24', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(148, 15, '2026-03-25', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(149, 15, '2026-03-26', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(150, 15, '2026-03-27', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(151, 15, '2026-03-05', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(152, 15, '2026-03-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(153, 15, '2026-03-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(154, 15, '2026-03-16', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(155, 15, '2026-03-17', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(156, 15, '2026-03-18', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(157, 15, '2026-03-19', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(158, 15, '2026-03-20', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(159, 16, '2026-02-05', 1, 'MEDICO', '08:00:00', '11:00:00', 3.00, ''),
(160, 17, '2026-02-06', 1, 'MEDICO', '09:00:00', '17:00:00', 8.00, 'colonoscopias ;)'),
(161, 18, '2026-02-08', 1, 'LIBRE', NULL, NULL, NULL, ''),
(162, 18, '2026-02-15', 1, 'LIBRE', NULL, NULL, NULL, ''),
(163, 18, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(164, 18, '2026-02-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(165, 18, '2026-02-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(166, 18, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(167, 18, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(168, 18, '2026-02-14', 1, 'LIBRE', NULL, NULL, NULL, ''),
(169, 19, '2026-03-08', 1, 'LIBRE', NULL, NULL, NULL, 'Copiado del mes anterior'),
(170, 19, '2026-03-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(171, 19, '2026-03-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(172, 19, '2026-03-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(173, 19, '2026-03-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(174, 19, '2026-03-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, 'Copiado del mes anterior'),
(175, 19, '2026-03-14', 1, 'LIBRE', NULL, NULL, NULL, 'Copiado del mes anterior'),
(176, 19, '2026-03-15', 1, 'LIBRE', NULL, NULL, NULL, 'Copiado del mes anterior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `CIF` varchar(20) DEFAULT NULL,
  `CCC` varchar(30) DEFAULT NULL,
  `panel_destino` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre`, `direccion`, `telefono`, `CIF`, `CCC`, `panel_destino`) VALUES
(1, 'ensenyem', 'Dirección 1', '123456789', 'CIF123456', 'CCC123456', 'panel_ensenyem'),
(2, 'sm', 'Dirección 2', '987654321', 'CIF654321', 'CCC654321', 'panel_sm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_usuario`
--

CREATE TABLE `empresa_usuario` (
  `id_empresa` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `empresa_usuario`
--

INSERT INTO `empresa_usuario` (`id_empresa`, `id_usuario`, `admin`, `activo`) VALUES
(1, 1, 1, 1),
(1, 2, 1, 1),
(1, 4, 0, 1),
(1, 6, 0, 1),
(2, 1, 1, 1),
(2, 3, 1, 1),
(2, 5, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichaje`
--

CREATE TABLE `fichaje` (
  `id_fichaje` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_pausa` time DEFAULT NULL,
  `hora_reanudacion` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `fichaje`
--

INSERT INTO `fichaje` (`id_fichaje`, `id_usuario`, `fecha`, `hora_entrada`, `hora_pausa`, `hora_reanudacion`, `hora_salida`, `tipo`, `observaciones`) VALUES
(1, 1, '2025-12-17', '11:32:33', '11:32:36', '11:32:38', '11:32:40', 'normal', NULL),
(2, 4, '2025-12-17', '09:00:00', '11:34:00', '11:37:46', '11:37:59', 'normal', ''),
(3, 4, '2025-12-17', '11:38:00', '11:38:00', '11:38:01', '11:38:01', 'normal', NULL),
(4, 4, '2025-12-17', '11:38:02', NULL, NULL, NULL, 'normal', NULL),
(5, 1, '2026-02-02', '09:50:42', '13:41:56', '13:41:58', '13:41:59', 'normal', NULL),
(6, 4, '2026-02-02', '13:22:46', NULL, NULL, '13:28:05', 'normal', NULL),
(7, 4, '2026-02-02', '13:28:05', '13:33:10', '13:33:15', '13:33:18', 'normal', NULL),
(8, 1, '2026-02-02', '09:37:00', '13:42:00', '13:42:00', '17:42:00', 'normal', NULL),
(9, 1, '2026-02-02', '13:37:16', '13:42:18', '13:42:22', '13:42:22', 'normal', NULL),
(10, 1, '2026-02-02', '13:37:23', '13:42:31', NULL, NULL, 'normal', NULL),
(11, 4, '2026-02-03', '08:48:00', NULL, NULL, NULL, 'normal', NULL),
(12, 4, '2026-01-31', '09:00:00', NULL, NULL, '17:00:00', 'normal', ''),
(13, 4, '2026-02-04', '08:58:55', NULL, NULL, NULL, 'normal', NULL),
(14, 1, '2026-02-04', '11:30:40', NULL, NULL, '19:43:40', 'normal', NULL),
(15, 1, '2026-02-04', '19:38:48', '19:43:53', '19:43:56', '19:44:00', 'normal', NULL),
(16, 6, '2026-02-04', '20:42:00', NULL, NULL, '23:58:00', 'normal', NULL),
(17, 6, '2026-02-04', '19:57:57', NULL, NULL, NULL, 'normal', NULL),
(18, 1, '2026-02-09', '19:04:37', '19:09:38', NULL, '19:09:38', 'normal', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_validaciones`
--

CREATE TABLE `historial_validaciones` (
  `id_historial` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `accion` enum('APROBADO','RECHAZADO','MODIFICADO') NOT NULL,
  `motivo` text DEFAULT NULL,
  `validado_por` int(11) NOT NULL,
  `validado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `historial_validaciones`
--

INSERT INTO `historial_validaciones` (`id_historial`, `id_solicitud`, `accion`, `motivo`, `validado_por`, `validado_en`) VALUES
(1, 1, 'RECHAZADO', 'lkdjañlk', 1, '2025-12-17 10:46:01'),
(2, 2, 'APROBADO', NULL, 1, '2026-02-02 09:01:48'),
(3, 9, 'RECHAZADO', 'aspdofa jasodjf', 1, '2026-02-02 10:17:36'),
(4, 10, 'APROBADO', NULL, 1, '2026-02-02 12:44:14'),
(5, 11, 'RECHAZADO', 'no te puedes asignar dias festivos', 1, '2026-02-03 08:23:03'),
(6, 12, 'APROBADO', NULL, 1, '2026-02-03 08:23:23'),
(7, 13, 'RECHAZADO', 'no te lo digo mas veces', 1, '2026-02-03 08:25:24'),
(8, 18, 'APROBADO', NULL, 1, '2026-02-04 18:52:26'),
(9, 16, 'RECHAZADO', 'necesito justificante cita médica', 1, '2026-02-04 18:53:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `orden_dia` tinyint(4) DEFAULT 1,
  `tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO') DEFAULT 'TRABAJO',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `horas_totales` decimal(4,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `solo_trabajo` tinyint(4) GENERATED ALWAYS AS (case when `tipo_jornada` = 'TRABAJO' then 1 else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `id_usuario`, `id_empresa`, `fecha`, `orden_dia`, `tipo_jornada`, `hora_inicio`, `hora_fin`, `horas_totales`, `observaciones`, `creado_en`, `actualizado_en`) VALUES
(1, 2, 1, '2026-02-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-02-02 08:59:11', '2026-02-02 08:59:11'),
(4, 4, 1, '2026-02-04', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 09:01:48', '2026-02-02 09:01:48'),
(6, 4, 1, '2026-02-06', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 09:01:48', '2026-02-02 12:44:14'),
(7, 2, 1, '2026-02-06', 1, 'TRABAJO', '09:00:00', '16:00:00', 7.00, NULL, '2026-02-02 09:02:20', '2026-02-02 12:46:40'),
(12, 4, 1, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(13, 4, 1, '2026-02-12', 1, 'MEDICO', '09:00:00', '10:00:00', 1.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(15, 4, 1, '2026-02-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(18, 4, 1, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(20, 4, 1, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(21, 4, 1, '2026-02-16', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(22, 4, 1, '2026-02-17', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(23, 4, 1, '2026-02-18', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(24, 4, 1, '2026-02-19', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(25, 4, 1, '2026-02-20', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(26, 4, 1, '2026-02-11', 1, 'FESTIVO', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(27, 4, 1, '2026-02-10', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(28, 4, 1, '2026-02-13', 1, 'MEDICO', '09:00:00', '10:00:00', 1.00, '', '2026-02-02 12:44:14', '2026-02-02 12:44:14'),
(29, 1, 1, '2026-02-02', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-02-02 12:45:35', '2026-02-02 12:45:35'),
(30, 1, 1, '2026-02-04', 1, 'VACACIONES', '09:00:00', '17:00:00', NULL, NULL, '2026-02-02 12:45:41', '2026-02-02 12:45:41'),
(31, 2, 1, '2026-02-04', 1, 'MEDICO', '09:00:00', '17:00:00', NULL, 'malito :(', '2026-02-02 12:45:52', '2026-02-02 12:45:52'),
(32, 2, 1, '2026-02-05', 1, 'TRABAJO', '09:00:00', '16:00:00', 7.00, NULL, '2026-02-02 12:46:22', '2026-02-04 10:38:06'),
(33, 4, 1, '2026-02-23', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-03 08:23:23', '2026-02-03 08:23:23'),
(34, 4, 1, '2026-02-24', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-03 08:23:23', '2026-02-03 08:23:23'),
(35, 4, 1, '2026-02-25', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-03 08:23:23', '2026-02-03 08:23:23'),
(36, 4, 1, '2026-02-26', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-03 08:23:23', '2026-02-03 08:23:23'),
(37, 4, 1, '2026-02-27', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-03 08:23:23', '2026-02-03 08:23:23'),
(38, 4, 1, '2026-02-02', 1, 'LIBRE', '09:00:00', '17:00:00', NULL, NULL, '2026-02-04 08:32:51', '2026-02-04 08:32:51'),
(39, 2, 1, '2026-02-02', 1, 'TRABAJO', '09:00:00', '15:00:00', 6.00, NULL, '2026-02-04 11:00:06', '2026-02-04 11:06:36'),
(42, 2, 1, '2026-02-02', 1, 'VACACIONES', '09:00:00', '17:00:00', NULL, NULL, '2026-02-04 11:06:08', '2026-02-04 11:06:28'),
(44, 6, 1, '2026-02-08', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(45, 6, 1, '2026-02-15', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(46, 6, 1, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(47, 6, 1, '2026-02-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(48, 6, 1, '2026-02-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(49, 6, 1, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(50, 6, 1, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(51, 6, 1, '2026-02-14', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `mensaje`, `tipo`, `leida`, `fecha_creacion`) VALUES
(1, 4, 'El administrador ha actualizado tu horario para el día 07/02/2026', 'cambio_horario', 1, '2026-02-02 09:59:49'),
(4, 4, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 1, '2026-02-02 10:00:20'),
(6, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-02 10:15:03'),
(8, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-02 12:38:12'),
(11, 2, 'El administrador ha actualizado tu horario para el día 04/02/2026', 'cambio_horario', 0, '2026-02-02 12:45:52'),
(12, 2, 'El administrador ha actualizado tu horario para el día 05/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:22'),
(13, 2, 'El administrador ha actualizado tu horario para el día 06/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:31'),
(14, 2, 'El administrador ha actualizado tu horario para el día 06/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:40'),
(16, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:20:43'),
(18, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:21:15'),
(19, 4, '❌ Tu solicitud de HORARIO_MES ha sido RECHAZADA. Motivo: no te puedes asignar dias festivos', 'resultado_peticion', 1, '2026-02-03 08:23:03'),
(20, 4, '✅ Tu solicitud de HORARIO_MES ha sido APROBADA.', 'resultado_peticion', 1, '2026-02-03 08:23:23'),
(22, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:24:18'),
(23, 4, '❌ Tu solicitud de HORARIO_MES ha sido RECHAZADA. Motivo: no te lo digo mas veces', 'resultado_peticion', 1, '2026-02-03 08:25:24'),
(24, 4, '📝 El administrador ha modificado tu fichaje del día 03/02/2026.', 'cambio_fichaje', 1, '2026-02-03 09:03:46'),
(25, 4, '➕ El administrador ha añadido un nuevo fichaje a tu registro del día 31/01/2026.', 'fichaje_anadido', 1, '2026-02-03 09:04:22'),
(26, 1, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 1, '2026-02-03 09:58:55'),
(27, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 09:58:55'),
(28, 4, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 08:32:51'),
(29, 1, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 1, '2026-02-04 09:21:43'),
(30, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:21:43'),
(31, 1, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 1, '2026-02-04 09:22:39'),
(32, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:22:39'),
(33, 1, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 1, '2026-02-04 09:23:15'),
(34, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:23:15'),
(35, 2, 'El administrador ha actualizado tu horario para el día 05/02/2026', 'cambio_horario', 0, '2026-02-04 10:38:06'),
(36, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:00:06'),
(37, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:05:45'),
(38, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:08'),
(39, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:17'),
(40, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:28'),
(41, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:36'),
(42, 1, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 1, '2026-02-04 18:50:33'),
(43, 2, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 0, '2026-02-04 18:50:33'),
(44, 6, '✅ Tu solicitud de HORARIO_MES ha sido APROBADA.', 'resultado_peticion', 1, '2026-02-04 18:52:26'),
(45, 4, '❌ Tu solicitud de MEDICO ha sido RECHAZADA. Motivo: necesito justificante cita médica', 'resultado_peticion', 0, '2026-02-04 18:53:05'),
(46, 6, '📝 El administrador ha modificado tu fichaje del día 04/02/2026.', 'cambio_fichaje', 1, '2026-02-04 18:58:29'),
(47, 1, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 1, '2026-02-04 19:04:07'),
(48, 2, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 0, '2026-02-04 19:04:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `tipo_reporte` varchar(50) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `generado_por` int(11) NOT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de reportes generados';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_horario`
--

CREATE TABLE `solicitudes_horario` (
  `id_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `tipo_solicitud` enum('HORARIO_MES','VACACIONES','MEDICO') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `motivo_rechazo` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `validado_por` int(11) DEFAULT NULL,
  `validado_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `solicitudes_horario`
--

INSERT INTO `solicitudes_horario` (`id_solicitud`, `id_usuario`, `id_empresa`, `tipo_solicitud`, `fecha_inicio`, `fecha_fin`, `estado`, `motivo_rechazo`, `observaciones`, `validado_por`, `validado_en`, `creado_en`) VALUES
(1, 4, 1, 'HORARIO_MES', '2026-01-01', '2026-01-03', 'RECHAZADO', 'lkdjañlk', 'Solicitud de horario para el mes de January 1970', 1, '2025-12-17 10:46:01', '2025-12-17 10:38:27'),
(2, 4, 1, 'HORARIO_MES', '2026-02-02', '2026-02-06', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-02-02 09:01:48', '2026-02-02 09:00:56'),
(9, 4, 1, 'HORARIO_MES', '2026-02-09', '2026-02-27', 'RECHAZADO', 'aspdofa jasodjf', 'Solicitud de horario para el mes de January 1970', 1, '2026-02-02 10:17:36', '2026-02-02 10:15:03'),
(10, 4, 1, 'MEDICO', '2026-02-02', '2026-02-20', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-02-02 12:44:14', '2026-02-02 12:38:12'),
(11, 4, 1, 'HORARIO_MES', '2026-02-05', '2026-02-05', 'RECHAZADO', 'no te puedes asignar dias festivos', 'Solicitud de horario para el mes de January 1970', 1, '2026-02-03 08:23:03', '2026-02-03 08:20:43'),
(12, 4, 1, 'HORARIO_MES', '2026-02-23', '2026-02-27', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-02-03 08:23:23', '2026-02-03 08:21:15'),
(13, 4, 1, 'HORARIO_MES', '2026-02-05', '2026-02-05', 'RECHAZADO', 'no te lo digo mas veces', 'Solicitud de horario para el mes de January 1970', 1, '2026-02-03 08:25:24', '2026-02-03 08:24:18'),
(14, 4, 1, 'HORARIO_MES', '2026-02-05', '2026-02-05', 'PENDIENTE', NULL, 'Solicitud de horario para el mes de January 1970', NULL, NULL, '2026-02-03 09:58:55'),
(15, 4, 1, 'HORARIO_MES', '2026-03-02', '2026-03-27', 'PENDIENTE', NULL, 'Solicitud de horario para el mes de March 2026', NULL, NULL, '2026-02-04 09:21:43'),
(16, 4, 1, 'MEDICO', '2026-02-05', '2026-02-05', 'RECHAZADO', 'necesito justificante cita médica', 'Solicitud de horario para el mes de January 1970', 1, '2026-02-04 18:53:05', '2026-02-04 09:22:39'),
(17, 4, 1, 'MEDICO', '2026-02-06', '2026-02-06', 'PENDIENTE', NULL, 'Solicitud de horario para el mes de January 1970', NULL, NULL, '2026-02-04 09:23:15'),
(18, 6, 1, 'HORARIO_MES', '2026-02-08', '2026-02-15', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-02-04 18:52:26', '2026-02-04 18:50:33'),
(19, 6, 1, 'HORARIO_MES', '2026-03-08', '2026-03-15', 'PENDIENTE', NULL, 'Solicitud de horario para el mes de March 2026', NULL, NULL, '2026-02-04 19:04:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `NIF` varchar(20) DEFAULT NULL,
  `Numero_Afiliciacion` varchar(30) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `foto_perfil` varchar(255) DEFAULT 'uploads/perfil_default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellidos`, `NIF`, `Numero_Afiliciacion`, `email`, `password_hash`, `activo`, `foto_perfil`, `created_at`) VALUES
(1, 'AdminGlobal', 'Uno', 'NIF001', 'AF001', 'admin.global@example.com', '$2y$10$1VRV4Yu1qjj5qrtgCPLHQudPPgAeXulc116gRiTphW1hkwhQJyvsS', 1, 'secciones/uploads/perfil_1_1770203555.jpeg', '2025-12-17 10:27:38'),
(2, 'AdminEnsenyem', 'Dos', 'NIF002', 'AF002', 'admin.ensenyem@example.com', '$2y$10$sV/vGfLN2uzMNZ7vhTITguY657Fo5Kzo3ivRmMRclE922IiHEFAxS', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38'),
(3, 'AdminSM', 'Tres', 'NIF003', 'AF003', 'admin.sm@example.com', '$2y$10$vMmuGbP/zM3thcIbBdQEpOw4wXoNK4uxgdJ50TD7Ltob1rJ4x2.7a', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38'),
(4, 'Usuario1', 'Cuatro', 'NIF004', 'AF004', 'usuario1@example.com', '$2y$10$H2tCqTTD6wnf9HTNeJ1L5O9Tmomw/ZMQN1ShK5dS7DhrgeslAcsaO', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38'),
(5, 'Usuario2', 'Cinco', 'NIF005', 'AF005', 'usuario2@example.com', '$2y$10$pQywce28rJfzrR6PUvd6gOQbZ5p9Bp53t2pjD1sbl5ZrNv96A8lU.', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38'),
(6, 'Perico', 'Palote', NULL, NULL, 'valencia2026@gmail.com', '$2y$10$O.4MwdJhsYuhFnjU03PT5uDAVWVG3pqoZoUffKnEf9d7LRxacYUZa', 1, 'secciones/uploads/perfil_6_1770230847.jpeg', '2026-02-04 18:45:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id_vacacion` int(11) NOT NULL,
  `id_solicitud` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias_totales` int(11) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `comentario` text DEFAULT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_resolucion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones_acumulado`
--

CREATE TABLE `vacaciones_acumulado` (
  `id_vacaciones_acum` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `año` year(4) NOT NULL,
  `dias_totales` int(11) NOT NULL,
  `dias_utilizados` int(11) NOT NULL DEFAULT 0,
  `dias_restantes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_horarios_dia`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_horarios_dia` (
`id_horario` int(11)
,`id_usuario` int(11)
,`id_empresa` int(11)
,`fecha` date
,`orden_dia` tinyint(4)
,`tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO')
,`hora_inicio` time
,`hora_fin` time
,`horas_totales` decimal(4,2)
,`observaciones` text
,`nombre` varchar(100)
,`apellidos` varchar(150)
,`empresa_nombre` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_horarios_dia`
--
DROP TABLE IF EXISTS `vista_horarios_dia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_horarios_dia`  AS SELECT `h`.`id_horario` AS `id_horario`, `h`.`id_usuario` AS `id_usuario`, `h`.`id_empresa` AS `id_empresa`, `h`.`fecha` AS `fecha`, `h`.`orden_dia` AS `orden_dia`, `h`.`tipo_jornada` AS `tipo_jornada`, `h`.`hora_inicio` AS `hora_inicio`, `h`.`hora_fin` AS `hora_fin`, `h`.`horas_totales` AS `horas_totales`, `h`.`observaciones` AS `observaciones`, `u`.`nombre` AS `nombre`, `u`.`apellidos` AS `apellidos`, `e`.`nombre` AS `empresa_nombre` FROM ((`horarios` `h` join `usuario` `u` on(`h`.`id_usuario` = `u`.`id_usuario`)) join `empresa` `e` on(`h`.`id_empresa` = `e`.`id_empresa`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_solicitud_horario`
--
ALTER TABLE `detalle_solicitud_horario`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_id_solicitud` (`id_solicitud`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `CIF` (`CIF`);

--
-- Indices de la tabla `empresa_usuario`
--
ALTER TABLE `empresa_usuario`
  ADD PRIMARY KEY (`id_empresa`,`id_usuario`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `fichaje`
--
ALTER TABLE `fichaje`
  ADD PRIMARY KEY (`id_fichaje`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `historial_validaciones`
--
ALTER TABLE `historial_validaciones`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_solicitud` (`id_solicitud`),
  ADD KEY `idx_validador` (`validado_por`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD UNIQUE KEY `uq_trabajo_por_dia` (`id_usuario`,`id_empresa`,`fecha`,`solo_trabajo`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_empresa` (`id_empresa`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `generado_por` (`generado_por`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`anio`,`mes`),
  ADD KEY `idx_empresa` (`id_empresa`);

--
-- Indices de la tabla `solicitudes_horario`
--
ALTER TABLE `solicitudes_horario`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_empresa` (`id_empresa`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `fk_sh_validador` (`validado_por`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `NIF` (`NIF`);

--
-- Indices de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id_vacacion`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_solicitud` (`id_solicitud`);

--
-- Indices de la tabla `vacaciones_acumulado`
--
ALTER TABLE `vacaciones_acumulado`
  ADD PRIMARY KEY (`id_vacaciones_acum`),
  ADD UNIQUE KEY `uk_usuario_año` (`id_usuario`,`año`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_solicitud_horario`
--
ALTER TABLE `detalle_solicitud_horario`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `fichaje`
--
ALTER TABLE `fichaje`
  MODIFY `id_fichaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `historial_validaciones`
--
ALTER TABLE `historial_validaciones`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes_horario`
--
ALTER TABLE `solicitudes_horario`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id_vacacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vacaciones_acumulado`
--
ALTER TABLE `vacaciones_acumulado`
  MODIFY `id_vacaciones_acum` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresa_usuario`
--
ALTER TABLE `empresa_usuario`
  ADD CONSTRAINT `fk_eu_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `fk_horarios_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`generado_por`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_horario`
--
ALTER TABLE `solicitudes_horario`
  ADD CONSTRAINT `fk_sh_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sh_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sh_validador` FOREIGN KEY (`validado_por`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `fk_vac_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_horario` (`id_solicitud`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vac_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacaciones_acumulado`
--
ALTER TABLE `vacaciones_acumulado`
  ADD CONSTRAINT `fk_va_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
