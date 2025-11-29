-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-11-2025 a las 04:09:36
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
-- Estructura de tabla para la tabla `constancias`
--

CREATE TABLE `constancias` (
  `id_constancia` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `nombre_solicitante` varchar(150) NOT NULL,
  `dni_solicitante` varchar(20) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','pagado') NOT NULL DEFAULT 'pendiente',
  `id_pago` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `id_usuario`, `nombres`, `apellidos`, `dni`, `telefono`, `correo`, `especialidad`, `estado`, `fecha_creacion`) VALUES
(1, 12, 'MAYDA', 'BRAVO TORRES', NULL, NULL, NULL, 'Educación Primaria', 'activo', '2025-09-24 20:47:00'),
(2, 13, 'JULIO CESAR', 'CAVIEDES ALVAREZ', NULL, NULL, NULL, 'Educación Secundaria', 'activo', '2025-09-24 20:47:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_salon` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `mencion` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` enum('activo','inactivo','graduado') DEFAULT 'activo',
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado_pago` enum('pendiente','pagado','vencido') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `id_usuario`, `id_salon`, `nombres`, `apellidos`, `dni`, `mencion`, `fecha_nacimiento`, `direccion`, `telefono`, `estado`, `monto`, `fecha_vencimiento`, `estado_pago`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, NULL, 1, 'carlos jr', 'camacaro', '74859612', 'ff', '2025-09-02', '', '987654123', 'activo', 0.00, NULL, 'pendiente', '2025-10-19 05:36:19', '2025-10-19 05:36:19'),
(5, NULL, 1, 'LUIS PEDRO', 'LINARES ASCENCIO', '74852136', 'ff', '0000-00-00', 'Jr San Salvador', '983923774', 'activo', 0.00, NULL, 'pendiente', '2025-11-27 06:27:57', '2025-11-27 06:30:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante_padre`
--

CREATE TABLE `estudiante_padre` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_padre` int(11) NOT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiante_padre`
--

INSERT INTO `estudiante_padre` (`id`, `id_estudiante`, `id_padre`, `parentesco`, `fecha_creacion`) VALUES
(1, 1, 1, 'Padre', '2025-10-19 05:42:04'),
(2, 1, 12, 'Madre', '2025-11-27 06:31:57'),
(3, 5, 16, 'Tutor Legal', '2025-11-27 06:32:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_recibos`
--

CREATE TABLE `facturas_recibos` (
  `id_comprobante` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `tipo` enum('factura','recibo') NOT NULL,
  `numero` varchar(50) NOT NULL,
  `fecha_emision` date NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `igv` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `xml_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `estado` enum('generado','anulado') DEFAULT 'generado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grados`
--

CREATE TABLE `grados` (
  `id_grado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nivel` enum('Inicial','Primaria','Secundaria') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grados`
--

INSERT INTO `grados` (`id_grado`, `nombre`, `nivel`) VALUES
(1, '1er grado', 'Primaria'),
(2, '2do grado', 'Primaria'),
(3, '3er grado', 'Primaria'),
(4, '4to grado', 'Primaria'),
(5, '5to grado', 'Primaria'),
(6, '6to grado', 'Primaria'),
(7, '1er grado', 'Secundaria'),
(8, '2do grado', 'Secundaria'),
(9, '3er grado', 'Secundaria'),
(10, '4to grado', 'Secundaria'),
(11, '5to grado', 'Secundaria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padres`
--

CREATE TABLE `padres` (
  `id_padre` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `relacion` enum('Padre','Madre','Tutor') NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `padres`
--

INSERT INTO `padres` (`id_padre`, `id_usuario`, `nombres`, `apellidos`, `dni`, `telefono`, `correo`, `direccion`, `relacion`, `estado`, `fecha_creacion`) VALUES
(1, NULL, 'carlos21', 'camacaro', '74859612', '987654123', '', '', 'Padre', 'activo', '2025-10-19 01:21:17'),
(12, NULL, 'LUIS', 'LINARES ASCENCIO', '', '983923774', 'nikomclela1234@gmail.com', 'Jr San Salvador', 'Madre', 'activo', '2025-11-27 05:56:17'),
(15, NULL, 'CARLOS', 'MACARIOP', '74852145', '951478523', 'SANTIAGO@GMAIL.COM', 'jR. cARLOS', 'Madre', 'activo', '2025-11-27 05:59:22'),
(16, NULL, 'Carlos Miguel', 'Pedregal Carloil', '74851245', '987654321', '', '', 'Tutor', 'activo', '2025-11-27 06:15:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `concepto` varchar(200) NOT NULL,
  `banco` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta') DEFAULT 'efectivo',
  `fecha_pago` date NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `aumento` decimal(10,2) DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `foto_baucher` varchar(255) NOT NULL,
  `usuario_registro` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Superadmin', 'Rol del super administrador'),
(2, 'Administrador', 'Rol de administrador'),
(3, 'Colaborador', 'Rol de colaborador'),
(4, 'Padre', 'Padre de familia'),
(5, 'Estudiante', 'Estudiante'),
(6, 'Docente', 'Docente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salones`
--

CREATE TABLE `salones` (
  `id_salon` int(11) NOT NULL,
  `id_grado` int(11) NOT NULL,
  `id_seccion` int(11) NOT NULL,
  `id_docente` int(11) DEFAULT NULL,
  `anio` year(4) NOT NULL,
  `cupo_maximo` int(11) DEFAULT 30,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `salones`
--

INSERT INTO `salones` (`id_salon`, `id_grado`, `id_seccion`, `id_docente`, `anio`, `cupo_maximo`, `estado`) VALUES
(1, 1, 1, 1, '2025', 30, 'activo'),
(2, 7, 28, 2, '2025', 30, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id_seccion` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id_seccion`, `nombre`, `descripcion`) VALUES
(1, 'Maripositas', '1er grado - Maripositas'),
(2, 'Sabios', '1er grado - Sabios'),
(3, 'Capibaras', '1er grado - Capibaras'),
(4, 'Hormiguitas', '1er grado - Hormiguitas'),
(5, 'Arcoiris', '1er grado - Arcoiris'),
(6, 'Innovadores', '1er grado - Innovadores'),
(7, 'Piloto', '1er grado - Piloto'),
(8, 'Ositos', '2do grado - Ositos'),
(9, 'Palomitas', '2do grado - Palomitas'),
(10, 'Oruguitas', '2do grado - Oruguitas'),
(11, 'Conejillos', '2do grado - Conejillos'),
(12, 'Piloto', '2do grado - Piloto'),
(13, 'Semillitas', '3er grado - Semillitas'),
(14, 'Abejitas', '3er grado - Abejitas'),
(15, 'Huellitas', '3er grado - Huellitas'),
(16, 'Estrellitas', '3er grado - Estrellitas'),
(17, 'Leones', '4to grado - Leones'),
(18, 'Triunfadores', '4to grado - Triunfadores'),
(19, 'Halcones', '4to grado - Halcones'),
(20, 'Nuevos', '4to grado - Nuevos'),
(21, 'Genios', '5to grado - Genios'),
(22, 'Generosos', '5to grado - Generosos'),
(23, 'Angelitos', '5to grado - Angelitos'),
(24, 'Alegres', '6to grado - Alegres'),
(25, 'Poderosos', '6to grado - Poderosos'),
(26, 'Honestos', '6to grado - Honestos'),
(27, 'Piloto', '6to grado - Piloto'),
(28, 'Curiosos', '1er grado Secundaria - Curiosos'),
(29, 'Dichosos', '1er grado Secundaria - Dichosos'),
(30, 'Forjadores', '1er grado Secundaria - Forjadores'),
(31, 'Nuevo', '1er grado Secundaria - Nuevo'),
(32, 'Increibles', '2do grado Secundaria - Increibles'),
(33, 'Amables', '2do grado Secundaria - Amables'),
(34, 'Indestructibles', '2do grado Secundaria - Indestructibles'),
(35, 'Lideres', '3er grado Secundaria - Lideres'),
(36, 'Campeones', '3er grado Secundaria - Campeones'),
(37, 'Proceres', '4to grado Secundaria - Proceres'),
(38, 'Heroes', '4to grado Secundaria - Heroes'),
(39, 'Unico', '5to grado Secundaria - Unico');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_roles`
--

INSERT INTO `usuarios_roles` (`id`, `id_usuario`, `id_rol`, `activo`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 1),
(3, 3, 2, 1),
(4, 4, 3, 1),
(5, 5, 3, 1),
(6, 6, 5, 1),
(7, 7, 5, 1),
(8, 8, 4, 1),
(9, 9, 4, 1),
(10, 10, 6, 1),
(11, 11, 6, 1),
(12, 12, 6, 1),
(13, 13, 6, 1),
(16, 14, 2, 0),
(17, 15, 2, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `constancias`
--
ALTER TABLE `constancias`
  ADD PRIMARY KEY (`id_constancia`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_pago` (`id_pago`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_salon` (`id_salon`);

--
-- Indices de la tabla `estudiante_padre`
--
ALTER TABLE `estudiante_padre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_estudiante_padre` (`id_estudiante`,`id_padre`),
  ADD KEY `estudiante_padre_ibfk_2` (`id_padre`);

--
-- Indices de la tabla `facturas_recibos`
--
ALTER TABLE `facturas_recibos`
  ADD PRIMARY KEY (`id_comprobante`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `id_pago` (`id_pago`);

--
-- Indices de la tabla `grados`
--
ALTER TABLE `grados`
  ADD PRIMARY KEY (`id_grado`);

--
-- Indices de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `padres`
--
ALTER TABLE `padres`
  ADD PRIMARY KEY (`id_padre`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `salones`
--
ALTER TABLE `salones`
  ADD PRIMARY KEY (`id_salon`),
  ADD KEY `id_grado` (`id_grado`),
  ADD KEY `id_seccion` (`id_seccion`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id_seccion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `constancias`
--
ALTER TABLE `constancias`
  MODIFY `id_constancia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estudiante_padre`
--
ALTER TABLE `estudiante_padre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `facturas_recibos`
--
ALTER TABLE `facturas_recibos`
  MODIFY `id_comprobante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grados`
--
ALTER TABLE `grados`
  MODIFY `id_grado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `padres`
--
ALTER TABLE `padres`
  MODIFY `id_padre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `salones`
--
ALTER TABLE `salones`
  MODIFY `id_salon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id_seccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `fk_docente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `usuarios_roles_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
