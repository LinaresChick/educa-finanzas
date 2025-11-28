-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-11-2025 a las 09:21:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_educativo_finanzas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rol` enum('Superadmin','Administrador','Colaborador','Padre','Estudiante','Docente') NOT NULL DEFAULT 'Estudiante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `password`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `rol`) VALUES
(1, 'Super Administrador', 'superadmin@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Superadmin'),
(2, 'Administrador 1', 'admin1@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Administrador'),
(3, 'Administrador 2', 'admin2@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Administrador'),
(4, 'Colaborador 1', 'colab1@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Colaborador'),
(5, 'Colaborador 2', 'colab2@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Colaborador'),
(6, 'Estudiante Primaria', 'estu_primaria@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-09-24 15:08:07', 'Estudiante'),
(7, 'Estudiante Secundaria', 'estu_secundaria@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-20 08:41:03', 'Estudiante'),
(8, 'Padre Familia 1', 'padre1@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Padre'),
(9, 'Padre Familia 2', 'padre2@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Padre'),
(10, 'Docente Primaria', 'docente1@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Docente'),
(11, 'Docente Secundaria', 'docente2@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 15:08:07', '2025-11-28 07:03:20', 'Docente'),
(12, 'MAYDA BRAVO TORRES', 'mayda.bravo@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 20:47:00', '2025-11-28 07:03:20', 'Docente'),
(13, 'JULIO CESAR CAVIEDES ALVAREZ23', 'julio.caviedes@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', '2025-09-24 20:47:17', '2025-11-28 07:03:20', 'Docente'),
(14, 'carlos', 'admin123@educa.edu', '$2y$10$WKuzNw2BTj.IF7DvOwafGeXTskB41mFjHwemMf6u95LFGuJ08a7.a', 'inactivo', '2025-11-28 07:24:05', '2025-11-28 08:13:37', 'Estudiante'),
(15, 'Pruebas', 'nikomclela1234@gmail.com', '$2y$10$oc7UOC53rPBIBoUlMdz64OSYnDUrUEzNob5jhLet/krAr29aiql0C', 'activo', '2025-11-28 07:30:48', '2025-11-28 08:10:47', 'Administrador');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
