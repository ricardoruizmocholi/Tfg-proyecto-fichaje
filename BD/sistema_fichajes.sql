-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-04-2026 a las 11:35:53
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
  `tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO','PARTIDA_M','PARTIDA_T') DEFAULT 'TRABAJO',
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
(176, 19, '2026-03-15', 1, 'LIBRE', NULL, NULL, NULL, 'Copiado del mes anterior'),
(177, 20, '2026-05-08', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(178, 20, '2026-04-07', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, ''),
(179, 20, '2026-04-07', 1, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, ''),
(180, 21, '2026-04-08', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, ''),
(181, 21, '2026-04-08', 1, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, ''),
(182, 21, '2026-04-08', 1, 'MEDICO', '08:00:00', '09:00:00', 1.00, ''),
(183, 22, '2026-04-11', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, ''),
(184, 22, '2026-04-11', 1, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, ''),
(185, 23, '2026-04-12', 1, 'LIBRE', NULL, NULL, NULL, ''),
(186, 24, '2026-04-11', 1, 'LIBRE', NULL, NULL, NULL, ''),
(187, 25, '2026-04-20', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(188, 25, '2026-04-21', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(189, 25, '2026-04-22', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(190, 25, '2026-04-23', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(191, 25, '2026-04-24', 1, 'VACACIONES', NULL, NULL, NULL, ''),
(192, 26, '2026-06-01', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(193, 26, '2026-06-02', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(194, 26, '2026-06-03', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(195, 26, '2026-06-04', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(196, 26, '2026-06-05', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(197, 26, '2026-06-08', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(198, 26, '2026-06-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(199, 26, '2026-06-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(200, 26, '2026-06-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, ''),
(201, 26, '2026-06-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id_documento`, `id_usuario`, `id_empresa`, `titulo`, `mes`, `anio`, `ruta_archivo`, `fecha_subida`) VALUES
(2, 4, 1, 'Nomina abril', 4, 2026, 'uploads/nominas/nomina_4_4_2026_1775723609.pdf', '2026-04-09 08:33:29');

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
(1, 'empresa 1', 'Dirección 1', '123456789', 'CIF123456', 'CCC123456', 'panel_ensenyem'),
(2, 'empresa 2', 'Dirección 2', '987654321', 'CIF654321', 'CCC654321', 'panel_sm');

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
(18, 1, '2026-02-09', '19:04:37', '19:09:38', NULL, '19:09:38', 'normal', NULL),
(19, 2, '2026-02-26', '12:48:00', NULL, NULL, '14:48:00', 'normal', ''),
(20, 4, '2026-04-06', '01:59:00', '13:04:00', '13:04:00', '13:04:00', 'normal', NULL),
(21, 4, '2026-04-06', '13:25:57', NULL, NULL, '17:44:24', 'normal', NULL),
(26, 1, '2026-04-06', '11:41:00', '17:41:00', '17:41:00', '23:41:00', 'normal', NULL),
(27, 1, '2026-04-07', '08:55:00', '09:55:00', '09:55:00', '14:55:00', 'normal', NULL),
(28, 1, '2026-04-07', '17:55:00', NULL, NULL, '20:17:00', 'normal', NULL),
(32, 4, '2026-04-07', '10:26:00', NULL, NULL, '14:07:00', 'normal', NULL),
(33, 1, '2026-04-09', '09:13:00', NULL, NULL, '19:47:00', 'normal', NULL),
(34, 4, '2026-04-09', '10:36:00', NULL, NULL, '17:07:00', 'normal', NULL),
(35, 1, '2026-04-10', '11:06:46', NULL, NULL, '20:32:43', 'normal', NULL),
(36, 4, '2026-04-10', '10:30:00', NULL, NULL, '21:47:00', 'normal', NULL),
(37, 1, '2026-04-11', '10:44:00', NULL, NULL, '22:46:00', 'normal', NULL),
(38, 1, '2026-04-08', '04:48:00', NULL, NULL, '21:49:00', 'normal', NULL),
(39, 1, '2026-04-01', '04:49:00', NULL, NULL, '20:49:00', 'normal', NULL),
(40, 1, '2026-04-02', '01:50:00', NULL, NULL, '23:50:00', 'normal', NULL),
(41, 1, '2026-04-03', '01:51:00', NULL, NULL, '23:59:00', 'normal', NULL),
(42, 1, '2026-04-04', '01:51:00', NULL, NULL, '23:51:00', 'normal', NULL),
(43, 1, '2026-04-05', '01:52:00', NULL, NULL, '23:52:00', 'normal', NULL),
(44, 1, '2026-04-13', '00:54:00', NULL, NULL, '23:54:00', 'normal', NULL),
(45, 1, '2026-04-14', '00:01:00', NULL, NULL, '22:55:00', 'normal', NULL),
(46, 1, '2026-04-15', '00:56:00', NULL, NULL, '23:56:00', 'normal', NULL);

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
(9, 16, 'RECHAZADO', 'necesito justificante cita médica', 1, '2026-02-04 18:53:05'),
(10, 20, 'APROBADO', NULL, 1, '2026-04-06 11:28:12'),
(11, 19, 'RECHAZADO', 'xg', 1, '2026-04-06 11:28:21'),
(12, 21, 'APROBADO', NULL, 1, '2026-04-06 14:45:44'),
(13, 17, 'RECHAZADO', 'no ya tubiste', 1, '2026-04-06 16:27:33'),
(14, 14, 'RECHAZADO', 'porque si', 1, '2026-04-06 16:27:59'),
(15, 15, 'RECHAZADO', 'asd asda d', 1, '2026-04-06 16:28:14'),
(16, 22, 'RECHAZADO', 'no te necesito un sabado', 1, '2026-04-06 16:28:35'),
(17, 23, 'APROBADO', NULL, 1, '2026-04-06 16:28:43'),
(18, 24, 'RECHAZADO', 'lkjzx', 1, '2026-04-10 18:33:15'),
(19, 25, 'APROBADO', NULL, 1, '2026-04-10 18:33:20'),
(20, 26, 'RECHAZADO', 'no me sale para que son estos dias', 1, '2026-04-10 18:34:16');

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
  `tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO','PARTIDA_M','PARTIDA_T') DEFAULT 'TRABAJO',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `horas_totales` decimal(4,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(13, 4, 1, '2026-02-12', 2, 'MEDICO', '09:00:00', '10:00:00', 1.00, '', '2026-02-02 12:44:14', '2026-04-06 10:22:53'),
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
(28, 4, 1, '2026-02-13', 2, 'MEDICO', '09:00:00', '10:00:00', 1.00, '', '2026-02-02 12:44:14', '2026-04-06 10:22:53'),
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
(42, 2, 1, '2026-02-02', 2, 'VACACIONES', '09:00:00', '17:00:00', NULL, NULL, '2026-02-04 11:06:08', '2026-04-06 10:22:53'),
(44, 6, 1, '2026-02-08', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(45, 6, 1, '2026-02-15', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(46, 6, 1, '2026-02-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(47, 6, 1, '2026-02-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(48, 6, 1, '2026-02-11', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(49, 6, 1, '2026-02-12', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(50, 6, 1, '2026-02-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(51, 6, 1, '2026-02-14', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-02-04 18:52:26', '2026-02-04 18:52:26'),
(52, 4, 1, '2026-05-08', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, '', '2026-04-06 11:28:12', '2026-04-06 11:28:12'),
(61, 4, 1, '2026-04-08', 1, 'PARTIDA_M', '09:00:00', '14:00:00', NULL, NULL, '2026-04-06 15:42:15', '2026-04-06 15:42:15'),
(63, 4, 1, '2026-04-06', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, NULL, '2026-04-06 15:42:49', '2026-04-06 15:42:49'),
(64, 4, 1, '2026-04-07', 3, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, NULL, '2026-04-06 15:42:49', '2026-04-06 15:42:49'),
(66, 4, 1, '2026-04-09', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, NULL, '2026-04-06 15:42:49', '2026-04-06 15:42:49'),
(67, 4, 1, '2026-04-10', 1, 'PARTIDA_M', '09:00:00', '14:00:00', 5.00, NULL, '2026-04-06 15:42:49', '2026-04-06 15:42:49'),
(68, 4, 1, '2026-04-06', 2, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, NULL, '2026-04-06 15:43:23', '2026-04-06 15:43:23'),
(69, 4, 1, '2026-04-07', 4, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, NULL, '2026-04-06 15:43:23', '2026-04-06 15:43:23'),
(70, 4, 1, '2026-04-08', 2, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, NULL, '2026-04-06 15:43:23', '2026-04-06 15:43:23'),
(71, 4, 1, '2026-04-09', 2, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, NULL, '2026-04-06 15:43:23', '2026-04-06 15:43:23'),
(72, 4, 1, '2026-04-10', 2, 'PARTIDA_T', '16:00:00', '19:00:00', 3.00, NULL, '2026-04-06 15:43:23', '2026-04-06 15:43:23'),
(73, 4, 1, '2026-04-12', 1, 'LIBRE', NULL, NULL, NULL, '', '2026-04-06 16:28:43', '2026-04-06 16:28:43'),
(74, 6, 1, '2026-04-08', 1, 'PARTIDA_M', '09:00:00', '14:00:00', NULL, NULL, '2026-04-07 10:27:19', '2026-04-07 10:27:19'),
(75, 6, 1, '2026-04-08', 2, 'PARTIDA_T', '16:00:00', '19:00:00', NULL, NULL, '2026-04-07 10:27:27', '2026-04-07 10:27:27'),
(76, 1, 1, '2026-04-07', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(77, 1, 1, '2026-04-08', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(78, 1, 1, '2026-04-09', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(79, 1, 1, '2026-04-10', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(80, 1, 1, '2026-04-13', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(81, 1, 1, '2026-04-14', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(82, 1, 1, '2026-04-15', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(83, 1, 1, '2026-04-16', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(85, 1, 1, '2026-04-20', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(86, 1, 1, '2026-04-21', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(87, 1, 1, '2026-04-22', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(88, 1, 1, '2026-04-23', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(89, 1, 1, '2026-04-24', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(90, 1, 1, '2026-04-27', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(91, 1, 1, '2026-04-28', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(92, 1, 1, '2026-04-29', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(93, 1, 1, '2026-04-30', 1, 'TRABAJO', '09:00:00', '17:00:00', 8.00, NULL, '2026-04-07 10:27:46', '2026-04-07 10:27:46'),
(94, 4, 1, '2026-04-20', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-04-10 18:33:20', '2026-04-10 18:33:20'),
(95, 4, 1, '2026-04-21', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-04-10 18:33:20', '2026-04-10 18:33:20'),
(96, 4, 1, '2026-04-22', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-04-10 18:33:20', '2026-04-10 18:33:20'),
(97, 4, 1, '2026-04-23', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-04-10 18:33:20', '2026-04-10 18:33:20'),
(98, 4, 1, '2026-04-24', 1, 'VACACIONES', NULL, NULL, NULL, '', '2026-04-10 18:33:20', '2026-04-10 18:33:20'),
(99, 6, 1, '2026-04-20', 1, 'VACACIONES', NULL, NULL, NULL, NULL, '2026-04-11 08:43:33', '2026-04-11 08:43:33'),
(100, 6, 1, '2026-04-21', 1, 'VACACIONES', NULL, NULL, NULL, NULL, '2026-04-11 08:43:33', '2026-04-11 08:43:33'),
(101, 6, 1, '2026-04-22', 1, 'VACACIONES', NULL, NULL, NULL, NULL, '2026-04-11 08:43:33', '2026-04-11 08:43:33'),
(102, 6, 1, '2026-04-23', 1, 'VACACIONES', NULL, NULL, NULL, NULL, '2026-04-11 08:43:33', '2026-04-11 08:43:33'),
(103, 6, 1, '2026-04-24', 1, 'VACACIONES', NULL, NULL, NULL, NULL, '2026-04-11 08:43:33', '2026-04-11 08:43:33'),
(104, 6, 1, '2026-04-17', 1, 'FESTIVO', NULL, NULL, NULL, NULL, '2026-04-11 08:44:10', '2026-04-11 08:44:10'),
(105, 4, 1, '2026-04-17', 1, 'FESTIVO', NULL, NULL, NULL, NULL, '2026-04-11 08:44:23', '2026-04-11 08:44:23'),
(106, 1, 1, '2026-04-17', 1, 'FESTIVO', NULL, NULL, NULL, NULL, '2026-04-11 08:44:36', '2026-04-11 08:44:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horas_extra`
--

