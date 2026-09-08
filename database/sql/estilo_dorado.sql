-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-estilodorado.alwaysdata.net
-- Generation Time: Sep 08, 2026 at 04:04 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `estilodorado_estilo_dorado`
--

-- --------------------------------------------------------

--
-- Table structure for table `administradores`
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
-- Table structure for table `asistente_feedback`
--

CREATE TABLE `asistente_feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `voto` varchar(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistente_feedback`
--

INSERT INTO `asistente_feedback` (`id`, `id_producto`, `voto`, `created_at`) VALUES
(1, 1, 'up', '2026-09-06 19:36:38'),
(2, 4, 'up', '2026-09-06 19:36:39'),
(3, 8, 'up', '2026-09-06 19:36:40'),
(4, 90, 'up', '2026-09-07 17:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `asistente_logs`
--

CREATE TABLE `asistente_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `mensaje` varchar(500) NOT NULL,
  `tipo` varchar(32) NOT NULL DEFAULT 'consulta',
  `n_productos` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `productos` varchar(500) DEFAULT NULL,
  `productos_json` text DEFAULT NULL,
  `queja_tipo` varchar(40) DEFAULT NULL,
  `urgencia` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `driver` varchar(16) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cliente_nombre` varchar(120) DEFAULT NULL,
  `cliente_email` varchar(150) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistente_logs`
--

INSERT INTO `asistente_logs` (`id`, `id_cliente`, `mensaje`, `tipo`, `n_productos`, `productos`, `productos_json`, `queja_tipo`, `urgencia`, `whatsapp`, `driver`, `created_at`, `cliente_nombre`, `cliente_email`, `celular`) VALUES
(1, NULL, 'tienes peluches?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 07:43:23', NULL, NULL, NULL),
(2, NULL, 'tengo una queja', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 07:51:21', NULL, NULL, NULL),
(3, NULL, 'tengo una queja', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 07:52:19', NULL, NULL, NULL),
(4, NULL, 'tienes peluches de ositos?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 07:52:43', NULL, NULL, NULL),
(5, NULL, 'tienes regalos para cumpleaños?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 08:06:06', NULL, NULL, NULL),
(6, NULL, 'tengo una queja de un producto', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 08:06:12', NULL, NULL, NULL),
(7, NULL, 'hay peluches de oso?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 08:06:25', NULL, NULL, NULL),
(8, NULL, 'quiero un regalo para cumpleaños  para mujer', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 08:47:07', NULL, NULL, NULL),
(9, NULL, 'mi producto llego aplastado', 'catalogo', 3, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 08:47:22', NULL, NULL, NULL),
(10, NULL, 'mi producto llego aplastado', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 08:48:22', NULL, NULL, NULL),
(11, NULL, 'mi producto llego empapado', 'catalogo', 3, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 08:48:33', NULL, NULL, NULL),
(12, NULL, 'mi producto me llego oliendo raro y mal', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 08:49:04', NULL, NULL, NULL),
(13, NULL, 'tienes peluches de stich?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:05:40', NULL, NULL, NULL),
(14, NULL, 'estan bonitos, gracias', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:06:04', NULL, NULL, NULL),
(15, NULL, 'tienes chocolates?', 'catalogo', 1, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:07:46', NULL, NULL, NULL),
(16, NULL, 'estan preciosos gracias', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:08:12', NULL, NULL, NULL),
(17, NULL, 'tienes peluches de oso?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:08:31', NULL, NULL, NULL),
(18, NULL, 'estan bonitos gracias', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:08:40', NULL, NULL, NULL),
(19, NULL, 'estan preciosos', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 09:08:53', NULL, NULL, NULL),
(20, NULL, 'mi producto llego mal', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 09:26:31', NULL, NULL, NULL),
(21, NULL, 'mi producto llegó aplastado', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 09:59:43', NULL, NULL, NULL),
(22, NULL, 'tienes peluches de oso?', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 10:01:13', NULL, NULL, NULL),
(23, NULL, 'quiero un regalo de cumpleaños para una mujer', 'catalogo', 6, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-29 10:13:52', NULL, NULL, NULL),
(24, NULL, 'mi producto llego aplastado', 'whatsapp', 0, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 10:14:08', NULL, NULL, NULL),
(25, NULL, 'quiero un peluche de oso', 'whatsapp', 1, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 10:17:11', NULL, NULL, NULL),
(26, NULL, 'quiero un regalo para una persona de 10 años', 'whatsapp', 1, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 10:17:41', NULL, NULL, NULL),
(27, NULL, 'quiero un regalo para una persona de 60 años', 'whatsapp', 1, NULL, NULL, NULL, 0, 1, 'rules', '2026-08-29 10:18:02', NULL, NULL, NULL),
(28, NULL, 'hola quiero un regalo para un niño de 10 años', 'catalogo', 6, 'Detalle Personalizado 19, Carteles Personalizados 11, Carteles Personalizados 10, Carteles Personalizados 9, Carteles Personalizados 8, Carteles Personalizados 13', NULL, NULL, 0, 0, 'rules', '2026-08-31 05:16:29', NULL, NULL, NULL),
(29, NULL, 'hola quiero un regalo para una mujer 20 años', 'catalogo', 6, 'Flores Personalizadas 11, Flores Personalizadas 9, Detalle Personalizado 5, Detalle Personalizado 17, Detalle Personalizado 3, Detalle Personalizado 2', NULL, NULL, 0, 0, 'rules', '2026-08-31 05:16:59', NULL, NULL, NULL),
(30, NULL, 'me gusta', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-08-31 05:17:15', NULL, NULL, NULL),
(31, NULL, 'hola Dori estoy buscando algo para mi esposa', 'catalogo', 6, 'Flores Personalizadas 17, Flores Personalizadas 6, Flores Personalizadas 5, Flores Personalizadas 11, Flores Personalizadas 16, Flores Personalizadas 10', '[{\"id\":42,\"nombre\":\"Flores Personalizadas 17\",\"precio\":17,\"stock\":20,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ZZbb2gB.jpeg\"},{\"id\":31,\"nombre\":\"Flores Personalizadas 6\",\"precio\":20,\"stock\":19,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/QI02QTQ.jpeg\"},{\"id\":30,\"nombre\":\"Flores Personalizadas 5\",\"precio\":14,\"stock\":17,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/JZ9xylm.jpeg\"},{\"id\":36,\"nombre\":\"Flores Personalizadas 11\",\"precio\":34,\"stock\":17,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/lRzO0Sm.jpeg\"},{\"id\":41,\"nombre\":\"Flores Personalizadas 16\",\"precio\":16,\"stock\":17,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/AaUz7aP.jpeg\"},{\"id\":35,\"nombre\":\"Flores Personalizadas 10\",\"precio\":22,\"stock\":15,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/oYqOTfs.jpeg\"}]', NULL, 0, 0, 'rules', '2026-08-31 09:56:14', NULL, NULL, NULL),
(32, NULL, 'mi pedido aun no llega', 'queja_espera', 0, NULL, NULL, 'otro', 1, 0, 'rules', '2026-08-31 09:56:39', NULL, NULL, NULL),
(33, NULL, '111', 'queja_espera', 0, NULL, NULL, 'otro', 1, 0, 'rules', '2026-08-31 09:56:52', NULL, NULL, NULL),
(34, NULL, '987456123', 'whatsapp', 0, NULL, NULL, 'otro', 1, 1, 'rules', '2026-08-31 09:57:08', NULL, NULL, NULL),
(35, NULL, 'No llegó / extravío · Pedido #112 · mi pedido no llega', 'whatsapp', 0, NULL, NULL, 'no_llego', 1, 1, 'rules', '2026-09-01 07:28:31', NULL, NULL, NULL),
(36, NULL, 'No llegó / extravío · Pedido #112 · mi producto no llega · Detalle Cerdito × 1', 'whatsapp', 1, 'Detalle Cerdito', '[{\"id\":73,\"nombre\":\"Detalle Cerdito\",\"precio\":28,\"stock\":10,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/INitiUa.jpeg\"}]', 'no_llego', 1, 1, 'rules', '2026-09-01 07:46:47', NULL, NULL, NULL),
(37, NULL, 'Producto dañado o malogrado · Pedido #121 · mi producto llego en mal estado · Detalle Personalizado 6 × 1', 'whatsapp', 1, 'Detalle Personalizado 6', '[{\"id\":6,\"nombre\":\"Detalle Personalizado 6\",\"precio\":38,\"stock\":19,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/YCxjYH8.jpeg\"}]', 'producto_danado', 1, 1, 'rules', '2026-09-01 09:20:54', NULL, NULL, NULL),
(38, NULL, 'que vendes', 'catalogo', 6, 'Detalle Personalizado 1, Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 4, Detalle Personalizado 7, Detalle Personalizado 8', '[{\"id\":1,\"nombre\":\"Detalle Personalizado 1\",\"precio\":30,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/MsH5l46.jpeg\"},{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":28,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":26,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":4,\"nombre\":\"Detalle Personalizado 4\",\"precio\":26,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/1O09Fw0.jpeg\"},{\"id\":7,\"nombre\":\"Detalle Personalizado 7\",\"precio\":38,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/cwuFeGu.jpeg\"},{\"id\":8,\"nombre\":\"Detalle Personalizado 8\",\"precio\":32,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/qqX4fdT.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-04 07:18:12', NULL, NULL, NULL),
(39, NULL, 'que cosas vendes', 'catalogo', 6, 'Cajita Dulcera, Caja Hot Wheels, Carteles Personalizados 18, Billetera Chicago Dorado, Billetera Chicago, Cajita Peluche', '[{\"id\":89,\"nombre\":\"Cajita Dulcera\",\"precio\":26,\"stock\":60,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/vBuRH3k.jpeg\"},{\"id\":90,\"nombre\":\"Caja Hot Wheels\",\"precio\":36,\"stock\":47,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/d0fY7ej.jpeg\"},{\"id\":61,\"nombre\":\"Carteles Personalizados 18\",\"precio\":24,\"stock\":42,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/T6I2ltu.jpeg\"},{\"id\":85,\"nombre\":\"Billetera Chicago Dorado\",\"precio\":22,\"stock\":39,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/IKA88Xf.jpeg\"},{\"id\":83,\"nombre\":\"Billetera Chicago\",\"precio\":20,\"stock\":36,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/4FuocIX.jpeg\"},{\"id\":74,\"nombre\":\"Cajita Peluche\",\"precio\":22,\"stock\":34,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/4TbqWDC.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 16:27:08', NULL, NULL, NULL),
(40, NULL, 'Hola Dori', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-05 17:56:57', NULL, NULL, NULL),
(41, NULL, 'regalo para mi novia', 'catalogo', 6, 'Flores Personalizadas 9, Flores Personalizadas 11, Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 17, Detalle Personalizado 5', '[{\"id\":34,\"nombre\":\"Flores Personalizadas 9\",\"precio\":29.45,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/830UXBN.jpeg\"},{\"id\":36,\"nombre\":\"Flores Personalizadas 11\",\"precio\":32.3,\"stock\":19,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/lRzO0Sm.jpeg\"},{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":26.6,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":24.7,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":17,\"nombre\":\"Detalle Personalizado 17\",\"precio\":30.4,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ni5iNy7.jpeg\"},{\"id\":5,\"nombre\":\"Detalle Personalizado 5\",\"precio\":27.55,\"stock\":16,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/RupYOeX.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 17:57:16', NULL, NULL, NULL),
(42, NULL, 'Producto dañado o malogrado · mi producto llego dañado', 'whatsapp', 0, NULL, NULL, 'producto_danado', 1, 0, 'rules', '2026-09-05 17:58:03', NULL, NULL, NULL),
(43, NULL, 'quiero alguna sugerencias de bolsos', 'catalogo', 4, 'Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 5, Detalle Personalizado 17', '[{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":26.6,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":24.7,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":5,\"nombre\":\"Detalle Personalizado 5\",\"precio\":27.55,\"stock\":16,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/RupYOeX.jpeg\"},{\"id\":17,\"nombre\":\"Detalle Personalizado 17\",\"precio\":30.4,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ni5iNy7.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 17:58:33', NULL, NULL, NULL),
(44, NULL, 'perfumes ??', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-05 17:58:52', NULL, NULL, NULL),
(45, NULL, 'peluches ?', 'catalogo', 6, 'Peluche Stich, Peluche Stich Rosa, Cajita Peluche, Detalle Personalizado 15, Detalle Personalizado 16, Detalle Personalizado 17', '[{\"id\":77,\"nombre\":\"Peluche Stich\",\"precio\":15.2,\"stock\":25,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/I0mb7tJ.jpeg\"},{\"id\":78,\"nombre\":\"Peluche Stich Rosa\",\"precio\":15.2,\"stock\":24,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/EMvrnWs.jpeg\"},{\"id\":74,\"nombre\":\"Cajita Peluche\",\"precio\":20.9,\"stock\":34,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/4TbqWDC.jpeg\"},{\"id\":15,\"nombre\":\"Detalle Personalizado 15\",\"precio\":25.65,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/48jYAFG.jpeg\"},{\"id\":16,\"nombre\":\"Detalle Personalizado 16\",\"precio\":31.35,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/usKlR8q.jpeg\"},{\"id\":17,\"nombre\":\"Detalle Personalizado 17\",\"precio\":30.4,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ni5iNy7.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 17:59:15', NULL, NULL, NULL),
(46, NULL, 'Perfume?', 'catalogo', 1, 'Perfume Dance', '[{\"id\":71,\"nombre\":\"Perfume Dance\",\"precio\":36.1,\"stock\":17,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/T4GDI86.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 17:59:51', NULL, NULL, NULL),
(47, NULL, 'Que puedo regalar. A un niño de 10 años', 'catalogo', 6, 'Detalle Personalizado 19, Carteles Personalizados 8, Carteles Personalizados 9, Carteles Personalizados 12, Carteles Personalizados 13, Carteles Personalizados 11', '[{\"id\":19,\"nombre\":\"Detalle Personalizado 19\",\"precio\":24.7,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/cWNMU0X.jpeg\"},{\"id\":51,\"nombre\":\"Carteles Personalizados 8\",\"precio\":32.3,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/SabxbJP.jpeg\"},{\"id\":52,\"nombre\":\"Carteles Personalizados 9\",\"precio\":37.05,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ZT5LBbx.jpeg\"},{\"id\":55,\"nombre\":\"Carteles Personalizados 12\",\"precio\":28.5,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/CLL880S.jpeg\"},{\"id\":56,\"nombre\":\"Carteles Personalizados 13\",\"precio\":32.3,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/0HoSBDT.jpeg\"},{\"id\":54,\"nombre\":\"Carteles Personalizados 11\",\"precio\":32.3,\"stock\":19,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/gCPkcGl.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 18:45:42', NULL, NULL, NULL),
(48, 1, 'reclamar un pedido', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-05 23:41:04', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(49, 1, 'pedido en mal estado', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-05 23:41:21', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(50, 1, 'se me cancelo un pedido', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-05 23:41:50', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(51, 1, 'quiero un regalo para mi novia', 'catalogo', 6, 'Flores Personalizadas 9, Flores Personalizadas 11, Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 17, Detalle Personalizado 5', '[{\"id\":34,\"nombre\":\"Flores Personalizadas 9\",\"precio\":31,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/830UXBN.jpeg\"},{\"id\":36,\"nombre\":\"Flores Personalizadas 11\",\"precio\":34,\"stock\":19,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/lRzO0Sm.jpeg\"},{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":28,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":26,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":17,\"nombre\":\"Detalle Personalizado 17\",\"precio\":32,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/ni5iNy7.jpeg\"},{\"id\":5,\"nombre\":\"Detalle Personalizado 5\",\"precio\":29,\"stock\":16,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/RupYOeX.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-05 23:42:10', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(52, 1, 'Producto dañado o malogrado · Pedido #11 · me llego un pedido en mal estado · Cajita Dulcera × 1', 'whatsapp', 1, 'Cajita Dulcera', '[{\"id\":89,\"nombre\":\"Cajita Dulcera\",\"precio\":26,\"stock\":59,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/vBuRH3k.jpeg\"}]', 'producto_danado', 1, 0, 'rules', '2026-09-05 23:43:28', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(53, 1, 'quiero ver mis pedidos', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 16:09:11', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(54, 1, 'Hola, quiero saber regalos amarillos tienes ??', 'catalogo', 6, 'Detalle Personalizado 1, Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 4, Detalle Personalizado 7, Detalle Personalizado 8', '[{\"id\":1,\"nombre\":\"Detalle Personalizado 1\",\"precio\":30,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/MsH5l46.jpeg\"},{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":28,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":26,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":4,\"nombre\":\"Detalle Personalizado 4\",\"precio\":26,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/1O09Fw0.jpeg\"},{\"id\":7,\"nombre\":\"Detalle Personalizado 7\",\"precio\":38,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/cwuFeGu.jpeg\"},{\"id\":8,\"nombre\":\"Detalle Personalizado 8\",\"precio\":32,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/qqX4fdT.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-06 19:17:20', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(55, 1, 'hoy es el dia de las flores amarillas, me puedes sugerir algunos productos ??', 'catalogo', 6, 'Detalle Personalizado 4, Flores Personalizadas 1, Flores Personalizadas 2, Flores Personalizadas 3, Flores Personalizadas 6, Detalle Personalizado 1', '[{\"id\":4,\"nombre\":\"Detalle Personalizado 4\",\"precio\":26,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/1O09Fw0.jpeg\"},{\"id\":26,\"nombre\":\"Flores Personalizadas 1\",\"precio\":18,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/Y0pxvC7.jpeg\"},{\"id\":27,\"nombre\":\"Flores Personalizadas 2\",\"precio\":19,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/mXLtfhU.jpeg\"},{\"id\":28,\"nombre\":\"Flores Personalizadas 3\",\"precio\":17,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/28jCsWO.jpeg\"},{\"id\":31,\"nombre\":\"Flores Personalizadas 6\",\"precio\":20,\"stock\":20,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/QI02QTQ.jpeg\"},{\"id\":1,\"nombre\":\"Detalle Personalizado 1\",\"precio\":30,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/MsH5l46.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-06 19:20:48', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(56, 1, 'quiero ver que pedidos o compras tengo ??', 'catalogo', 6, 'Detalle Personalizado 1, Detalle Personalizado 4, Flores Personalizadas 1, Flores Personalizadas 2, Flores Personalizadas 3, Flores Personalizadas 6', '[{\"id\":1,\"nombre\":\"Detalle Personalizado 1\",\"precio\":30,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/MsH5l46.jpeg\"},{\"id\":4,\"nombre\":\"Detalle Personalizado 4\",\"precio\":26,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/1O09Fw0.jpeg\"},{\"id\":26,\"nombre\":\"Flores Personalizadas 1\",\"precio\":18,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/Y0pxvC7.jpeg\"},{\"id\":27,\"nombre\":\"Flores Personalizadas 2\",\"precio\":19,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/mXLtfhU.jpeg\"},{\"id\":28,\"nombre\":\"Flores Personalizadas 3\",\"precio\":17,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/28jCsWO.jpeg\"},{\"id\":31,\"nombre\":\"Flores Personalizadas 6\",\"precio\":20,\"stock\":20,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/QI02QTQ.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-06 19:27:59', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(57, NULL, 'quiero algo para mi papa', 'catalogo', 6, 'Billetera Chicago, Billetera Renzo Costa, Billetera Chicago Dorado, Billetera Puma, Billetera Chicago Clasica, Bolso Verde Militar', '[{\"id\":83,\"nombre\":\"Billetera Chicago\",\"precio\":20,\"stock\":36,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/4FuocIX.jpeg\"},{\"id\":84,\"nombre\":\"Billetera Renzo Costa\",\"precio\":70,\"stock\":33,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/cQwVUIT.jpeg\"},{\"id\":85,\"nombre\":\"Billetera Chicago Dorado\",\"precio\":22,\"stock\":39,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/IKA88Xf.jpeg\"},{\"id\":87,\"nombre\":\"Billetera Puma\",\"precio\":28,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/awUkCBo.jpeg\"},{\"id\":86,\"nombre\":\"Billetera Chicago Clasica\",\"precio\":24,\"stock\":24,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/OMeOB96.jpeg\"},{\"id\":63,\"nombre\":\"Bolso Verde Militar\",\"precio\":20,\"stock\":30,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/35Z5QbX.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-06 19:30:37', NULL, NULL, NULL),
(58, NULL, 'quiero algo para mi mama', 'catalogo', 6, 'Detalle Personalizado 1, Detalle Personalizado 2, Detalle Personalizado 3, Detalle Personalizado 4, Detalle Personalizado 7, Detalle Personalizado 8', '[{\"id\":1,\"nombre\":\"Detalle Personalizado 1\",\"precio\":30,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/MsH5l46.jpeg\"},{\"id\":2,\"nombre\":\"Detalle Personalizado 2\",\"precio\":28,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/zkgrxFy.jpeg\"},{\"id\":3,\"nombre\":\"Detalle Personalizado 3\",\"precio\":26,\"stock\":32,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/VVoqfq4.jpeg\"},{\"id\":4,\"nombre\":\"Detalle Personalizado 4\",\"precio\":26,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/1O09Fw0.jpeg\"},{\"id\":7,\"nombre\":\"Detalle Personalizado 7\",\"precio\":38,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/cwuFeGu.jpeg\"},{\"id\":8,\"nombre\":\"Detalle Personalizado 8\",\"precio\":32,\"stock\":31,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/qqX4fdT.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-06 19:36:25', NULL, NULL, NULL),
(59, 1, 'quiero ver mis pedidos o mis compras ??}', 'pedido', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 20:21:50', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(60, 1, 'pero quiero ver todos mis pedidos que tengo ??', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 20:22:16', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(61, 1, 'No llegó / extravío · Pedido #20 · no me llego el producto · Caja Hot Wheels × 1', 'whatsapp', 1, 'Caja Hot Wheels', '[{\"id\":90,\"nombre\":\"Caja Hot Wheels\",\"precio\":36,\"stock\":48,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/d0fY7ej.jpeg\"}]', 'no_llego', 1, 0, 'rules', '2026-09-06 20:24:35', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(62, 1, 'pedido 20', 'pedido', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 20:30:41', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(63, 1, 'No llegó / extravío · Pedido #20 · no me llego un producto · Caja Hot Wheels × 1', 'whatsapp', 1, 'Caja Hot Wheels', '[{\"id\":90,\"nombre\":\"Caja Hot Wheels\",\"precio\":36,\"stock\":48,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/d0fY7ej.jpeg\"}]', 'no_llego', 1, 0, 'rules', '2026-09-06 20:31:25', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(64, 1, 'Es el pedido 20', 'pedido', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 20:48:12', 'Juan Jose Aranda Cordova', 'a21sjuanjo@gmail.com', '916183671'),
(65, 5, 'pedido 21', 'pedido', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 20:59:46', 'MELVIN VICTOR AGUILAR HUAMANZANA', 'i2421708@continental.edu.pe', '916464315'),
(66, 5, 'Quiero comunicarme con el dueño', 'sin_producto', 0, NULL, NULL, NULL, 0, 0, 'rules', '2026-09-06 21:00:31', 'MELVIN VICTOR AGUILAR HUAMANZANA', 'i2421708@continental.edu.pe', '916464315'),
(67, 5, 'No llegó / extravío · Pedido #21 · mi pedido no llega · Caja Hot Wheels × 1', 'whatsapp', 1, 'Caja Hot Wheels', '[{\"id\":90,\"nombre\":\"Caja Hot Wheels\",\"precio\":36,\"stock\":47,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/d0fY7ej.jpeg\"}]', 'no_llego', 1, 0, 'rules', '2026-09-07 17:49:27', 'MELVIN VICTOR AGUILAR HUAMANZANA', 'i2421708@continental.edu.pe', '916464315'),
(68, 5, 'quiero un regalo para mi papa', 'catalogo', 1, 'Caja Hot Wheels', '[{\"id\":90,\"nombre\":\"Caja Hot Wheels\",\"precio\":36,\"stock\":47,\"imagen_url\":\"https:\\/\\/i.imgur.com\\/d0fY7ej.jpeg\"}]', NULL, 0, 0, 'rules', '2026-09-07 17:53:44', 'MELVIN VICTOR AGUILAR HUAMANZANA', 'i2421708@continental.edu.pe', '916464315');

-- --------------------------------------------------------

--
-- Table structure for table `auditoria`
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
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-geo:nominatim:0ca32c1d3e75a2007db081f40227b7b4', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:4901710;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0653911\";s:3:\"lon\";s:11:\"-77.0452294\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"112, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"112\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654411\";i:1;s:11:\"-12.0653411\";i:2;s:11:\"-77.0452794\";i:3;s:11:\"-77.0451794\";}}}', 1787367356),
('laravel-cache-geo:nominatim:248e06354af17ae30691e28701827d8e', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:4163386;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0654016\";s:3:\"lon\";s:11:\"-77.0453296\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"102, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"102\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654516\";i:1;s:11:\"-12.0653516\";i:2;s:11:\"-77.0453796\";i:3;s:11:\"-77.0452796\";}}}', 1787371676),
('laravel-cache-geo:nominatim:2b8e37edddd96a74dcffd090182027ae', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073704;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135878;s:3:\"lat\";s:11:\"-12.0652463\";s:3:\"lon\";s:11:\"-77.0440711\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"201, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"201\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0652963\";i:1;s:11:\"-12.0651963\";i:2;s:11:\"-77.0441211\";i:3;s:11:\"-77.0440211\";}}}', 1788224026),
('laravel-cache-geo:nominatim:3501a3237e8cb765c142919c0f7eedd4', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3675315;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:4:\"node\";s:6:\"osm_id\";i:5084453028;s:3:\"lat\";s:11:\"-12.0638769\";s:3:\"lon\";s:11:\"-77.0335310\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:7.730144197752372E-5;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:83:\"601, Avenida 28 de Julio, La Victoria, Lima, Lima Metropolitana, Lima, 15001, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"601\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:11:\"La Victoria\";s:4:\"city\";s:11:\"La Victoria\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15001\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0639269\";i:1;s:11:\"-12.0638269\";i:2;s:11:\"-77.0335810\";i:3;s:11:\"-77.0334810\";}}}', 1788238243),
('laravel-cache-geo:nominatim:3f5387367269d1d2324971cbfad88d67', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3596630;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:1101869808;s:3:\"lat\";s:11:\"-12.2231725\";s:3:\"lon\";s:11:\"-76.9748949\";s:5:\"class\";s:7:\"highway\";s:4:\"type\";s:8:\"cycleway\";s:10:\"place_rank\";i:27;s:10:\"importance\";d:0.04007730144197756;s:11:\"addresstype\";s:4:\"road\";s:4:\"name\";s:30:\"Ciclovía Defensores del Morro\";s:12:\"display_name\";s:99:\"Ciclovía Defensores del Morro, Sector 13, Chorrillos, Lima, Lima Metropolitana, Lima, 15829, Perú\";s:7:\"address\";a:11:{s:4:\"road\";s:30:\"Ciclovía Defensores del Morro\";s:7:\"quarter\";s:9:\"Sector 13\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15829\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.2287790\";i:1;s:11:\"-12.2173807\";i:2;s:11:\"-76.9798429\";i:3;s:11:\"-76.9695981\";}}}', 1788237948),
('laravel-cache-geo:nominatim:471b2e0d3b752f3bd77a8aecc1736f7f', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3670660;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:891955464;s:3:\"lat\";s:11:\"-12.1786085\";s:3:\"lon\";s:11:\"-77.0149078\";s:5:\"class\";s:7:\"highway\";s:4:\"type\";s:7:\"primary\";s:10:\"place_rank\";i:26;s:10:\"importance\";d:0.05341063477531087;s:11:\"addresstype\";s:4:\"road\";s:4:\"name\";s:28:\"Avenida Defensores del Morro\";s:12:\"display_name\";s:86:\"Avenida Defensores del Morro, Chorrillos, Lima, Lima Metropolitana, Lima, 15064, Perú\";s:7:\"address\";a:11:{s:4:\"road\";s:28:\"Avenida Defensores del Morro\";s:6:\"suburb\";s:10:\"Chorrillos\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15064\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.1795646\";i:1;s:11:\"-12.1776518\";i:2;s:11:\"-77.0158186\";i:3;s:11:\"-77.0139963\";}}}', 1788237748),
('laravel-cache-geo:nominatim:5951d8a4fcc59761f707c3b0f3e982de', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3643301;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:891955467;s:3:\"lat\";s:11:\"-12.1786836\";s:3:\"lon\";s:11:\"-77.0149860\";s:5:\"class\";s:7:\"highway\";s:4:\"type\";s:7:\"primary\";s:10:\"place_rank\";i:26;s:10:\"importance\";d:0.05341063477531087;s:11:\"addresstype\";s:4:\"road\";s:4:\"name\";s:28:\"Avenida Defensores del Morro\";s:12:\"display_name\";s:86:\"Avenida Defensores del Morro, Chorrillos, Lima, Lima Metropolitana, Lima, 15064, Perú\";s:7:\"address\";a:11:{s:4:\"road\";s:28:\"Avenida Defensores del Morro\";s:6:\"suburb\";s:10:\"Chorrillos\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15064\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.1796391\";i:1;s:11:\"-12.1777282\";i:2;s:11:\"-77.0158949\";i:3;s:11:\"-77.0140772\";}}}', 1786831309),
('laravel-cache-geo:nominatim:69fdfd188fdd827a3893b0407165fede', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073721;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0654005\";s:3:\"lon\";s:11:\"-77.0453196\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"103, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"103\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654505\";i:1;s:11:\"-12.0653505\";i:2;s:11:\"-77.0453696\";i:3;s:11:\"-77.0452696\";}}}', 1785719921),
('laravel-cache-geo:nominatim:829ce9bacddfa33a21cda76642c355f4', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073721;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0654005\";s:3:\"lon\";s:11:\"-77.0453196\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"103, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"103\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654505\";i:1;s:11:\"-12.0653505\";i:2;s:11:\"-77.0453696\";i:3;s:11:\"-77.0452696\";}}}', 1787972028),
('laravel-cache-geo:nominatim:888f9c8150930dc00c4867776b096beb', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:4115206;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0653797\";s:3:\"lon\";s:11:\"-77.0451193\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"123, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"123\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654297\";i:1;s:11:\"-12.0653297\";i:2;s:11:\"-77.0451693\";i:3;s:11:\"-77.0450693\";}}}', 1787973500),
('laravel-cache-geo:nominatim:c10c0feae6625563d43b47dd7ab61a31', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073721;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0654005\";s:3:\"lon\";s:11:\"-77.0453196\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"103, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"103\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654505\";i:1;s:11:\"-12.0653505\";i:2;s:11:\"-77.0453696\";i:3;s:11:\"-77.0452696\";}}}', 1787363725),
('laravel-cache-geo:nominatim:d44effda8a4169e2d85229e0c66d4757', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:5073755;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135879;s:3:\"lat\";s:11:\"-12.0651691\";s:3:\"lon\";s:11:\"-77.0433604\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"301, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"301\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0652191\";i:1;s:11:\"-12.0651191\";i:2;s:11:\"-77.0434104\";i:3;s:11:\"-77.0433104\";}}}', 1788226611),
('laravel-cache-geo:nominatim:e6e0ba8ca8f3c7bef3fce64a5b912a2c', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3934766;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:522135877;s:3:\"lat\";s:11:\"-12.0653797\";s:3:\"lon\";s:11:\"-77.0451193\";s:5:\"class\";s:5:\"place\";s:4:\"type\";s:5:\"house\";s:10:\"place_rank\";i:30;s:10:\"importance\";d:9.99999999995449E-6;s:11:\"addresstype\";s:5:\"place\";s:4:\"name\";s:0:\"\";s:12:\"display_name\";s:85:\"123, Avenida 28 de Julio, Jesús María, Lima, Lima Metropolitana, Lima, 15083, Perú\";s:7:\"address\";a:12:{s:12:\"house_number\";s:3:\"123\";s:4:\"road\";s:19:\"Avenida 28 de Julio\";s:6:\"suburb\";s:13:\"Jesús María\";s:4:\"city\";s:13:\"Jesús María\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:8:\"postcode\";s:5:\"15083\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.0654297\";i:1;s:11:\"-12.0653297\";i:2;s:11:\"-77.0451693\";i:3;s:11:\"-77.0450693\";}}}', 1787973493),
('laravel-cache-geo:nominatim:f00a0e4776515aa108dbb574507c6cf0', 'a:1:{i:0;a:15:{s:8:\"place_id\";i:3624233;s:7:\"licence\";s:70:\"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright\";s:8:\"osm_type\";s:3:\"way\";s:6:\"osm_id\";i:437322593;s:3:\"lat\";s:11:\"-12.1771963\";s:3:\"lon\";s:11:\"-77.0155390\";s:5:\"class\";s:7:\"landuse\";s:4:\"type\";s:11:\"residential\";s:10:\"place_rank\";i:24;s:10:\"importance\";d:0.08007730144197754;s:11:\"addresstype\";s:11:\"residential\";s:4:\"name\";s:15:\"Alameda Huaylas\";s:12:\"display_name\";s:66:\"Alameda Huaylas, Chorrillos, Lima, Lima Metropolitana, Lima, Perú\";s:7:\"address\";a:10:{s:11:\"residential\";s:15:\"Alameda Huaylas\";s:6:\"suburb\";s:10:\"Chorrillos\";s:4:\"city\";s:10:\"Chorrillos\";s:6:\"region\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl6\";s:6:\"PE-LMA\";s:14:\"state_district\";s:18:\"Lima Metropolitana\";s:5:\"state\";s:4:\"Lima\";s:14:\"ISO3166-2-lvl4\";s:6:\"PE-LIM\";s:7:\"country\";s:5:\"Perú\";s:12:\"country_code\";s:2:\"pe\";}s:11:\"boundingbox\";a:4:{i:0;s:11:\"-12.1776470\";i:1;s:11:\"-12.1767456\";i:2;s:11:\"-77.0160105\";i:3;s:11:\"-77.0150676\";}}}', 1786831544);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `created_at`) VALUES
(1, 'Detalles', 'Detalles personalizados con rosas artificiales y variedad en regalos adicionales', '2025-10-04 04:07:25'),
(2, 'Florales', 'Flores naturales con arreglos personalizados.', '2025-10-04 04:07:25'),
(3, 'Carteles', 'Carteles con mensajes personalizados.', '2025-10-04 04:07:25'),
(4, 'Perfumeria', 'Perfumes para damas y caballeros de diferentes marcas, precios, etc.', '2025-10-04 04:07:25'),
(5, 'Variados', 'Detalles, Regalos, Peluches entre otros para regalar en cualquier ocasión', '2025-10-16 02:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
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
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `telefono`, `email`, `direccion`, `contrasena`, `created_at`) VALUES
(1, 'Juan Jose', 'Aranda Cordova', '916183671', 'a21sjuanjo@gmail.com', 'Av. Flor de Mayo 948', '$2y$12$yAXOdf1/r9gz/.bSxbV5/eKt7ycuIQMVfuzXellEi5m3SA3uTodAK', '2026-09-05 23:23:11'),
(2, 'JUAN JOSE', 'ARANDA CORDOVA', NULL, 'i1125818@continental.edu.pe', NULL, '$2y$12$NB.L6iLFIn423mIV.cVFl.NOXOIs8.eFAe43YhIlbEMMYh9t9890m', '2026-09-05 23:26:54'),
(3, 'JUAN', 'ARANDA CORDOVA', '916183671', 'juan1995@gmail.com', 'Av. Huancavelica 2885 - El tambo - Huancayo', '$2y$12$4umjC3B2S5QDadt0KlqcVeIJRpzgekCno8G93vOq0Uzdx21YR39MK', '2026-09-06 01:27:45'),
(4, 'Linda', 'Loarte', NULL, 'lindaloarte6@gmail.com', NULL, '$2y$12$tbl3tU64YhF1SowZEBroleszKbvGrIlBX17TmpghR0xPl5t5xj4Om', '2026-09-06 01:56:58'),
(5, 'MELVIN VICTOR', 'AGUILAR HUAMANZANA', '916464315', 'i2421708@continental.edu.pe', NULL, '$2y$12$JMmd/8ZO3Aj7Jl3nUmdL1.UVBRJI1yVy4TGzc.fQRiRLFokDWqY/q', '2026-09-07 03:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `detalles_pedidos`
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
-- Dumping data for table `detalles_pedidos`
--

INSERT INTO `detalles_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 90, 1, 34.20),
(2, 2, 3, 1, 24.70),
(3, 2, 11, 1, 30.40),
(5, 4, 83, 1, 19.00),
(6, 4, 88, 1, 23.75),
(7, 5, 89, 1, 24.70),
(8, 6, 90, 1, 34.20),
(9, 7, 90, 1, 34.20),
(10, 8, 83, 1, 19.00),
(11, 8, 87, 1, 26.60),
(12, 9, 89, 1, 24.70),
(13, 10, 87, 1, 26.60),
(14, 11, 89, 1, 24.70),
(15, 12, 90, 1, 34.20),
(16, 13, 89, 1, 26.00),
(17, 14, 89, 1, 26.00),
(18, 15, 89, 2, 26.00),
(19, 16, 18, 15, 24.00),
(20, 17, 18, 15, 24.00),
(21, 18, 18, 30, 24.00),
(22, 19, 90, 1, 36.00),
(23, 20, 90, 1, 36.00),
(24, 21, 90, 1, 36.00),
(25, 22, 90, 1, 32.40);

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
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
-- Dumping data for table `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `apellido`, `cargo`, `email`, `telefono`, `contrasena`, `password`, `created_at`) VALUES
(1, 'Elvis', 'Lopez', 'Administrador', 'elvis.estilodorado@gmail.com', '934567890', NULL, '$2y$12$IU4XVev3aZodppazV/Izwuy.CgdOCDwljB9FTsxqNcqIIIjKPA9qe', '2025-10-04 04:07:25'),
(2, 'Juan', 'Aranda', 'Soporte Técnico', 'juan.estilodorado@gmail.com', '945678901', NULL, '$2y$12$7cSmIZBketruAwJDRDugEe3aYXBknV4/.drykTKYvO53TbbEjmZHa', '2025-10-04 04:07:25'),
(3, 'Yerson', 'Ramos', 'Encargado de Stock', 'yerson.estilodorado@gmail.com', '912345678', NULL, '$2y$12$01stwe4brB.P5o6RwPofC.WANlhGp9Ic6kUFrD.62Zg98NV5R4xC.', '2025-10-04 04:07:25'),
(4, 'Derly', 'Pineda', 'Vendedor', 'derly.estilodorado@gmail.com', '923456789', NULL, '$2y$12$PMPX9pMFG8R7uPpSthL9reerMueQJip.gDYaAReYZ2kzw/fFzzARm', '2025-10-04 04:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `empleado_rol`
--

CREATE TABLE `empleado_rol` (
  `id_empleado` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empleado_rol`
