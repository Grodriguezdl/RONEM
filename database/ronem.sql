-- RONEM - Estructura de la base de datos con datos de catálogo y usuarios de prueba.
-- No contiene datos personales reales.
-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Tiempo de generación: 27-07-2026 a las 23:36:07
-- Versión del servidor: 11.8.8-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ronem`
--
CREATE DATABASE IF NOT EXISTS `ronem` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ronem`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Canjes`
--

DROP TABLE IF EXISTS `Canjes`;
CREATE TABLE `Canjes` (
  `Id_canje` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_recompensa` int(11) NOT NULL,
  `Puntos_utilizados` int(11) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Pendiente','Disponible','Utilizado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `Codigo_canje` varchar(30) NOT NULL,
  `Fecha_utilizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Categorias`
--

DROP TABLE IF EXISTS `Categorias`;
CREATE TABLE `Categorias` (
  `Id_categoria` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Categorias`
--

INSERT INTO `Categorias` (`Id_categoria`, `Nombre`) VALUES(1, 'Accesorios');
INSERT INTO `Categorias` (`Id_categoria`, `Nombre`) VALUES(2, 'Repuestos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Detalle_venta`
--

DROP TABLE IF EXISTS `Detalle_venta`;
CREATE TABLE `Detalle_venta` (
  `Id_detalle` int(11) NOT NULL,
  `Id_venta` int(11) DEFAULT NULL,
  `Id_producto` int(11) DEFAULT NULL,
  `Cantidad` int(11) DEFAULT NULL,
  `Precio_unidad` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Detalle_venta`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Estados`
--

DROP TABLE IF EXISTS `Estados`;
CREATE TABLE `Estados` (
  `Id_estado` int(11) NOT NULL,
  `Nombre` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Estados`
--

INSERT INTO `Estados` (`Id_estado`, `Nombre`) VALUES(1, 'Pendiente');
INSERT INTO `Estados` (`Id_estado`, `Nombre`) VALUES(2, 'Aprobado');
INSERT INTO `Estados` (`Id_estado`, `Nombre`) VALUES(3, 'Rechazado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Estados_motoescuela`
--

DROP TABLE IF EXISTS `Estados_motoescuela`;
CREATE TABLE `Estados_motoescuela` (
  `Id_estado_motoescuela` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Porcentaje_progreso` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `Orden_estado` int(11) NOT NULL DEFAULT 1,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `Fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `Estados_motoescuela`
--

INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(1, 'Solicitud recibida', 'La solicitud fue recibida y se encuentra pendiente de revisión.', 0, 1, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(2, 'Inscripción confirmada', 'La inscripción del estudiante fue aprobada.', 10, 2, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(3, 'Curso iniciado', 'El estudiante comenzó formalmente el curso.', 20, 3, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(4, 'Clases teóricas', 'El estudiante se encuentra realizando la fase teórica.', 40, 4, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(5, 'Práctica básica', 'El estudiante inició las prácticas básicas de conducción.', 60, 5, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(6, 'Práctica avanzada', 'El estudiante se encuentra realizando prácticas avanzadas.', 80, 6, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(7, 'Evaluación final', 'El estudiante está realizando su evaluación final.', 90, 7, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(8, 'Curso completado', 'El estudiante completó satisfactoriamente el curso.', 100, 8, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(9, 'Curso suspendido', 'El proceso del estudiante se encuentra suspendido.', 0, 9, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(10, 'Curso cancelado', 'El proceso del estudiante fue cancelado.', 0, 10, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(11, 'Solicitud recibida', 'La solicitud fue recibida y se encuentra pendiente de revisión.', 0, 1, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(12, 'Inscripción confirmada', 'La inscripción del estudiante fue aprobada.', 10, 2, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(13, 'Curso iniciado', 'El estudiante comenzó formalmente el curso.', 20, 3, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(14, 'Clases teóricas', 'El estudiante se encuentra realizando la fase teórica.', 40, 4, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(15, 'Práctica básica', 'El estudiante inició las prácticas básicas de conducción.', 60, 5, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(16, 'Práctica avanzada', 'El estudiante se encuentra realizando prácticas avanzadas.', 80, 6, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(17, 'Evaluación final', 'El estudiante está realizando su evaluación final.', 90, 7, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(18, 'Curso completado', 'El estudiante completó satisfactoriamente el curso.', 100, 8, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(19, 'Curso suspendido', 'El proceso del estudiante se encuentra suspendido.', 0, 9, 1, '2026-07-22 06:25:06');
INSERT INTO `Estados_motoescuela` (`Id_estado_motoescuela`, `Nombre`, `Descripcion`, `Porcentaje_progreso`, `Orden_estado`, `Estado`, `Fecha_creacion`) VALUES(20, 'Curso cancelado', 'El proceso del estudiante fue cancelado.', 0, 10, 1, '2026-07-22 06:25:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Estado_mantenimiento`
--

DROP TABLE IF EXISTS `Estado_mantenimiento`;
CREATE TABLE `Estado_mantenimiento` (
  `Id_estado` int(11) NOT NULL,
  `Nombre` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Estado_mantenimiento`
--

INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(1, 'Recibida');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(2, 'En Diagnostico');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(3, 'Esperando Repuesto');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(4, 'En Reparacion');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(5, 'Prueba de Ruta');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(6, 'Lista para Entrega');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(7, 'Entregada');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(8, 'Cancelada');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(9, 'Garantia');
INSERT INTO `Estado_mantenimiento` (`Id_estado`, `Nombre`) VALUES(10, 'Finalizada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Fidelizacion`
--

DROP TABLE IF EXISTS `Fidelizacion`;
CREATE TABLE `Fidelizacion` (
  `Id_fidelizacion` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Puntos_disponibles` int(11) NOT NULL DEFAULT 0,
  `Total_puntos_ganados` int(11) NOT NULL DEFAULT 0,
  `Total_puntos_usados` int(11) NOT NULL DEFAULT 0,
  `Fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Fidelizacion`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Historial`
--

DROP TABLE IF EXISTS `Historial`;
CREATE TABLE `Historial` (
  `Id_historial` int(11) NOT NULL,
  `Id_orden` int(11) DEFAULT NULL,
  `Id_estado` int(11) DEFAULT NULL,
  `Comentario` varchar(255) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT NULL,
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Historial_escuela`
--

DROP TABLE IF EXISTS `Historial_escuela`;
CREATE TABLE `Historial_escuela` (
  `Id_historial` int(11) NOT NULL,
  `Id_solicitud` int(11) NOT NULL,
  `Id_estado_anterior` int(11) DEFAULT NULL,
  `Id_estado_nuevo` int(11) NOT NULL,
  `Comentario` text DEFAULT NULL,
  `Fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Manuales`
--

DROP TABLE IF EXISTS `Manuales`;
CREATE TABLE `Manuales` (
  `Id_manual` int(11) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Archivo_URL` varchar(255) NOT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Movimientos_puntos`
--

DROP TABLE IF EXISTS `Movimientos_puntos`;
CREATE TABLE `Movimientos_puntos` (
  `Id_movimiento` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_venta` int(11) DEFAULT NULL,
  `Tipo` enum('Ganado','Canjeado','Ajuste','Vencido') NOT NULL,
  `Puntos` int(11) NOT NULL,
  `Motivo` varchar(255) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Niveles`
--

DROP TABLE IF EXISTS `Niveles`;
CREATE TABLE `Niveles` (
  `Id_nivel` int(11) NOT NULL,
  `Nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Niveles`
--

INSERT INTO `Niveles` (`Id_nivel`, `Nombre`) VALUES(1, 'Sin experiencia');
INSERT INTO `Niveles` (`Id_nivel`, `Nombre`) VALUES(2, 'Principiante');
INSERT INTO `Niveles` (`Id_nivel`, `Nombre`) VALUES(3, 'Intermedio');
INSERT INTO `Niveles` (`Id_nivel`, `Nombre`) VALUES(4, 'Avanzado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Noticias`
--

DROP TABLE IF EXISTS `Noticias`;
CREATE TABLE `Noticias` (
  `Id_noticia` int(11) NOT NULL,
  `Imagen_URL` varchar(255) DEFAULT NULL,
  `Descripcion` varchar(200) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Noticias`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Ordenes`
--

DROP TABLE IF EXISTS `Ordenes`;
CREATE TABLE `Ordenes` (
  `Id_orden` int(11) NOT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Id_usuario_tecnico` int(11) DEFAULT NULL,
  `Id_vehiculo` int(11) DEFAULT NULL,
  `Id_solicitud` int(11) DEFAULT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `Fecha_entrega` datetime DEFAULT NULL,
  `Id_estado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Ordenes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Productos`
--

DROP TABLE IF EXISTS `Productos`;
CREATE TABLE `Productos` (
  `Id_producto` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Id_categoria` int(11) DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Productos`
--

INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(15, 'Casco Integral LS23', 'Casco certificado', 850.00, 15, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(16, 'Aceite Motul 20W50', 'Aceite sintetico', 120.00, 40, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(17, 'Guantes Pro', 'Guantes para moto', 150.00, 30, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(18, 'Rodilleras', 'Proteccion', 180.00, 15, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(19, 'Casco Abatible', 'Casco premium', 1200.00, 5, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(20, 'Aceite Castrol', 'Lubricante', 110.00, 28, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(21, 'Candado Disco', 'Seguridad', 95.00, 25, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(22, 'Impermeable', 'Traje lluvia', 200.00, 15, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(23, 'Casco Deportivo', 'Diseño racing', 961.00, 10, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(24, 'Kit Herramientas', 'Mantenimiento', 300.00, 10, 1, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(29, 'Bujia Iridium', 'Bujia de moto', 515.00, 0, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(30, 'Bujia Iridium', 'Bujia de moto', 515.00, 0, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(31, 'Filtro de aire', 'Filtro de aire', 80000.00, 9999, 2, 1);
INSERT INTO `Productos` (`Id_producto`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Id_categoria`, `Estado`) VALUES(32, 'manguera', 'manguera', 5.00, 5, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Productos_servicio`
--

DROP TABLE IF EXISTS `Productos_servicio`;
CREATE TABLE `Productos_servicio` (
  `Id_producto_servicio` int(11) NOT NULL,
  `Id_servicio` int(11) DEFAULT NULL,
  `Nombre` varchar(150) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Destacado` tinyint(1) DEFAULT NULL,
  `Orden` int(11) DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Productos_servicio`
--

INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(1, 1, 'Shampoo premium para motos', 'Producto especializado para lavar motocicletas.', 85.00, 20, 0, 1, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(2, 1, 'Cera líquida protectora', 'Cera líquida para proteger y dar brillo.', 95.00, 18, 0, 2, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(3, 1, 'Limpiador de cadena', 'Producto para remover grasa y suciedad de la cadena.', 78.00, 24, 1, 3, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(4, 1, 'Desengrasante profesional', 'Desengrasante para piezas y superficies.', 92.00, 16, 0, 4, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(5, 1, 'Pulidor de cromados', 'Producto para pulir superficies cromadas.', 88.00, 12, 1, 5, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(6, 1, 'Limpiador de asientos', 'Producto para limpiar y proteger los asientos.', 72.00, 19, 0, 6, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(7, 2, 'Aceite sintético premium', 'Aceite sintético para proteger el motor.', 165.00, 30, 1, 1, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(8, 2, 'Lubricante de cadena', 'Lubricante para reducir el desgaste de la cadena.', 82.00, 28, 1, 2, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(9, 2, 'Protector anticorrosión', 'Protección contra humedad y oxidación.', 96.00, 17, 0, 3, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(10, 2, 'Aditivo limpiador de motor', 'Aditivo para limpiar componentes internos del motor.', 110.00, 14, 0, 4, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(11, 2, 'Líquido de frenos DOT 4', 'Líquido para sistemas de freno DOT 4.', 75.00, 26, 1, 5, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(12, 2, 'Refrigerante de alta calidad', 'Refrigerante para proteger el sistema de enfriamiento.', 105.00, 21, 0, 6, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(13, 3, 'Kit de llaves hexagonales', 'Juego de llaves para mantenimiento general.', 145.00, 15, 0, 1, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(14, 3, 'Juego de destornilladores', 'Destornilladores de diferentes tamaños.', 135.00, 13, 0, 2, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(15, 3, 'Llave de torque profesional', 'Herramienta para aplicar el torque correcto.', 480.00, 8, 1, 3, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(16, 3, 'Extractor de cadena', 'Herramienta para desmontar cadenas.', 175.00, 10, 1, 4, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(17, 3, 'Calibrador de presión', 'Medidor para verificar la presión de las llantas.', 98.00, 22, 0, 5, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(18, 3, 'Kit de reparación básico', 'Kit para ajustes y reparaciones menores.', 225.00, 11, 0, 6, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(19, 4, 'Cover protector premium', 'Cubierta para proteger la motocicleta.', 265.00, 14, 1, 1, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(20, 4, 'Candado de disco', 'Candado de seguridad para el disco de freno.', 190.00, 18, 0, 2, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(21, 4, 'Soporte de celular', 'Soporte ajustable para teléfono celular.', 155.00, 20, 0, 3, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(22, 4, 'Cargador USB impermeable', 'Cargador USB resistente a salpicaduras.', 175.00, 16, 1, 4, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(23, 4, 'Espejo retrovisor deportivo', 'Espejo deportivo para motocicletas.', 220.00, 12, 0, 5, 1, '2026-07-21 03:30:24');
INSERT INTO `Productos_servicio` (`Id_producto_servicio`, `Id_servicio`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Destacado`, `Orden`, `Estado`, `Fecha`) VALUES(24, 4, 'Grips ergonómicos', 'Puños cómodos para mejorar el control.', 125.00, 24, 0, 6, 1, '2026-07-21 03:30:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Recompensas`
--

DROP TABLE IF EXISTS `Recompensas`;
CREATE TABLE `Recompensas` (
  `Id_recompensa` int(11) NOT NULL,
  `Nombre` varchar(120) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Tipo` enum('Descuento','Servicio') NOT NULL,
  `Puntos_requeridos` int(11) NOT NULL,
  `Porcentaje_descuento` decimal(5,2) DEFAULT NULL,
  `Nombre_servicio` varchar(150) DEFAULT NULL,
  `Imagen_URL` varchar(255) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `Fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Recompensas`
--

INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(1, '5% de descuento', 'Obtén un 5% de descuento en una compra.', 'Descuento', 50, 5.00, NULL, NULL, NULL, 1, '2026-07-22 06:00:10');
INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(2, '10% de descuento', 'Obtén un 10% de descuento en una compra.', 'Descuento', 100, 10.00, NULL, NULL, NULL, 1, '2026-07-22 06:00:10');
INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(3, 'Limpieza y lubricación de cadena', 'Servicio gratuito de limpieza, revisión y lubricación de cadena.', 'Servicio', 150, NULL, 'Limpieza y lubricación de cadena', NULL, NULL, 1, '2026-07-22 06:00:10');
INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(4, 'Servicio preventivo gratuito', 'Incluye inspección general y tareas básicas de prevención.', 'Servicio', 300, NULL, 'Servicio preventivo motos', NULL, NULL, 1, '2026-07-22 06:00:10');
INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(5, 'Servicio menor gratuito', 'Canje válido por un servicio menor para motocicleta.', 'Servicio', 500, NULL, 'Servicio menor', NULL, NULL, 1, '2026-07-22 06:00:10');
INSERT INTO `Recompensas` (`Id_recompensa`, `Nombre`, `Descripcion`, `Tipo`, `Puntos_requeridos`, `Porcentaje_descuento`, `Nombre_servicio`, `Imagen_URL`, `Stock`, `Estado`, `Fecha_creacion`) VALUES(6, 'Diagnóstico mecánico gratuito', 'Diagnóstico general de una falla mecánica.', 'Servicio', 650, NULL, 'Diagnóstico y reparación mecánica', NULL, NULL, 1, '2026-07-22 06:00:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Repuestos`
--

DROP TABLE IF EXISTS `Repuestos`;
CREATE TABLE `Repuestos` (
  `Id_repuesto` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Imagen_URL` varchar(255) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Repuestos`
--

INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(1, 'Filtro de Aceite', 'Filtro Yamaha', 'img/Repuestos/filtro-aceite.jpg', 50, 75.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(2, 'Pastillas de Freno', 'Juego delantero', 'img/Repuestos/pastillas-freno.jpg', 40, 120.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(3, 'Cadena', 'Cadena reforzada', 'img/Repuestos/cadena.jpg', 30, 250.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(4, 'Bujia NGK', 'Bujia original', 'img/Repuestos/bujia-ngk.jpg', 100, 35.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(5, 'Bateria 12V', 'Bateria sellada', 'img/Repuestos/bateria-12v.jpg', 20, 450.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(6, 'Llanta Delantera', '90/90-17', 'img/Repuestos/llanta-delantera.jpg', 15, 350.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(7, 'Llanta Trasera', '120/80-17', 'img/Repuestos/llanta-trasera.jpg', 15, 500.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(8, 'Kit Arrastre', 'Piñon y corona', 'img/Repuestos/kit-arrastre.jpg', 12, 650.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(9, 'Cable Acelerador', 'Original', 'img/Repuestos/cable-acelerador.jpg', 25, 60.00);
INSERT INTO `Repuestos` (`Id_repuesto`, `Nombre`, `Descripcion`, `Imagen_URL`, `Stock`, `Precio`) VALUES(10, 'Amortiguador', 'Trasero', 'img/Repuestos/amortiguador.jpg', 10, 800.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Roles`
--

DROP TABLE IF EXISTS `Roles`;
CREATE TABLE `Roles` (
  `Id_rol` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Roles`
--

INSERT INTO `Roles` (`Id_rol`, `Nombre`) VALUES(5, 'admin_taller');
INSERT INTO `Roles` (`Id_rol`, `Nombre`) VALUES(1, 'administrador');
INSERT INTO `Roles` (`Id_rol`, `Nombre`) VALUES(4, 'cliente');
INSERT INTO `Roles` (`Id_rol`, `Nombre`) VALUES(2, 'empleado');
INSERT INTO `Roles` (`Id_rol`, `Nombre`) VALUES(3, 'tecnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Roles_usuarios`
--

DROP TABLE IF EXISTS `Roles_usuarios`;
CREATE TABLE `Roles_usuarios` (
  `Id_USROL` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Roles_usuarios`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Servicios`
--

DROP TABLE IF EXISTS `Servicios`;
CREATE TABLE `Servicios` (
  `Id_servicio` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Icono` varchar(100) DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT NULL,
  `Orden` int(11) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Servicios`
--

INSERT INTO `Servicios` (`Id_servicio`, `Nombre`, `Descripcion`, `Icono`, `Estado`, `Orden`, `Fecha`) VALUES(1, 'Lavado y Limpieza', 'Productos especializados para limpiar y proteger tu motocicleta.', 'bi bi-droplet-half', 1, 1, '2026-07-21 03:30:24');
INSERT INTO `Servicios` (`Id_servicio`, `Nombre`, `Descripcion`, `Icono`, `Estado`, `Orden`, `Fecha`) VALUES(2, 'Cuidado y Mantenimiento', 'Productos para conservar el rendimiento y funcionamiento de tu motocicleta.', 'bi bi-shield-check', 1, 2, '2026-07-21 03:30:24');
INSERT INTO `Servicios` (`Id_servicio`, `Nombre`, `Descripcion`, `Icono`, `Estado`, `Orden`, `Fecha`) VALUES(3, 'Herramientas', 'Herramientas para reparación, ajuste y mantenimiento de motocicletas.', 'bi bi-wrench-adjustable', 1, 3, '2026-07-21 03:30:24');
INSERT INTO `Servicios` (`Id_servicio`, `Nombre`, `Descripcion`, `Icono`, `Estado`, `Orden`, `Fecha`) VALUES(4, 'Accesorios Premium', 'Accesorios para mejorar la seguridad, comodidad y apariencia de tu motocicleta.', 'bi bi-stars', 1, 4, '2026-07-21 03:30:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Solicitudes_Empleo`
--

DROP TABLE IF EXISTS `Solicitudes_Empleo`;
CREATE TABLE `Solicitudes_Empleo` (
  `Id_solicitud` int(11) NOT NULL,
  `Nombre_completo` varchar(255) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(150) DEFAULT NULL,
  `Ruta_cv` varchar(255) DEFAULT NULL,
  `Comentario` varchar(200) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT NULL,
  `Estado` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Solicitudes_Empleo`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Solicitudes_Escuela`
--

DROP TABLE IF EXISTS `Solicitudes_Escuela`;
CREATE TABLE `Solicitudes_Escuela` (
  `Id_solicitud` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_estado_motoescuela` int(11) DEFAULT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Apellido` varchar(100) DEFAULT NULL,
  `DPI` varchar(20) DEFAULT NULL,
  `Fecha_nacimiento` date DEFAULT NULL,
  `Genero` varchar(20) DEFAULT NULL,
  `Altura_cm` decimal(5,2) DEFAULT NULL,
  `Peso_kg` decimal(5,2) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Direccion` varchar(200) DEFAULT NULL,
  `Id_nivel` int(11) DEFAULT NULL,
  `Id_tipo_licencia` int(11) DEFAULT NULL,
  `Nombre_emergencia` varchar(100) DEFAULT NULL,
  `Contacto_emergencia` varchar(100) DEFAULT NULL,
  `Condiciones_medicas` varchar(255) DEFAULT NULL,
  `Acepta_terminos` tinyint(1) DEFAULT NULL,
  `Fecha_solicitud` timestamp NULL DEFAULT current_timestamp(),
  `Id_estado` int(11) DEFAULT 1,
  `Edad` int(11) DEFAULT NULL,
  `Fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Solicitudes_taller`
--

DROP TABLE IF EXISTS `Solicitudes_taller`;
CREATE TABLE `Solicitudes_taller` (
  `Id_solicitud` int(11) NOT NULL,
  `Id_cliente` int(11) DEFAULT NULL,
  `Id_vehiculo` int(11) DEFAULT NULL,
  `Descripcion_problema` text DEFAULT NULL,
  `Fecha_solicitada` datetime DEFAULT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(150) DEFAULT NULL,
  `Fecha_solicitud` timestamp NULL DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tipos_licencia`
--

DROP TABLE IF EXISTS `Tipos_licencia`;
CREATE TABLE `Tipos_licencia` (
  `Id_tipo` int(11) NOT NULL,
  `Nombre` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Tipos_licencia`
--

INSERT INTO `Tipos_licencia` (`Id_tipo`, `Nombre`) VALUES(1, 'Tipo A');
INSERT INTO `Tipos_licencia` (`Id_tipo`, `Nombre`) VALUES(2, 'Tipo B');
INSERT INTO `Tipos_licencia` (`Id_tipo`, `Nombre`) VALUES(3, 'Tipo C');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tipos_solicitud`
--

DROP TABLE IF EXISTS `Tipos_solicitud`;
CREATE TABLE `Tipos_solicitud` (
  `Id_tipo` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Tipos_solicitud`
--

INSERT INTO `Tipos_solicitud` (`Id_tipo`, `Nombre`) VALUES(1, 'Moto Escuela');
INSERT INTO `Tipos_solicitud` (`Id_tipo`, `Nombre`) VALUES(2, 'Servicio Tecnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Uso_repuestos`
--

DROP TABLE IF EXISTS `Uso_repuestos`;
CREATE TABLE `Uso_repuestos` (
  `Id_uso` int(11) NOT NULL,
  `Id_orden` int(11) DEFAULT NULL,
  `Id_repuesto` int(11) DEFAULT NULL,
  `Cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Usuarios`
--

DROP TABLE IF EXISTS `Usuarios`;
CREATE TABLE `Usuarios` (
  `Id_usuario` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Correo` varchar(150) NOT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `Token_verificacion` varchar(64) DEFAULT NULL,
  `Token_expira` datetime DEFAULT NULL,
  `Google_ID` varchar(255) DEFAULT NULL,
  `Tipo_Login` varchar(20) NOT NULL DEFAULT 'manual',
  `Correo_Verificado` tinyint(1) NOT NULL DEFAULT 1,
  `Fecha_Registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `Token_Reset` varchar(64) DEFAULT NULL,
  `Token_Reset_Expira` datetime DEFAULT NULL,
  `Intentos_Login` int(11) NOT NULL DEFAULT 0,
  `Bloqueado_Hasta` datetime DEFAULT NULL,
  `Ultimo_Login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Usuarios`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Vehiculos`
--

DROP TABLE IF EXISTS `Vehiculos`;
CREATE TABLE `Vehiculos` (
  `Id_vehiculo` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Marca` varchar(100) DEFAULT NULL,
  `Linea` varchar(100) DEFAULT NULL,
  `Modelo` int(11) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Placa` varchar(30) DEFAULT NULL,
  `No_chasis` varchar(50) DEFAULT NULL,
  `Tipo_vehiculo` varchar(100) DEFAULT NULL,
  `Cilindraje` int(11) DEFAULT NULL,
  `Combustible` varchar(50) DEFAULT NULL,
  `Kilometraje` int(11) DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT NULL,
  `Imagen_URL` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Vehiculos`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Ventas`
--

DROP TABLE IF EXISTS `Ventas`;
CREATE TABLE `Ventas` (
  `Id_venta` int(11) NOT NULL,
  `Id_cliente` int(11) DEFAULT NULL,
  `Fecha` timestamp NULL DEFAULT NULL,
  `Total` decimal(10,2) DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Ventas`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Verificaciones`
--

DROP TABLE IF EXISTS `Verificaciones`;
CREATE TABLE `Verificaciones` (
  `Id_verificacion` int(10) UNSIGNED NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Tipo` varchar(40) NOT NULL,
  `Codigo_hash` varchar(255) NOT NULL,
  `Expira_en` datetime NOT NULL,
  `Reenvio_disponible_en` datetime NOT NULL,
  `Intentos` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `Max_intentos` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `Utilizado` tinyint(1) NOT NULL DEFAULT 0,
  `Creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `Actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `Verificaciones`
--


--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Canjes`
--
ALTER TABLE `Canjes`
  ADD PRIMARY KEY (`Id_canje`),
  ADD UNIQUE KEY `Codigo_canje` (`Codigo_canje`),
  ADD KEY `fk_canje_usuario` (`Id_usuario`),
  ADD KEY `fk_canje_recompensa` (`Id_recompensa`);

--
-- Indices de la tabla `Categorias`
--
ALTER TABLE `Categorias`
  ADD PRIMARY KEY (`Id_categoria`);

--
-- Indices de la tabla `Detalle_venta`
--
ALTER TABLE `Detalle_venta`
  ADD PRIMARY KEY (`Id_detalle`),
  ADD KEY `Id_venta` (`Id_venta`),
  ADD KEY `Id_producto` (`Id_producto`);

--
-- Indices de la tabla `Estados`
--
ALTER TABLE `Estados`
  ADD PRIMARY KEY (`Id_estado`);

--
-- Indices de la tabla `Estados_motoescuela`
--
ALTER TABLE `Estados_motoescuela`
  ADD PRIMARY KEY (`Id_estado_motoescuela`);

--
-- Indices de la tabla `Estado_mantenimiento`
--
ALTER TABLE `Estado_mantenimiento`
  ADD PRIMARY KEY (`Id_estado`);

--
-- Indices de la tabla `Fidelizacion`
--
ALTER TABLE `Fidelizacion`
  ADD PRIMARY KEY (`Id_fidelizacion`),
  ADD UNIQUE KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `Historial`
--
ALTER TABLE `Historial`
  ADD PRIMARY KEY (`Id_historial`),
  ADD KEY `Id_orden` (`Id_orden`),
  ADD KEY `Id_estado` (`Id_estado`),
  ADD KEY `fk_historial_usuario` (`Id_usuario`);

--
-- Indices de la tabla `Historial_escuela`
--
ALTER TABLE `Historial_escuela`
  ADD PRIMARY KEY (`Id_historial`),
  ADD KEY `fk_historial_escuela_solicitud` (`Id_solicitud`),
  ADD KEY `fk_historial_escuela_usuario` (`Id_usuario`),
  ADD KEY `fk_historial_escuela_estado_anterior` (`Id_estado_anterior`),
  ADD KEY `fk_historial_escuela_estado_nuevo` (`Id_estado_nuevo`);

--
-- Indices de la tabla `Manuales`
--
ALTER TABLE `Manuales`
  ADD PRIMARY KEY (`Id_manual`),
  ADD KEY `fk_manual_usuario` (`Id_usuario`);

--
-- Indices de la tabla `Movimientos_puntos`
--
ALTER TABLE `Movimientos_puntos`
  ADD PRIMARY KEY (`Id_movimiento`),
  ADD KEY `fk_movimiento_usuario` (`Id_usuario`),
  ADD KEY `fk_movimiento_venta` (`Id_venta`);

--
-- Indices de la tabla `Niveles`
--
ALTER TABLE `Niveles`
  ADD PRIMARY KEY (`Id_nivel`);

--
-- Indices de la tabla `Noticias`
--
ALTER TABLE `Noticias`
  ADD PRIMARY KEY (`Id_noticia`);

--
-- Indices de la tabla `Ordenes`
--
ALTER TABLE `Ordenes`
  ADD PRIMARY KEY (`Id_orden`),
  ADD KEY `Id_estado` (`Id_estado`),
  ADD KEY `fk_ordenes_solicitud_escuela` (`Id_solicitud`),
  ADD KEY `fk_orden_vehiculo` (`Id_vehiculo`),
  ADD KEY `fk_ordenes_usuario_cliente` (`Id_usuario`),
  ADD KEY `fk_ordenes_usuario_tecnico` (`Id_usuario_tecnico`);

--
-- Indices de la tabla `Productos`
--
ALTER TABLE `Productos`
  ADD PRIMARY KEY (`Id_producto`),
  ADD KEY `Id_categoria` (`Id_categoria`);

--
-- Indices de la tabla `Productos_servicio`
--
ALTER TABLE `Productos_servicio`
  ADD PRIMARY KEY (`Id_producto_servicio`),
  ADD KEY `Id_servicio` (`Id_servicio`);

--
-- Indices de la tabla `Recompensas`
--
ALTER TABLE `Recompensas`
  ADD PRIMARY KEY (`Id_recompensa`);

--
-- Indices de la tabla `Repuestos`
--
ALTER TABLE `Repuestos`
  ADD PRIMARY KEY (`Id_repuesto`);

--
-- Indices de la tabla `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`Id_rol`),
  ADD UNIQUE KEY `uk_roles_nombre` (`Nombre`);

--
-- Indices de la tabla `Roles_usuarios`
--
ALTER TABLE `Roles_usuarios`
  ADD PRIMARY KEY (`Id_USROL`),
  ADD UNIQUE KEY `uk_usuario_rol` (`Id_usuario`,`Id_rol`),
  ADD KEY `Id_rol` (`Id_rol`);

--
-- Indices de la tabla `Servicios`
--
ALTER TABLE `Servicios`
  ADD PRIMARY KEY (`Id_servicio`);

--
-- Indices de la tabla `Solicitudes_Empleo`
--
ALTER TABLE `Solicitudes_Empleo`
  ADD PRIMARY KEY (`Id_solicitud`);

--
-- Indices de la tabla `Solicitudes_Escuela`
--
ALTER TABLE `Solicitudes_Escuela`
  ADD PRIMARY KEY (`Id_solicitud`),
  ADD KEY `Id_nivel` (`Id_nivel`),
  ADD KEY `Id_tipo_licencia` (`Id_tipo_licencia`),
  ADD KEY `Id_estado` (`Id_estado`),
  ADD KEY `fk_solicitud_escuela_usuario` (`Id_usuario`),
  ADD KEY `fk_solicitud_estado_motoescuela` (`Id_estado_motoescuela`);

--
-- Indices de la tabla `Solicitudes_taller`
--
ALTER TABLE `Solicitudes_taller`
  ADD PRIMARY KEY (`Id_solicitud`),
  ADD KEY `Id_cliente` (`Id_cliente`),
  ADD KEY `Id_vehiculo` (`Id_vehiculo`);

--
-- Indices de la tabla `Tipos_licencia`
--
ALTER TABLE `Tipos_licencia`
  ADD PRIMARY KEY (`Id_tipo`);

--
-- Indices de la tabla `Tipos_solicitud`
--
ALTER TABLE `Tipos_solicitud`
  ADD PRIMARY KEY (`Id_tipo`);

--
-- Indices de la tabla `Uso_repuestos`
--
ALTER TABLE `Uso_repuestos`
  ADD PRIMARY KEY (`Id_uso`),
  ADD KEY `Id_orden` (`Id_orden`),
  ADD KEY `Id_repuesto` (`Id_repuesto`);

--
-- Indices de la tabla `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD PRIMARY KEY (`Id_usuario`),
  ADD UNIQUE KEY `uk_usuarios_correo` (`Correo`);

--
-- Indices de la tabla `Vehiculos`
--
ALTER TABLE `Vehiculos`
  ADD PRIMARY KEY (`Id_vehiculo`),
  ADD UNIQUE KEY `uk_vehiculos_placa` (`Placa`),
  ADD UNIQUE KEY `uk_vehiculos_chasis` (`No_chasis`),
  ADD UNIQUE KEY `uk_vehiculo_placa` (`Placa`),
  ADD UNIQUE KEY `uk_vehiculo_chasis` (`No_chasis`),
  ADD KEY `fk_vehiculo_usuario` (`Id_usuario`);

--
-- Indices de la tabla `Ventas`
--
ALTER TABLE `Ventas`
  ADD PRIMARY KEY (`Id_venta`),
  ADD KEY `Id_cliente` (`Id_cliente`);

--
-- Indices de la tabla `Verificaciones`
--
ALTER TABLE `Verificaciones`
  ADD PRIMARY KEY (`Id_verificacion`),
  ADD KEY `idx_verificacion_usuario` (`Id_usuario`),
  ADD KEY `idx_verificacion_tipo` (`Tipo`),
  ADD KEY `idx_verificacion_busqueda` (`Id_usuario`,`Tipo`,`Utilizado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Canjes`
--
ALTER TABLE `Canjes`
  MODIFY `Id_canje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Categorias`
--
ALTER TABLE `Categorias`
  MODIFY `Id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Detalle_venta`
--
ALTER TABLE `Detalle_venta`
  MODIFY `Id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Estados`
--
ALTER TABLE `Estados`
  MODIFY `Id_estado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Estados_motoescuela`
--
ALTER TABLE `Estados_motoescuela`
  MODIFY `Id_estado_motoescuela` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Estado_mantenimiento`
--
ALTER TABLE `Estado_mantenimiento`
  MODIFY `Id_estado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Fidelizacion`
--
ALTER TABLE `Fidelizacion`
  MODIFY `Id_fidelizacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Historial`
--
ALTER TABLE `Historial`
  MODIFY `Id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Historial_escuela`
--
ALTER TABLE `Historial_escuela`
  MODIFY `Id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Manuales`
--
ALTER TABLE `Manuales`
  MODIFY `Id_manual` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Movimientos_puntos`
--
ALTER TABLE `Movimientos_puntos`
  MODIFY `Id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Niveles`
--
ALTER TABLE `Niveles`
  MODIFY `Id_nivel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Noticias`
--
ALTER TABLE `Noticias`
  MODIFY `Id_noticia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Ordenes`
--
ALTER TABLE `Ordenes`
  MODIFY `Id_orden` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Productos`
--
ALTER TABLE `Productos`
  MODIFY `Id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Productos_servicio`
--
ALTER TABLE `Productos_servicio`
  MODIFY `Id_producto_servicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Recompensas`
--
ALTER TABLE `Recompensas`
  MODIFY `Id_recompensa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Repuestos`
--
ALTER TABLE `Repuestos`
  MODIFY `Id_repuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Roles`
--
ALTER TABLE `Roles`
  MODIFY `Id_rol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Roles_usuarios`
--
ALTER TABLE `Roles_usuarios`
  MODIFY `Id_USROL` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Servicios`
--
ALTER TABLE `Servicios`
  MODIFY `Id_servicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Solicitudes_Empleo`
--
ALTER TABLE `Solicitudes_Empleo`
  MODIFY `Id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Solicitudes_Escuela`
--
ALTER TABLE `Solicitudes_Escuela`
  MODIFY `Id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Solicitudes_taller`
--
ALTER TABLE `Solicitudes_taller`
  MODIFY `Id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Tipos_licencia`
--
ALTER TABLE `Tipos_licencia`
  MODIFY `Id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Tipos_solicitud`
--
ALTER TABLE `Tipos_solicitud`
  MODIFY `Id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Uso_repuestos`
--
ALTER TABLE `Uso_repuestos`
  MODIFY `Id_uso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Vehiculos`
--
ALTER TABLE `Vehiculos`
  MODIFY `Id_vehiculo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Ventas`
--
ALTER TABLE `Ventas`
  MODIFY `Id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Verificaciones`
--
ALTER TABLE `Verificaciones`
  MODIFY `Id_verificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Canjes`
--
ALTER TABLE `Canjes`
  ADD CONSTRAINT `fk_canje_recompensa` FOREIGN KEY (`Id_recompensa`) REFERENCES `Recompensas` (`Id_recompensa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_canje_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Detalle_venta`
--
ALTER TABLE `Detalle_venta`
  ADD CONSTRAINT `Detalle_venta_ibfk_1` FOREIGN KEY (`Id_venta`) REFERENCES `Ventas` (`Id_venta`),
  ADD CONSTRAINT `Detalle_venta_ibfk_2` FOREIGN KEY (`Id_producto`) REFERENCES `Productos` (`Id_producto`);

--
-- Filtros para la tabla `Fidelizacion`
--
ALTER TABLE `Fidelizacion`
  ADD CONSTRAINT `fk_fidelizacion_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Historial`
--
ALTER TABLE `Historial`
  ADD CONSTRAINT `Historial_ibfk_1` FOREIGN KEY (`Id_orden`) REFERENCES `Ordenes` (`Id_orden`),
  ADD CONSTRAINT `Historial_ibfk_2` FOREIGN KEY (`Id_estado`) REFERENCES `Estado_mantenimiento` (`Id_estado`),
  ADD CONSTRAINT `Historial_ibfk_3` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`);

--
-- Filtros para la tabla `Historial_escuela`
--
ALTER TABLE `Historial_escuela`
  ADD CONSTRAINT `Historial_escuela_ibfk_1` FOREIGN KEY (`Id_solicitud`) REFERENCES `Solicitudes_Escuela` (`Id_solicitud`),
  ADD CONSTRAINT `Historial_escuela_ibfk_2` FOREIGN KEY (`Id_estado_anterior`) REFERENCES `Estados` (`Id_estado`),
  ADD CONSTRAINT `Historial_escuela_ibfk_3` FOREIGN KEY (`Id_estado_nuevo`) REFERENCES `Estados` (`Id_estado`),
  ADD CONSTRAINT `fk_historial_escuela_estado_anterior` FOREIGN KEY (`Id_estado_anterior`) REFERENCES `Estados_motoescuela` (`Id_estado_motoescuela`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_escuela_estado_nuevo` FOREIGN KEY (`Id_estado_nuevo`) REFERENCES `Estados_motoescuela` (`Id_estado_motoescuela`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_escuela_solicitud` FOREIGN KEY (`Id_solicitud`) REFERENCES `Solicitudes_Escuela` (`Id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_escuela_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historialescuela_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`);

--
-- Filtros para la tabla `Manuales`
--
ALTER TABLE `Manuales`
  ADD CONSTRAINT `fk_manual_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `Movimientos_puntos`
--
ALTER TABLE `Movimientos_puntos`
  ADD CONSTRAINT `fk_movimiento_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movimiento_venta` FOREIGN KEY (`Id_venta`) REFERENCES `Ventas` (`Id_venta`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `Ordenes`
--
ALTER TABLE `Ordenes`
  ADD CONSTRAINT `Ordenes_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `Ordenes_ibfk_2` FOREIGN KEY (`Id_usuario_tecnico`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `Ordenes_ibfk_3` FOREIGN KEY (`Id_vehiculo`) REFERENCES `Vehiculos` (`Id_vehiculo`),
  ADD CONSTRAINT `Ordenes_ibfk_4` FOREIGN KEY (`Id_estado`) REFERENCES `Estado_mantenimiento` (`Id_estado`),
  ADD CONSTRAINT `fk_orden_cliente` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `fk_orden_tecnico` FOREIGN KEY (`Id_usuario_tecnico`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `fk_orden_vehiculo` FOREIGN KEY (`Id_vehiculo`) REFERENCES `Vehiculos` (`Id_vehiculo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ordenes_solicitud_escuela` FOREIGN KEY (`Id_solicitud`) REFERENCES `Solicitudes_Escuela` (`Id_solicitud`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ordenes_usuario_cliente` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ordenes_usuario_tecnico` FOREIGN KEY (`Id_usuario_tecnico`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `Productos`
--
ALTER TABLE `Productos`
  ADD CONSTRAINT `Productos_ibfk_1` FOREIGN KEY (`Id_categoria`) REFERENCES `Categorias` (`Id_categoria`);

--
-- Filtros para la tabla `Productos_servicio`
--
ALTER TABLE `Productos_servicio`
  ADD CONSTRAINT `Productos_servicio_ibfk_1` FOREIGN KEY (`Id_servicio`) REFERENCES `Servicios` (`Id_servicio`);

--
-- Filtros para la tabla `Roles_usuarios`
--
ALTER TABLE `Roles_usuarios`
  ADD CONSTRAINT `Roles_usuarios_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `Roles_usuarios_ibfk_2` FOREIGN KEY (`Id_rol`) REFERENCES `Roles` (`Id_rol`);

--
-- Filtros para la tabla `Solicitudes_Escuela`
--
ALTER TABLE `Solicitudes_Escuela`
  ADD CONSTRAINT `Solicitudes_Escuela_ibfk_1` FOREIGN KEY (`Id_nivel`) REFERENCES `Niveles` (`Id_nivel`),
  ADD CONSTRAINT `Solicitudes_Escuela_ibfk_2` FOREIGN KEY (`Id_tipo_licencia`) REFERENCES `Tipos_licencia` (`Id_tipo`),
  ADD CONSTRAINT `Solicitudes_Escuela_ibfk_3` FOREIGN KEY (`Id_estado`) REFERENCES `Estados` (`Id_estado`),
  ADD CONSTRAINT `fk_solicitud_escuela_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitud_estado_motoescuela` FOREIGN KEY (`Id_estado_motoescuela`) REFERENCES `Estados_motoescuela` (`Id_estado_motoescuela`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `Solicitudes_taller`
--
ALTER TABLE `Solicitudes_taller`
  ADD CONSTRAINT `Solicitudes_taller_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `Solicitudes_taller_ibfk_2` FOREIGN KEY (`Id_vehiculo`) REFERENCES `Vehiculos` (`Id_vehiculo`);

--
-- Filtros para la tabla `Uso_repuestos`
--
ALTER TABLE `Uso_repuestos`
  ADD CONSTRAINT `Uso_repuestos_ibfk_1` FOREIGN KEY (`Id_orden`) REFERENCES `Ordenes` (`Id_orden`),
  ADD CONSTRAINT `Uso_repuestos_ibfk_2` FOREIGN KEY (`Id_repuesto`) REFERENCES `Repuestos` (`Id_repuesto`);

--
-- Filtros para la tabla `Vehiculos`
--
ALTER TABLE `Vehiculos`
  ADD CONSTRAINT `Vehiculos_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`),
  ADD CONSTRAINT `fk_vehiculo_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Ventas`
--
ALTER TABLE `Ventas`
  ADD CONSTRAINT `Ventas_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `Usuarios` (`Id_usuario`);

--
-- Filtros para la tabla `Verificaciones`
--
ALTER TABLE `Verificaciones`
  ADD CONSTRAINT `fk_verificaciones_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `Usuarios` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
-- --------------------------------------------------------
-- Usuarios de prueba (contraseña de todos: Demo1234)
-- --------------------------------------------------------
INSERT INTO `Usuarios` (`Id_usuario`, `Nombre`, `Apellido`, `Correo`, `Contrasena`, `Activo`, `Tipo_Login`, `Correo_Verificado`) VALUES(1, 'Admin', 'Demo', 'admin@ronem.demo', '$2y$10$8gXOupgtA/EKW/dB9aHpB.hrpOD2wxiprPUoRgDXCsjLzygs0p2AS', 1, 'manual', 1);
INSERT INTO `Usuarios` (`Id_usuario`, `Nombre`, `Apellido`, `Correo`, `Contrasena`, `Activo`, `Tipo_Login`, `Correo_Verificado`) VALUES(2, 'Empleado', 'Demo', 'empleado@ronem.demo', '$2y$10$8gXOupgtA/EKW/dB9aHpB.hrpOD2wxiprPUoRgDXCsjLzygs0p2AS', 1, 'manual', 1);
INSERT INTO `Usuarios` (`Id_usuario`, `Nombre`, `Apellido`, `Correo`, `Contrasena`, `Activo`, `Tipo_Login`, `Correo_Verificado`) VALUES(3, 'Tecnico', 'Demo', 'tecnico@ronem.demo', '$2y$10$8gXOupgtA/EKW/dB9aHpB.hrpOD2wxiprPUoRgDXCsjLzygs0p2AS', 1, 'manual', 1);
INSERT INTO `Usuarios` (`Id_usuario`, `Nombre`, `Apellido`, `Correo`, `Contrasena`, `Activo`, `Tipo_Login`, `Correo_Verificado`) VALUES(4, 'Cliente', 'Demo', 'cliente@ronem.demo', '$2y$10$8gXOupgtA/EKW/dB9aHpB.hrpOD2wxiprPUoRgDXCsjLzygs0p2AS', 1, 'manual', 1);
INSERT INTO `Usuarios` (`Id_usuario`, `Nombre`, `Apellido`, `Correo`, `Contrasena`, `Activo`, `Tipo_Login`, `Correo_Verificado`) VALUES(5, 'Taller', 'Demo', 'taller@ronem.demo', '$2y$10$8gXOupgtA/EKW/dB9aHpB.hrpOD2wxiprPUoRgDXCsjLzygs0p2AS', 1, 'manual', 1);
INSERT INTO `Roles_usuarios` (`Id_USROL`, `Id_usuario`, `Id_rol`) VALUES(1, 1, 1);
INSERT INTO `Roles_usuarios` (`Id_USROL`, `Id_usuario`, `Id_rol`) VALUES(2, 2, 2);
INSERT INTO `Roles_usuarios` (`Id_USROL`, `Id_usuario`, `Id_rol`) VALUES(3, 3, 3);
INSERT INTO `Roles_usuarios` (`Id_USROL`, `Id_usuario`, `Id_rol`) VALUES(4, 4, 4);
INSERT INTO `Roles_usuarios` (`Id_USROL`, `Id_usuario`, `Id_rol`) VALUES(5, 5, 5);
INSERT INTO `Fidelizacion` (`Id_fidelizacion`, `Id_usuario`, `Puntos_disponibles`, `Total_puntos_ganados`, `Total_puntos_usados`) VALUES(1, 4, 0, 0, 0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;