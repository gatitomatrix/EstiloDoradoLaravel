-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-08-2026 a las 22:08:23
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
-- Base de datos: `estilo_dorado_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nivel_acceso` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `tabla_afectada` varchar(80) NOT NULL,
  `clave_primaria_valor` varchar(80) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empleado` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `datos_anteriores` text DEFAULT NULL,
  `datos_nuevos` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-geo:nominatim:5951d8a4fcc59761f707c3b0f3e982de', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3643301;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:891955467;s:3:\"lat\";s:11:\"-12.1786836\";s:3:\"lon\";s:11:\"-77.0149860\";s:5:\"class\";s:7:\"highway\";s:4:\"type\";s:7:\"primary\";s:10:\"place_rank\";i:26;s:10:\"importance\";d:0.05341063477531087;s:11:\"addresstype\";s:4:\"road\";s:4:\"name\";s:28:\"Avenida Defensores del Morro\";s:12:\"display_name\";s:86:\"Avenida Defensores del Morro, Chorrillos, Lima, Lima Metropolitana, Lima, 15064, Perú\";s:7:\"address\";a:11:{s:4:\"road\";s:28:\"Avenida Defensores del Morro\";s:6:\"suburb\";s:10:\"Chorrillos\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15064\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.1796391\";i:1;s:11:\"-12.1777282\";i:2;s:11:\"-77.0158949\";i:3;s:11:\"-77.0140772\";}}}', 1786831309),
('laravel-cache-geo:nominatim:69fdfd188fdd827a3893b0407165fede', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073721;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0654005\";s:3:\"lon\";s:11:\"-77.0453196\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"103, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"103\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654505\";i:1;s:11:\"-12.0653505\";i:2;s:11:\"-77.0453696\";i:3;s:11:\"-77.0452696\";}}}', 1785719921),
('laravel-cache-geo:nominatim:f00a0e4776515aa108dbb574507c6cf0', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3624233;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:437322593;s:3:\"lat\";s:11:\"-12.1771963\";s:3:\"lon\";s:11:\"-77.0155390\";s:5:\"class\";s:7:\"landuse\";s:4:\"type\";s:11:\"residential\";s:10:\"place_rank\";i:24;s:10:\"importance\";d:0.08007730144197754;s:11:\"addresstype\";s:11:\"residential\";s:4:\"name\";s:15:\"Alameda Huaylas\";s:12:\"display_name\";s:66:\"Alameda Huaylas, Chorrillos, Lima, Lima Metropolitana, Lima, Perú\";s:7:\"address\";a:10:{s:11:\"residential\";s:15:\"Alameda Huaylas\";s:6:\"suburb\";s:10:\"Chorrillos\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.1776470\";i:1;s:11:\"-12.1767456\";i:2;s:11:\"-77.0160105\";i:3;s:11:\"-77.0150676\";}}}', 1786831544);

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
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `created_at`) VALUES
(1, 'Detalles', 'Detalles personalizados con rosas artificiales y variedad en regalos adicionales', '2025-10-04 04:07:25'),
(2, 'Florales', 'Flores naturales con arreglos personalizados.', '2025-10-04 04:07:25'),
(3, 'Carteles', 'Carteles con mensajes personalizados.', '2025-10-04 04:07:25'),
(4, 'Perfumeria', 'Perfumes para damas y caballeros de diferentes marcas, precios, etc.', '2025-10-04 04:07:25'),
(5, 'Variados', 'Detalles, Regalos, Peluches entre otros para regalar en cualquier ocasión', '2025-10-16 02:31:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`, `contrasena`, `created_at`) VALUES
(1, 'Andrea', 'Huaman', '999001122', 'andrea.huaman@gmail.com', 'Av. Central 504, Cerro de Pasco', '$2y$12$cmIrb/MN.a0FhQoIJOQCdO0pbLLqQz7Y7NDDUFk7KopXpyfd6DvXe', '2025-10-04 04:07:25'),
(2, 'Jose', 'Mendoza', '988765432', 'jose.mendoza@gmail.com', 'Jr. Grau 123, Cerro de Pasco', '$2y$12$.JedDlFfaZkaP8Nl6MQjxe/djqJPEhgBQh79wThljOwTTL82jkRBG', '2025-10-04 04:07:25'),
(3, 'Lucia', 'Perez Corzo', '977001144', 'lucia.perez@gmail.com', 'Av. Los Libertadores 333, Huancayo', '$2y$12$9CgALV8fqVxLXTi3R7en..jTty2sGdYWG90vJyRnvWyz.by/IH9dm', '2025-10-04 04:07:25'),
(4, 'Juan Jose', 'Aranda Cordova', '916183671', 'a21sjuanjo@gmail.com', 'Av Huancavelica N° 2885', '$2y$12$2MW.eC5F9K4E3xDt.DLZSOc2JctecpQ.nEdDOQKYkSVGK/cgverDG', '2025-10-11 01:08:35'),
(5, 'Elizabeth', 'Pari Carrion', '927497331', 'elizabeth.pari@gmail.com', 'Av. Las flores 528, Huancayo', '$2y$12$DVruOFpjqFrklI6WNwXUhO2qalDYoghaI/SBn5XHOmVWKH1WUF4O.', '2025-10-16 06:47:58'),
(6, 'Lidia', 'Cordova Ombone', '950125387', 'lidia.cordova@gmail.com', 'Jr. Los cedros 124, Cerro de Pasco', '$2y$12$/nWH4WGQBASb90kg0ByCqONmR8GQPZJF5wfc.mBZ9le5T745f8s62', '2025-10-16 06:47:58'),
(7, 'venta', 'tienda', '000000000', '00000000000', 'tienda', 'tienda', '2025-10-28 08:26:56'),
(10, 'asdasd', 'asdasd', '999999999999999', 'asdasdasd@gmail.com', 'asdasda', '$2y$12$2XyAoueVEB6RuRWhdeYC1.4gRh.cUv/IUBvsgwBmrKdrUWz9jcwom', '2025-11-07 01:04:38'),
(11, 'Gatito', NULL, NULL, 'gatito@test.com', NULL, '$2y$12$1wPVJxbkGSGK0/3sRcTHUOXaq7.KCcNndHM6L71yt812XZXF13gTq', '2026-07-11 00:15:40'),
(12, 'Tavara', 'Castillo', '995678412', 'TavaraCastillo123@hotmail.com', 'calle 321', '$2y$12$YrhlQ6Ek.LYR1IN2KpwN9.hDUYp1EFVHUdV0ApWzPe4xpVJR34VTq', '2026-07-11 01:02:41'),
(13, 'Juan Perez', NULL, NULL, 'Juan11@test.com', NULL, '$2y$12$3GORES6u071hK8/DKaamZezQbDFLRbiN97MeODYOoLPY6KX.XWP8C', '2026-07-18 01:56:51'),
(14, 'manuel carrion', NULL, NULL, 'carrion@test.com', NULL, '$2y$12$MwQyvOynAEGOA.Ir1K.ZdeMA9oCCwQ7noB/zk2oq/ZvLuwrHSF596', '2026-07-18 02:07:19'),
(15, 'juan arjona', NULL, NULL, 'juan@test.com', NULL, '$2y$12$Fs/no2mXJ3ZwV7McomZpGeC02LMybtG4Rn/modZbkZISW8MsIQ9YK', '2026-07-18 03:20:38'),
(16, 'Pepe Torre', NULL, NULL, 'pepe@test.com', NULL, '$2y$12$acaA5gxamTCrrcTGO/WMP.ZsamnDYsyRE7/GUoreWdqGME0vqwEKe', '2026-08-02 23:21:51'),
(17, 'Pepe Torre', NULL, NULL, 'pepe@hotmail.com', NULL, '$2y$12$9CTAt1j8AOP2SXJi8f3U5OeVlwWdII8oETIPuylaAbGNIETPniHsC', '2026-08-02 23:22:45'),
(18, 'Cliente', 'Google Demo', NULL, 'demo.google@estilodorado.local', NULL, '$2y$12$6aedq0NR5mOiQUA5KTDR3uMGaKLMDI5jhxFmuJy.6gSYN81keXasG', '2026-08-09 22:43:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedidos`
--

CREATE TABLE `detalles_pedidos` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pedidos`
--

INSERT INTO `detalles_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 2, 30.00),
(2, 1, 26, 1, 25.00),
(3, 1, 44, 1, 22.00),
(4, 2, 2, 1, 28.00),
(5, 2, 27, 2, 27.00),
(6, 3, 3, 2, 26.00),
(7, 3, 45, 1, 24.00),
(8, 3, 62, 1, 32.00),
(9, 4, 4, 1, 26.00),
(10, 4, 28, 1, 29.00),
(11, 4, 46, 1, 25.00),
(12, 4, 76, 2, 25.00),
(13, 5, 5, 2, 29.00),
(14, 5, 63, 1, 34.00),
(15, 6, 6, 1, 38.00),
(16, 6, 29, 2, 30.00),
(17, 6, 47, 1, 27.00),
(18, 7, 7, 1, 38.00),
(19, 7, 30, 1, 32.00),
(20, 7, 77, 2, 27.00),
(21, 8, 8, 1, 32.00),
(22, 8, 48, 1, 28.00),
(23, 9, 9, 1, 24.00),
(24, 9, 31, 1, 34.00),
(25, 9, 49, 1, 30.00),
(26, 9, 78, 2, 28.00),
(27, 10, 10, 2, 28.00),
(28, 10, 32, 1, 36.00),
(29, 10, 64, 1, 36.00),
(30, 11, 11, 1, 30.00),
(31, 11, 79, 2, 30.00),
(32, 12, 12, 2, 32.00),
(33, 12, 33, 1, 38.00),
(34, 12, 50, 1, 32.00),
(35, 13, 13, 1, 34.00),
(36, 13, 34, 1, 25.00),
(37, 13, 65, 2, 38.00),
(38, 14, 14, 2, 36.00),
(39, 14, 51, 1, 32.00),
(40, 15, 15, 1, 26.00),
(41, 15, 35, 2, 27.00),
(42, 15, 52, 1, 34.00),
(43, 15, 80, 1, 25.00),
(44, 16, 16, 1, 28.00),
(45, 16, 36, 2, 29.00),
(46, 17, 17, 2, 30.00),
(47, 17, 53, 1, 30.00),
(48, 17, 81, 1, 27.00),
(49, 18, 18, 1, 32.00),
(50, 18, 37, 1, 32.00),
(51, 18, 66, 2, 32.00),
(52, 19, 19, 1, 34.00),
(53, 19, 38, 1, 34.00),
(54, 19, 54, 1, 30.00),
(55, 19, 82, 2, 27.00),
(56, 20, 20, 2, 36.00),
(57, 20, 67, 1, 34.00),
(58, 21, 21, 1, 26.00),
(59, 21, 39, 2, 34.00),
(60, 21, 83, 1, 36.00),
(61, 22, 22, 1, 28.00),
(62, 22, 55, 1, 32.00),
(63, 22, 84, 2, 38.00),
(64, 23, 23, 1, 30.00),
(65, 23, 40, 2, 36.00),
(66, 24, 24, 1, 32.00),
(67, 24, 41, 1, 38.00),
(68, 24, 56, 1, 30.00),
(69, 24, 85, 2, 25.00),
(72, 27, 9, 1, 24.00),
(76, 31, 89, 2, 26.00),
(78, 33, 90, 1, 36.00),
(79, 34, 88, 2, 25.00),
(80, 35, 87, 2, 28.00),
(81, 36, 81, 4, 14.00),
(83, 38, 82, 5, 12.00),
(86, 41, 80, 4, 18.00),
(87, 42, 83, 3, 20.00),
(88, 43, 85, 3, 22.00),
(89, 44, 87, 2, 28.00),
(92, 47, 87, 2, 28.00),
(93, 48, 90, 1, 36.00),
(94, 49, 87, 1, 28.00),
(95, 50, 85, 1, 22.00),
(96, 51, 87, 1, 28.00),
(97, 52, 89, 1, 26.00),
(98, 52, 81, 1, 14.00),
(99, 53, 89, 1, 26.00),
(100, 54, 77, 2, 16.00),
(101, 55, 89, 1, 26.00),
(102, 56, 86, 2, 24.00),
(103, 57, 90, 1, 36.00),
(104, 58, 89, 1, 26.00),
(105, 59, 90, 1, 36.00),
(106, 60, 85, 1, 22.00),
(107, 61, 88, 1, 25.00),
(108, 62, 85, 1, 22.00),
(109, 63, 89, 1, 26.00),
(110, 63, 84, 1, 70.00),
(111, 64, 90, 2, 36.00),
(112, 65, 90, 2, 36.00),
(113, 66, 90, 1, 36.00),
(114, 67, 86, 1, 24.00),
(115, 68, 89, 1, 26.00),
(116, 69, 89, 1, 26.00),
(117, 73, 74, 2, 22.00),
(118, 74, 89, 1, 26.00),
(119, 75, 90, 1, 36.00),
(120, 76, 90, 2, 36.00),
(121, 77, 86, 2, 24.00),
(122, 77, 90, 1, 36.00),
(123, 78, 90, 1, 36.00),
(124, 79, 89, 1, 26.00),
(125, 80, 90, 2, 36.00),
(126, 81, 89, 13, 26.00),
(127, 81, 61, 12, 24.00),
(128, 87, 90, 1, 36.00),
(129, 87, 87, 1, 28.00),
(130, 88, 90, 1, 36.00),
(131, 88, 87, 1, 28.00),
(132, 89, 90, 1, 36.00),
(133, 89, 85, 1, 22.00),
(134, 90, 89, 1, 26.00),
(135, 90, 90, 1, 36.00),
(136, 91, 90, 1, 36.00),
(137, 91, 86, 1, 24.00),
(138, 92, 89, 1, 26.00),
(139, 92, 78, 2, 16.00),
(140, 93, 90, 3, 36.00),
(141, 93, 81, 3, 14.00),
(142, 94, 74, 2, 22.00),
(143, 95, 89, 1, 26.00),
(144, 95, 90, 1, 36.00),
(145, 96, 89, 1, 26.00),
(146, 96, 88, 1, 25.00),
(147, 97, 83, 1, 20.00),
(148, 98, 83, 1, 20.00),
(149, 99, 75, 1, 38.00),
(150, 100, 89, 1, 26.00),
(151, 101, 68, 1, 42.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `apellido`, `cargo`, `email`, `telefono`, `contrasena`, `password`, `created_at`) VALUES
(1, 'Elvis', 'Lopez', 'Administrador', 'elvis.estilodorado@gmail.com', '934567890', NULL, '$2y$12$IU4XVev3aZodppazV/Izwuy.CgdOCDwljB9FTsxqNcqIIIjKPA9qe', '2025-10-04 04:07:25'),
(2, 'Juan', 'Aranda', 'Soporte Técnico', 'juan.estilodorado@gmail.com', '945678901', NULL, '$2y$12$7cSmIZBketruAwJDRDugEe3aYXBknV4/.drykTKYvO53TbbEjmZHa', '2025-10-04 04:07:25'),
(3, 'Yerson', 'Ramos', 'Encargado de Stock', 'yerson.estilodorado@gmail.com', '912345678', NULL, '$2y$12$01stwe4brB.P5o6RwPofC.WANlhGp9Ic6kUFrD.62Zg98NV5R4xC.', '2025-10-04 04:07:25'),
(4, 'Derly', 'Pineda', 'Vendedor', 'derly.estilodorado@gmail.com', '923456789', NULL, '$2y$12$PMPX9pMFG8R7uPpSthL9reerMueQJip.gDYaAReYZ2kzw/fFzzARm', '2025-10-04 04:07:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_rol`
--

CREATE TABLE `empleado_rol` (
  `id_empleado` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado_rol`
--

INSERT INTO `empleado_rol` (`id_empleado`, `id_rol`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

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
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_movimiento` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacion` text DEFAULT NULL,
  `referencia_tipo` enum('pedido','ajuste','otro') DEFAULT 'otro',
  `referencia_id` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_movimiento`, `id_producto`, `tipo_movimiento`, `cantidad`, `fecha`, `observacion`, `referencia_tipo`, `referencia_id`, `id_empleado`) VALUES
(1, 1, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(2, 2, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(3, 3, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(4, 4, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(5, 5, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(6, 6, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(7, 7, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(8, 8, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(9, 9, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(10, 10, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(11, 11, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(12, 12, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(13, 13, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(14, 14, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(15, 15, 'entrada', 11, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(16, 16, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(17, 17, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(18, 18, 'entrada', 17, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(19, 19, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(20, 20, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(21, 21, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(22, 22, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(23, 23, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(24, 24, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(25, 25, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(26, 26, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(27, 27, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(28, 28, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(29, 29, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(30, 30, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(31, 31, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(32, 32, 'entrada', 11, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(33, 33, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(34, 34, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(35, 35, 'entrada', 17, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(36, 36, 'entrada', 19, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(37, 37, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(38, 38, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(39, 39, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(40, 40, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(41, 41, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(42, 42, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(43, 43, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(44, 44, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(45, 45, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(46, 46, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(47, 47, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(48, 48, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(49, 49, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(50, 50, 'entrada', 11, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(51, 51, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(52, 52, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(53, 53, 'entrada', 17, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(54, 54, 'entrada', 19, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(55, 55, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(56, 56, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(57, 57, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(58, 58, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(59, 59, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(60, 60, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(61, 61, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(62, 62, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(63, 63, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(64, 64, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(65, 65, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(66, 66, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(67, 67, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(68, 68, 'entrada', 11, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(69, 69, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(70, 70, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(71, 71, 'entrada', 17, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(72, 72, 'entrada', 19, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(73, 73, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(74, 74, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(75, 75, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(76, 76, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(77, 77, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(78, 78, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(79, 79, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(80, 80, 'entrada', 18, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(81, 81, 'entrada', 20, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(82, 82, 'entrada', 11, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(83, 83, 'entrada', 13, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(84, 84, 'entrada', 15, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(85, 85, 'entrada', 17, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(86, 86, 'entrada', 19, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(87, 87, 'entrada', 10, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(88, 88, 'entrada', 12, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(89, 89, 'entrada', 14, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(90, 90, 'entrada', 16, '2025-08-31 14:00:00', 'Stock inicial cargado', 'otro', NULL, 3),
(128, 1, 'salida', 2, '2025-09-01 17:00:00', 'Salida por pedido #1', 'pedido', 1, 3),
(129, 26, 'salida', 1, '2025-09-01 17:00:00', 'Salida por pedido #1', 'pedido', 1, 3),
(130, 44, 'salida', 1, '2025-09-01 17:00:00', 'Salida por pedido #1', 'pedido', 1, 3),
(131, 7, 'salida', 1, '2025-09-09 17:15:00', 'Salida por pedido #7', 'pedido', 7, 3),
(132, 30, 'salida', 1, '2025-09-09 17:15:00', 'Salida por pedido #7', 'pedido', 7, 3),
(133, 77, 'salida', 2, '2025-09-09 17:15:00', 'Salida por pedido #7', 'pedido', 7, 3),
(134, 13, 'salida', 1, '2025-09-17 17:35:00', 'Salida por pedido #13', 'pedido', 13, 3),
(135, 34, 'salida', 1, '2025-09-17 17:35:00', 'Salida por pedido #13', 'pedido', 13, 3),
(136, 65, 'salida', 2, '2025-09-17 17:35:00', 'Salida por pedido #13', 'pedido', 13, 3),
(137, 19, 'salida', 1, '2025-09-25 17:55:00', 'Salida por pedido #19', 'pedido', 19, 3),
(138, 38, 'salida', 1, '2025-09-25 17:55:00', 'Salida por pedido #19', 'pedido', 19, 3),
(139, 54, 'salida', 1, '2025-09-25 17:55:00', 'Salida por pedido #19', 'pedido', 19, 3),
(140, 82, 'salida', 2, '2025-09-25 17:55:00', 'Salida por pedido #19', 'pedido', 19, 3),
(141, 2, 'salida', 1, '2025-09-02 18:20:00', 'Salida por pedido #2', 'pedido', 2, 3),
(142, 27, 'salida', 2, '2025-09-02 18:20:00', 'Salida por pedido #2', 'pedido', 2, 3),
(143, 8, 'salida', 1, '2025-09-10 18:00:00', 'Salida por pedido #8', 'pedido', 8, 3),
(144, 48, 'salida', 1, '2025-09-10 18:00:00', 'Salida por pedido #8', 'pedido', 8, 3),
(145, 14, 'salida', 2, '2025-09-18 18:45:00', 'Salida por pedido #14', 'pedido', 14, 3),
(146, 51, 'salida', 1, '2025-09-18 18:45:00', 'Salida por pedido #14', 'pedido', 14, 3),
(147, 20, 'salida', 2, '2025-09-26 18:15:00', 'Salida por pedido #20', 'pedido', 20, 3),
(148, 67, 'salida', 1, '2025-09-26 18:15:00', 'Salida por pedido #20', 'pedido', 20, 3),
(149, 3, 'salida', 2, '2025-09-03 19:10:00', 'Salida por pedido #3', 'pedido', 3, 3),
(150, 45, 'salida', 1, '2025-09-03 19:10:00', 'Salida por pedido #3', 'pedido', 3, 3),
(151, 62, 'salida', 1, '2025-09-03 19:10:00', 'Salida por pedido #3', 'pedido', 3, 3),
(152, 9, 'salida', 1, '2025-09-11 19:40:00', 'Salida por pedido #9', 'pedido', 9, 3),
(153, 31, 'salida', 1, '2025-09-11 19:40:00', 'Salida por pedido #9', 'pedido', 9, 3),
(154, 49, 'salida', 1, '2025-09-11 19:40:00', 'Salida por pedido #9', 'pedido', 9, 3),
(155, 78, 'salida', 2, '2025-09-11 19:40:00', 'Salida por pedido #9', 'pedido', 9, 3),
(156, 15, 'salida', 1, '2025-09-19 19:20:00', 'Salida por pedido #15', 'pedido', 15, 3),
(157, 35, 'salida', 2, '2025-09-19 19:20:00', 'Salida por pedido #15', 'pedido', 15, 3),
(158, 52, 'salida', 1, '2025-09-19 19:20:00', 'Salida por pedido #15', 'pedido', 15, 3),
(159, 80, 'salida', 1, '2025-09-19 19:20:00', 'Salida por pedido #15', 'pedido', 15, 3),
(160, 21, 'salida', 1, '2025-09-27 19:30:00', 'Salida por pedido #21', 'pedido', 21, 3),
(161, 39, 'salida', 2, '2025-09-27 19:30:00', 'Salida por pedido #21', 'pedido', 21, 3),
(162, 83, 'salida', 1, '2025-09-27 19:30:00', 'Salida por pedido #21', 'pedido', 21, 3),
(163, 4, 'salida', 1, '2025-09-04 16:45:00', 'Salida por pedido #4', 'pedido', 4, 3),
(164, 28, 'salida', 1, '2025-09-04 16:45:00', 'Salida por pedido #4', 'pedido', 4, 3),
(165, 46, 'salida', 1, '2025-09-04 16:45:00', 'Salida por pedido #4', 'pedido', 4, 3),
(166, 76, 'salida', 2, '2025-09-04 16:45:00', 'Salida por pedido #4', 'pedido', 4, 3),
(167, 10, 'salida', 2, '2025-09-12 21:05:00', 'Salida por pedido #10', 'pedido', 10, 3),
(168, 32, 'salida', 1, '2025-09-12 21:05:00', 'Salida por pedido #10', 'pedido', 10, 3),
(169, 64, 'salida', 1, '2025-09-12 21:05:00', 'Salida por pedido #10', 'pedido', 10, 3),
(170, 16, 'salida', 1, '2025-09-20 20:00:00', 'Salida por pedido #16', 'pedido', 16, 3),
(171, 36, 'salida', 2, '2025-09-20 20:00:00', 'Salida por pedido #16', 'pedido', 16, 3),
(172, 22, 'salida', 1, '2025-09-28 22:40:00', 'Salida por pedido #22', 'pedido', 22, 3),
(173, 55, 'salida', 1, '2025-09-28 22:40:00', 'Salida por pedido #22', 'pedido', 22, 3),
(174, 84, 'salida', 2, '2025-09-28 22:40:00', 'Salida por pedido #22', 'pedido', 22, 3),
(175, 5, 'salida', 2, '2025-09-05 22:30:00', 'Salida por pedido #5', 'pedido', 5, 3),
(176, 63, 'salida', 1, '2025-09-05 22:30:00', 'Salida por pedido #5', 'pedido', 5, 3),
(177, 11, 'salida', 1, '2025-09-13 16:50:00', 'Salida por pedido #11', 'pedido', 11, 3),
(178, 79, 'salida', 2, '2025-09-13 16:50:00', 'Salida por pedido #11', 'pedido', 11, 3),
(179, 17, 'salida', 2, '2025-09-21 16:35:00', 'Salida por pedido #17', 'pedido', 17, 3),
(180, 53, 'salida', 1, '2025-09-21 16:35:00', 'Salida por pedido #17', 'pedido', 17, 3),
(181, 81, 'salida', 1, '2025-09-21 16:35:00', 'Salida por pedido #17', 'pedido', 17, 3),
(182, 23, 'salida', 1, '2025-09-29 23:50:00', 'Salida por pedido #23', 'pedido', 23, 3),
(183, 40, 'salida', 2, '2025-09-29 23:50:00', 'Salida por pedido #23', 'pedido', 23, 3),
(184, 6, 'salida', 1, '2025-09-06 23:05:00', 'Salida por pedido #6', 'pedido', 6, 3),
(185, 29, 'salida', 2, '2025-09-06 23:05:00', 'Salida por pedido #6', 'pedido', 6, 3),
(186, 47, 'salida', 1, '2025-09-06 23:05:00', 'Salida por pedido #6', 'pedido', 6, 3),
(187, 12, 'salida', 2, '2025-09-14 23:25:00', 'Salida por pedido #12', 'pedido', 12, 3),
(188, 33, 'salida', 1, '2025-09-14 23:25:00', 'Salida por pedido #12', 'pedido', 12, 3),
(189, 50, 'salida', 1, '2025-09-14 23:25:00', 'Salida por pedido #12', 'pedido', 12, 3),
(190, 18, 'salida', 1, '2025-09-23 01:10:00', 'Salida por pedido #18', 'pedido', 18, 3),
(191, 37, 'salida', 1, '2025-09-23 01:10:00', 'Salida por pedido #18', 'pedido', 18, 3),
(192, 66, 'salida', 2, '2025-09-23 01:10:00', 'Salida por pedido #18', 'pedido', 18, 3),
(193, 24, 'salida', 1, '2025-10-01 00:05:00', 'Salida por pedido #24', 'pedido', 24, 3),
(194, 41, 'salida', 1, '2025-10-01 00:05:00', 'Salida por pedido #24', 'pedido', 24, 3),
(195, 56, 'salida', 1, '2025-10-01 00:05:00', 'Salida por pedido #24', 'pedido', 24, 3),
(196, 85, 'salida', 2, '2025-10-01 00:05:00', 'Salida por pedido #24', 'pedido', 24, 3),
(197, 76, 'entrada', 1, '2025-10-27 05:00:00', 'Llegada de Stock', 'ajuste', NULL, 1);

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
(4, '2025_10_10_043039_create_personal_access_tokens_table', 2),
(5, '2025_10_22_094958_add_comprobante_fields_to_pedidos_table', 3),
(6, '2025_10_24_044553_alter_pedidos_increase_comprobante_lengths', 4),
(8, '2025_10_25_000001_add_comprobantes_json_to_pedidos', 5),
(9, '2025_10_26_232429_add_password_to_empleados_table', 5),
(10, '2026_08_10_000001_add_etiquetas_to_productos_table', 6),
(11, '2026_08_16_000001_restore_empty_etiquetas', 7);

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
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT 0.00,
  `forma_pago` varchar(50) DEFAULT NULL,
  `direccion_entrega` text DEFAULT NULL,
  `culqi_id` varchar(80) DEFAULT NULL,
  `comprobante_tipo` varchar(20) NOT NULL,
  `comprobante_serie` varchar(10) NOT NULL,
  `comprobante_numero` int(10) UNSIGNED NOT NULL,
  `sunat_xml` varchar(255) DEFAULT NULL,
  `sunat_cdr` varchar(255) DEFAULT NULL,
  `sunat_pdf` varchar(255) DEFAULT NULL,
  `comprobantes_json` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `fecha_pedido`, `estado`, `total`, `forma_pago`, `direccion_entrega`, `culqi_id`, `comprobante_tipo`, `comprobante_serie`, `comprobante_numero`, `sunat_xml`, `sunat_cdr`, `sunat_pdf`, `comprobantes_json`) VALUES
(1, 1, '2025-09-01 00:00:00', 'pagado', 107.00, 'efectivo', 'Av. Central 504, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(2, 2, '2025-09-02 11:20:00', 'pagado', 82.00, 'tarjeta', 'Jr. Grau 123, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(3, 3, '2025-09-03 12:10:00', 'enviado', 108.00, 'yape', 'Av. Los Libertadores 333, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(4, 4, '2025-09-04 09:45:00', 'entregado', 130.00, 'efectivo', 'Av Huancavelica N° 2885', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(5, 5, '2025-09-05 15:30:00', 'cancelado', 92.00, 'tarjeta', 'Av. Las flores 528, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(6, 6, '2025-09-06 00:00:00', 'pagado', 125.00, 'yape', 'Jr. Los cedros 124, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(7, 1, '2025-09-09 10:15:00', 'pagado', 124.00, 'tarjeta', 'Av. Central 504, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(8, 2, '2025-09-10 11:00:00', 'enviado', 60.00, 'efectivo', 'Jr. Grau 123, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(9, 3, '2025-09-11 12:40:00', 'entregado', 144.00, 'yape', 'Av. Los Libertadores 333, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(10, 4, '2025-09-12 14:05:00', 'cancelado', 128.00, 'tarjeta', 'Av Huancavelica N° 2885', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(11, 5, '2025-09-13 00:00:00', 'pagado', 90.00, 'efectivo', 'Av. Las flores 528, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(12, 6, '2025-09-14 16:25:00', 'pagado', 134.00, 'yape', 'Jr. Los cedros 124, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(13, 1, '2025-09-17 10:35:00', 'enviado', 135.00, 'efectivo', 'Av. Central 504, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(14, 2, '2025-09-18 11:45:00', 'entregado', 104.00, 'tarjeta', 'Jr. Grau 123, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(15, 3, '2025-09-19 12:20:00', 'cancelado', 139.00, 'yape', 'Av. Los Libertadores 333, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(16, 4, '2025-09-20 00:00:00', 'pagado', 86.00, 'efectivo', 'Av Huancavelica N° 2885', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(17, 5, '2025-09-21 09:35:00', 'pagado', 117.00, 'tarjeta', 'Av. Las flores 528, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(18, 6, '2025-09-22 18:10:00', 'enviado', 128.00, 'yape', 'Jr. Los cedros 124, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(19, 1, '2025-09-25 10:55:00', 'entregado', 152.00, 'efectivo', 'Av. Central 504, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(20, 2, '2025-09-26 11:15:00', 'cancelado', 106.00, 'tarjeta', 'Jr. Grau 123, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(21, 3, '2025-09-27 00:00:00', 'pagado', 130.00, 'yape', 'Av. Los Libertadores 333, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(22, 4, '2025-09-28 15:40:00', 'pagado', 136.00, 'efectivo', 'Av Huancavelica N° 2885', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(23, 5, '2025-09-29 16:50:00', 'enviado', 102.00, 'tarjeta', 'Av. Las flores 528, Huancayo', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(24, 6, '2025-09-30 17:05:00', 'entregado', 150.00, 'yape', 'Jr. Los cedros 124, Cerro de Pasco', NULL, 'BO', 'B0001', 0, NULL, NULL, NULL, NULL),
(27, 4, '2025-10-23 05:51:22', 'pagado', 24.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_0VI4nqyxnfmYurl1', 'BO', 'F0001', 1, 'comprobantes/xml/F0001-00000001.xml', NULL, 'comprobantes/pdf/F0001-00000001.pdf', NULL),
(31, 4, '2025-10-24 08:40:40', 'pagado', 52.00, 'tarjeta', 'Prolongación Yauli SN, Chaupimarca, Pasco, Pasco', 'tkn_test_gJ1qmYJlLwd4zAgj', 'FA', 'F0001', 2, 'comprobantes/xml/F0001-00000002.xml', NULL, 'comprobantes/pdf/F0001-00000002.pdf', NULL),
(33, 4, '2025-10-25 01:05:52', 'pagado', 36.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_QF1F33Wg0thS8Cx8', 'FA', 'F001', 1, 'comprobantes/xml/FA/F001/F001-00000001.xml', 'comprobantes/cdr/FA/R-F001-00000001.zip', 'comprobantes/pdf/FA/F001/F001-00000001.pdf', NULL),
(34, 4, '2025-10-25 07:22:12', 'pagado', 50.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_Sd8w0krY0oUWSSQx', 'FA', 'F001', 2, 'comprobantes/xml/FA/F001/F001-00000002.xml', 'comprobantes/cdr/FA/R-F001-00000002.zip', 'comprobantes/pdf/FA/F001/F001-00000002.pdf', NULL),
(35, 4, '2025-10-25 07:30:05', 'pagado', 56.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_zFDz2GkI8eHYi7Cb', 'BO', 'B001', 1, 'comprobantes/xml/BO/B001/B001-00000001.xml', 'comprobantes/cdr/BO/R-B001-00000001.zip', 'comprobantes/pdf/BO/B001/B001-00000001.pdf', NULL),
(36, 4, '2025-10-25 08:36:57', 'pagado', 56.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_bEKNdTRCoa4WQZ7J', 'FA', 'F001', 3, 'comprobantes/xml/FA/F001/F001-00000003.xml', 'comprobantes/cdr/FA/R-F001-00000003.zip', 'comprobantes/pdf/FA/F001/F001-00000003.pdf', NULL),
(38, 4, '2025-10-25 09:47:52', 'pagado', 60.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_HExAQI27kMLT8O4V', 'FA', 'F001', 4, 'comprobantes/xml/FA/F001/F001-00000004.xml', 'comprobantes/cdr/FA/R-F001-00000004.zip', 'comprobantes/pdf/FA/F001/F001-00000004.pdf', NULL),
(41, 4, '2025-10-25 18:09:35', 'pagado', 72.00, 'tarjeta', 'Prolongacion Yauli  SN, Chaupimarca, Pasco, Pasco', 'tkn_test_SJBj7BgkJzng0pIn', 'FA', 'F001', 5, 'comprobantes/xml/FA/F001/F001-00000005.xml', 'comprobantes/cdr/FA/R-F001-00000005.zip', 'comprobantes/pdf/FA/F001/F001-00000005.pdf', NULL),
(42, 4, '2025-10-25 18:19:58', 'pagado', 60.00, 'tarjeta', 'Av. Huancavelica  2885, El Tambo, Huancayo, Junin', 'tkn_test_BpRApJNiTYcs5d6e', 'BO', 'B001', 2, 'comprobantes/xml/BO/B001/B001-00000002.xml', 'comprobantes/cdr/BO/R-B001-00000002.zip', 'comprobantes/pdf/BO/B001/B001-00000002.pdf', NULL),
(43, 4, '2025-10-25 18:24:08', 'pagado', 66.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_QLCTTqJ8qpVllOZK', 'BO', 'B001', 3, 'comprobantes/xml/BO/B001/B001-00000003.xml', 'comprobantes/cdr/BO/R-B001-00000003.zip', 'comprobantes/pdf/BO/B001/B001-00000003.pdf', NULL),
(44, 4, '2025-10-25 18:38:41', 'pagado', 56.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_w1AnQE1OFtNBRodn', 'FA', 'F001', 6, 'comprobantes/xml/FA/F001/F001-00000006.xml', 'comprobantes/cdr/FA/R-F001-00000006.zip', 'comprobantes/pdf/FA/F001/F001-00000006.pdf', NULL),
(47, 4, '2025-10-25 19:10:09', 'pagado', 56.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_yVPuBblRnwL6SpQT', 'BO', 'B001', 4, 'comprobantes/xml/BO/B001/B001-00000004.xml', 'comprobantes/cdr/BO/R-B001-00000004.zip', 'comprobantes/pdf/BO/B001/B001-00000004.pdf', NULL),
(48, 4, '2025-10-25 20:30:22', 'pagado', 36.00, 'tarjeta', 'Av. Huancavelica  1860, El Tambo, Huancayo, Junin', 'tkn_test_zLfUFW8uG0US9tbI', 'FA', 'F001', 7, 'comprobantes/xml/FA/F001/F001-00000007.xml', 'comprobantes/cdr/FA/R-F001-00000007.zip', 'comprobantes/pdf/FA/F001/F001-00000007.pdf', NULL),
(49, 4, '2025-10-25 20:57:36', 'pagado', 28.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_nub9HOpDNYuDkVI1', 'BO', 'B001', 5, 'comprobantes/xml/BO/B001/B001-00000005.xml', 'comprobantes/cdr/BO/R-B001-00000005.zip', 'comprobantes/pdf/BO/B001/B001-00000005.pdf', NULL),
(50, 4, '2025-10-25 21:02:51', 'pagado', 22.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_iWrbV9CqrebVGd1h', 'BO', 'B001', 6, 'comprobantes/xml/BO/B001/B001-00000006.xml', 'comprobantes/cdr/BO/R-B001-00000006.zip', 'comprobantes/pdf/BO/B001/B001-00000006.pdf', NULL),
(51, 4, '2025-10-25 21:23:25', 'pagado', 28.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_fiCWRyy1o9vmou2L', 'FA', 'F001', 8, 'comprobantes/xml/FA/F001/F001-00000008.xml', 'comprobantes/cdr/FA/R-F001-00000008.zip', 'comprobantes/pdf/FA/F001/F001-00000008.pdf', NULL),
(52, 4, '2025-10-26 01:53:11', 'pendiente', 40.00, 'efectivo', 'Retiro en tienda -, , ,', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(53, 4, '2025-10-26 01:56:34', 'pagado', 26.00, 'tarjeta', 'HUANCAVELICA 2885, El Tambo, Huancayo, Junin', 'tkn_test_MsKGV2aj1OPxsV1n', 'FA', 'F001', 9, 'comprobantes/xml/FA/F001/F001-00000009.xml', 'comprobantes/cdr/FA/R-F001-00000009.zip', 'comprobantes/pdf/FA/F001/F001-00000009.pdf', NULL),
(54, 4, '2025-10-26 01:59:45', 'pagado', 32.00, 'tarjeta', 'AV. HUANCAVELICA  2885, El Tambo, Huancayo, Junin', 'tkn_test_opU4vPNwVYwcO5RW', 'BO', 'B001', 7, 'comprobantes/xml/BO/B001/B001-00000007.xml', 'comprobantes/cdr/BO/R-B001-00000007.zip', 'comprobantes/pdf/BO/B001/B001-00000007.pdf', NULL),
(55, 4, '2025-10-26 02:38:39', 'pendiente', 26.00, 'efectivo', 'HUANCAVELICA 2885, El Tambo, Huancayo, Junin', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(56, 4, '2025-10-26 02:50:57', 'pendiente', 48.00, 'efectivo', 'Capillas 123, Capillas, Castrovirreyna, Huancavelica', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(57, 4, '2025-10-26 03:03:20', 'pagado', 36.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_tsWXnczVLNXeCNYP', 'BO', 'B001', 8, 'comprobantes/xml/BO/B001/B001-00000008.xml', 'comprobantes/cdr/BO/R-B001-00000008.zip', 'comprobantes/pdf/BO/B001/B001-00000008.pdf', NULL),
(58, 4, '2025-10-26 03:05:50', 'pagado', 26.00, 'tarjeta', 'Retiro en tienda -, , ,', 'tkn_test_Pb9d4NYnYNNvAlEI', 'BO', 'B001', 9, 'comprobantes/xml/BO/B001/B001-00000009.xml', 'comprobantes/cdr/BO/R-B001-00000009.zip', 'comprobantes/pdf/BO/B001/B001-00000009.pdf', NULL),
(59, 4, '2025-10-26 03:46:59', 'pendiente', 36.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(60, 4, '2025-10-26 03:49:31', 'pagado', 22.00, 'tarjeta', 'Retiro en tienda -', 'tkn_test_uSYMtHHlTCfiDcuX', 'BO', 'B001', 10, 'comprobantes/xml/BO/B001/B001-00000010.xml', 'comprobantes/cdr/BO/R-B001-00000010.zip', 'comprobantes/pdf/BO/B001/B001-00000010.pdf', NULL),
(61, 4, '2025-10-26 03:52:23', 'pagado', 25.00, 'tarjeta', 'Achonga  123, Anchonga, Angaraes, Huancavelica', 'tkn_test_7lAFA34hYMRNv5Az', 'BO', 'B001', 11, 'comprobantes/xml/BO/B001/B001-00000011.xml', 'comprobantes/cdr/BO/R-B001-00000011.zip', 'comprobantes/pdf/BO/B001/B001-00000011.pdf', NULL),
(62, 4, '2025-10-26 03:55:29', 'pagado', 22.00, 'tarjeta', 'Retiro en tienda -', 'ype_test_iE96KhrgB9IYrmZe', 'BO', 'B001', 12, 'comprobantes/xml/BO/B001/B001-00000012.xml', 'comprobantes/cdr/BO/R-B001-00000012.zip', 'comprobantes/pdf/BO/B001/B001-00000012.pdf', NULL),
(63, 4, '2025-10-26 05:05:27', 'pagado', 96.00, 'tarjeta', 'Jirón Arequipa 2885, El Tambo, Huancayo, Junin', 'ype_test_xxXLUIbTzKeEre1N', 'BO', 'B001', 13, 'comprobantes/xml/BO/B001/B001-00000013.xml', 'comprobantes/cdr/BO/R-B001-00000013.zip', 'comprobantes/pdf/BO/B001/B001-00000013.pdf', NULL),
(64, 4, '2025-10-28 05:55:22', 'pendiente', 72.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(65, 4, '2025-10-28 05:59:32', 'pendiente', 72.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(66, 6, '2025-10-28 00:00:00', 'pagado', 36.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(67, 6, '2025-10-28 00:00:00', 'entregado', 24.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(68, 6, '2025-10-28 07:55:38', 'pagado', 26.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(69, 6, '2025-10-28 08:02:06', 'entregado', 26.00, 'tarjeta', 'Jirón Andahuaylas 123, Abancay, Abancay, Apurimac', 'tkn_test_cnIrSdTTnOGz2Ud5', 'BO', 'B001', 14, 'comprobantes/xml/BO/B001/B001-00000014.xml', 'comprobantes/cdr/BO/R-B001-00000014.zip', 'comprobantes/pdf/BO/B001/B001-00000014.pdf', NULL),
(73, 7, '2025-10-28 00:00:00', 'pagado', 44.00, 'efectivo', 'Atención en tienda', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(74, 6, '2025-10-28 13:29:57', 'pagado', 26.00, 'tarjeta', 'Quinua  123, Quinua, Huamanga, Ayacucho', 'tkn_test_lGZxdZxKeB52368Z', 'BO', 'B001', 15, 'comprobantes/xml/BO/B001/B001-00000015.xml', 'comprobantes/cdr/BO/R-B001-00000015.zip', 'comprobantes/pdf/BO/B001/B001-00000015.pdf', NULL),
(75, 4, '2025-10-28 00:00:00', 'pagado', 36.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(76, 4, '2025-10-31 00:00:00', 'enviado', 72.00, 'tarjeta', 'Retiro en tienda -', 'tkn_test_sAOzyF6dTuVzrYJb', 'BO', 'B001', 16, 'comprobantes/xml/BO/B001/B001-00000016.xml', NULL, 'comprobantes/pdf/BO/B001/B001-00000016.pdf', NULL),
(77, 7, '2025-11-03 00:00:00', 'entregado', 84.00, 'efectivo', 'Atención en tienda', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(78, 4, '2025-11-14 00:00:00', 'entregado', 36.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(79, 4, '2025-11-15 00:47:30', 'pendiente', 26.00, 'efectivo', 'Retiro en tienda -', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL),
(80, 4, '2026-05-22 02:17:38', 'pagado', 72.00, 'tarjeta', 'Retiro en tienda -', 'tkn_test_4u92J4bIbrsKhN7O', 'BO', 'B001', 17, 'comprobantes/xml/BO/B001/B001-00000017.xml', NULL, 'comprobantes/pdf/BO/B001/B001-00000017.pdf', NULL),
(81, 4, '2026-05-23 03:52:23', 'pagado', 626.00, 'tarjeta', 'Avenida Huancavelica 390, El Tambo, Huancayo, Junin', 'tkn_test_FuMfGOKEeO1K4KVG', 'BO', 'B001', 18, 'comprobantes/xml/BO/B001/B001-00000018.xml', NULL, 'comprobantes/pdf/BO/B001/B001-00000018.pdf', NULL),
(87, 12, '2026-07-17 23:35:06', 'pendiente', 64.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(88, 12, '2026-07-18 01:11:20', 'pendiente', 64.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(89, 12, '2026-07-18 01:21:44', 'pendiente', 58.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(90, 12, '2026-07-18 01:23:10', 'pendiente', 62.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(91, 12, '2026-07-18 01:41:26', 'pagado', 60.00, 'yape', 'calle 123', 'ype_mobile_1785639948369', 'BO', 'B001', 19, 'comprobantes/xml/BO/B001/B001-00000019.xml', 'comprobantes/cdr/BO/R-B001-00000019.zip', 'comprobantes/pdf/BO/B001/B001-00000019.pdf', NULL),
(92, 13, '2026-07-18 01:58:01', 'pendiente', 58.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(93, 14, '2026-07-18 02:11:12', 'pendiente', 150.00, 'yape', '123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(94, 12, '2026-07-18 03:18:41', 'pagado', 44.00, 'tarjeta', 'calle 123', 'tok_mobile_1785640420646', 'BO', 'B001', 20, 'comprobantes/xml/BO/B001/B001-00000020.xml', 'comprobantes/cdr/BO/R-B001-00000020.zip', 'comprobantes/pdf/BO/B001/B001-00000020.pdf', NULL),
(95, 12, '2026-07-29 01:25:27', 'pendiente', 62.00, 'yape', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(96, 12, '2026-08-02 00:36:24', 'pendiente', 51.00, 'tarjeta', 'calle 123', NULL, 'BO', 'B001', 0, NULL, NULL, NULL, NULL),
(97, 12, '2026-08-02 18:09:25', 'pagado', 20.00, 'tarjeta', 'Avenida 9 de Diciembre 318 – Lima/Lima/Lima', 'tok_mobile_1785694162305', 'FA', 'F001', 10, 'comprobantes/xml/FA/F001/F001-00000010.xml', 'comprobantes/cdr/FA/R-F001-00000010.zip', 'comprobantes/pdf/FA/F001/F001-00000010.pdf', NULL),
(98, 12, '2026-08-02 18:09:53', 'pagado', 20.00, 'tarjeta', 'Avenida 9 de Diciembre 318 – Lima/Lima/Lima', 'tok_mobile_1785694192596', 'FA', 'F001', 11, 'comprobantes/xml/FA/F001/F001-00000011.xml', 'comprobantes/cdr/FA/R-F001-00000011.zip', 'comprobantes/pdf/FA/F001/F001-00000011.pdf', NULL),
(99, 12, '2026-08-02 23:19:22', 'pagado', 38.00, 'tarjeta', 'Avenida 28 de Julio 103 – Lima/Lima/Lima', 'tok_mobile_1785712758167', 'FA', 'F001', 12, 'comprobantes/xml/FA/F001/F001-00000012.xml', 'comprobantes/cdr/FA/R-F001-00000012.zip', 'comprobantes/pdf/FA/F001/F001-00000012.pdf', NULL),
(100, 12, '2026-08-03 00:20:17', 'pagado', 26.00, 'tarjeta', 'Avenida 28 de Julio 103 – Lima/Lima/Lima', 'tok_mobile_1785716415334', 'BO', 'B001', 21, 'comprobantes/xml/BO/B001/B001-00000021.xml', 'comprobantes/cdr/BO/R-B001-00000021.zip', 'comprobantes/pdf/BO/B001/B001-00000021.pdf', NULL),
(101, 12, '2026-08-15 22:17:13', 'pagado', 42.00, 'tarjeta', 'Avenida Defensores del Morro Cdra. 16 – Chorrillos/Lima/Lima', 'tok_mobile_1786832230690', 'BO', 'B001', 22, 'comprobantes/xml/BO/B001/B001-00000022.xml', 'comprobantes/cdr/BO/R-B001-00000022.zip', 'comprobantes/pdf/BO/B001/B001-00000022.pdf', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_estado_historial`
--

CREATE TABLE `pedido_estado_historial` (
  `id_historial` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `estado_anterior` enum('pendiente','pagado','enviado','entregado','cancelado') DEFAULT NULL,
  `estado_nuevo` enum('pendiente','pagado','enviado','entregado','cancelado') NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `comentario` text DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_estado_historial`
--

INSERT INTO `pedido_estado_historial` (`id_historial`, `id_pedido`, `estado_anterior`, `estado_nuevo`, `fecha`, `comentario`, `id_empleado`) VALUES
(1, 1, NULL, 'pendiente', '2025-09-01 16:00:00', 'Pedido registrado', NULL),
(2, 2, NULL, 'pendiente', '2025-09-02 17:20:00', 'Pedido registrado', NULL),
(3, 3, NULL, 'pendiente', '2025-09-03 18:10:00', 'Pedido registrado', NULL),
(4, 4, NULL, 'pendiente', '2025-09-04 15:45:00', 'Pedido registrado', NULL),
(5, 5, NULL, 'pendiente', '2025-09-05 21:30:00', 'Pedido registrado', NULL),
(6, 6, NULL, 'pendiente', '2025-09-06 22:05:00', 'Pedido registrado', NULL),
(7, 7, NULL, 'pendiente', '2025-09-09 16:15:00', 'Pedido registrado', NULL),
(8, 8, NULL, 'pendiente', '2025-09-10 17:00:00', 'Pedido registrado', NULL),
(9, 9, NULL, 'pendiente', '2025-09-11 18:40:00', 'Pedido registrado', NULL),
(10, 10, NULL, 'pendiente', '2025-09-12 20:05:00', 'Pedido registrado', NULL),
(11, 11, NULL, 'pendiente', '2025-09-13 15:50:00', 'Pedido registrado', NULL),
(12, 12, NULL, 'pendiente', '2025-09-14 22:25:00', 'Pedido registrado', NULL),
(13, 13, NULL, 'pendiente', '2025-09-17 16:35:00', 'Pedido registrado', NULL),
(14, 14, NULL, 'pendiente', '2025-09-18 17:45:00', 'Pedido registrado', NULL),
(15, 15, NULL, 'pendiente', '2025-09-19 18:20:00', 'Pedido registrado', NULL),
(16, 16, NULL, 'pendiente', '2025-09-20 19:00:00', 'Pedido registrado', NULL),
(17, 17, NULL, 'pendiente', '2025-09-21 15:35:00', 'Pedido registrado', NULL),
(18, 18, NULL, 'pendiente', '2025-09-23 00:10:00', 'Pedido registrado', NULL),
(19, 19, NULL, 'pendiente', '2025-09-25 16:55:00', 'Pedido registrado', NULL),
(20, 20, NULL, 'pendiente', '2025-09-26 17:15:00', 'Pedido registrado', NULL),
(21, 21, NULL, 'pendiente', '2025-09-27 18:30:00', 'Pedido registrado', NULL),
(22, 22, NULL, 'pendiente', '2025-09-28 21:40:00', 'Pedido registrado', NULL),
(23, 23, NULL, 'pendiente', '2025-09-29 22:50:00', 'Pedido registrado', NULL),
(24, 24, NULL, 'pendiente', '2025-09-30 23:05:00', 'Pedido registrado', NULL),
(32, 2, 'pendiente', 'pagado', '2025-09-02 22:20:00', 'Pago confirmado', NULL),
(33, 3, 'pendiente', 'pagado', '2025-09-03 23:10:00', 'Pago confirmado', NULL),
(34, 4, 'pendiente', 'pagado', '2025-09-04 20:45:00', 'Pago confirmado', NULL),
(35, 7, 'pendiente', 'pagado', '2025-09-09 21:15:00', 'Pago confirmado', NULL),
(36, 8, 'pendiente', 'pagado', '2025-09-10 22:00:00', 'Pago confirmado', NULL),
(37, 9, 'pendiente', 'pagado', '2025-09-11 23:40:00', 'Pago confirmado', NULL),
(38, 12, 'pendiente', 'pagado', '2025-09-15 03:25:00', 'Pago confirmado', NULL),
(39, 13, 'pendiente', 'pagado', '2025-09-17 21:35:00', 'Pago confirmado', NULL),
(40, 14, 'pendiente', 'pagado', '2025-09-18 22:45:00', 'Pago confirmado', NULL),
(41, 17, 'pendiente', 'pagado', '2025-09-21 20:35:00', 'Pago confirmado', NULL),
(42, 18, 'pendiente', 'pagado', '2025-09-23 05:10:00', 'Pago confirmado', NULL),
(43, 19, 'pendiente', 'pagado', '2025-09-25 21:55:00', 'Pago confirmado', NULL),
(44, 22, 'pendiente', 'pagado', '2025-09-29 02:40:00', 'Pago confirmado', NULL),
(45, 23, 'pendiente', 'pagado', '2025-09-30 03:50:00', 'Pago confirmado', NULL),
(46, 24, 'pendiente', 'pagado', '2025-10-01 04:05:00', 'Pago confirmado', NULL),
(47, 3, 'pagado', 'enviado', '2025-09-04 23:10:00', 'Pedido despachado', NULL),
(48, 4, 'pagado', 'enviado', '2025-09-05 20:45:00', 'Pedido despachado', NULL),
(49, 8, 'pagado', 'enviado', '2025-09-11 22:00:00', 'Pedido despachado', NULL),
(50, 9, 'pagado', 'enviado', '2025-09-12 23:40:00', 'Pedido despachado', NULL),
(51, 13, 'pagado', 'enviado', '2025-09-18 21:35:00', 'Pedido despachado', NULL),
(52, 14, 'pagado', 'enviado', '2025-09-19 22:45:00', 'Pedido despachado', NULL),
(53, 18, 'pagado', 'enviado', '2025-09-24 05:10:00', 'Pedido despachado', NULL),
(54, 19, 'pagado', 'enviado', '2025-09-26 21:55:00', 'Pedido despachado', NULL),
(55, 23, 'pagado', 'enviado', '2025-10-01 03:50:00', 'Pedido despachado', NULL),
(56, 24, 'pagado', 'enviado', '2025-10-02 04:05:00', 'Pedido despachado', NULL),
(62, 4, 'enviado', 'entregado', '2025-09-07 14:45:00', 'Pedido entregado al cliente', NULL),
(63, 9, 'enviado', 'entregado', '2025-09-14 17:40:00', 'Pedido entregado al cliente', NULL),
(64, 14, 'enviado', 'entregado', '2025-09-21 16:45:00', 'Pedido entregado al cliente', NULL),
(65, 19, 'enviado', 'entregado', '2025-09-28 15:55:00', 'Pedido entregado al cliente', NULL),
(66, 24, 'enviado', 'entregado', '2025-10-03 22:05:00', 'Pedido entregado al cliente', NULL),
(69, 5, 'pendiente', 'cancelado', '2025-09-06 06:30:00', 'Pedido cancelado por el cliente', NULL),
(70, 10, 'pendiente', 'cancelado', '2025-09-13 05:05:00', 'Pedido cancelado por el cliente', NULL),
(71, 15, 'pendiente', 'cancelado', '2025-09-20 03:20:00', 'Pedido cancelado por el cliente', NULL),
(72, 20, 'pendiente', 'cancelado', '2025-09-27 02:15:00', 'Pedido cancelado por el cliente', NULL),
(73, 69, 'pagado', 'entregado', '2025-10-28 14:53:13', 'Producto Entregado', 1),
(74, 68, 'pendiente', 'pagado', '2025-10-28 14:55:43', 'Pedido Pagado', 3),
(75, 67, 'pendiente', 'pagado', '2025-10-28 15:19:43', 'Pedido Pagado', 3),
(76, 87, NULL, 'pendiente', '2026-07-18 04:35:06', 'Pedido creado desde la aplicación móvil', NULL),
(77, 88, NULL, 'pendiente', '2026-07-18 06:11:20', 'Pedido creado desde la aplicación móvil', NULL),
(78, 89, NULL, 'pendiente', '2026-07-18 06:21:44', 'Pedido creado desde la aplicación móvil', NULL),
(79, 90, NULL, 'pendiente', '2026-07-18 06:23:10', 'Pedido creado desde la aplicación móvil', NULL),
(80, 91, NULL, 'pendiente', '2026-07-18 06:41:26', 'Pedido creado desde la aplicación móvil', NULL),
(81, 92, NULL, 'pendiente', '2026-07-18 06:58:01', 'Pedido creado desde la aplicación móvil', NULL),
(82, 93, NULL, 'pendiente', '2026-07-18 07:11:12', 'Pedido creado desde la aplicación móvil', NULL),
(83, 94, NULL, 'pendiente', '2026-07-18 08:18:41', 'Pedido creado desde la aplicación móvil', NULL),
(84, 95, NULL, 'pendiente', '2026-07-29 06:25:27', 'Pedido creado desde la aplicación móvil', NULL),
(85, 96, NULL, 'pendiente', '2026-08-02 05:36:24', 'Pedido creado desde la aplicación móvil', NULL),
(86, 91, 'pendiente', 'pagado', '2026-08-02 08:06:14', 'Pago completado por el cliente (app móvil)', NULL),
(87, 94, 'pendiente', 'pagado', '2026-08-02 08:13:43', 'Pago completado por el cliente (app móvil)', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
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
(21, 'App\\Models\\Cliente', 4, 'token_cliente', '733c986b8548f49dc0a079ae963baded83e93f66b30a06b47c49147bcf2bd097', '[\"*\"]', '2025-10-26 10:37:14', NULL, '2025-10-22 08:21:46', '2025-10-26 10:37:14'),
(22, 'App\\Models\\Cliente', 4, 'token_cliente', 'cda0d8749130a51c6140d99e2b83d3eb9713ad4a4da1b478eabc28b6f6e73476', '[\"*\"]', '2025-10-26 10:40:06', NULL, '2025-10-26 10:40:01', '2025-10-26 10:40:06'),
(23, 'App\\Models\\Cliente', 6, 'token_cliente', '000b2d36280e50623ddcd5f52d86782499c75017d61e7084bb3e7bdb002b2813', '[\"*\"]', '2025-10-26 10:40:42', NULL, '2025-10-26 10:40:41', '2025-10-26 10:40:42'),
(24, 'App\\Models\\Cliente', 6, 'token_cliente', '996064ec148030bb847dad79409b5d8b17da1bf5b7a050fe4dcc24922ac54df9', '[\"*\"]', '2025-10-26 10:44:37', NULL, '2025-10-26 10:44:33', '2025-10-26 10:44:37'),
(25, 'App\\Models\\Cliente', 6, 'token_cliente', '3ff27c696cf08f25238a1bfbad57aa745e0449857d2db6f97665213fa07d51fd', '[\"*\"]', '2025-10-26 10:57:53', NULL, '2025-10-26 10:57:47', '2025-10-26 10:57:53'),
(26, 'App\\Models\\Cliente', 4, 'token_cliente', '0c7021fe804dc3c4307061ea6a46e5c224d88906a66d0a821b07d6030d2c923f', '[\"*\"]', '2025-10-26 12:15:57', NULL, '2025-10-26 10:59:43', '2025-10-26 12:15:57'),
(27, 'App\\Models\\Empleado', 1, 'panel-admin', 'ff8d5f23d04b66c554d81a074c1ed5b1a6e68ad8cc6a88042ca748b1f5ac3213', '[\"*\"]', '2025-10-27 05:09:24', NULL, '2025-10-27 05:04:43', '2025-10-27 05:09:24'),
(28, 'App\\Models\\Empleado', 1, 'panel-admin', 'b3060a9114b97efff93afca27ccaecec9b38327b0c07ac3172638d1a80ac82d9', '[\"*\"]', '2025-10-27 05:26:46', NULL, '2025-10-27 05:26:44', '2025-10-27 05:26:46'),
(29, 'App\\Models\\Empleado', 2, 'panel-admin', '3d2000ee2384d9e2eabda1b97d2b9756d3dc74cd43fb43ad8b0de9547e231fd1', '[\"*\"]', '2025-10-27 05:27:22', NULL, '2025-10-27 05:27:21', '2025-10-27 05:27:22'),
(30, 'App\\Models\\Empleado', 3, 'panel-admin', 'fe0c70516b9602a500eedef720d2c2d67de00f27055656ac81ada1838ada61db', '[\"*\"]', '2025-10-27 05:27:39', NULL, '2025-10-27 05:27:38', '2025-10-27 05:27:39'),
(31, 'App\\Models\\Empleado', 4, 'panel-admin', 'd368081d3f8f02b763adc3ba4420903e6abc569e37b842f2424eaffa15e7aa40', '[\"*\"]', '2025-10-27 05:27:55', NULL, '2025-10-27 05:27:53', '2025-10-27 05:27:55'),
(32, 'App\\Models\\Empleado', 1, 'panel-admin', '60130a0cfa77cdeb769fc9d892e635eb0dd9deaea1e0aa4752e30fbb315f672a', '[\"*\"]', '2025-10-27 05:59:48', NULL, '2025-10-27 05:32:34', '2025-10-27 05:59:48'),
(33, 'App\\Models\\Empleado', 1, 'panel-admin', '5fe458aaee7f067ed539e1e7cfd078510dd3b2d36006e71ff342796c5a383327', '[\"*\"]', '2025-10-27 06:34:17', NULL, '2025-10-27 06:00:09', '2025-10-27 06:34:17'),
(34, 'App\\Models\\Empleado', 1, 'panel-admin', 'd4ceed975d93a97fff432dee215214cc6fe339fc9ebbb90842617d36e7d85617', '[\"*\"]', '2025-10-27 06:43:10', NULL, '2025-10-27 06:34:27', '2025-10-27 06:43:10'),
(35, 'App\\Models\\Empleado', 2, 'panel-admin', '80a053517c8eab86cb60f9c76c8b8019dd4c272873a6f1639d538b1f3422fd49', '[\"*\"]', '2025-10-27 06:45:24', NULL, '2025-10-27 06:43:54', '2025-10-27 06:45:24'),
(36, 'App\\Models\\Empleado', 1, 'panel-admin', 'a5e2d026dfc691db0542342a73135f0df9b43200d8f56a77af5c0da1904d73be', '[\"*\"]', '2025-10-27 07:32:28', NULL, '2025-10-27 06:46:05', '2025-10-27 07:32:28'),
(37, 'App\\Models\\Empleado', 1, 'panel-admin', '0966ede3523ff21a71408cd5c78670cdc69094d5462ab2c4a21d021f4409b871', '[\"*\"]', '2025-10-27 09:34:42', NULL, '2025-10-27 07:32:40', '2025-10-27 09:34:42'),
(38, 'App\\Models\\Empleado', 1, 'panel-admin', 'b962da9142db552d3b20a02d11fc59d61d253fe97da273aabc3d7fc826ba3fce', '[\"*\"]', '2025-10-27 09:39:54', NULL, '2025-10-27 09:36:41', '2025-10-27 09:39:54'),
(39, 'App\\Models\\Empleado', 1, 'panel-admin', 'b6a5977016c309edef52b62c0d7675a5c6be249d301463bc8a8702a0e4f2a3c7', '[\"*\"]', NULL, NULL, '2025-10-27 09:48:28', '2025-10-27 09:48:28'),
(40, 'App\\Models\\Empleado', 1, 'panel-admin', 'a3c8c0ab1e00dec793d207ed82ece30b0e8e7f303ba27f9ff0bde6ac906d041e', '[\"*\"]', '2025-10-27 09:58:08', NULL, '2025-10-27 09:51:20', '2025-10-27 09:58:08'),
(41, 'App\\Models\\Empleado', 2, 'panel-admin', '8f719d1d42e68eb1225945ce5af9cecbd7dbe1d17b4e61e045bd9891f4ab2037', '[\"*\"]', '2025-10-27 10:14:31', NULL, '2025-10-27 09:58:29', '2025-10-27 10:14:31'),
(42, 'App\\Models\\Empleado', 1, 'panel-admin', '592d6e83e95dcb82321fde13b33be6ecf877ba6264ab5a66e4ea0b568e8530c6', '[\"*\"]', '2025-10-27 10:39:41', NULL, '2025-10-27 10:39:26', '2025-10-27 10:39:41'),
(43, 'App\\Models\\Empleado', 1, 'panel-admin', 'ec8e385d3b98bc4fd3d9247358be1838f3be4f4f62463f6bb419d63db5fcab7b', '[\"*\"]', '2025-10-27 10:40:16', NULL, '2025-10-27 10:40:01', '2025-10-27 10:40:16'),
(44, 'App\\Models\\Empleado', 3, 'panel-admin', 'f1a3bfef9ff74b267c7b81eded6580c0a10d6525b7b71ff88770c18840f58231', '[\"*\"]', '2025-10-27 10:50:05', NULL, '2025-10-27 10:40:40', '2025-10-27 10:50:05'),
(45, 'App\\Models\\Empleado', 1, 'panel-admin', '74024ba8683e1751eb5544044d878fc4424c7509b811c57a5f1edad15e96f478', '[\"*\"]', NULL, NULL, '2025-10-27 10:50:14', '2025-10-27 10:50:14'),
(46, 'App\\Models\\Empleado', 1, 'panel-admin', '9b800c7a9a6d011146f8829f0f8d47b27dabba94d860d26886c4fdda818ce424', '[\"*\"]', NULL, NULL, '2025-10-27 10:50:16', '2025-10-27 10:50:16'),
(47, 'App\\Models\\Empleado', 1, 'panel-admin', '2ad42e32a65bb7c36b6765ad983722e3fd3bd09c2eaeb053a9d4754aac0796ae', '[\"*\"]', NULL, NULL, '2025-10-27 10:50:33', '2025-10-27 10:50:33'),
(48, 'App\\Models\\Empleado', 1, 'panel-admin', '5083744c69e06992b7ac60f9dd1c0fe6de91778113d851e5635e37a55a70bba1', '[\"*\"]', NULL, NULL, '2025-10-27 10:51:06', '2025-10-27 10:51:06'),
(49, 'App\\Models\\Empleado', 1, 'panel-admin', '2791b6a7912600e400cf0fe5e51fdd9154660d8a6714666d45bb6cbb3459fa04', '[\"*\"]', NULL, NULL, '2025-10-27 10:51:08', '2025-10-27 10:51:08'),
(50, 'App\\Models\\Empleado', 1, 'panel-admin', 'c9a79067db0183fc8efb27ffa613732ce825a6798d82746aebc2e9894456e427', '[\"*\"]', NULL, NULL, '2025-10-27 10:51:15', '2025-10-27 10:51:15'),
(51, 'App\\Models\\Empleado', 1, 'panel-admin', '1ea63cee48b02f7eb04727478eb378305acd56982013f57a756ef462aa3dfa5a', '[\"*\"]', NULL, NULL, '2025-10-27 10:51:19', '2025-10-27 10:51:19'),
(52, 'App\\Models\\Empleado', 1, 'panel-admin', '134cac6cccf5cba0c6784469a5af9b078756ed72f289f0fb2f765f06b8328430', '[\"*\"]', NULL, NULL, '2025-10-27 10:52:33', '2025-10-27 10:52:33'),
(53, 'App\\Models\\Empleado', 2, 'panel-admin', '30d2e20bfe1cbfd22a88973f76404fdca4a1a68363a7e885939d4280fd5dd22d', '[\"*\"]', NULL, NULL, '2025-10-27 10:53:32', '2025-10-27 10:53:32'),
(54, 'App\\Models\\Empleado', 2, 'panel-admin', '0e429f7c8be73c7c53c5f53350c9b117c18c32a4f735c8b9b15c945dff983554', '[\"*\"]', NULL, NULL, '2025-10-27 10:53:50', '2025-10-27 10:53:50'),
(55, 'App\\Models\\Empleado', 1, 'panel-admin', 'fb3e2535c0055b3660d22e53bc98b0fd4fa5e410bd9c90ea48dbd6535e0b2c42', '[\"*\"]', NULL, NULL, '2025-10-27 10:56:53', '2025-10-27 10:56:53'),
(56, 'App\\Models\\Empleado', 1, 'panel-admin', 'd830da191455e78cc4209ed4f40a13d33428029a96b2a903650d16c29e340798', '[\"*\"]', NULL, NULL, '2025-10-27 11:05:28', '2025-10-27 11:05:28'),
(57, 'App\\Models\\Empleado', 1, 'panel-admin', '5e27a9f4f7406964da678c3cf9ab018eab47a92a265bc3c6a7191e72cd0265b3', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:34', '2025-10-27 11:11:34'),
(58, 'App\\Models\\Empleado', 1, 'panel-admin', '7a0b9690d0ec78026de5c59f95918933a445b72205c9701f1eeaeecf432b6596', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:37', '2025-10-27 11:11:37'),
(59, 'App\\Models\\Empleado', 1, 'panel-admin', '87dad9f12d994052ce850fc5d2cb1fe09feff5da7b71678392749724a8da59d1', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:38', '2025-10-27 11:11:38'),
(60, 'App\\Models\\Empleado', 1, 'panel-admin', '0b2e018ef7494861075edaab9b823192a710105a06612bf933eaafa4a7df3509', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:40', '2025-10-27 11:11:40'),
(61, 'App\\Models\\Empleado', 1, 'panel-admin', '5fb2b9662d331eba4f9bacdc5cc52d1488059eb7f3af38004ebfcd2c9f941438', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:42', '2025-10-27 11:11:42'),
(62, 'App\\Models\\Empleado', 1, 'panel-admin', '8e378c5f62f2653cae8f4286ff48faa473ef7ede718e4958f2109e9939d7ba70', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:43', '2025-10-27 11:11:43'),
(63, 'App\\Models\\Empleado', 1, 'panel-admin', '7a47307ab482239b9c3d07f862cc7744aef7c9a7a2d48b80d082eafa7f410abc', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:44', '2025-10-27 11:11:44'),
(64, 'App\\Models\\Empleado', 1, 'panel-admin', '5b049c1c4301142e6012c201c28d27430a00b5dfc7765bd0f64addfefabae240', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:45', '2025-10-27 11:11:45'),
(65, 'App\\Models\\Empleado', 1, 'panel-admin', '2ef913c5aa57fffea83b79a366cd8c3423fd0d907226295b98b0dbaff9fc08ed', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:46', '2025-10-27 11:11:46'),
(66, 'App\\Models\\Empleado', 1, 'panel-admin', 'f12d19539546b8d3b344c40bda7173f0b5eb50147476d3a0e744338c8a54c7b9', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:46', '2025-10-27 11:11:46'),
(67, 'App\\Models\\Empleado', 1, 'panel-admin', '1bec653a4686dcfbb7b5770a1424f8d8710875238f0bae0cd4192c223f70ee4a', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:47', '2025-10-27 11:11:47'),
(68, 'App\\Models\\Empleado', 1, 'panel-admin', 'e19d86bfeb386f5b28f0cfa4107f2d8bd9b9dcb6a7743b0c024ca7660259f2ea', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:47', '2025-10-27 11:11:47'),
(69, 'App\\Models\\Empleado', 1, 'panel-admin', 'bd2de94ef50cdf66a2ed493f99c4f3c649252a26c2b12d3b0cd751c51f494ce2', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:48', '2025-10-27 11:11:48'),
(70, 'App\\Models\\Empleado', 1, 'panel-admin', '35cac9aab82b748e9c00c249744c81fc115f5e451502de2dc6984fc070231e97', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:48', '2025-10-27 11:11:48'),
(71, 'App\\Models\\Empleado', 1, 'panel-admin', '152972e0271da7bca59eaf3cb1d2f4ea6bb511bfa90a80bc0e02d08d8d6e916d', '[\"*\"]', NULL, NULL, '2025-10-27 11:11:53', '2025-10-27 11:11:53'),
(72, 'App\\Models\\Empleado', 1, 'panel-admin', '401e697d523f90856800381777efd1202b72e30040f062ca24bc11a98382d8e5', '[\"*\"]', NULL, NULL, '2025-10-27 11:15:45', '2025-10-27 11:15:45'),
(73, 'App\\Models\\Cliente', 4, 'token_cliente', 'ce32e6455afbbf92e290c26112dc067d63145ce0ed255a205320e094f7626c82', '[\"*\"]', '2025-10-27 11:32:07', NULL, '2025-10-27 11:32:06', '2025-10-27 11:32:07'),
(74, 'App\\Models\\Cliente', 4, 'token_cliente', '64014b263cffa9f65b6c40366a5acb48c6db60f54dc7b100bb7464bc1d409383', '[\"*\"]', '2025-10-27 12:08:57', NULL, '2025-10-27 12:08:53', '2025-10-27 12:08:57'),
(75, 'App\\Models\\Cliente', 4, 'token_cliente', '255bcfbcbf4bfb52b9fec820e5cb33f9b7320bc0aa3da597e30375be421a4a59', '[\"*\"]', '2025-10-27 22:49:02', NULL, '2025-10-27 22:49:01', '2025-10-27 22:49:02'),
(76, 'App\\Models\\Empleado', 1, 'panel-admin', '35bca28604fa0a3508616ab9d77e0493161986b58479483622b0fc845f033e44', '[\"*\"]', NULL, NULL, '2025-10-27 22:50:09', '2025-10-27 22:50:09'),
(77, 'App\\Models\\Empleado', 2, 'panel-admin', 'e2f9ef731648b53840c09d7fd0c28c543e2215c4510972baaaa5a4186443b640', '[\"*\"]', NULL, NULL, '2025-10-27 23:00:47', '2025-10-27 23:00:47'),
(78, 'App\\Models\\Empleado', 1, 'panel-admin', '31cd1a4ac0576e9c54cb5af431fdfd5e1b3e86e13c02ac5246c4890951ebc7f3', '[\"*\"]', NULL, NULL, '2025-10-27 23:03:00', '2025-10-27 23:03:00'),
(79, 'App\\Models\\Empleado', 1, 'panel-admin', 'a8da35f324044f08c90af4af0c21dffa83e4e6ad840253e04a96fdd7632db182', '[\"*\"]', NULL, NULL, '2025-10-27 23:03:10', '2025-10-27 23:03:10'),
(80, 'App\\Models\\Empleado', 1, 'panel-admin', '6e5deb3ee8d3c65820eb37d0894ad78c7372025d0cd6efc81d235ff7ee8334a5', '[\"*\"]', NULL, NULL, '2025-10-27 23:03:31', '2025-10-27 23:03:31'),
(81, 'App\\Models\\Empleado', 1, 'panel-admin', '69d0729695d8ae24d8b43e60a740d014e4a90760300b0a08341aa963a24078f8', '[\"*\"]', NULL, NULL, '2025-10-27 23:24:42', '2025-10-27 23:24:42'),
(82, 'App\\Models\\Empleado', 1, 'panel-admin', '1bc21e72898ee49271bf2edb77abaf38f5eb2fdc3ece4104facb04cee0ada669', '[\"*\"]', NULL, NULL, '2025-10-27 23:27:58', '2025-10-27 23:27:58'),
(83, 'App\\Models\\Empleado', 1, 'panel-admin', 'e8e5177cec1d91e5b15507ea0fb9933984764b971b369582b6f0398e88055cb9', '[\"*\"]', NULL, NULL, '2025-10-27 23:31:58', '2025-10-27 23:31:58'),
(84, 'App\\Models\\Empleado', 2, 'panel-admin', '1e977f888498ec4dd899aa221a4c6d786f28efbb232c0c74a9ec85d4275fef19', '[\"*\"]', NULL, NULL, '2025-10-27 23:32:11', '2025-10-27 23:32:11'),
(85, 'App\\Models\\Empleado', 1, 'panel-admin', 'f9087e97414ec8ebf449b14216e8038005ee1100b3ac775d205de73ae86a795d', '[\"*\"]', NULL, NULL, '2025-10-27 23:58:25', '2025-10-27 23:58:25'),
(86, 'App\\Models\\Empleado', 1, 'panel-admin', '350a5228346565b1f4ff2deb7a4a22117160500cc56087e35fdf449aed402d5c', '[\"*\"]', NULL, NULL, '2025-10-28 00:01:53', '2025-10-28 00:01:53'),
(87, 'App\\Models\\Empleado', 1, 'panel-admin', '559028ca75dbc59ef8b51121adbbd09782a07d9295942c6f9bfa54adf8a85108', '[\"*\"]', '2025-10-28 00:14:43', NULL, '2025-10-28 00:14:43', '2025-10-28 00:14:43'),
(88, 'App\\Models\\Empleado', 1, 'panel-admin', '4e49923fe48e94512e71aea29cbdf367ab67c2e5049378b135c434ed61370028', '[\"*\"]', '2025-10-28 00:14:57', NULL, '2025-10-28 00:14:56', '2025-10-28 00:14:57'),
(89, 'App\\Models\\Empleado', 1, 'panel-admin', '2a742ec67a8236b1b3dbb60d134cecb4d5079192ff42fa5f006211342c7cfc1f', '[\"*\"]', '2025-10-28 01:11:49', NULL, '2025-10-28 01:11:49', '2025-10-28 01:11:49'),
(90, 'App\\Models\\Empleado', 1, 'panel-admin', '4e327218cca2829e9e9ede4c8777ec06c7fc899ffa56d3da34ebbf3593eb3ae1', '[\"*\"]', '2025-10-28 01:12:04', NULL, '2025-10-28 01:12:04', '2025-10-28 01:12:04'),
(91, 'App\\Models\\Empleado', 1, 'panel-admin', '74fae8d091ef3a243e5b087b5dbc95495bd8efc1041815b0d8c81c97252f1629', '[\"*\"]', '2025-10-28 01:13:23', NULL, '2025-10-28 01:13:22', '2025-10-28 01:13:23'),
(98, 'App\\Models\\Empleado', 1, 'panel-admin', '0432fd2f03e69710b1cd862f97815d0e3398d36139033c202127630f34b2a84a', '[\"*\"]', '2025-10-28 02:13:57', NULL, '2025-10-28 02:13:56', '2025-10-28 02:13:57'),
(99, 'App\\Models\\Empleado', 1, 'panel-admin', 'e5bab72c3a124af14f5ffdb05b2f96b9ad825c6dfafd1bd43f475f24bf8761a7', '[\"*\"]', '2025-10-28 03:03:51', NULL, '2025-10-28 03:03:50', '2025-10-28 03:03:51'),
(101, 'App\\Models\\Empleado', 3, 'panel-admin', '63dc9b46c5964b44d26e1b96264cf261ff9d7e4b2f37cf9a2bdb6466b9f61149', '[\"*\"]', '2025-10-28 04:34:21', NULL, '2025-10-28 03:48:27', '2025-10-28 04:34:21'),
(103, 'App\\Models\\Empleado', 3, 'panel-admin', 'e096456cf4d28980cc274065ab6060cbafe5cd008d4b873227bdcaf7a38a54c2', '[\"*\"]', '2025-10-28 04:54:26', NULL, '2025-10-28 04:38:28', '2025-10-28 04:54:26'),
(104, 'App\\Models\\Empleado', 1, 'panel-admin', '5eb49b217dbac711753ac512b260c84dca48b341bd31b8f01d9345cf380583cc', '[\"*\"]', '2025-10-28 05:52:36', NULL, '2025-10-28 05:50:30', '2025-10-28 05:52:36'),
(107, 'App\\Models\\Empleado', 1, 'panel-admin', 'dbcc7f077dbb19459ad930db55073e966bc6da8642428d594f6a5b0d43605976', '[\"*\"]', '2025-10-28 07:32:57', NULL, '2025-10-28 07:02:26', '2025-10-28 07:32:57'),
(109, 'App\\Models\\Empleado', 1, 'panel-admin', '25560f545b6c2cdaf8c8fb884b60763f7b5deaa7a1945950d3b52cad13139caa', '[\"*\"]', '2025-10-28 08:23:14', NULL, '2025-10-28 08:22:01', '2025-10-28 08:23:14'),
(110, 'App\\Models\\Empleado', 1, 'panel-admin', '5d16b6e3c7d7de630bae91868949a2c9f34fefad9d6a2f7fa07e150c9d66ceed', '[\"*\"]', '2025-10-28 08:52:07', NULL, '2025-10-28 08:42:57', '2025-10-28 08:52:07'),
(111, 'App\\Models\\Empleado', 1, 'panel-admin', '201cafe0147f3d05d3ebba70f35423c39cf560288ced45bb8212d1729f1753e5', '[\"*\"]', '2025-10-28 09:31:24', NULL, '2025-10-28 09:16:22', '2025-10-28 09:31:24'),
(112, 'App\\Models\\Empleado', 1, 'panel-admin', 'fed0847fbe30bb19ad4a175f6efc33d8cdd69a1e11d77a75a29de30b20a27a28', '[\"*\"]', '2025-10-28 09:45:09', NULL, '2025-10-28 09:32:12', '2025-10-28 09:45:09'),
(118, 'App\\Models\\Empleado', 1, 'panel-admin', '6e3eeb2a12f87d27a5f715095e9188db86a6f3e3366a69977bf902b6aad6a469', '[\"*\"]', '2025-10-28 10:53:47', NULL, '2025-10-28 10:43:45', '2025-10-28 10:53:47'),
(120, 'App\\Models\\Empleado', 1, 'panel-admin', 'a4cdcbca0a2e571a35bf7a950236cf6828ea4d66ac8d5b8bb531c4624cfd583f', '[\"*\"]', NULL, NULL, '2025-10-28 10:56:31', '2025-10-28 10:56:31'),
(121, 'App\\Models\\Empleado', 1, 'panel-admin', 'd2261627fd5474a801f580efacf49baec6c2f2903cbbf36c4e58802598c2c444', '[\"*\"]', '2025-10-28 10:58:28', NULL, '2025-10-28 10:58:26', '2025-10-28 10:58:28'),
(123, 'App\\Models\\Empleado', 1, 'panel-admin', 'c4928e2d3cdb74dc1250570135c117c96a0cfca45e2f7e73ee8f521c335408eb', '[\"*\"]', NULL, NULL, '2025-10-28 11:03:19', '2025-10-28 11:03:19'),
(124, 'App\\Models\\Empleado', 1, 'panel-admin', 'd49a91606b54d203f0067cd136979811b716e4db3415e3d525344e52b81963b9', '[\"admin\"]', '2025-10-28 11:25:48', NULL, '2025-10-28 11:25:48', '2025-10-28 11:25:48'),
(125, 'App\\Models\\Empleado', 1, 'panel-admin', 'd2bf3f720064c470d9795406bf5a0d9d5a61542ea9a51731a9dce06de5a4ef53', '[\"admin\"]', '2025-10-28 11:26:00', NULL, '2025-10-28 11:25:59', '2025-10-28 11:26:00'),
(126, 'App\\Models\\Cliente', 4, 'token_cliente', '97ea9deabde0c10a08a721545de24ff8229641348e9d97e4aa7567f9015aaa14', '[\"client\"]', '2025-10-28 11:49:49', NULL, '2025-10-28 11:26:25', '2025-10-28 11:49:49'),
(127, 'App\\Models\\Empleado', 1, 'panel-admin', '675ce4c4b58602b1bed7b1feb72439db77a37eb5d1f7b14f4d1f671c7c8f9c99', '[\"admin\"]', NULL, NULL, '2025-10-28 11:30:47', '2025-10-28 11:30:47'),
(128, 'App\\Models\\Cliente', 4, 'token_cliente', '2d22a05ffeac8de27a5c144eac3db4ad6ff524a9327ef17fa1033cb9e9672d53', '[\"*\"]', '2025-10-28 11:52:04', NULL, '2025-10-28 11:52:03', '2025-10-28 11:52:04'),
(129, 'App\\Models\\Empleado', 1, 'panel-admin', '1d57a0d59426a1e613514023a117b6cb5a337c860e8a8bc20b68255cf0567b60', '[\"*\"]', '2025-10-28 11:53:23', NULL, '2025-10-28 11:53:23', '2025-10-28 11:53:23'),
(130, 'App\\Models\\Cliente', 4, 'token_cliente', '131944e3511b37d237f30a45c8b9712707d6576f60e22989ade30f059e4941fb', '[\"*\"]', '2025-10-28 12:04:24', NULL, '2025-10-28 12:04:23', '2025-10-28 12:04:24'),
(131, 'App\\Models\\Empleado', 1, 'panel-admin', '590c7ad3ae6417998fc3f7da4582093f34d3757d9d7b0a279c7a83d35969012b', '[\"*\"]', '2025-10-28 12:04:40', NULL, '2025-10-28 12:04:40', '2025-10-28 12:04:40'),
(132, 'App\\Models\\Cliente', 6, 'token_cliente', '04ed4d7d007c98fd5e608ded5e4c29cc8b1a8fb3474fd17278396d7e6ac7fbfe', '[\"*\"]', '2025-10-28 12:27:35', NULL, '2025-10-28 12:27:34', '2025-10-28 12:27:35'),
(133, 'App\\Models\\Empleado', 1, 'panel-admin', '279f6723aa77ed1f11044ccd8cda47297bfbe52538c279158b14c8526cb81771', '[\"*\"]', '2025-10-28 12:34:36', NULL, '2025-10-28 12:28:03', '2025-10-28 12:34:36'),
(134, 'App\\Models\\Cliente', 6, 'token_cliente', '8d8f8edcb9999c8cac442fdce178bf5dddb87c787eddc3356b5fb7cbf7a4c91b', '[\"*\"]', '2025-10-28 12:43:45', NULL, '2025-10-28 12:43:24', '2025-10-28 12:43:45'),
(135, 'App\\Models\\Empleado', 1, 'panel-admin', '89296501cfb5e5a33076beda49c7ea8e6424d327ffa0dbdb4ba0629ea0017f0e', '[\"*\"]', '2025-10-28 12:44:13', NULL, '2025-10-28 12:44:08', '2025-10-28 12:44:13'),
(137, 'App\\Models\\Empleado', 1, 'panel-admin', '635fb31372dd67341e14ae368be1d3201db29395909df9fa47c918a14d6351aa', '[\"*\"]', '2025-10-28 13:50:01', NULL, '2025-10-28 12:55:17', '2025-10-28 13:50:01'),
(140, 'App\\Models\\Empleado', 1, 'panel-admin', '38aa1f357e0846a0fc8e52854a09beefa564f51f02825d087dfc8e36da6d1f38', '[\"*\"]', '2025-10-28 15:05:13', NULL, '2025-10-28 15:05:10', '2025-10-28 15:05:13'),
(142, 'App\\Models\\Empleado', 1, 'panel-admin', '12700e5f757b41b33cd337e7340729fb80b4bf87b75a4db4530887f4d49d8a45', '[\"*\"]', '2025-10-28 15:09:21', NULL, '2025-10-28 15:09:11', '2025-10-28 15:09:21'),
(143, 'App\\Models\\Empleado', 4, 'panel-admin', 'dee644e2c5442a9024841e620364345ebef84d3e6b8bf67f04cde244a1dc3f44', '[\"*\"]', '2025-10-28 15:25:45', NULL, '2025-10-28 15:16:12', '2025-10-28 15:25:45'),
(145, 'App\\Models\\Empleado', 1, 'panel-admin', 'b72fb1287e0fa12aad226ee119cd774ca3abdd5dffaa3702f7a393f0b5da9b03', '[\"*\"]', '2025-10-28 15:44:06', NULL, '2025-10-28 15:43:54', '2025-10-28 15:44:06'),
(146, 'App\\Models\\Empleado', 1, 'panel-admin', '72cfed0f84f4c371b7b4513e62b23f56dbe802ba24ade072ab6ad8004ab2383f', '[\"*\"]', '2025-10-28 16:23:26', NULL, '2025-10-28 16:17:44', '2025-10-28 16:23:26'),
(147, 'App\\Models\\Empleado', 1, 'panel-admin', 'f65b896a58e8fbc459841c4c08c00f40eb4444b880f834330c3f314a7853c462', '[\"*\"]', '2025-10-28 20:16:36', NULL, '2025-10-28 18:21:22', '2025-10-28 20:16:36'),
(150, 'App\\Models\\Empleado', 1, 'panel-admin', '85c2a20d13b5d47cd2ee4438c0551b9f2447a174a35bc7dcd86412877040e937', '[\"*\"]', '2025-10-28 21:08:50', NULL, '2025-10-28 20:20:43', '2025-10-28 21:08:50'),
(153, 'App\\Models\\Empleado', 1, 'panel-admin', '773da8b007af83671d8b9cf43d43ec2405255c0b33ef74f5cbe21f6bf69b0c5c', '[\"*\"]', '2025-10-28 23:17:03', NULL, '2025-10-28 23:17:00', '2025-10-28 23:17:03'),
(155, 'App\\Models\\Empleado', 1, 'panel-admin', 'ae4eaabedbe81e5510bc194e5b9903a605e5bd4415fadd85d3015ee968818b23', '[\"*\"]', '2025-10-28 23:45:52', NULL, '2025-10-28 23:31:00', '2025-10-28 23:45:52'),
(156, 'App\\Models\\Empleado', 1, 'panel-admin', 'c13c01158e36f21ac6f59b2cf90b288c6a1e2710f3e531b300a940706aee921b', '[\"*\"]', '2025-10-29 00:11:55', NULL, '2025-10-28 23:59:48', '2025-10-29 00:11:55'),
(157, 'App\\Models\\Empleado', 1, 'panel-admin', '7760bfae66164b4809539103ca816df04814e47dfe250ccf07967a05fdfaee96', '[\"*\"]', '2025-10-29 00:15:35', NULL, '2025-10-29 00:15:16', '2025-10-29 00:15:35'),
(158, 'App\\Models\\Empleado', 1, 'panel-admin', '9f72e3c333f0f0f4b45857c099b4d411be970e0f9e4d44ed58abb33d48b9cf13', '[\"*\"]', '2025-10-29 00:40:38', NULL, '2025-10-29 00:18:19', '2025-10-29 00:40:38'),
(159, 'App\\Models\\Empleado', 1, 'panel-admin', 'cea1e27e3416597065dffda8a5475c3f820d62285fb1fadf885df3d173825c5e', '[\"*\"]', '2025-10-29 01:36:38', NULL, '2025-10-29 00:46:55', '2025-10-29 01:36:38'),
(164, 'App\\Models\\Empleado', 1, 'panel-admin', '21c765093accf5e2b75378b0da6b52caefeabce31f5261756b1f8a61888255ec', '[\"*\"]', '2025-10-29 02:43:40', NULL, '2025-10-29 02:43:14', '2025-10-29 02:43:40'),
(171, 'App\\Models\\Cliente', 4, 'token_cliente', 'c615c4d6c6142c37833e13419ddc8920d42a0423098998e66cf65f68d090ac68', '[\"*\"]', '2025-10-31 11:24:06', NULL, '2025-10-31 11:19:53', '2025-10-31 11:24:06'),
(172, 'App\\Models\\Empleado', 1, 'panel-admin', '21dc953d85e9df20e9c58c5a0a10c7c8f176ab1776b13adf215538f1ae9782e2', '[\"*\"]', '2025-10-31 11:24:06', NULL, '2025-10-31 11:23:53', '2025-10-31 11:24:06'),
(173, 'App\\Models\\Empleado', 1, 'panel-admin', '561b6fd3aa9ab6447d0adb298b23446d3d39054c90b7a9f9079c9155879be932', '[\"*\"]', '2025-11-05 08:18:16', NULL, '2025-11-05 07:05:34', '2025-11-05 08:18:16'),
(181, 'App\\Models\\Empleado', 2, 'panel-admin', 'dd78bee5bfb8864fc9d578332787318a910820c7277023ac55cbd60fe1218a96', '[\"*\"]', '2025-11-05 09:25:02', NULL, '2025-11-05 09:24:52', '2025-11-05 09:25:02'),
(183, 'App\\Models\\Empleado', 1, 'panel-admin', '22335330ef34b03bd58f92c3ad8841842e33d37d9dad66afaaa93049296539a1', '[\"*\"]', '2025-11-07 09:53:12', NULL, '2025-11-07 09:52:32', '2025-11-07 09:53:12'),
(186, 'App\\Models\\Empleado', 4, 'panel-admin', '717be339e9ddd87d8a2bdf6a5947e712f19a989ea8279489015ffa2c6da2d325', '[\"*\"]', '2025-11-15 05:49:41', NULL, '2025-11-15 05:28:52', '2025-11-15 05:49:41'),
(188, 'App\\Models\\Empleado', 1, 'panel-admin', '8f55277fa7a816a53635a6a7945047f80b8a4cf142b6aa7c91a2cbbd35e003fe', '[\"*\"]', '2026-05-22 07:20:13', NULL, '2026-05-22 07:18:37', '2026-05-22 07:20:13'),
(191, 'App\\Models\\Empleado', 1, 'panel-admin', '8f575c83356d2bbeb3278a0a656c8282acb131c86e0a218b0b3b404df396fb31', '[\"*\"]', '2026-05-23 08:02:28', NULL, '2026-05-23 08:02:27', '2026-05-23 08:02:28'),
(192, 'App\\Models\\Cliente', 4, 'token_cliente', '6f6025393ae3f8a7a5e4a856012e83648b74dad6188b27bf085ec122245f9365', '[\"*\"]', '2026-05-23 08:52:25', NULL, '2026-05-23 08:09:46', '2026-05-23 08:52:25'),
(193, 'App\\Models\\Cliente', 11, 'token_cliente', '72e8ce965cf09c203d4cdb4aa9b542daaf8a7c762f1a5700ca49ad7cb67d4cec', '[\"*\"]', NULL, NULL, '2026-07-11 05:15:40', '2026-07-11 05:15:40'),
(194, 'App\\Models\\Cliente', 11, 'token_cliente', '87a4b60ce0a66a705d4513daa3c0daaacf8e990116d6a40acb3614cd553fb5d2', '[\"*\"]', NULL, NULL, '2026-07-11 05:15:54', '2026-07-11 05:15:54'),
(195, 'App\\Models\\Cliente', 12, 'token_cliente', '3803a818a82068d175128958cdca4116812842d92a5b213ebc70fc29a1206ccf', '[\"*\"]', NULL, NULL, '2026-07-11 06:02:42', '2026-07-11 06:02:42'),
(196, 'App\\Models\\Cliente', 12, 'token_cliente', 'c4de9be97e1898fa334c0e8ef2c0ef4e14a7caa0ff8e7bb2a4391c4eb9302a95', '[\"*\"]', NULL, NULL, '2026-07-11 06:17:59', '2026-07-11 06:17:59'),
(197, 'App\\Models\\Cliente', 12, 'token_cliente', 'd9b795af2884a1b485151b382aeb1345179376d09cf65964b54c6cc0482f9485', '[\"*\"]', NULL, NULL, '2026-07-11 06:48:31', '2026-07-11 06:48:31'),
(198, 'App\\Models\\Cliente', 12, 'token_cliente', '013537bed82339a3d5de6fe7ed676ea7c545aea6731fb89cb7a814f3feda05a1', '[\"*\"]', '2026-07-11 19:33:54', NULL, '2026-07-11 19:33:08', '2026-07-11 19:33:54'),
(199, 'App\\Models\\Cliente', 12, 'token_cliente', '2a295f2fdba7804b7ffebe33660624f5498195e784cfbc19609755d208ed5a07', '[\"*\"]', '2026-07-11 19:41:52', NULL, '2026-07-11 19:41:35', '2026-07-11 19:41:52'),
(200, 'App\\Models\\Cliente', 12, 'token_cliente', 'fc45bffa2fd67e90a67dcdbf421d8e7f95543864edc40d854c01804e4894e82c', '[\"*\"]', '2026-07-11 19:55:40', NULL, '2026-07-11 19:54:35', '2026-07-11 19:55:40'),
(201, 'App\\Models\\Cliente', 12, 'token_cliente', '372ca80e71eb273aa23a6b4ccc1d1db09ddcb216e6498feec216c200ac465d0e', '[\"*\"]', '2026-07-11 20:06:54', NULL, '2026-07-11 20:06:35', '2026-07-11 20:06:54'),
(202, 'App\\Models\\Cliente', 12, 'token_cliente', 'cf2f35235a9e6f9264a3d7d42cb1bc23de0b308a9e7beb8577822ff9be81e404', '[\"*\"]', '2026-07-13 00:51:35', NULL, '2026-07-13 00:49:32', '2026-07-13 00:51:35'),
(203, 'App\\Models\\Cliente', 12, 'token_cliente', '6847bf76636caedfbcb3606513391db273efe16bd242dac239f4f50f0032987e', '[\"*\"]', '2026-07-13 08:19:19', NULL, '2026-07-13 08:18:55', '2026-07-13 08:19:19'),
(204, 'App\\Models\\Cliente', 12, 'token_cliente', 'e15d910291186af6054f7c2407f90756b5cefc795b74947fa97f60cab4c9d49b', '[\"*\"]', '2026-07-13 09:13:09', NULL, '2026-07-13 09:12:49', '2026-07-13 09:13:09'),
(205, 'App\\Models\\Cliente', 12, 'token_cliente', '0e40ca8e666955fdea1d553dbcafa4e183e5b31708134c69cbd2d276f74245b1', '[\"*\"]', '2026-07-17 05:40:24', NULL, '2026-07-17 05:28:49', '2026-07-17 05:40:24'),
(206, 'App\\Models\\Cliente', 12, 'token_cliente', 'd6552954c3c21d216a8ac55a92fac17c7620b77e30b75d7afd7ca92353552759', '[\"*\"]', '2026-07-17 20:03:15', NULL, '2026-07-17 19:25:17', '2026-07-17 20:03:15'),
(207, 'App\\Models\\Cliente', 12, 'token_cliente', 'ab37ed72da9d438740eb254dcc9a58e857444d0280a2154e1952eddeebe5dab9', '[\"*\"]', '2026-07-17 20:07:20', NULL, '2026-07-17 20:07:12', '2026-07-17 20:07:20'),
(208, 'App\\Models\\Cliente', 12, 'token_cliente', '96392fb0b8b20c5993b68ed3896369dbc4ea3b55261753175d43ac1bd6331089', '[\"*\"]', '2026-07-17 20:39:12', NULL, '2026-07-17 20:39:02', '2026-07-17 20:39:12'),
(209, 'App\\Models\\Cliente', 12, 'token_cliente', '138da6c660c456e4f745feba891fc16b05a900414e03f74acebfaa9dc60f3bcc', '[\"*\"]', '2026-07-17 21:20:06', NULL, '2026-07-17 21:19:50', '2026-07-17 21:20:06'),
(210, 'App\\Models\\Cliente', 12, 'token_cliente', 'd371a61b2ea173be4d66c90f27e2bce6563920430c246383d8ec7eb1e530e507', '[\"*\"]', '2026-07-17 21:29:22', NULL, '2026-07-17 21:29:12', '2026-07-17 21:29:22'),
(211, 'App\\Models\\Cliente', 12, 'token_cliente', 'ed4babdbcf257d280001c23cf7228588e7b856bdf2e26e871322a0de9cc32ad7', '[\"*\"]', '2026-07-17 21:39:44', NULL, '2026-07-17 21:39:30', '2026-07-17 21:39:44'),
(212, 'App\\Models\\Cliente', 12, 'token_cliente', '684d344862761151056f15fd24861a1f12e9ddcab5a0b174024de4b26d731f5e', '[\"*\"]', '2026-07-17 22:00:29', NULL, '2026-07-17 22:00:19', '2026-07-17 22:00:29'),
(213, 'App\\Models\\Cliente', 12, 'token_cliente', '62dbad64acb8881bcf6ba49725c6c2a801cc035d4a574d350d2b32f9287c3017', '[\"*\"]', '2026-07-17 23:05:12', NULL, '2026-07-17 23:05:01', '2026-07-17 23:05:12'),
(214, 'App\\Models\\Cliente', 12, 'token_cliente', 'f4faad607765d98669ed44f99a8fb35a4b35da35d3a942e0c7681c9ea925fe93', '[\"*\"]', '2026-07-18 00:36:38', NULL, '2026-07-18 00:36:26', '2026-07-18 00:36:38'),
(215, 'App\\Models\\Cliente', 12, 'token_cliente', 'db5541213f21268aab13074ece54a1373a285b2dae9ea86c7883121bdc87d6b1', '[\"*\"]', '2026-07-18 00:57:53', NULL, '2026-07-18 00:57:42', '2026-07-18 00:57:53'),
(216, 'App\\Models\\Cliente', 12, 'token_cliente', 'dbe70eb79375707823e7c48f9a5c18166a76e80b0f29d2b3c6d4f29c4d4b59ad', '[\"*\"]', '2026-07-18 01:16:02', NULL, '2026-07-18 01:15:55', '2026-07-18 01:16:02'),
(217, 'App\\Models\\Cliente', 12, 'token_cliente', 'd8a68e4c4d33057553d8deb605cdcdc94509bbf8e9767629f4ceea9a38685cf8', '[\"*\"]', '2026-07-18 01:21:32', NULL, '2026-07-18 01:21:22', '2026-07-18 01:21:32'),
(218, 'App\\Models\\Cliente', 12, 'token_cliente', 'fc0504b340e318337a171b401f0e5dc368e74b575071d3d90c512f060527de54', '[\"*\"]', '2026-07-18 02:02:59', NULL, '2026-07-18 01:42:48', '2026-07-18 02:02:59'),
(219, 'App\\Models\\Cliente', 12, 'token_cliente', '8eb9e208be86f48bc3623535f46caf4317d6a18cf1d98a0d61887e53bbc7a15d', '[\"*\"]', '2026-07-18 04:35:05', NULL, '2026-07-18 04:34:49', '2026-07-18 04:35:05'),
(220, 'App\\Models\\Cliente', 12, 'token_cliente', '48f4bfd0f3d8f8df986389e6453704b90bb76db3ff6328d3e6676adefbc16849', '[\"*\"]', '2026-07-18 06:12:05', NULL, '2026-07-18 06:11:11', '2026-07-18 06:12:05'),
(221, 'App\\Models\\Cliente', 12, 'token_cliente', '020487169d3367bfdd0c557d90fbc41f46a4d440207ec6e5f5a8191253ce3450', '[\"*\"]', '2026-07-18 06:21:50', NULL, '2026-07-18 06:21:34', '2026-07-18 06:21:50'),
(222, 'App\\Models\\Cliente', 12, 'token_cliente', '08f18723150503aeba345aa8742ee198b86f97a276dab098765b423c9103cc75', '[\"*\"]', '2026-07-18 06:23:10', NULL, '2026-07-18 06:22:54', '2026-07-18 06:23:10'),
(223, 'App\\Models\\Cliente', 12, 'token_cliente', '4340965cd50b593e57255e7946fa20a2ed67938ba1943b9b6e7e50f8efd73cba', '[\"*\"]', '2026-07-18 06:41:54', NULL, '2026-07-18 06:41:08', '2026-07-18 06:41:54'),
(224, 'App\\Models\\Cliente', 13, 'token_cliente', '7aaec3f9405af0f0b24467f95717c34ac3233b9a4f4faf771a5dd5ba43b659d0', '[\"*\"]', '2026-07-18 06:58:04', NULL, '2026-07-18 06:56:51', '2026-07-18 06:58:04'),
(225, 'App\\Models\\Cliente', 14, 'token_cliente', '03b1e1b1ae3b3454ced3ea44df5b1da1b482249651a4077907bbf3f2fdb2162a', '[\"*\"]', '2026-07-18 07:11:30', NULL, '2026-07-18 07:07:19', '2026-07-18 07:11:30'),
(226, 'App\\Models\\Cliente', 12, 'token_cliente', '63d61c3efab798c88d0a81daca121f11ef44258a80b04e4a9032d470b673f0fb', '[\"*\"]', '2026-07-18 08:18:49', NULL, '2026-07-18 08:18:26', '2026-07-18 08:18:49'),
(227, 'App\\Models\\Cliente', 15, 'token_cliente', 'e77738e946668c585b73748a57ae32da9c065d4e9ba1ed5301df0f35ba993e80', '[\"*\"]', NULL, NULL, '2026-07-18 08:20:38', '2026-07-18 08:20:38'),
(230, 'App\\Models\\Cliente', 12, 'token_cliente', '8a09cfe3d63e7010ca1385f51fbbc0dbd523dc0b10e25e4e519e717cf21107ef', '[\"*\"]', NULL, NULL, '2026-08-02 05:01:24', '2026-08-02 05:01:24'),
(234, 'App\\Models\\Cliente', 16, 'token_cliente', '9b2f969114d5da70e395a88df5bf6435fac48d47c346eb9e9a120e4cb0656291', '[\"*\"]', NULL, NULL, '2026-08-03 04:21:51', '2026-08-03 04:21:51'),
(235, 'App\\Models\\Cliente', 17, 'token_cliente', '86a57ec76f76b449be2f5ff95682c67fa810fed836c70e4fa3eea9846b9a2ff2', '[\"*\"]', NULL, NULL, '2026-08-03 04:22:45', '2026-08-03 04:22:45'),
(237, 'App\\Models\\Empleado', 1, 'panel-admin', '906a2dd02536a22823dc36fddd98e702732fe483dbb15dcec604818dd74e0ba9', '[\"*\"]', '2026-08-10 02:39:21', NULL, '2026-08-10 02:32:32', '2026-08-10 02:39:21'),
(238, 'App\\Models\\Empleado', 1, 'panel-admin', 'e266a6542bd791a557ae198cad5af2e70556da42c5f70af4ad66f9cdadb1f094', '[\"*\"]', '2026-08-10 02:45:02', NULL, '2026-08-10 02:42:46', '2026-08-10 02:45:02'),
(239, 'App\\Models\\Empleado', 1, 'panel-admin', 'ba4ae3f2ef213569e4023929a6690c0bbeda6d867d1b6d3cabfeb82a4f3837a8', '[\"admin\"]', '2026-08-10 09:17:48', NULL, '2026-08-10 02:59:00', '2026-08-10 09:17:48'),
(241, 'App\\Models\\Cliente', 18, 'token_cliente', '4be79c9878bb9ddb04f7ae74fc2e27ef42abd5a397a9df223b354bc9606469e0', '[\"client\"]', '2026-08-10 03:43:49', NULL, '2026-08-10 03:43:48', '2026-08-10 03:43:49'),
(242, 'App\\Models\\Cliente', 12, 'token_cliente', '5d4ecb5a8b24b459d9bb17cce11b503c1dd95f44fc30bee16cb68480538c8a0b', '[\"client\"]', '2026-08-16 04:40:15', NULL, '2026-08-16 01:03:15', '2026-08-16 04:40:15'),
(243, 'App\\Models\\Empleado', 1, 'panel-admin', '2983b0998533b5c78904d1f27bb8e141efb9b325fe4bafc382f2aac18fbfccc4', '[\"admin\"]', '2026-08-16 09:03:18', NULL, '2026-08-16 05:31:49', '2026-08-16 09:03:18'),
(245, 'App\\Models\\Cliente', 12, 'token_cliente', 'a9c2cf9abcade1609bbbf9203ed53f0a087ad689d6b862d8e46eb971a6eb5e52', '[\"client\"]', '2026-08-16 08:56:33', NULL, '2026-08-16 08:56:32', '2026-08-16 08:56:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `etiquetas` varchar(500) DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `id_categoria` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `slug` varchar(140) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `etiquetas`, `precio_compra`, `precio_venta`, `stock`, `id_categoria`, `id_proveedor`, `imagen_url`, `estado`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Detalle Personalizado 1', 'Detalle personalizado con Flores Moradas, dulces vizzio y globo de Happy Birthday', 'flores,regalo,arreglo,cumpleaños,detalle,detalles,dulces,fiesta,globos,personalizado', 18.00, 30.00, 8, 1, 1, 'https://i.imgur.com/MsH5l46.jpeg', 'activo', 'detalles-personalizados-1', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(2, 'Detalle Personalizado 2', 'Detalle personalizado con peluche de Stich Rosa, Dulces Variados y flores artificiales rosas - rojas ', 'peluche,flores,juguete,regalo,arreglo,detalle,dulces,infantil,personalizado,stich,rosa', 19.00, 28.00, 11, 1, 1, 'https://i.imgur.com/zkgrxFy.jpeg', 'activo', 'detalles-personalizados-2', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(3, 'Detalle Personalizado 3', 'Detalle personalizado para pedido de enamoramiento con flores azules y liston de frase con detalles', 'flores,regalo,arreglo,azul,detalle,enamorados,pareja,personalizado,romance', 14.00, 26.00, 12, 1, 1, 'https://i.imgur.com/VVoqfq4.jpeg', 'activo', 'detalles-personalizados-3', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(4, 'Detalle Personalizado 4', 'Detalle personalizado con mensaje personalizado y nombre completo con flores amarillas y forrado especial', 'flores,regalo,arreglo,detalle,personalizado', 15.00, 26.00, 15, 1, 1, 'https://i.imgur.com/1O09Fw0.jpeg', 'activo', 'detalles-personalizados-4', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(5, 'Detalle Personalizado 5', 'Detalle personalizado con Gatito rosa, dulces variados con flores rosas - rojas', 'peluche,flores,juguete,regalo,arreglo,detalle,dulces,gatito,infantil,personalizado,rosa', 18.00, 29.00, 16, 1, 1, 'https://i.imgur.com/RupYOeX.jpeg', 'activo', 'detalles-personalizados-5', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(6, 'Detalle Personalizado 6', 'Detalle personalizado con Osito de camisa, dulces variados con una cerveza cuzqueña mini y forrado especial', 'peluche,juguete,regalo,adulto,cerveza,detalle,dulces,infantil,osito,personalizado', 22.00, 38.00, 19, 1, 1, 'https://i.imgur.com/YCxjYH8.jpeg', 'activo', 'detalles-personalizados-6', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(7, 'Detalle Personalizado 7', 'Detalle personalizado de HotWheels con flores azules - blancas y liston azul', 'flores,juguete,regalo,arreglo,auto,azul,detalle,hotwheels,personalizado', 22.00, 38.00, 14, 1, 1, 'https://i.imgur.com/cwuFeGu.jpeg', 'activo', 'detalles-personalizados-7', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(8, 'Detalle Personalizado 8', 'Detalle personalizado con cerveza corona, mensaje personalizado, flores azules con detalles personalizados', 'flores,regalo,adulto,arreglo,azul,cerveza,detalle,personalizado', 20.00, 32.00, 12, 1, 1, 'https://i.imgur.com/qqX4fdT.jpeg', 'activo', 'detalles-personalizados-8', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(9, 'Detalle Personalizado 9', 'Detalle personalizado con mensaje personalizado y flores rosas - rojas', 'flores,regalo,arreglo,detalle,personalizado,rosa', 12.00, 24.00, 14, 1, 1, 'https://i.imgur.com/BdWLEqS.png', 'activo', 'detalles-personalizados-9', '2025-10-04 04:07:25', '2026-08-10 03:58:40'),
(10, 'Detalle Personalizado 10', 'Detalle de Globos verdes con variedad de dulces de todo tipo y con adornos rojo y blanco', 'regalo,detalle,dulces,fiesta,globos,personalizado', 16.00, 28.00, 10, 1, 1, 'https://i.imgur.com/BdTunkn.jpeg', 'activo', 'detalles-personalizados-10', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(11, 'Detalle Personalizado 11', 'Detalle de Globo con cinta morada, con el interior de peluche Morado con detalles blancos', 'peluche,juguete,regalo,detalle,fiesta,globos,infantil,personalizado', 19.00, 32.00, 13, 1, 1, 'https://i.imgur.com/O2fXkxQ.jpeg', 'activo', 'detalles-personalizados-11', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(12, 'Detalle Personalizado 12', 'Detalle Personalizado con fotografias de parejas, dulces variados de todo tipo y peluche de pinguino', 'peluche,juguete,regalo,detalle,dulces,foto,infantil,pareja,personalizado,romance', 18.00, 32.00, 14, 1, 1, 'https://i.imgur.com/n2x4uoC.jpeg', 'activo', 'detalles-personalizados-12', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(13, 'Detalle Personalizado 13', 'Detalle con globos de distintas formas y con cajita de dulces, cerveza cuzqueña mini y peluche de cerdito - tiburon', 'peluche,caja,cajita,juguete,regalo,adulto,cerveza,detalle,dulces,fiesta,globos,personalizado', 23.00, 38.00, 17, 1, 1, 'https://i.imgur.com/1sMugxD.jpeg', 'activo', 'detalles-personalizados-13', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(14, 'Detalle Personalizado 14', 'Detalle Personalizado con tematica de Alianza Lima con productos variados en caja circular con cinta blanca', 'caja,cajita,regalo,alianza,deporte,detalle,futbol,personalizado', 22.00, 33.00, 18, 1, 1, 'https://i.imgur.com/mgDcdTo.jpeg', 'activo', 'detalles-personalizados-14', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(15, 'Detalle Personalizado 15', 'Detalle con cajita circular con cinta azul con productos variados y osito marron', 'peluche,caja,cajita,juguete,regalo,azul,detalle,infantil,osito,personalizado', 18.00, 27.00, 10, 1, 1, 'https://i.imgur.com/48jYAFG.jpeg', 'activo', 'detalles-personalizados-15', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(16, 'Detalle Personalizado 16', 'Detalle con tematica futbol colores azul y blanco, con productos variados y osito de ovejita', 'peluche,juguete,regalo,azul,detalle,infantil,personalizado', 20.00, 33.00, 12, 1, 1, 'https://i.imgur.com/usKlR8q.jpeg', 'activo', 'detalles-personalizados-16', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(17, 'Detalle Personalizado 17', 'Detalle con mensaje personalizado, bolsa roja con mica transparente y arreglo floral de margarita con fondo negro y peluche de oso', 'peluche,flores,bolso,juguete,regalo,accesorio,arreglo,detalle,infantil,moda,personalizado', 18.00, 32.00, 13, 1, 1, 'https://i.imgur.com/ni5iNy7.jpeg', 'activo', 'detalles-personalizados-17', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(18, 'Detalle Personalizado 18', 'Detalle Personalizado para el dia del hombre con tematica de Hootwheels en celeste y azul', 'regalo,azul,detalle,personalizado', 16.00, 24.00, 16, 1, 1, 'https://i.imgur.com/cRyHnel.jpeg', 'activo', 'detalles-personalizados-18', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(19, 'Detalle Personalizado 19', 'Detalle Personalizado para el dia del hombre en cajita blanco y negro, con gaseosa personalizada, hotwheels, dulces y osito pequeño', 'peluche,caja,cajita,juguete,regalo,auto,detalle,dulces,hotwheels,infantil,personalizado', 19.00, 26.00, 11, 1, 1, 'https://i.imgur.com/cWNMU0X.jpeg', 'activo', 'detalles-personalizados-19', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(20, 'Detalle Personalizado 20', 'Detalle Personalizado para el dia del hombre con tematica de Hotwheels con 3 unidades en cinta azul formal', 'juguete,regalo,auto,azul,detalle,hotwheels,personalizado', 20.00, 30.00, 12, 1, 1, 'https://i.imgur.com/6tvtlha.jpeg', 'activo', 'detalles-personalizados-20', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(21, 'Detalle Personalizado 21', 'Detalle Personalizado para el dia del hombre con tematica de Hotwheels de coleccion, mensaje personalizado con detalle de periodico', 'juguete,regalo,auto,detalle,hotwheels,personalizado', 22.00, 32.00, 15, 1, 1, 'https://i.imgur.com/yZUD2wB.jpeg', 'activo', 'detalles-personalizados-21', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(22, 'Detalle Personalizado 22', 'Detalle con globos de distintas formas y colores, con cajita de dulces, cerveza cuzqueña mini y peluche de cerdito - tiburon', 'peluche,caja,cajita,juguete,regalo,adulto,cerdita,cerveza,detalle,dulces,fiesta,personalizado', 23.00, 36.00, 17, 1, 1, 'https://i.imgur.com/tgLeXyV.jpeg', 'activo', 'detalles-personalizados-22', '2025-10-16 04:17:26', '2026-08-10 03:58:40'),
(23, 'Detalle Personalizado 23', 'Detalle Personalizado con globo especial, dulces variados y 3 cervezas coronas en cajita con detalles verde claro', 'caja,cajita,regalo,adulto,cerveza,detalle,dulces,fiesta,globos,personalizado', 21.00, 30.00, 19, 1, 1, 'https://i.imgur.com/rCEIbka.jpeg', 'activo', 'detalles-personalizados-23', '2025-10-16 04:17:26', '2026-08-10 03:58:41'),
(24, 'Detalle Personalizado 24', 'Detalle Personalizado para el dia del padre con tematica de Alianza Lima con dulces variados y cerveza corona', 'regalo,adulto,alianza,cerveza,deporte,detalle,dulces,futbol,personalizado', 18.00, 26.00, 11, 1, 1, 'https://i.imgur.com/F78neeT.jpeg', 'activo', 'detalles-personalizados-24', '2025-10-16 04:17:26', '2026-08-10 03:58:41'),
(25, 'Detalle Personalizado 25', 'Detalles variados con cajitas personalizadas con dulces al interior, eleccion a su gusto', 'caja,cajita,regalo,detalle,dulces,personalizado', 14.00, 20.00, 14, 1, 1, 'https://i.imgur.com/kuFrO2J.jpeg', 'activo', 'detalles-personalizados-25', '2025-10-16 04:17:26', '2026-08-10 03:58:41'),
(26, 'Flores Personalizadas 1', 'Flores Personalizadas amarillas con centro rojo de princesa con detalles dorados y mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 12.00, 18.00, 9, 2, 2, 'https://i.imgur.com/Y0pxvC7.jpeg', 'activo', 'flores-personalizadas-1', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(27, 'Flores Personalizadas 2', 'Flores Personalizadas con tematica Winnie Poh con flores rojas y amarillas con detalles rojos y mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 15.00, 19.00, 10, 2, 2, 'https://i.imgur.com/mXLtfhU.jpeg', 'activo', 'flores-personalizadas-2', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(28, 'Flores Personalizadas 3', 'Flores Personalizadas color amarillo intenso con flores amarillas y detalles plomos con mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 12.00, 17.00, 13, 2, 2, 'https://i.imgur.com/28jCsWO.jpeg', 'activo', 'flores-personalizadas-3', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(29, 'Flores Personalizadas 4', 'Flores Personalizadas con tematica de margaritas con detalles rojos', 'flores,regalo,arreglo,detalle,florales,personalizado', 10.00, 16.00, 14, 2, 2, 'https://i.imgur.com/akC11ah.jpeg', 'activo', 'flores-personalizadas-4', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(30, 'Flores Personalizadas 5', 'Flores Personalizadas con tematica de margaritas y detalles blanco y dorado con mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 8.00, 14.00, 17, 2, 2, 'https://i.imgur.com/JZ9xylm.jpeg', 'activo', 'flores-personalizadas-5', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(31, 'Flores Personalizadas 6', 'Flores Personalizadas amarillas con centro rojo de princesa con detalles dorados y mensaje personalizado con fotos personalizadas impresas', 'flores,regalo,arreglo,detalle,florales,foto,personalizado', 14.00, 20.00, 19, 2, 2, 'https://i.imgur.com/QI02QTQ.jpeg', 'activo', 'flores-personalizadas-6', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(32, 'Flores Personalizadas 7', 'Flor Personalizada de color rojo dentro de globo con detalles en balde rojo', 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado', 10.00, 16.00, 10, 2, 2, 'https://i.imgur.com/xOqGK0E.jpeg', 'activo', 'flores-personalizadas-7', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(33, 'Flores Personalizadas 8', 'Flor Personalizada de color blanco y violeta dentro de globo con detalles violetas en balde blanco', 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado', 12.00, 18.00, 12, 2, 2, 'https://i.imgur.com/mKRLQPJ.jpeg', 'activo', 'flores-personalizadas-8', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(34, 'Flores Personalizadas 9', 'Flores Personalizadas con globos variados en cajita circular con cinta rosada con regalo collar y peluche Koala y dulces', 'peluche,flores,caja,cajita,juguete,regalo,arreglo,detalle,dulces,fiesta,florales,personalizado', 22.00, 31.00, 14, 2, 2, 'https://i.imgur.com/830UXBN.jpeg', 'activo', 'flores-personalizadas-9', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(35, 'Flores Personalizadas 10', 'Flores Personalizadas de colores rojo dentro de globo con detalles en balde rojo', 'flores,regalo,arreglo,detalle,fiesta,florales,globos,personalizado', 16.00, 22.00, 15, 2, 2, 'https://i.imgur.com/oYqOTfs.jpeg', 'activo', 'flores-personalizadas-10', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(36, 'Flores Personalizadas 11', 'Flores Personalizadas Rosas en cajita con cinta y globos dorados con peluche de oso pequeño, dulces variados y champagne pequeño', 'peluche,flores,caja,cajita,juguete,regalo,arreglo,detalle,dulces,fiesta,florales,personalizado', 22.00, 34.00, 17, 2, 2, 'https://i.imgur.com/lRzO0Sm.jpeg', 'activo', 'flores-personalizadas-11', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(37, 'Flores Personalizadas 12', 'Flor Personalizada tematica margarita con detalle en blanco', 'flores,regalo,arreglo,detalle,florales,personalizado', 10.00, 16.00, 9, 2, 2, 'https://i.imgur.com/mEd6PRL.jpeg', 'activo', 'flores-personalizadas-12', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(38, 'Flores Personalizadas 13', 'Flor Personalizada tematica margarita con detalle en verde y blanco con letras', 'flores,regalo,arreglo,detalle,florales,personalizado', 12.00, 17.00, 11, 2, 2, 'https://i.imgur.com/FubA0BC.jpeg', 'activo', 'flores-personalizadas-13', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(39, 'Flores Personalizadas 14', 'Flor Personalizada tematica margarita con detalle en rojo y fondo de periodico', 'flores,regalo,arreglo,detalle,florales,personalizado', 14.00, 19.00, 12, 2, 2, 'https://i.imgur.com/UhAfqle.jpeg', 'activo', 'flores-personalizadas-14', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(40, 'Flores Personalizadas 15', 'Flores Personalizadas de color amarillo y rojo con detalles dorados y rojos', 'flores,regalo,arreglo,detalle,florales,personalizado', 8.00, 14.00, 14, 2, 2, 'https://i.imgur.com/ClNO5T8.jpeg', 'activo', 'flores-personalizadas-15', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(41, 'Flores Personalizadas 16', 'Flores Personalizadas de color amarillo intenso con detalles amarillos pasteles y mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 11.00, 16.00, 17, 2, 2, 'https://i.imgur.com/AaUz7aP.jpeg', 'activo', 'flores-personalizadas-16', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(42, 'Flores Personalizadas 17', 'Flor Personalizada tematica margarita con detalle en rojo pastel y fondo de letras', 'flores,regalo,arreglo,detalle,florales,personalizado', 12.00, 17.00, 20, 2, 2, 'https://i.imgur.com/ZZbb2gB.jpeg', 'activo', 'flores-personalizadas-17', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(43, 'Flores Personalizadas 18', 'Flor Personalizada tematica margarita con detalle en blanco violetizado', 'flores,regalo,arreglo,detalle,florales,personalizado', 9.00, 13.00, 13, 2, 2, 'https://i.imgur.com/LEMYaOo.jpeg', 'activo', 'flores-personalizadas-18', '2025-10-16 04:50:02', '2026-08-10 03:58:41'),
(44, 'Carteles Personalizados 1', 'Carteles Personalizados de cumpleaños con mensaje personalizado y detalles con letras de colores', 'cartel,regalo,carteles,cumpleaños,fiesta,mensaje,personalizado', 12.00, 18.00, 9, 3, 4, 'https://i.imgur.com/XrcPqD2.jpeg', 'activo', 'carteles-personalizados-1', '2025-10-16 05:20:14', '2026-08-10 03:58:41'),
(45, 'Carteles Personalizados 2', 'Cajita con tematica de Naruto con cartelito personalizado y fotos, dulces variados y camiseta al gusto del cliente', 'cartel,caja,cajita,regalo,carteles,detalle,dulces,foto,mensaje,personalizado', 36.00, 54.00, 11, 3, 4, 'https://i.imgur.com/XIeJh0J.jpeg', 'activo', 'carteles-personalizados-2', '2025-10-16 05:20:14', '2026-08-10 03:58:41'),
(46, 'Carteles Personalizados 3', 'Cajita con cartelito personalizado y taza personalizada con margaritas doradas', 'cartel,caja,cajita,regalo,carteles,detalle,mensaje,personalizado', 24.00, 30.00, 13, 3, 4, 'https://i.imgur.com/3ojXh4e.jpeg', 'activo', 'carteles-personalizados-3', '2025-10-16 05:20:14', '2026-08-10 03:58:41'),
(47, 'Carteles Personalizados 4', 'Cajita con cartelito personalizado con flores amarillas y dulces amarillos con joya y su cajita', 'flores,cartel,caja,cajita,regalo,arreglo,carteles,detalle,dulces,mensaje,personalizado', 32.00, 48.00, 15, 3, 4, 'https://i.imgur.com/YpDOnUH.jpeg', 'activo', 'carteles-personalizados-4', '2025-10-16 05:20:14', '2026-08-10 03:58:41'),
(48, 'Carteles Personalizados 5', 'Cajita personalizada dorada con cartelito personalizado, flores variadas y dulces amarillos', 'flores,cartel,caja,cajita,regalo,arreglo,carteles,detalle,dulces,mensaje,personalizado', 22.00, 28.00, 17, 3, 4, 'https://i.imgur.com/J0pbElm.jpeg', 'activo', 'carteles-personalizados-5', '2025-10-16 05:20:14', '2026-08-10 03:58:41'),
(49, 'Carteles Personalizados 6', 'Cajita personalizada con cartelito personalizado, dulces variados con detalle de Hotwheels', 'cartel,caja,cajita,juguete,regalo,auto,carteles,detalle,dulces,hotwheels,mensaje,personalizado', 22.00, 29.00, 19, 3, 4, 'https://i.imgur.com/LwE4oht.jpeg', 'activo', 'carteles-personalizados-6', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(50, 'Carteles Personalizados 7', 'Cajita personalizada con cartelito personalizado, dulces variados con mensaje personalizado y su Hotwheels', 'cartel,caja,cajita,juguete,regalo,auto,carteles,detalle,dulces,hotwheels,mensaje,personalizado', 22.00, 30.00, 10, 3, 4, 'https://i.imgur.com/zHvvF9f.jpeg', 'activo', 'carteles-personalizados-7', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(51, 'Carteles Personalizados 8', 'Cajita personalizada con cartelito personalizado, con globos y flores variadas, dulces dorados y osito pequeño', 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado', 25.00, 34.00, 12, 3, 4, 'https://i.imgur.com/SabxbJP.jpeg', 'activo', 'carteles-personalizados-8', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(52, 'Carteles Personalizados 9', 'Cajita personalizada con cartelito personalizado, con globos y flores variadas, dulces variados y osito grande con polera', 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado', 30.00, 39.00, 14, 3, 4, 'https://i.imgur.com/ZT5LBbx.jpeg', 'activo', 'carteles-personalizados-9', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(53, 'Carteles Personalizados 10', 'Cajita circular personalizada con cartelito personalizado, cerveza cuzqueña mini, dulces variados y osito con gorra', 'peluche,cartel,caja,cajita,juguete,regalo,adulto,carteles,cerveza,detalle,dulces,personalizado', 26.00, 32.00, 16, 3, 4, 'https://i.imgur.com/y9lPGOc.jpeg', 'activo', 'carteles-personalizados-10', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(54, 'Carteles Personalizados 11', 'Cajita personalizada con cartelito personalizado, con osito pequeño y cerveza china con dulces variados', 'peluche,cartel,caja,cajita,juguete,regalo,adulto,carteles,cerveza,detalle,dulces,personalizado', 28.00, 34.00, 18, 3, 4, 'https://i.imgur.com/gCPkcGl.jpeg', 'activo', 'carteles-personalizados-11', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(55, 'Carteles Personalizados 12', 'Cajita personalizada con cartelito personalizado, osito rosado de Koala, dulces variados con detalles rosa y rojo', 'peluche,cartel,caja,cajita,juguete,regalo,carteles,detalle,dulces,infantil,mensaje,personalizado', 24.00, 30.00, 9, 3, 4, 'https://i.imgur.com/CLL880S.jpeg', 'activo', 'carteles-personalizados-12', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(56, 'Carteles Personalizados 13', 'Cajita personalizada con cartelito personalizado, con flores doradas y rojas, dulces sublime con osito pequeño y ropita con detalles rojos y dorados', 'peluche,flores,cartel,caja,cajita,juguete,regalo,arreglo,carteles,detalle,dulces,personalizado', 25.00, 34.00, 11, 3, 4, 'https://i.imgur.com/0HoSBDT.jpeg', 'activo', 'carteles-personalizados-13', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(57, 'Carteles Personalizados 14', 'Copita personalizada con cartelito personalizado, con globo de letras rojo, dulces variados y detalles rosas', 'cartel,regalo,carteles,dulces,fiesta,globos,mensaje,personalizado,rosa', 18.00, 24.00, 14, 3, 4, 'https://i.imgur.com/XSHgm5e.jpeg', 'activo', 'carteles-personalizados-14', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(58, 'Carteles Personalizados 15', 'Cajita personalizada con cartelito personalizado, globo especial verde con dulces y bebidas variados en detalles verde', 'cartel,caja,cajita,regalo,carteles,detalle,dulces,fiesta,globos,mensaje,personalizado', 20.00, 30.00, 16, 3, 4, 'https://i.imgur.com/PtQtC13.jpeg', 'activo', 'carteles-personalizados-15', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(59, 'Carteles Personalizados 16', 'Cajita circular personalizada con cartelito personalizado, globo transparente con dulce de forma de corazón y globos, detalles rojos', 'cartel,caja,cajita,regalo,carteles,detalle,dulces,fiesta,globos,mensaje,personalizado', 18.00, 24.00, 18, 3, 4, 'https://i.imgur.com/EsdqoF3.jpeg', 'activo', 'carteles-personalizados-16', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(60, 'Carteles Personalizados 17', 'Cajita personalizada con cartelito personalizado, con globo azul transparente y dulces variados con cuzqueña mini y detalles azules', 'cartel,caja,cajita,regalo,adulto,azul,carteles,cerveza,detalle,dulces,fiesta,globos', 28.00, 36.00, 20, 3, 4, 'https://i.imgur.com/7E6g8w1.jpeg', 'activo', 'carteles-personalizados-17', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(61, 'Carteles Personalizados 18', 'Copita personalizada con cartelito personalizado, con globo especial rojo con dulces variados', 'cartel,regalo,carteles,dulces,fiesta,globos,mensaje,personalizado', 18.00, 24.00, 13, 3, 4, 'https://i.imgur.com/T6I2ltu.jpeg', 'activo', 'carteles-personalizados-18', '2025-10-16 05:20:14', '2026-08-10 03:58:42'),
(62, 'Bolso Verde', 'Bolso verde pastel para mujer', 'bolso,accesorio,moda,verde,regalo', 16.00, 22.00, 20, 4, 3, 'https://i.imgur.com/i9TF3JX.jpeg', 'activo', 'perfumeria-1', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(63, 'Bolso Verde Militar', 'Bolso verde militar para hombre', 'bolso,accesorio,moda,militar,verde,regalo', 15.00, 20.00, 11, 4, 3, 'https://i.imgur.com/35Z5QbX.jpeg', 'activo', 'perfumeria-2', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(64, 'Bolso Cuero', 'Bolso de Cuero dos colores con detalle de osito', 'bolso,accesorio,cuero,moda,regalo', 22.00, 28.00, 13, 4, 3, 'https://i.imgur.com/15dJ6ug.jpeg', 'activo', 'perfumeria-3', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(65, 'Bolsa Cuero Crema', 'Bolso de cuero de color crema con detalle de oso grande', 'bolso,accesorio,bolsa,crema,cuero,moda,regalo', 23.00, 29.00, 14, 4, 3, 'https://i.imgur.com/ZUInleX.jpeg', 'activo', 'perfumeria-4', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(66, 'Bolso Piton Bear', 'Bolso de Cuero dos colores con detalle de Piton Bear', 'bolso,accesorio,bear,moda,piton,regalo', 24.00, 35.00, 16, 4, 3, 'https://i.imgur.com/qATC0jc.jpeg', 'activo', 'perfumeria-5', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(67, 'Bolso Piton Duo', 'Bolso de Cuero con detalle de Piton Bear Duo de Ositos', 'bolso,accesorio,moda,piton,regalo', 25.00, 36.00, 19, 4, 3, 'https://i.imgur.com/MViBb95.jpeg', 'activo', 'perfumeria-6', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(68, 'Mochila Piton', 'Mochila de Piton con detalle de osito con lentes', 'bolso,accesorio,mochila,moda,piton,regalo', 30.00, 42.00, 11, 4, 3, 'https://i.imgur.com/c7xdjE6.jpeg', 'activo', 'perfumeria-7', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(69, 'Bolso Mano Piton', 'Bolso de mano modelo Piton con detalle pequeño de oso', 'bolso,accesorio,mano,moda,piton,regalo', 21.00, 34.00, 13, 4, 3, 'https://i.imgur.com/WxjnMwF.jpeg', 'activo', 'perfumeria-8', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(70, 'Cajita Personalizada', 'Cajita personalizada con detalles dorados, osito pequeño y dulces variados', 'caja,cajita,regalo,detalle,dulces,especial,chocolate,personalizado', 18.00, 26.00, 15, 4, 3, 'https://i.imgur.com/moHI1nS.jpeg', 'activo', 'perfumeria-9', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(71, 'Perfume Dance', 'Perfume Dance de Shakira', 'perfume,fragancia,perfumeria,dance,regalo', 23.00, 38.00, 17, 4, 3, 'https://i.imgur.com/T4GDI86.jpeg', 'activo', 'perfumeria-10', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(72, 'Cajita Personalizada Circular', 'Cajita Personalizada Circular, con osito mediano, dulces variados y detalles rojo, dorado', 'caja,cajita,regalo,circular,detalle,dulces,osito,personalizado', 20.00, 28.00, 19, 4, 3, 'https://i.imgur.com/nXXlfZW.jpeg', 'activo', 'perfumeria-11', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(73, 'Detalle Cerdito', 'Detalle blanco con dorado con flores rosas, peluche de cerdito rosa con capucha de tiburon', 'peluche,flores,juguete,regalo,arreglo,cerdita,cerdito,detalle,infantil,rosa,tiburon', 21.00, 28.00, 10, 4, 3, 'https://i.imgur.com/INitiUa.jpeg', 'activo', 'perfumeria-12', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(74, 'Cajita Peluche', 'Cajita circular con globo transparente con el interior de oso rosado pastel', 'peluche,caja,cajita,juguete,regalo,detalle,fiesta,globos,infantil,rosa', 16.00, 22.00, 10, 4, 3, 'https://i.imgur.com/4TbqWDC.jpeg', 'activo', 'perfumeria-13', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(75, 'Caja Sorpresa Negra', 'Caja Sorpresa Negra con interior secreto de Hot wheels', 'caja,cajita,juguete,regalo,auto,detalle,hotwheels,negra,sorpresa', 25.00, 38.00, 14, 4, 3, 'https://i.imgur.com/Tk9ey3J.jpeg', 'activo', 'perfumeria-14', '2025-10-16 05:40:17', '2026-08-10 03:58:42'),
(76, 'Cajita Circular Rosa', 'Cajita Circular Rosa con peluche de Koala', 'peluche,caja,cajita,juguete,regalo,circular,detalle,infantil,rosa,variado,koala', 18.00, 25.00, 20, 5, 5, 'https://i.imgur.com/hmCk7Im.jpeg', 'activo', 'variados-1', '2025-10-16 06:46:05', '2026-08-16 00:37:42'),
(77, 'Peluche Stich', 'Peluche Stich Normal tamaño normal', 'peluche,juguete,regalo,infantil,stich,variados,orejas largas,orejon', 10.00, 16.00, 20, 5, 5, 'https://i.imgur.com/I0mb7tJ.jpeg', 'activo', 'variados-2', '2025-10-16 06:46:05', '2026-08-16 00:37:42'),
(78, 'Peluche Stich Rosa', 'Peluche Stich Rosa tamaño normal', 'peluche,juguete,regalo,infantil,rosa,stich,variados,orejas largas,orejon', 10.00, 16.00, 20, 5, 5, 'https://i.imgur.com/EMvrnWs.jpeg', 'activo', 'variados-3', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(79, 'Cajita Pinguino', 'Cajita con globos personalizados, con dulces variados y peluche pinguino', 'peluche,caja,cajita,juguete,regalo,detalle,dulces,fiesta,globos,infantil,pinguino,variados', 14.00, 20.00, 20, 5, 5, 'https://i.imgur.com/bJBc3La.jpeg', 'activo', 'variados-4', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(80, 'Relojes clasicos', 'Relojes clasicos en dorado y negro', 'reloj,regalo,accesorio,clasicos,variados', 10.00, 18.00, 17, 5, 5, 'https://i.imgur.com/5R1bVHX.jpeg', 'activo', 'variados-5', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(81, 'Pulseras Metalizadas', 'Pulseras Metalizadas con funda negra', 'pulsera,regalo,accesorio,metalizadas,variados', 7.00, 14.00, 16, 5, 5, 'https://i.imgur.com/w5OCoSb.jpeg', 'activo', 'variados-6', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(82, 'Pulseras Negras', 'Pulseras negras con metal negro metalizado', 'pulsera,regalo,accesorio,negras,variados', 6.00, 12.00, 20, 5, 5, 'https://i.imgur.com/YB7lSp7.jpeg', 'activo', 'variados-7', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(83, 'Billetera Chicago', 'Billetera Chicago con funda negra y roja', 'billetera,regalo,accesorio,caballero,chicago,marca,variados', 11.00, 20.00, 12, 5, 5, 'https://i.imgur.com/4FuocIX.jpeg', 'activo', 'variados-8', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(84, 'Billetera Renzo Costa', 'Billetera Renzo Costa funda negra', 'billetera,regalo,accesorio,caballero,marca,renzo,costa,variados', 44.00, 70.00, 13, 5, 5, 'https://i.imgur.com/cQwVUIT.jpeg', 'activo', 'variados-9', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(85, 'Billetera Chicago Dorado', 'Billetera Chicago con funda negra y dorada', 'billetera,regalo,accesorio,caballero,chicago,dorado,marca,variados', 15.00, 22.00, 14, 5, 5, 'https://i.imgur.com/IKA88Xf.jpeg', 'activo', 'variados-10', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(86, 'Billetera Chicago Clasica', 'Billetera Chicago con funda clasica', 'billetera,regalo,accesorio,caballero,chicago,clasica,marca,variados', 16.00, 24.00, 18, 5, 5, 'https://i.imgur.com/OMeOB96.jpeg', 'activo', 'variados-11', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(87, 'Billetera Puma', 'Billetera Puma color marron cuero', 'billetera,regalo,accesorio,caballero,marca,puma,variados', 18.00, 28.00, 20, 5, 5, 'https://i.imgur.com/awUkCBo.jpeg', 'activo', 'variados-12', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(88, 'Cerdita Tiburon', 'Detalle con peluche de cerdita tiburon color rosado con flores rosadas', 'peluche,flores,juguete,regalo,arreglo,cerdita,detalle,infantil,rosa,tiburon,variados', 18.00, 25.00, 11, 5, 5, 'https://i.imgur.com/fxppNkL.jpeg', 'activo', 'variados-13', '2025-10-16 06:46:05', '2026-08-10 03:58:42'),
(89, 'Cajita Dulcera', 'Cajita circular con globos personalizados y dulces color rojo y dorado', 'caja,cajita,regalo,detalle,dulcera,dulces,fiesta,globos,personalizado,novia,esposa,variados', 19.00, 26.00, 10, 5, 5, 'https://i.imgur.com/vBuRH3k.jpeg', 'activo', 'variados-14', '2025-10-16 06:46:05', '2026-08-10 03:58:43'),
(90, 'Caja Hot Wheels', 'Caja hot wheels con dulces y bebidas variadas con carrito Hot wheels', 'caja,cajita,juguete,regalo,auto,detalle,dulces,hotwheels,variados', 20.00, 36.00, 20, 5, 5, 'https://i.imgur.com/d0fY7ej.jpeg', 'activo', 'variados-15', '2025-10-16 06:46:05', '2026-08-10 03:58:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagenes`
--

CREATE TABLE `producto_imagenes` (
  `id_imagen` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL,
  `orden` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `contacto`, `telefono`, `email`, `direccion`, `created_at`) VALUES
(1, 'Detalles Lima S.A.C.', 'María Gonzales', '987654321', 'detalleslima.sac@gmail.com', 'Av. Arequipa 1200, Lima', '2025-10-04 04:07:25'),
(2, 'Floreria Orquidea', 'Carlos Paredes', '945871230', 'ventas_floreria.orquidea@gmail.com', 'Jr. Huallaga 221, Lima', '2025-10-04 04:07:25'),
(3, 'Perfumeria Fragance', 'Rosa Núñez', '910567843', 'floreria.fragance_hyo@gmail.com', 'Av. Los Incas 504, Huancayo', '2025-10-04 04:07:25'),
(4, 'Peluches P\'Luche', 'Ivan Rojas', '965436789', 'peluches_pluche@gmail.com', 'Jr. Huaytapallana 1267, Huanuco', '2025-10-16 03:24:04'),
(5, 'Tienda D\'Todo', 'Paolo Lopez', '945673123', 'tiendas.dtodo@gmail.com', 'Av. Daniel Alcides 348, Cerro de Pasco', '2025-10-16 03:24:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'ADMIN'),
(2, 'SOPORTE'),
(3, 'STOCK'),
(4, 'VENTAS');

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

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ecXm38mEbiMm6tyODClmKnZ6K4VduwogUH0VRYMo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUTFsTUVMWWZGYm00cFlzbEF5RFpRMG8xUFZzdm9jeFFUYzB2Q21qcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1760071251),
('gCV2HeyjttUUxmossp1vn8IsKGOXvbYdoCKsaaW1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3pJSDBPR0NXOFZMY0VYanNzQjFZNGdvYld3UVJzTEJCb0NicTJmVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759555256),
('Gt4iKLgFc33uk1fxZWnnARzEkCSlWnV06fAamPN5', NULL, '127.0.0.1', 'PostmanRuntime/7.37.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWEpLRzAxNThJR1NQTVFWQlNPNmJCSzcyblhGQVRFVlh3dWZpaUFWQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1760075786);

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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `id_empleado` (`id_empleado`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `id_cliente` (`id_cliente`);

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
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_clientes_email` (`email`);

--
-- Indices de la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `idx_detalles_pedidos` (`id_pedido`,`id_producto`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_empleados_email` (`email`);

--
-- Indices de la tabla `empleado_rol`
--
ALTER TABLE `empleado_rol`
  ADD PRIMARY KEY (`id_empleado`,`id_rol`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `idx_inv_producto_fecha` (`id_producto`,`fecha`);

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
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedidos_cliente_fecha` (`id_cliente`,`fecha_pedido`);

--
-- Indices de la tabla `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `idx_productos_nombre` (`nombre`),
  ADD KEY `idx_productos_cat_nombre` (`id_categoria`,`nombre`);

--
-- Indices de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditoria_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD CONSTRAINT `detalles_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `empleado_rol`
--
ALTER TABLE `empleado_rol`
  ADD CONSTRAINT `empleado_rol_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE,
  ADD CONSTRAINT `empleado_rol_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  ADD CONSTRAINT `inventario_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  ADD CONSTRAINT `pedido_estado_historial_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_estado_historial_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Filtros para la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `producto_imagenes_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