--

INSERT INTO `empleado_rol` (`id_empleado`, `id_rol`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `inventario`
--

CREATE TABLE `inventario` (
  `id_movimiento` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` varchar(20) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacion` text DEFAULT NULL,
  `referencia_tipo` varchar(20) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventario`
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
(197, 76, 'entrada', 1, '2025-10-27 05:00:00', 'Llegada de Stock', 'ajuste', NULL, 1),
(198, 89, 'liberacion', 2, '2026-09-06 15:44:18', 'Reserva por pedido #15 · Pedido cancelado, stock liberado', 'pedido', 15, NULL),
(199, 18, 'liberacion', 15, '2026-09-06 16:14:21', 'Reserva por pedido #16 · Pedido cancelado, stock liberado', 'pedido', 16, NULL),
(200, 90, 'salida', 1, '2026-09-06 17:25:10', 'Reserva por pedido #7 · Entregado', 'pedido', 7, NULL),
(201, 18, 'liberacion', 15, '2026-09-06 17:34:14', 'Reserva por pedido #17 · Pedido cancelado, stock liberado', 'pedido', 17, NULL),
(202, 18, 'liberacion', 15, '2026-09-06 17:40:58', 'Liberación pedido #16', 'pedido', 16, NULL),
(203, 18, 'liberacion', 30, '2026-09-06 17:45:06', 'Reserva por pedido #18 · Pedido cancelado, stock liberado', 'pedido', 18, NULL),
(204, 18, 'salida', 30, '2026-09-06 17:46:58', 'Reserva por pedido #18 · Entregado', 'pedido', 18, NULL),
(205, 90, 'liberacion', 1, '2026-09-06 17:52:01', 'Reserva por pedido #19 · Pedido cancelado, stock liberado', 'pedido', 19, NULL),
(206, 90, 'reserva', 1, '2026-09-06 20:01:26', 'Reserva por pedido #20', 'pedido', 20, NULL),
(207, 90, 'liberacion', 1, '2026-09-06 20:56:13', 'Reserva por pedido #21 · Pedido cancelado, stock liberado', 'pedido', 21, NULL),
(208, 90, 'salida', 1, '2026-09-06 20:58:53', 'Reserva por pedido #21 · Entregado', 'pedido', 21, NULL),
(209, 90, 'liberacion', 1, '2026-09-06 21:08:22', 'Reserva por pedido #22 · Pedido cancelado, stock liberado', 'pedido', 22, NULL),
(210, 83, 'entrada', 10, '2026-09-06 22:00:00', 'Productos navideños · Ref. compra: F001-01', 'compra', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
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
(11, '2026_08_16_000001_restore_empty_etiquetas', 7),
(12, '2026_08_28_000001_create_asistente_logs_table', 8),
(13, '2026_08_30_000001_add_detalle_to_asistente_logs', 9),
(14, '2026_08_30_120000_add_productos_json_to_asistente_logs', 10),
(15, '2026_09_01_000001_promociones_y_descuento_producto', 11),
(16, '2026_09_04_000001_asistente_logs_cliente', 12),
(17, '2026_09_04_120000_create_asistente_feedback', 12),
(18, '2026_09_06_000001_add_costo_envio_to_pedidos', 13),
(19, '2026_09_06_120000_add_telefono_contacto_to_pedidos', 14),
(20, '2026_09_06_180000_ampliar_tipos_inventario', 15),
(21, '2026_09_07_000001_add_coords_and_cpe_to_pedidos', 16);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT 0.00,
  `costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `envio_etiqueta` varchar(160) DEFAULT NULL,
  `forma_pago` varchar(50) DEFAULT NULL,
  `direccion_entrega` text DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `culqi_id` varchar(80) DEFAULT NULL,
  `comprobante_tipo` varchar(20) NOT NULL,
  `comprobante_serie` varchar(10) NOT NULL,
  `comprobante_numero` int(10) UNSIGNED NOT NULL,
  `sunat_xml` varchar(255) DEFAULT NULL,
  `sunat_cdr` varchar(255) DEFAULT NULL,
  `sunat_pdf` varchar(255) DEFAULT NULL,
  `comprobantes_json` longtext DEFAULT NULL,
  `lat_entrega` decimal(10,7) DEFAULT NULL,
  `lng_entrega` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `fecha_pedido`, `estado`, `total`, `costo_envio`, `envio_etiqueta`, `forma_pago`, `direccion_entrega`, `telefono_contacto`, `culqi_id`, `comprobante_tipo`, `comprobante_serie`, `comprobante_numero`, `sunat_xml`, `sunat_cdr`, `sunat_pdf`, `comprobantes_json`, `lat_entrega`, `lng_entrega`) VALUES
(1, 1, '2026-09-05 21:34:55', 'pagado', 34.20, 0.00, NULL, 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_FJUQPqvplGPlSj1k', 'BO', 'B001', 1, 'comprobantes/xml/BO/B001/B001-00000001.xml', NULL, 'comprobantes/pdf/BO/B001/B001-00000001.pdf', NULL, NULL, NULL),
(2, 4, '2026-09-05 21:35:48', 'pagado', 55.10, 0.00, NULL, 'tarjeta', 'Avenida Alfredo Benavides 3583 – Santiago De Surco/Lima/Lima', NULL, 'tkn_test_C33aJbcYgXaKc085', 'FA', 'F001', 1, 'comprobantes/xml/FA/F001/F001-00000001.xml', NULL, 'comprobantes/pdf/FA/F001/F001-00000001.pdf', NULL, NULL, NULL),
(4, 4, '2026-09-05 22:40:37', 'pagado', 64.75, 22.00, 'Envío Shalom + domicilio Lima – Callao', 'tarjeta', 'Av. 28 de julio 1101 – Lima/Lima/Lima', NULL, 'tkn_test_prRlMwzcOF2HFPlN', 'FA', 'F001', 2, 'comprobantes/xml/FA/F001/F001-00000002.xml', NULL, 'comprobantes/pdf/FA/F001/4-F001-00000002.pdf', NULL, NULL, NULL),
(5, 1, '2026-09-05 22:55:55', 'pagado', 41.70, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_Gw4frv4AHS8WlhP9', 'BO', 'B001', 2, 'comprobantes/xml/BO/B001/5-B001-00000002.xml', NULL, 'comprobantes/pdf/BO/B001/5-B001-00000002.pdf', NULL, NULL, NULL),
(6, 1, '2026-09-05 23:03:30', 'pagado', 51.20, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_qpHZSs8dGJwItPnG', 'BO', 'B001', 3, 'comprobantes/xml/BO/B001/6-B001-00000003.xml', NULL, 'comprobantes/pdf/BO/B001/6-B001-00000003.pdf', NULL, NULL, NULL),
(7, 1, '2026-09-05 00:00:00', 'entregado', 51.20, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_oD8fqO25m6RdsgEN', 'BO', 'B001', 4, 'comprobantes/xml/BO/B001/7-B001-00000004.xml', NULL, 'comprobantes/pdf/BO/B001/7-B001-00000004.pdf', NULL, NULL, NULL),
(8, 4, '2026-09-05 23:16:10', 'pagado', 62.60, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Shalom Mariscal Castilla 2500 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_aeo52UeF9xefUGsU', 'FA', 'F001', 3, 'comprobantes/xml/FA/F001/8-F001-00000003.xml', NULL, 'comprobantes/pdf/FA/F001/8-F001-00000003.pdf', NULL, NULL, NULL),
(9, 1, '2026-09-05 00:00:00', 'enviado', 41.70, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'tkn_test_9sMIG9FKDsXhcPi3', 'BO', 'B001', 5, 'comprobantes/xml/BO/B001/9-B001-00000005.xml', NULL, 'comprobantes/pdf/BO/B001/9-B001-00000005.pdf', NULL, NULL, NULL),
(10, 4, '2026-09-05 23:25:05', 'pagado', 38.60, 12.00, 'Envío Shalom agencia Lima – Callao', 'tarjeta', 'Shalom Cercado Tingo María — Av. Tingo María 1252-A (Lima)', NULL, 'tkn_test_Wdlo2kpMZlRyTsZm', 'FA', 'F001', 4, 'comprobantes/xml/FA/F001/10-F001-00000004.xml', NULL, 'comprobantes/pdf/FA/F001/10-F001-00000004.pdf', NULL, NULL, NULL),
(11, 1, '2026-09-05 00:00:00', 'pendiente', 41.70, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Avenida Huancavelica 2885 – El Tambo/Huancayo/Junin', NULL, 'ype_test_EeIB6xBIAMdrR3A8', 'BO', 'B001', 6, 'comprobantes/xml/BO/B001/11-B001-00000006.xml', NULL, 'comprobantes/pdf/BO/B001/11-B001-00000006.pdf', NULL, NULL, NULL),
(12, 1, '2026-09-05 00:00:00', 'cancelado', 34.20, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', NULL, NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 4, '2026-09-06 00:00:00', 'entregado', 38.00, 12.00, 'Envío Shalom agencia Lima – Callao', 'tarjeta', 'Shalom Cercado Tingo María — Av. Tingo María 1252-A (Lima)', NULL, 'tkn_test_yo1kM9sEad1mdqYk', 'FA', 'F001', 5, 'comprobantes/xml/FA/F001/13-F001-00000005.xml', 'comprobantes/cdr/FA/13-R-F001-00000005.zip', 'comprobantes/pdf/FA/F001/13-F001-00000005.pdf', NULL, NULL, NULL),
(14, 1, '2026-09-06 00:00:00', 'cancelado', 26.00, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 1, '2026-09-06 00:00:00', 'cancelado', 52.00, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 1, '2026-09-06 00:00:00', 'cancelado', 360.00, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 1, '2026-09-06 00:00:00', 'pagado', 360.00, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, 'comprobantes/pdf/BO/EF00/17-EF00-00000017.pdf', NULL, NULL, NULL),
(18, 1, '2026-09-06 19:45:01', 'entregado', 720.00, 0.00, 'Recojo en tienda', 'yape', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 1, '2026-09-06 19:51:56', 'cancelado', 36.00, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916183671', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 1, '2026-09-06 22:01:20', 'pagado', 48.00, 12.00, 'Envío Shalom agencia Huancayo', 'tarjeta', 'Shalom Mariscal Castilla — Av. Mariscal Castilla 2769 (El Tambo)', '916183671', 'tkn_test_QvkDgRPezNUbv3DL', 'BO', 'B001', 7, 'comprobantes/xml/BO/B001/20-B001-00000007.xml', 'comprobantes/cdr/BO/20-R-B001-00000007.zip', 'comprobantes/pdf/BO/B001/20-B001-00000007.pdf', NULL, NULL, NULL),
(21, 5, '2026-09-06 22:56:07', 'entregado', 53.00, 17.00, 'Envío Shalom + domicilio Huancayo', 'tarjeta', 'Av. Huancavelica 2885 – El Tambo/Huancayo/Junin', '916464315', 'tok_mobile_1788753364615', 'FA', 'F001', 6, 'comprobantes/xml/FA/F001/21-F001-00000006.xml', 'comprobantes/cdr/FA/21-R-F001-00000006.zip', 'comprobantes/pdf/FA/F001/21-F001-00000006.pdf', NULL, NULL, NULL),
(22, 5, '2026-09-06 23:08:16', 'cancelado', 32.40, 0.00, 'Recojo en tienda', 'efectivo', 'Retiro en tienda — Prolongación Yauli Nro. S/N Pasco - Pasco – Chaupimarca.', '916464315', NULL, 'EF', 'EF00', 0, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pedido_estado_historial`
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
-- Dumping data for table `pedido_estado_historial`
--

INSERT INTO `pedido_estado_historial` (`id_historial`, `id_pedido`, `estado_anterior`, `estado_nuevo`, `fecha`, `comentario`, `id_empleado`) VALUES
(1, 18, 'pendiente', 'cancelado', '2026-09-06 17:46:13', 'Cambio desde panel de pedidos', NULL),
(2, 18, 'cancelado', 'entregado', '2026-09-06 17:47:00', 'Cambio desde panel de pedidos', NULL),
(3, 19, 'pendiente', 'cancelado', '2026-09-06 19:22:34', 'Cambio desde panel de pedidos', NULL),
(4, 21, 'pagado', 'cancelado', '2026-09-06 20:57:50', 'Cambio desde panel de pedidos', NULL),
(5, 21, 'cancelado', 'entregado', '2026-09-06 20:58:55', 'Cambio desde panel de pedidos', NULL),
(6, 22, 'pendiente', 'cancelado', '2026-09-06 21:09:14', 'Cancelado por el cliente', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
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
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(271, 'App\\Models\\Empleado', 1, 'panel-admin', 'fe9ffc1852e6591edecc4e34e29db8d17faa7d53e6ac4021ecce29e93fc1bb52', '[\"admin\"]', '2026-09-05 18:55:59', NULL, '2026-09-05 17:06:16', '2026-09-05 18:55:59'),
(272, 'App\\Models\\Empleado', 1, 'panel-admin', 'ecee7a522f998e7e2e8c366ea9b182d7d8cda071eccff386f7478f9c9066c531', '[\"admin\"]', '2026-09-05 18:56:00', NULL, '2026-09-05 17:19:16', '2026-09-05 18:56:00'),
(274, 'App\\Models\\Cliente', 3, 'token_cliente', 'c32c4f667ce177ef5777c909fe3644ff9b37916183349eeafac53f0f2c90e4ae', '[\"client\"]', '2026-09-05 18:28:39', NULL, '2026-09-05 18:27:45', '2026-09-05 18:28:39'),
(281, 'App\\Models\\Empleado', 1, 'panel-admin', 'ef1a7bea4ab326b36683a33968642dc74bbe41415dccb822797a9ba5114572ce', '[\"admin\"]', '2026-09-05 21:14:09', NULL, '2026-09-05 18:56:24', '2026-09-05 21:14:09'),
(283, 'App\\Models\\Empleado', 1, 'panel-admin', '20ac5b5b70672e125dc63965f42d98c13406e4bf44373bcb261f02ea185d81fa', '[\"admin\"]', '2026-09-05 22:35:51', NULL, '2026-09-05 21:14:28', '2026-09-05 22:35:51'),
(285, 'App\\Models\\Cliente', 4, 'token_cliente', '180fc89e81da0f5fa01977641be42eacd682513d9e0b42eb92132fdf7c1883cf', '[\"client\"]', '2026-09-05 21:25:16', NULL, '2026-09-05 21:23:44', '2026-09-05 21:25:16'),
(287, 'App\\Models\\Cliente', 1, 'token_cliente', '82aacc6ea525923c380cdfdaa20d19281c720c3a7d5f63d4b57c2beb327d9d27', '[\"client\"]', '2026-09-05 21:48:26', NULL, '2026-09-05 21:27:32', '2026-09-05 21:48:26'),
(291, 'App\\Models\\Empleado', 1, 'panel-admin', '0aea417c52fb030b608fdcc68cccdc20d33fa5ae72cf4290ce3847779c4ab8fc', '[\"admin\"]', '2026-09-06 22:35:00', NULL, '2026-09-06 08:05:15', '2026-09-06 22:35:00'),
(294, 'App\\Models\\Empleado', 1, 'panel-admin', '1df0de2ed8ed38c82a59c26a58ed2b28974a179fb04d1fc0c20ff50e07134de5', '[\"admin\"]', '2026-09-06 19:00:34', NULL, '2026-09-06 18:59:54', '2026-09-06 19:00:34'),
(295, 'App\\Models\\Empleado', 1, 'panel-admin', '6a7baef351e6152a9db52d26448a4f051eb508d3668d606960842b9fcad82a79', '[\"admin\"]', '2026-09-06 22:50:09', NULL, '2026-09-06 19:01:28', '2026-09-06 22:50:09'),
(297, 'App\\Models\\Cliente', 1, 'token_cliente', '5c81f381750f6beb8c2f04fa7688fe199cc737c63f966b3742220decb51d2379', '[\"client\"]', '2026-09-06 22:45:42', NULL, '2026-09-06 20:46:22', '2026-09-06 22:45:42'),
(298, 'App\\Models\\Cliente', 5, 'token_cliente', '4df73d69e869f571f3431cedf206c2f8a17fab031cba85afd3231e47a8104d2d', '[\"client\"]', '2026-09-06 21:09:55', NULL, '2026-09-06 20:50:37', '2026-09-06 21:09:55'),
(299, 'App\\Models\\Empleado', 1, 'panel-admin', 'a878ed68e5d267bf9049bd2bdd8206765df6b885c222b35b5bbcb8bbc857ecaa', '[\"admin\"]', '2026-09-06 21:35:15', NULL, '2026-09-06 21:32:46', '2026-09-06 21:35:15'),
(300, 'App\\Models\\Empleado', 1, 'panel-admin', 'b4213415883799e78b1a64a6e0f3cd0a62200a017fe039d19227d935aa3937c8', '[\"admin\"]', '2026-09-07 19:03:48', NULL, '2026-09-07 17:47:25', '2026-09-07 19:03:48'),
(301, 'App\\Models\\Cliente', 5, 'token_cliente', '8e582327882f852ff2f9fd66000e94abd30ebd0f36517d70b6afc417dba0364b', '[\"client\"]', '2026-09-07 18:11:29', NULL, '2026-09-07 17:48:39', '2026-09-07 18:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `etiquetas` varchar(500) DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `oferta_hasta` date DEFAULT NULL,
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
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `etiquetas`, `precio_compra`, `precio_venta`, `descuento_pct`, `oferta_hasta`, `stock`, `id_categoria`, `id_proveedor`, `imagen_url`, `estado`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Detalle Personalizado 1', 'Detalle personalizado con Flores Moradas, dulces vizzio y globo de Happy Birthday', 'flores, regalo, arreglo, cumpleaños, detalle, detalles, dulces, fiesta, globos, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 18.00, 30.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/MsH5l46.jpeg', 'activo', 'detalles-personalizados-1', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(2, 'Detalle Personalizado 2', 'Detalle personalizado con peluche de Stich Rosa, Dulces Variados y flores artificiales rosas - rojas ', 'peluche, flores, juguete, regalo, arreglo, detalle, dulces, infantil, personalizado, stich, rosa, nino, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 19.00, 28.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/zkgrxFy.jpeg', 'activo', 'detalles-personalizados-2', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(3, 'Detalle Personalizado 3', 'Detalle personalizado para pedido de enamoramiento con flores azules y liston de frase con detalles', 'flores, regalo, arreglo, azul, detalle, enamorados, pareja, personalizado, romance, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 14.00, 26.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/VVoqfq4.jpeg', 'activo', 'detalles-personalizados-3', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(4, 'Detalle Personalizado 4', 'Detalle personalizado con mensaje personalizado y nombre completo con flores amarillas y forrado especial', 'flores, regalo, arreglo, detalle, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 15.00, 26.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/1O09Fw0.jpeg', 'activo', 'detalles-personalizados-4', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(5, 'Detalle Personalizado 5', 'Detalle personalizado con Gatito rosa, dulces variados con flores rosas - rojas', 'peluche, flores, juguete, regalo, arreglo, detalle, dulces, gatito, infantil, personalizado, rosa, nino, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 18.00, 29.00, 0.00, NULL, 16, 1, 1, 'https://i.imgur.com/RupYOeX.jpeg', 'activo', 'detalles-personalizados-5', '2025-10-04 04:07:25', '2026-08-30 18:53:39'),
(6, 'Detalle Personalizado 6', 'Detalle personalizado con Osito de camisa, dulces variados con una cerveza cuzqueña mini y forrado especial', 'peluche, juguete, regalo, adulto, cerveza, detalle, dulces, infantil, osito, personalizado, nino, edad:10, edad:20, joven, edad:30, edad:40', 22.00, 38.00, 0.00, NULL, 27, 1, 1, 'https://i.imgur.com/YCxjYH8.jpeg', 'activo', 'detalles-personalizados-6', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(7, 'Detalle Personalizado 7', 'Detalle personalizado de HotWheels con flores azules - blancas y liston azul', 'flores, juguete, regalo, arreglo, auto, azul, detalle, hotwheels, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 22.00, 38.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/cwuFeGu.jpeg', 'activo', 'detalles-personalizados-7', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(8, 'Detalle Personalizado 8', 'Detalle personalizado con cerveza corona, mensaje personalizado, flores azules con detalles personalizados', 'flores, regalo, adulto, arreglo, azul, cerveza, detalle, personalizado, mujer, joven, edad:20, edad:30, edad:40, edad:50, edad:60', 20.00, 32.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/qqX4fdT.jpeg', 'activo', 'detalles-personalizados-8', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(9, 'Detalle Personalizado 9', 'Detalle personalizado con mensaje personalizado y flores rosas - rojas', 'flores, regalo, arreglo, detalle, personalizado, rosa, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 12.00, 24.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/BdWLEqS.png', 'activo', 'detalles-personalizados-9', '2025-10-04 04:07:25', '2026-09-05 18:57:58'),
(10, 'Detalle Personalizado 10', 'Detalle de Globos verdes con variedad de dulces de todo tipo y con adornos rojo y blanco', 'regalo, detalle, dulces, fiesta, globos, personalizado, joven, adulto, edad:20, edad:30, edad:40', 16.00, 28.00, 0.00, NULL, 30, 1, 1, 'https://i.imgur.com/BdTunkn.jpeg', 'activo', 'detalles-personalizados-10', '2025-10-16 04:17:26', '2026-09-01 16:24:18'),
(11, 'Detalle Personalizado 11', 'Detalle de Globo con cinta morada, con el interior de peluche Morado con detalles blancos', 'peluche, juguete, regalo, detalle, fiesta, globos, infantil, personalizado, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 19.00, 32.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/O2fXkxQ.jpeg', 'activo', 'detalles-personalizados-11', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(12, 'Detalle Personalizado 12', 'Detalle Personalizado con fotografias de parejas, dulces variados de todo tipo y peluche de pinguino', 'peluche, juguete, regalo, detalle, dulces, foto, infantil, pareja, personalizado, romance, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 18.00, 32.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/n2x4uoC.jpeg', 'activo', 'detalles-personalizados-12', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(13, 'Detalle Personalizado 13', 'Detalle con globos de distintas formas y con cajita de dulces, cerveza cuzqueña mini y peluche de cerdito - tiburon', 'peluche, caja, cajita, juguete, regalo, adulto, cerveza, detalle, dulces, fiesta, globos, personalizado, nino, infantil, edad:10, edad:20, joven, edad:30, edad:40', 23.00, 38.00, 0.00, NULL, 18, 1, 1, 'https://i.imgur.com/1sMugxD.jpeg', 'activo', 'detalles-personalizados-13', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(14, 'Detalle Personalizado 14', 'Detalle Personalizado con tematica de Alianza Lima con productos variados en caja circular con cinta blanca', 'caja, cajita, regalo, alianza, deporte, detalle, futbol, personalizado, joven, adulto, edad:20, edad:30, edad:40', 22.00, 33.00, 0.00, NULL, 21, 1, 1, 'https://i.imgur.com/mgDcdTo.jpeg', 'activo', 'detalles-personalizados-14', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(15, 'Detalle Personalizado 15', 'Detalle con cajita circular con cinta azul con productos variados y osito marron', 'peluche, caja, cajita, juguete, regalo, azul, detalle, infantil, osito, personalizado, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 18.00, 27.00, 0.00, NULL, 30, 1, 1, 'https://i.imgur.com/48jYAFG.jpeg', 'activo', 'detalles-personalizados-15', '2025-10-16 04:17:26', '2026-09-01 16:24:18'),
(16, 'Detalle Personalizado 16', 'Detalle con tematica futbol colores azul y blanco, con productos variados y osito de ovejita', 'peluche, juguete, regalo, azul, detalle, infantil, personalizado, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 20.00, 33.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/usKlR8q.jpeg', 'activo', 'detalles-personalizados-16', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(17, 'Detalle Personalizado 17', 'Detalle con mensaje personalizado, bolsa roja con mica transparente y arreglo floral de margarita con fondo negro y peluche de oso', 'peluche, flores, bolso, juguete, regalo, accesorio, arreglo, detalle, infantil, moda, personalizado, nino, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 18.00, 32.00, 0.00, NULL, 32, 1, 1, 'https://i.imgur.com/ni5iNy7.jpeg', 'activo', 'detalles-personalizados-17', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(18, 'Detalle Personalizado 18', 'Detalle Personalizado para el dia del hombre con tematica de Hootwheels en celeste y azul', 'regalo, azul, detalle, personalizado, hombre, caballero, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 16.00, 24.00, 0.00, NULL, 2, 1, 1, 'https://i.imgur.com/cRyHnel.jpeg', 'activo', 'detalles-personalizados-18', '2025-10-16 04:17:26', '2026-09-06 17:46:57'),
(19, 'Detalle Personalizado 19', 'Detalle Personalizado para el dia del hombre en cajita blanco y negro, con gaseosa personalizada, hotwheels, dulces y osito pequeño', 'peluche, caja, cajita, juguete, regalo, auto, detalle, dulces, hotwheels, infantil, personalizado, nino, edad:10, edad:20, hombre, caballero, adulto, edad:30, edad:40, edad:50, edad:60', 19.00, 26.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/cWNMU0X.jpeg', 'activo', 'detalles-personalizados-19', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(20, 'Detalle Personalizado 20', 'Detalle Personalizado para el dia del hombre con tematica de Hotwheels con 3 unidades en cinta azul formal', 'juguete, regalo, auto, azul, detalle, hotwheels, personalizado, hombre, caballero, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 20.00, 30.00, 0.00, NULL, 30, 1, 1, 'https://i.imgur.com/6tvtlha.jpeg', 'activo', 'detalles-personalizados-20', '2025-10-16 04:17:26', '2026-09-01 16:24:18'),
(21, 'Detalle Personalizado 21', 'Detalle Personalizado para el dia del hombre con tematica de Hotwheels de coleccion, mensaje personalizado con detalle de periodico', 'juguete, regalo, auto, detalle, hotwheels, personalizado, hombre, caballero, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 22.00, 32.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/yZUD2wB.jpeg', 'activo', 'detalles-personalizados-21', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(22, 'Detalle Personalizado 22', 'Detalle con globos de distintas formas y colores, con cajita de dulces, cerveza cuzqueña mini y peluche de cerdito - tiburon', 'peluche, caja, cajita, juguete, regalo, adulto, cerdita, cerveza, detalle, dulces, fiesta, personalizado, nino, infantil, edad:10, edad:20, joven, edad:30, edad:40', 23.00, 36.00, 0.00, NULL, 18, 1, 1, 'https://i.imgur.com/tgLeXyV.jpeg', 'activo', 'detalles-personalizados-22', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(23, 'Detalle Personalizado 23', 'Detalle Personalizado con globo especial, dulces variados y 3 cervezas coronas en cajita con detalles verde claro', 'caja, cajita, regalo, adulto, cerveza, detalle, dulces, fiesta, globos, personalizado, joven, edad:20, edad:30, edad:40', 21.00, 30.00, 0.00, NULL, 20, 1, 1, 'https://i.imgur.com/rCEIbka.jpeg', 'activo', 'detalles-personalizados-23', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(24, 'Detalle Personalizado 24', 'Detalle Personalizado para el dia del padre con tematica de Alianza Lima con dulces variados y cerveza corona', 'regalo, adulto, alianza, cerveza, deporte, detalle, dulces, futbol, personalizado, joven, edad:20, edad:30, edad:40', 18.00, 26.00, 0.00, NULL, 31, 1, 1, 'https://i.imgur.com/F78neeT.jpeg', 'activo', 'detalles-personalizados-24', '2025-10-16 04:17:26', '2026-09-05 18:57:58'),
(25, 'Detalle Personalizado 25', 'Detalles variados con cajitas personalizadas con dulces al interior, eleccion a su gusto', 'caja, cajita, regalo, detalle, dulces, personalizado, joven, adulto, edad:20, edad:30, edad:40', 14.00, 20.00, 0.00, NULL, 30, 1, 1, 'https://i.imgur.com/kuFrO2J.jpeg', 'activo', 'detalles-personalizados-25', '2025-10-16 04:17:26', '2026-09-01 16:24:18'),
(26, 'Flores Personalizadas 1', 'Flores Personalizadas amarillas con centro rojo de princesa con detalles dorados y mensaje personalizado', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 12.00, 18.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/Y0pxvC7.jpeg', 'activo', 'flores-personalizadas-1', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(27, 'Flores Personalizadas 2', 'Flores Personalizadas con tematica Winnie Poh con flores rojas y amarillas con detalles rojos y mensaje personalizado', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 15.00, 19.00, 0.00, NULL, 32, 2, 2, 'https://i.imgur.com/mXLtfhU.jpeg', 'activo', 'flores-personalizadas-2', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(28, 'Flores Personalizadas 3', 'Flores Personalizadas color amarillo intenso con flores amarillas y detalles plomos con mensaje personalizado', 'flores,regalo,arreglo,detalle,florales,personalizado,mujer,joven,adulto,edad:20,edad:30,edad:40,edad:50,edad:60', 12.00, 17.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/28jCsWO.jpeg', 'activo', 'flores-personalizadas-3', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(29, 'Flores Personalizadas 4', 'Flores Personalizadas con tematica de margaritas con detalles rojos', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 10.00, 16.00, 0.00, NULL, 32, 2, 2, 'https://i.imgur.com/akC11ah.jpeg', 'activo', 'flores-personalizadas-4', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(30, 'Flores Personalizadas 5', 'Flores Personalizadas con tematica de margaritas y detalles blanco y dorado con mensaje personalizado', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 8.00, 14.00, 0.00, NULL, 18, 2, 2, 'https://i.imgur.com/JZ9xylm.jpeg', 'activo', 'flores-personalizadas-5', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(31, 'Flores Personalizadas 6', 'Flores Personalizadas amarillas con centro rojo de princesa con detalles dorados y mensaje personalizado con fotos personalizadas impresas', 'flores, regalo, arreglo, detalle, florales, foto, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 14.00, 20.00, 0.00, NULL, 20, 2, 2, 'https://i.imgur.com/QI02QTQ.jpeg', 'activo', 'flores-personalizadas-6', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(32, 'Flores Personalizadas 7', 'Flor Personalizada de color rojo dentro de globo con detalles en balde rojo', 'flores, regalo, arreglo, detalle, fiesta, florales, globos, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 10.00, 16.00, 0.00, NULL, 30, 2, 2, 'https://i.imgur.com/xOqGK0E.jpeg', 'activo', 'flores-personalizadas-7', '2025-10-16 04:50:02', '2026-09-01 16:24:18'),
(33, 'Flores Personalizadas 8', 'Flor Personalizada de color blanco y violeta dentro de globo con detalles violetas en balde blanco', 'flores, regalo, arreglo, detalle, fiesta, florales, globos, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 12.00, 18.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/mKRLQPJ.jpeg', 'activo', 'flores-personalizadas-8', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(34, 'Flores Personalizadas 9', 'Flores Personalizadas con globos variados en cajita circular con cinta rosada con regalo collar y peluche Koala y dulces', 'peluche, flores, caja, cajita, juguete, regalo, arreglo, detalle, dulces, fiesta, florales, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 22.00, 31.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/830UXBN.jpeg', 'activo', 'flores-personalizadas-9', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(35, 'Flores Personalizadas 10', 'Flores Personalizadas de colores rojo dentro de globo con detalles en balde rojo', 'flores, regalo, arreglo, detalle, fiesta, florales, globos, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 16.00, 22.00, 0.00, NULL, 30, 2, 2, 'https://i.imgur.com/oYqOTfs.jpeg', 'activo', 'flores-personalizadas-10', '2025-10-16 04:50:02', '2026-09-01 16:24:18'),
(36, 'Flores Personalizadas 11', 'Flores Personalizadas Rosas en cajita con cinta y globos dorados con peluche de oso pequeño, dulces variados y champagne pequeño', 'peluche, flores, caja, cajita, juguete, regalo, arreglo, detalle, dulces, fiesta, florales, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 22.00, 34.00, 0.00, NULL, 19, 2, 2, 'https://i.imgur.com/lRzO0Sm.jpeg', 'activo', 'flores-personalizadas-11', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(37, 'Flores Personalizadas 12', 'Flor Personalizada tematica margarita con detalle en blanco', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 10.00, 16.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/mEd6PRL.jpeg', 'activo', 'flores-personalizadas-12', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(38, 'Flores Personalizadas 13', 'Flor Personalizada tematica margarita con detalle en verde y blanco con letras', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 12.00, 17.00, 0.00, NULL, 31, 2, 2, 'https://i.imgur.com/FubA0BC.jpeg', 'activo', 'flores-personalizadas-13', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(39, 'Flores Personalizadas 14', 'Flor Personalizada tematica margarita con detalle en rojo y fondo de periodico', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 14.00, 19.00, 0.00, NULL, 32, 2, 2, 'https://i.imgur.com/UhAfqle.jpeg', 'activo', 'flores-personalizadas-14', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(40, 'Flores Personalizadas 15', 'Flores Personalizadas de color amarillo y rojo con detalles dorados y rojos', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 8.00, 14.00, 0.00, NULL, 32, 2, 2, 'https://i.imgur.com/ClNO5T8.jpeg', 'activo', 'flores-personalizadas-15', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(41, 'Flores Personalizadas 16', 'Flores Personalizadas de color amarillo intenso con detalles amarillos pasteles y mensaje personalizado', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 11.00, 16.00, 0.00, NULL, 18, 2, 2, 'https://i.imgur.com/AaUz7aP.jpeg', 'activo', 'flores-personalizadas-16', '2025-10-16 04:50:02', '2026-09-05 18:57:58'),
(42, 'Flores Personalizadas 17', 'Flor Personalizada tematica margarita con detalle en rojo pastel y fondo de letras', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 12.00, 17.00, 0.00, NULL, 20, 2, 2, 'https://i.imgur.com/ZZbb2gB.jpeg', 'activo', 'flores-personalizadas-17', '2025-10-16 04:50:02', '2026-08-30 18:53:41'),
(43, 'Flores Personalizadas 18', 'Flor Personalizada tematica margarita con detalle en blanco violetizado', 'flores, regalo, arreglo, detalle, florales, personalizado, mujer, joven, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 9.00, 13.00, 0.00, NULL, 30, 2, 2, 'https://i.imgur.com/LEMYaOo.jpeg', 'activo', 'flores-personalizadas-18', '2025-10-16 04:50:02', '2026-09-01 16:24:18'),
(44, 'Carteles Personalizados 1', 'Carteles Personalizados de cumpleaños con mensaje personalizado y detalles con letras de colores', 'cartel, regalo, carteles, cumpleaños, fiesta, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 12.00, 18.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/XrcPqD2.jpeg', 'activo', 'carteles-personalizados-1', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(45, 'Carteles Personalizados 2', 'Cajita con tematica de Naruto con cartelito personalizado y fotos, dulces variados y camiseta al gusto del cliente', 'cartel, caja, cajita, regalo, carteles, detalle, dulces, foto, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 36.00, 54.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/XIeJh0J.jpeg', 'activo', 'carteles-personalizados-2', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(46, 'Carteles Personalizados 3', 'Cajita con cartelito personalizado y taza personalizada con margaritas doradas', 'cartel, caja, cajita, regalo, carteles, detalle, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 24.00, 30.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/3ojXh4e.jpeg', 'activo', 'carteles-personalizados-3', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(47, 'Carteles Personalizados 4', 'Cajita con cartelito personalizado con flores amarillas y dulces amarillos con joya y su cajita', 'flores, cartel, caja, cajita, regalo, arreglo, carteles, detalle, dulces, mensaje, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 32.00, 48.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/YpDOnUH.jpeg', 'activo', 'carteles-personalizados-4', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(48, 'Carteles Personalizados 5', 'Cajita personalizada dorada con cartelito personalizado, flores variadas y dulces amarillos', 'flores, cartel, caja, cajita, regalo, arreglo, carteles, detalle, dulces, mensaje, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 22.00, 28.00, 0.00, NULL, 18, 3, 4, 'https://i.imgur.com/J0pbElm.jpeg', 'activo', 'carteles-personalizados-5', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(49, 'Carteles Personalizados 6', 'Cajita personalizada con cartelito personalizado, dulces variados con detalle de Hotwheels', 'cartel, caja, cajita, juguete, regalo, auto, carteles, detalle, dulces, hotwheels, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 22.00, 29.00, 0.00, NULL, 20, 3, 4, 'https://i.imgur.com/LwE4oht.jpeg', 'activo', 'carteles-personalizados-6', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(50, 'Carteles Personalizados 7', 'Cajita personalizada con cartelito personalizado, dulces variados con mensaje personalizado y su Hotwheels', 'cartel, caja, cajita, juguete, regalo, auto, carteles, detalle, dulces, hotwheels, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 22.00, 30.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/zHvvF9f.jpeg', 'activo', 'carteles-personalizados-7', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(51, 'Carteles Personalizados 8', 'Cajita personalizada con cartelito personalizado, con globos y flores variadas, dulces dorados y osito pequeño', 'peluche, flores, cartel, caja, cajita, juguete, regalo, arreglo, carteles, detalle, dulces, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 25.00, 34.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/SabxbJP.jpeg', 'activo', 'carteles-personalizados-8', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(52, 'Carteles Personalizados 9', 'Cajita personalizada con cartelito personalizado, con globos y flores variadas, dulces variados y osito grande con polera', 'peluche, flores, cartel, caja, cajita, juguete, regalo, arreglo, carteles, detalle, dulces, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 30.00, 39.00, 0.00, NULL, 30, 3, 4, 'https://i.imgur.com/ZT5LBbx.jpeg', 'activo', 'carteles-personalizados-9', '2025-10-16 05:20:14', '2026-09-01 16:24:18'),
(53, 'Carteles Personalizados 10', 'Cajita circular personalizada con cartelito personalizado, cerveza cuzqueña mini, dulces variados y osito con gorra', 'peluche, cartel, caja, cajita, juguete, regalo, adulto, carteles, cerveza, detalle, dulces, personalizado, nino, infantil, edad:10, edad:20, joven, edad:30, edad:40', 26.00, 32.00, 0.00, NULL, 18, 3, 4, 'https://i.imgur.com/y9lPGOc.jpeg', 'activo', 'carteles-personalizados-10', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(54, 'Carteles Personalizados 11', 'Cajita personalizada con cartelito personalizado, con osito pequeño y cerveza china con dulces variados', 'peluche, cartel, caja, cajita, juguete, regalo, adulto, carteles, cerveza, detalle, dulces, personalizado, nino, infantil, edad:10, edad:20, joven, edad:30, edad:40', 28.00, 34.00, 0.00, NULL, 19, 3, 4, 'https://i.imgur.com/gCPkcGl.jpeg', 'activo', 'carteles-personalizados-11', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(55, 'Carteles Personalizados 12', 'Cajita personalizada con cartelito personalizado, osito rosado de Koala, dulces variados con detalles rosa y rojo', 'peluche, cartel, caja, cajita, juguete, regalo, carteles, detalle, dulces, infantil, mensaje, personalizado, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 24.00, 30.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/CLL880S.jpeg', 'activo', 'carteles-personalizados-12', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(56, 'Carteles Personalizados 13', 'Cajita personalizada con cartelito personalizado, con flores doradas y rojas, dulces sublime con osito pequeño y ropita con detalles rojos y dorados', 'peluche, flores, cartel, caja, cajita, juguete, regalo, arreglo, carteles, detalle, dulces, personalizado, nino, infantil, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 25.00, 34.00, 0.00, NULL, 31, 3, 4, 'https://i.imgur.com/0HoSBDT.jpeg', 'activo', 'carteles-personalizados-13', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(57, 'Carteles Personalizados 14', 'Copita personalizada con cartelito personalizado, con globo de letras rojo, dulces variados y detalles rosas', 'cartel, regalo, carteles, dulces, fiesta, globos, mensaje, personalizado, rosa, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 18.00, 24.00, 0.00, NULL, 30, 3, 4, 'https://i.imgur.com/XSHgm5e.jpeg', 'activo', 'carteles-personalizados-14', '2025-10-16 05:20:14', '2026-09-01 16:24:18'),
(58, 'Carteles Personalizados 15', 'Cajita personalizada con cartelito personalizado, globo especial verde con dulces y bebidas variados en detalles verde', 'cartel, caja, cajita, regalo, carteles, detalle, dulces, fiesta, globos, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 20.00, 30.00, 0.00, NULL, 16, 3, 4, 'https://i.imgur.com/PtQtC13.jpeg', 'activo', 'carteles-personalizados-15', '2025-10-16 05:20:14', '2026-08-30 18:53:41'),
(59, 'Carteles Personalizados 16', 'Cajita circular personalizada con cartelito personalizado, globo transparente con dulce de forma de corazón y globos, detalles rojos', 'cartel, caja, cajita, regalo, carteles, detalle, dulces, fiesta, globos, mensaje, personalizado, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 18.00, 24.00, 0.00, NULL, 19, 3, 4, 'https://i.imgur.com/EsdqoF3.jpeg', 'activo', 'carteles-personalizados-16', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(60, 'Carteles Personalizados 17', 'Cajita personalizada con cartelito personalizado, con globo azul transparente y dulces variados con cuzqueña mini y detalles azules', 'cartel, caja, cajita, regalo, adulto, azul, carteles, cerveza, detalle, dulces, fiesta, globos, nino, infantil, edad:10, edad:20, joven, edad:30, edad:40', 28.00, 36.00, 0.00, NULL, 20, 3, 4, 'https://i.imgur.com/7E6g8w1.jpeg', 'activo', 'carteles-personalizados-17', '2025-10-16 05:20:14', '2026-08-30 18:53:41'),
(61, 'Carteles Personalizados 18', 'Copita personalizada con cartelito personalizado, con globo especial rojo con dulces variados', 'cartel, regalo, carteles, dulces, fiesta, globos, mensaje, personalizado, nino, infantil, edad:10, edad:20', 18.00, 24.00, 0.00, NULL, 42, 3, 4, 'https://i.imgur.com/T6I2ltu.jpeg', 'activo', 'carteles-personalizados-18', '2025-10-16 05:20:14', '2026-09-05 18:57:58'),
(62, 'Bolso Verde', 'Bolso verde pastel para mujer', 'bolso, accesorio, moda, verde, regalo, mujer, edad:20, edad:30, edad:40', 16.00, 22.00, 0.00, NULL, 22, 4, 3, 'https://i.imgur.com/i9TF3JX.jpeg', 'activo', 'perfumeria-1', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(63, 'Bolso Verde Militar', 'Bolso verde militar para hombre', 'bolso, accesorio, moda, militar, verde, regalo, hombre, caballero, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 15.00, 20.00, 0.00, NULL, 30, 4, 3, 'https://i.imgur.com/35Z5QbX.jpeg', 'activo', 'perfumeria-2', '2025-10-16 05:40:17', '2026-09-01 16:24:18'),
(64, 'Bolso Cuero', 'Bolso de Cuero dos colores con detalle de osito', 'bolso, accesorio, cuero, moda, regalo, joven, adulto, edad:20, edad:30, edad:40', 22.00, 28.00, 0.00, NULL, 30, 4, 3, 'https://i.imgur.com/15dJ6ug.jpeg', 'activo', 'perfumeria-3', '2025-10-16 05:40:17', '2026-09-01 16:24:18'),
(65, 'Bolsa Cuero Crema', 'Bolso de cuero de color crema con detalle de oso grande', 'bolso, accesorio, bolsa, crema, cuero, moda, regalo, joven, adulto, edad:20, edad:30, edad:40', 23.00, 29.00, 0.00, NULL, 32, 4, 3, 'https://i.imgur.com/ZUInleX.jpeg', 'activo', 'perfumeria-4', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(66, 'Bolso Piton Bear', 'Bolso de Cuero dos colores con detalle de Piton Bear', 'bolso, accesorio, bear, moda, piton, regalo, joven, adulto, edad:20, edad:30, edad:40', 24.00, 35.00, 0.00, NULL, 18, 4, 3, 'https://i.imgur.com/qATC0jc.jpeg', 'activo', 'perfumeria-5', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(67, 'Bolso Piton Duo', 'Bolso de Cuero con detalle de Piton Bear Duo de Ositos', 'bolso, accesorio, moda, piton, regalo, joven, adulto, edad:20, edad:30, edad:40', 25.00, 36.00, 0.00, NULL, 22, 4, 3, 'https://i.imgur.com/MViBb95.jpeg', 'activo', 'perfumeria-6', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(68, 'Mochila Piton', 'Mochila de Piton con detalle de osito con lentes', 'bolso, accesorio, mochila, moda, piton, regalo, joven, adulto, edad:20, edad:30, edad:40', 30.00, 42.00, 0.00, NULL, 31, 4, 3, 'https://i.imgur.com/c7xdjE6.jpeg', 'activo', 'perfumeria-7', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(69, 'Bolso Mano Piton', 'Bolso de mano modelo Piton con detalle pequeño de oso', 'bolso, accesorio, mano, moda, piton, regalo, joven, adulto, edad:20, edad:30, edad:40', 21.00, 34.00, 0.00, NULL, 30, 4, 3, 'https://i.imgur.com/WxjnMwF.jpeg', 'activo', 'perfumeria-8', '2025-10-16 05:40:17', '2026-09-01 16:24:18'),
(70, 'Cajita Personalizada', 'Cajita personalizada con detalles dorados, osito pequeño y dulces variados', 'caja, cajita, regalo, detalle, dulces, especial, chocolate, personalizado, joven, adulto, edad:20, edad:30, edad:40', 18.00, 26.00, 0.00, NULL, 30, 4, 3, 'https://i.imgur.com/moHI1nS.jpeg', 'activo', 'perfumeria-9', '2025-10-16 05:40:17', '2026-09-01 16:24:18'),
(71, 'Perfume Dance', 'Perfume Dance de Shakira', 'perfume, fragancia, perfumeria, dance, regalo, mujer, edad:20, edad:30, edad:40', 23.00, 38.00, 0.00, NULL, 17, 4, 3, 'https://i.imgur.com/T4GDI86.jpeg', 'activo', 'perfumeria-10', '2025-10-16 05:40:17', '2026-08-30 18:53:42'),
(72, 'Cajita Personalizada Circular', 'Cajita Personalizada Circular, con osito mediano, dulces variados y detalles rojo, dorado', 'caja, cajita, regalo, circular, detalle, dulces, osito, personalizado, joven, adulto, edad:20, edad:30, edad:40', 20.00, 28.00, 0.00, NULL, 19, 4, 3, 'https://i.imgur.com/nXXlfZW.jpeg', 'activo', 'perfumeria-11', '2025-10-16 05:40:17', '2026-08-30 18:53:42'),
(73, 'Detalle Cerdito', 'Detalle blanco con dorado con flores rosas, peluche de cerdito rosa con capucha de tiburon', 'peluche, flores, juguete, regalo, arreglo, cerdita, cerdito, detalle, infantil, rosa, tiburon, nino, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 21.00, 28.00, 0.00, NULL, 31, 4, 3, 'https://i.imgur.com/INitiUa.jpeg', 'activo', 'perfumeria-12', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(74, 'Cajita Peluche', 'Cajita circular con globo transparente con el interior de oso rosado pastel', 'peluche, caja, cajita, juguete, regalo, detalle, fiesta, globos, infantil, rosa, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 16.00, 22.00, 0.00, NULL, 34, 4, 3, 'https://i.imgur.com/4TbqWDC.jpeg', 'activo', 'perfumeria-13', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(75, 'Caja Sorpresa Negra', 'Caja Sorpresa Negra con interior secreto de Hot wheels', 'caja, cajita, juguete, regalo, auto, detalle, hotwheels, negra, sorpresa, nino, infantil, edad:10, edad:20, joven, adulto, edad:30, edad:40', 25.00, 38.00, 0.00, NULL, 31, 4, 3, 'https://i.imgur.com/Tk9ey3J.jpeg', 'activo', 'perfumeria-14', '2025-10-16 05:40:17', '2026-09-05 18:57:58'),
(76, 'Cajita Circular Rosa', 'Cajita Circular Rosa con peluche de Koala', 'peluche, caja, cajita, juguete, regalo, circular, detalle, infantil, rosa, variado, koala, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 18.00, 25.00, 0.00, NULL, 22, 5, 5, 'https://i.imgur.com/hmCk7Im.jpeg', 'activo', 'variados-1', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(77, 'Peluche Stich', 'Peluche Stich Normal tamaño normal', 'peluche, juguete, regalo, infantil, stich, variados, orejas largas, orejon, nino, edad:10, edad:20', 10.00, 16.00, 0.00, NULL, 25, 5, 5, 'https://i.imgur.com/I0mb7tJ.jpeg', 'activo', 'variados-2', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(78, 'Peluche Stich Rosa', 'Peluche Stich Rosa tamaño normal', 'peluche, juguete, regalo, infantil, rosa, stich, variados, orejas largas, orejon, nino, edad:10, edad:20', 10.00, 16.00, 0.00, NULL, 24, 5, 5, 'https://i.imgur.com/EMvrnWs.jpeg', 'activo', 'variados-3', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(79, 'Cajita Pinguino', 'Cajita con globos personalizados, con dulces variados y peluche pinguino', 'peluche, caja, cajita, juguete, regalo, detalle, dulces, fiesta, globos, infantil, pinguino, variados, nino, edad:10, edad:20, joven, adulto, edad:30, edad:40', 14.00, 20.00, 0.00, NULL, 22, 5, 5, 'https://i.imgur.com/bJBc3La.jpeg', 'activo', 'variados-4', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(80, 'Relojes clasicos', 'Relojes clasicos en dorado y negro', 'reloj,regalo,accesorio,clasicos,variados', 10.00, 18.00, 0.00, NULL, 22, 5, 5, 'https://i.imgur.com/5R1bVHX.jpeg', 'activo', 'variados-5', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(81, 'Pulseras Metalizadas', 'Pulseras Metalizadas con funda negra', 'pulsera,regalo,accesorio,metalizadas,variados', 7.00, 14.00, 0.00, NULL, 25, 5, 5, 'https://i.imgur.com/w5OCoSb.jpeg', 'activo', 'variados-6', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(82, 'Pulseras Negras', 'Pulseras negras con metal negro metalizado', 'pulsera,regalo,accesorio,negras,variados', 6.00, 12.00, 0.00, NULL, 27, 5, 5, 'https://i.imgur.com/YB7lSp7.jpeg', 'activo', 'variados-7', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(83, 'Billetera Chicago', 'Billetera Chicago con funda negra y roja', 'billetera, regalo, accesorio, caballero, chicago, marca, variados, hombre, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 11.00, 20.00, 0.00, NULL, 46, 5, 5, 'https://i.imgur.com/4FuocIX.jpeg', 'activo', 'variados-8', '2025-10-16 06:46:05', '2026-09-07 18:11:22'),
(84, 'Billetera Renzo Costa', 'Billetera Renzo Costa funda negra', 'billetera, regalo, accesorio, caballero, marca, renzo, costa, variados, hombre, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 44.00, 70.00, 0.00, NULL, 33, 5, 5, 'https://i.imgur.com/cQwVUIT.jpeg', 'activo', 'variados-9', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(85, 'Billetera Chicago Dorado', 'Billetera Chicago con funda negra y dorada', 'billetera,regalo,accesorio,caballero,chicago,dorado,marca,variados,hombre,adulto,edad:20,edad:30,edad:40,edad:50,edad:60', 15.00, 22.00, 0.00, NULL, 39, 5, 5, 'https://i.imgur.com/IKA88Xf.jpeg', 'activo', 'variados-10', '2025-10-16 06:46:05', '2026-09-06 21:37:22'),
(86, 'Billetera Chicago Clasica', 'Billetera Chicago con funda clasica', 'billetera, regalo, accesorio, caballero, chicago, clasica, marca, variados, hombre, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 16.00, 24.00, 0.00, NULL, 24, 5, 5, 'https://i.imgur.com/OMeOB96.jpeg', 'activo', 'variados-11', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(87, 'Billetera Puma', 'Billetera Puma color marron cuero', 'billetera, regalo, accesorio, caballero, marca, puma, variados, hombre, adulto, edad:20, edad:30, edad:40, edad:50, edad:60', 18.00, 28.00, 0.00, NULL, 30, 5, 5, 'https://i.imgur.com/awUkCBo.jpeg', 'activo', 'variados-12', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(88, 'Cerdita Tiburon', 'Detalle con peluche de cerdita tiburon color rosado con flores rosadas', 'peluche, flores, juguete, regalo, arreglo, cerdita, detalle, infantil, rosa, tiburon, variados, nino, edad:10, edad:20, mujer, joven, adulto, edad:30, edad:40, edad:50, edad:60', 18.00, 25.00, 0.00, NULL, 34, 5, 5, 'https://i.imgur.com/fxppNkL.jpeg', 'activo', 'variados-13', '2025-10-16 06:46:05', '2026-09-05 18:57:58'),
(89, 'Cajita Dulcera', 'Cajita circular con globos personalizados y dulces color rojo y dorado', 'caja, cajita, regalo, detalle, dulcera, dulces, fiesta, globos, personalizado, novia, esposa, variados, mujer, edad:20, edad:30, edad:40, joven, adulto', 19.00, 26.00, 0.00, NULL, 59, 5, 5, 'https://i.imgur.com/vBuRH3k.jpeg', 'activo', 'variados-14', '2025-10-16 06:46:05', '2026-09-06 15:45:20'),
(90, 'Caja Hot Wheels', 'Caja hot wheels con dulces y bebidas variadas con carrito Hot wheels', 'caja,cajita,juguete,regalo,auto,detalle,dulces,hotwheels,variados,nino,infantil,edad:10,edad:20,joven,adulto,edad:30,edad:40', 20.00, 36.00, 0.00, NULL, 47, 5, 5, 'https://i.imgur.com/d0fY7ej.jpeg', 'activo', 'variados-15', '2025-10-16 06:46:05', '2026-09-06 21:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `producto_imagenes`
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
-- Table structure for table `promociones`
--

CREATE TABLE `promociones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(120) NOT NULL DEFAULT 'Campaña',
  `texto_cinta` varchar(255) DEFAULT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promociones`
--

INSERT INTO `promociones` (`id`, `titulo`, `texto_cinta`, `porcentaje`, `fecha_inicio`, `fecha_fin`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Campaña de temporada', 'Por el mes de la primavera 10% en toda la tienda! Aprovecha!!', 10.00, '2026-09-06', '2026-09-06', 1, '2026-09-02 00:12:47', '2026-09-06 21:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
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
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `contacto`, `telefono`, `email`, `direccion`, `created_at`) VALUES
(1, 'Detalles Lima S.A.C.', 'María Gonzales', '987654321', 'detalleslima.sac@gmail.com', 'Av. Arequipa 1200, Lima', '2025-10-04 04:07:25'),
(2, 'Floreria Orquidea', 'Carlos Paredes', '945871230', 'ventas_floreria.orquidea@gmail.com', 'Jr. Huallaga 221, Lima', '2025-10-04 04:07:25'),
(3, 'Perfumeria Fragance', 'Rosa Núñez', '910567843', 'floreria.fragance_hyo@gmail.com', 'Av. Los Incas 504, Huancayo', '2025-10-04 04:07:25'),
(4, 'Peluches P\'Luche', 'Ivan Rojas', '965436789', 'peluches_pluche@gmail.com', 'Jr. Huaytapallana 1267, Huanuco', '2025-10-16 03:24:04'),
(5, 'Tienda D\'Todo', 'Paolo Lopez', '945673123', 'tiendas.dtodo@gmail.com', 'Av. Daniel Alcides 348, Cerro de Pasco', '2025-10-16 03:24:04'),
(7, 'Relojería Rolex', 'Roberto Molina', '917289021', 'relojeriarolex@gmail.com', 'Av. Huancayo 123', '2026-09-07 00:12:39');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'ADMIN'),
(2, 'SOPORTE'),
(3, 'STOCK'),
(4, 'VENTAS');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Table structure for table `users`
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
-- Indexes for dumped tables
--

--
-- Indexes for table `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `id_empleado` (`id_empleado`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indexes for table `asistente_feedback`
--
ALTER TABLE `asistente_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asistente_feedback_id_producto_voto_index` (`id_producto`,`voto`);

--
-- Indexes for table `asistente_logs`
--
ALTER TABLE `asistente_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_clientes_email` (`email`);

--
-- Indexes for table `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `idx_detalles_pedidos` (`id_pedido`,`id_producto`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_empleados_email` (`email`);

--
-- Indexes for table `empleado_rol`
--
ALTER TABLE `empleado_rol`
  ADD PRIMARY KEY (`id_empleado`,`id_rol`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `idx_inv_producto_fecha` (`id_producto`,`fecha`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedidos_cliente_fecha` (`id_cliente`,`fecha_pedido`);

--
-- Indexes for table `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `idx_productos_nombre` (`nombre`),
  ADD KEY `idx_productos_cat_nombre` (`id_categoria`,`nombre`);

--
-- Indexes for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asistente_feedback`
--
ALTER TABLE `asistente_feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `asistente_logs`
--
ALTER TABLE `asistente_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE;

--
-- Constraints for table `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditoria_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL;

--
-- Constraints for table `detalles_pedidos`
--
ALTER TABLE `detalles_pedidos`
  ADD CONSTRAINT `detalles_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Constraints for table `empleado_rol`
--
ALTER TABLE `empleado_rol`
  ADD CONSTRAINT `empleado_rol_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE,
  ADD CONSTRAINT `empleado_rol_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Constraints for table `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  ADD CONSTRAINT `inventario_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Constraints for table `pedido_estado_historial`
--
ALTER TABLE `pedido_estado_historial`
  ADD CONSTRAINT `pedido_estado_historial_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_estado_historial_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Constraints for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `producto_imagenes_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