CREATE TABLE `horas_extra` (
  `id_hora_extra` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `horas_acumuladas` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horas_extra`
--

INSERT INTO `horas_extra` (`id_hora_extra`, `id_usuario`, `anio`, `horas_acumuladas`) VALUES
(1, 1, 2026, 0.00),
(2, 2, 2026, 0.00),
(3, 3, 2026, 0.00),
(4, 4, 2026, 0.00),
(5, 5, 2026, 0.00),
(6, 6, 2026, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id_incidencia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `asunto` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `respuesta` text DEFAULT NULL,
  `estado` enum('PENDIENTE','RESUELTA') DEFAULT 'PENDIENTE',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `incidencias`
--

INSERT INTO `incidencias` (`id_incidencia`, `id_usuario`, `asunto`, `mensaje`, `respuesta`, `estado`, `fecha_creacion`, `fecha_respuesta`) VALUES
(1, 4, 'Fallo del sistema', 'paso  trlalalala', NULL, 'PENDIENTE', '2026-04-07 08:53:18', NULL);

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
(6, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-02 10:15:03'),
(8, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-02 12:38:12'),
(11, 2, 'El administrador ha actualizado tu horario para el día 04/02/2026', 'cambio_horario', 0, '2026-02-02 12:45:52'),
(12, 2, 'El administrador ha actualizado tu horario para el día 05/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:22'),
(13, 2, 'El administrador ha actualizado tu horario para el día 06/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:31'),
(14, 2, 'El administrador ha actualizado tu horario para el día 06/02/2026', 'cambio_horario', 0, '2026-02-02 12:46:40'),
(16, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:20:43'),
(18, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:21:15'),
(22, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 08:24:18'),
(27, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-03 09:58:55'),
(30, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:21:43'),
(32, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:22:39'),
(34, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-02-04 09:23:15'),
(35, 2, 'El administrador ha actualizado tu horario para el día 05/02/2026', 'cambio_horario', 0, '2026-02-04 10:38:06'),
(36, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:00:06'),
(37, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:05:45'),
(38, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:08'),
(39, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:17'),
(40, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:28'),
(41, 2, 'El administrador ha actualizado tu horario para el día 02/02/2026', 'cambio_horario', 0, '2026-02-04 11:06:36'),
(43, 2, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 0, '2026-02-04 18:50:33'),
(44, 6, '✅ Tu solicitud de HORARIO_MES ha sido APROBADA.', 'resultado_peticion', 1, '2026-02-04 18:52:26'),
(46, 6, '📝 El administrador ha modificado tu fichaje del día 04/02/2026.', 'cambio_fichaje', 1, '2026-02-04 18:58:29'),
(48, 2, 'Nueva solicitud de HORARIO_MES de Perico Palote', 'nueva_peticion', 0, '2026-02-04 19:04:07'),
(49, 2, '➕ El administrador ha añadido un nuevo fichaje a tu registro del día 26/02/2026.', 'fichaje_anadido', 0, '2026-02-26 11:47:58'),
(50, 2, '📝 El administrador ha modificado tu fichaje del día 26/02/2026.', 'cambio_fichaje', 0, '2026-02-26 11:48:17'),
(52, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-06 11:26:55'),
(54, 6, '❌ Tu solicitud de HORARIO_MES ha sido RECHAZADA. Motivo: xg', 'resultado_peticion', 0, '2026-04-06 11:28:21'),
(57, 2, 'Nueva solicitud de MEDICO de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-06 14:44:20'),
(64, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-06 16:26:05'),
(66, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-06 16:26:38'),
(75, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-07 09:16:32'),
(76, 6, 'El administrador ha actualizado tu horario para el día 08/04/2026', 'cambio_horario', 0, '2026-04-07 10:27:19'),
(77, 6, 'El administrador ha actualizado tu horario para el día 08/04/2026', 'cambio_horario', 0, '2026-04-07 10:27:27'),
(78, 4, 'Tienes una nueva nómina/documento disponible: Nomina abril', NULL, 1, '2026-04-09 08:12:37'),
(79, 4, ' Tienes un nuevo documento disponible: Nomina abril', NULL, 1, '2026-04-09 08:33:29'),
(80, 4, '📝 El administrador ha modificado tu fichaje del día 10/04/2026.', 'cambio_fichaje', 1, '2026-04-10 09:07:12'),
(81, 4, '📝 El administrador ha modificado tu fichaje del día 10/04/2026.', 'cambio_fichaje', 1, '2026-04-10 09:07:38'),
(83, 2, 'Nueva solicitud de VACACIONES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-10 18:31:18'),
(85, 2, 'Nueva solicitud de HORARIO_MES de Usuario1 Cuatro', 'nueva_peticion', 0, '2026-04-10 18:32:00'),
(86, 4, '❌ Tu solicitud de HORARIO_MES ha sido RECHAZADA. Motivo: lkjzx', 'resultado_peticion', 0, '2026-04-10 18:33:15'),
(87, 4, '✅ Tu solicitud de VACACIONES ha sido APROBADA.', 'resultado_peticion', 0, '2026-04-10 18:33:20'),
(88, 4, '❌ Tu solicitud de HORARIO_MES ha sido RECHAZADA. Motivo: no me sale para que son estos dias', 'resultado_peticion', 0, '2026-04-10 18:34:16'),
(89, 6, 'El administrador ha actualizado tu horario para el día 17/04/2026', 'cambio_horario', 0, '2026-04-11 08:44:10'),
(90, 4, 'El administrador ha actualizado tu horario para el día 17/04/2026', 'cambio_horario', 0, '2026-04-11 08:44:23'),
(93, 4, '📝 El administrador ha modificado tu fichaje del día 11/04/2026.', 'cambio_fichaje', 0, '2026-04-11 08:47:12'),
(95, 4, '📝 El administrador ha modificado tu fichaje del día 11/04/2026.', 'cambio_fichaje', 0, '2026-04-11 08:47:28'),
(96, 4, '📝 El administrador ha modificado tu fichaje del día 11/04/2026.', 'cambio_fichaje', 0, '2026-04-11 08:47:45'),
(102, 4, '📝 El administrador ha modificado tu fichaje del día 11/04/2026.', 'cambio_fichaje', 0, '2026-04-11 08:50:46');

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

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`id_reporte`, `id_usuario`, `id_empresa`, `tipo_reporte`, `mes`, `anio`, `fecha_generacion`, `generado_por`, `ruta_archivo`) VALUES
(3, 2, 1, 'registro_jornada', 3, 2026, '2026-02-26 11:45:34', 1, 'reportes_pdf/reporte_2_3_2026_1772106334.pdf'),
(16, 2, 1, 'anual', 0, 2026, '2026-04-10 08:51:18', 1, 'reportes_pdf/reporte_anual_2_2026_1775811078.pdf');

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
(14, 4, 1, 'HORARIO_MES', '2026-02-05', '2026-02-05', 'RECHAZADO', 'porque si', 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 16:27:59', '2026-02-03 09:58:55'),
(15, 4, 1, 'HORARIO_MES', '2026-03-02', '2026-03-27', 'RECHAZADO', 'asd asda d', 'Solicitud de horario para el mes de March 2026', 1, '2026-04-06 16:28:14', '2026-02-04 09:21:43'),
(16, 4, 1, 'MEDICO', '2026-02-05', '2026-02-05', 'RECHAZADO', 'necesito justificante cita médica', 'Solicitud de horario para el mes de January 1970', 1, '2026-02-04 18:53:05', '2026-02-04 09:22:39'),
(17, 4, 1, 'MEDICO', '2026-02-06', '2026-02-06', 'RECHAZADO', 'no ya tubiste', 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 16:27:33', '2026-02-04 09:23:15'),
(18, 6, 1, 'HORARIO_MES', '2026-02-08', '2026-02-15', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-02-04 18:52:26', '2026-02-04 18:50:33'),
(19, 6, 1, 'HORARIO_MES', '2026-03-08', '2026-03-15', 'RECHAZADO', 'xg', 'Solicitud de horario para el mes de March 2026', 1, '2026-04-06 11:28:21', '2026-02-04 19:04:07'),
(20, 4, 1, 'HORARIO_MES', '2026-04-07', '2026-05-08', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 11:28:12', '2026-04-06 11:26:55'),
(21, 4, 1, 'MEDICO', '2026-04-08', '2026-04-08', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 14:45:44', '2026-04-06 14:44:20'),
(22, 4, 1, 'HORARIO_MES', '2026-04-11', '2026-04-11', 'RECHAZADO', 'no te necesito un sabado', 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 16:28:35', '2026-04-06 16:26:05'),
(23, 4, 1, 'HORARIO_MES', '2026-04-12', '2026-04-12', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-04-06 16:28:43', '2026-04-06 16:26:38'),
(24, 4, 1, 'HORARIO_MES', '2026-04-11', '2026-04-11', 'RECHAZADO', 'lkjzx', 'Solicitud de horario para el mes de January 1970', 1, '2026-04-10 18:33:15', '2026-04-07 09:16:32'),
(25, 4, 1, 'VACACIONES', '2026-04-20', '2026-04-24', 'APROBADO', NULL, 'Solicitud de horario para el mes de January 1970', 1, '2026-04-10 18:33:20', '2026-04-10 18:31:18'),
(26, 4, 1, 'HORARIO_MES', '2026-06-01', '2026-06-12', 'RECHAZADO', 'no me sale para que son estos dias', 'Solicitud de horario para el mes de January 1970', 1, '2026-04-10 18:34:16', '2026-04-10 18:32:00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_contrato` enum('completa','parcial') DEFAULT 'completa',
  `horas_contrato_mes` int(11) DEFAULT 160,
  `vacaciones_totales_anuales` decimal(4,2) DEFAULT 30.00,
  `horas_extra_anuales_limite` int(11) DEFAULT 80
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellidos`, `NIF`, `Numero_Afiliciacion`, `email`, `password_hash`, `activo`, `foto_perfil`, `created_at`, `tipo_contrato`, `horas_contrato_mes`, `vacaciones_totales_anuales`, `horas_extra_anuales_limite`) VALUES
(1, 'AdminGlobal', 'Uno', 'NIF001', 'AF001', 'admin.global@example.com', '$2y$10$1VRV4Yu1qjj5qrtgCPLHQudPPgAeXulc116gRiTphW1hkwhQJyvsS', 1, 'secciones/uploads/perfil_1_1775548626.jpeg', '2025-12-17 10:27:38', 'completa', 160, 30.00, 80),
(2, 'AdminEnsenyem', 'Dos', 'NIF002', 'AF002', 'admin.ensenyem@example.com', '$2y$10$sV/vGfLN2uzMNZ7vhTITguY657Fo5Kzo3ivRmMRclE922IiHEFAxS', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38', 'completa', 160, 30.00, 80),
(3, 'AdminSM', 'Tres', 'NIF003', 'AF003', 'admin.sm@example.com', '$2y$10$vMmuGbP/zM3thcIbBdQEpOw4wXoNK4uxgdJ50TD7Ltob1rJ4x2.7a', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38', 'completa', 160, 30.00, 80),
(4, 'Usuario1', 'Cuatro', 'NIF004', 'AF004', 'usuario1@example.com', '$2y$10$H2tCqTTD6wnf9HTNeJ1L5O9Tmomw/ZMQN1ShK5dS7DhrgeslAcsaO', 1, 'secciones/uploads/perfil_4_1775550562.jpg', '2025-12-17 10:27:38', 'completa', 160, 30.00, 80),
(5, 'Usuario2', 'Cinco', 'NIF005', 'AF005', 'usuario2@example.com', '$2y$10$pQywce28rJfzrR6PUvd6gOQbZ5p9Bp53t2pjD1sbl5ZrNv96A8lU.', 1, 'uploads/perfil_default.png', '2025-12-17 10:27:38', 'completa', 160, 30.00, 80),
(6, 'Perico', 'Palote', NULL, NULL, 'valencia2026@gmail.com', '$2y$10$O.4MwdJhsYuhFnjU03PT5uDAVWVG3pqoZoUffKnEf9d7LRxacYUZa', 1, 'secciones/uploads/perfil_6_1770230847.jpeg', '2026-02-04 18:45:37', 'completa', 160, 30.00, 80);

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
  `dias_restantes` int(11) NOT NULL,
  `dias_disfrutados` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `vacaciones_acumulado`
--

INSERT INTO `vacaciones_acumulado` (`id_vacaciones_acum`, `id_usuario`, `año`, `dias_totales`, `dias_utilizados`, `dias_restantes`, `dias_disfrutados`) VALUES
(1, 4, '2026', 30, 5, 25, 0.00),
(2, 1, '2026', 30, 0, 30, 0.00);

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
,`tipo_jornada` enum('TRABAJO','VACACIONES','MEDICO','LIBRE','FESTIVO','PARTIDA_M','PARTIDA_T')
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
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_empresa` (`id_empresa`);

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
  ADD UNIQUE KEY `uq_horario_por_orden` (`id_usuario`,`id_empresa`,`fecha`,`orden_dia`),
  ADD UNIQUE KEY `uq_horario_orden` (`id_usuario`,`id_empresa`,`fecha`,`orden_dia`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_empresa` (`id_empresa`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `horas_extra`
--
ALTER TABLE `horas_extra`
  ADD PRIMARY KEY (`id_hora_extra`),
  ADD UNIQUE KEY `uq_usuario_anio` (`id_usuario`,`anio`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `id_usuario` (`id_usuario`);

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
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `fichaje`
--
ALTER TABLE `fichaje`
  MODIFY `id_fichaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `historial_validaciones`
--
ALTER TABLE `historial_validaciones`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `horas_extra`
--
ALTER TABLE `horas_extra`
  MODIFY `id_hora_extra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `solicitudes_horario`
--
ALTER TABLE `solicitudes_horario`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id_vacaciones_acum` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE;

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
-- Filtros para la tabla `horas_extra`
--
ALTER TABLE `horas_extra`
  ADD CONSTRAINT `horas_extra_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

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
