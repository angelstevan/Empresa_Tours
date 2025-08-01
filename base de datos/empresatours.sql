-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-07-2025 a las 03:31:31
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
-- Base de datos: `empresatours`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `numero_documento` int(11) NOT NULL,
  `nombres` varchar(50) DEFAULT NULL,
  `apellidos` varchar(50) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`numero_documento`, `nombres`, `apellidos`, `telefono`, `email`) VALUES
(0, 'lopez', 'zapata', 234123441, 'lopez@gmail.com'),
(112542, 'lopez', 'zapata', 1234213, 'lopez@gmail.com'),
(542215, 'guarin', 'peruano', 542215, 'guarin@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guias`
--

CREATE TABLE `guias` (
  `identificacion` int(11) NOT NULL,
  `nombres` varchar(50) DEFAULT NULL,
  `apellidos` varchar(50) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `guias`
--

INSERT INTO `guias` (`identificacion`, `nombres`, `apellidos`, `telefono`) VALUES
(123, 'pepe', 'loco', 12345);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `fecha_reserva` datetime DEFAULT NULL,
  `total` double(13,2) DEFAULT NULL,
  `cliente_numero_documento` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `fecha_reserva`, `total`, `cliente_numero_documento`, `estado`) VALUES
(1, '2025-07-22 15:38:58', 864.00, 112542, 'Eliminado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `precio` double(13,2) DEFAULT NULL,
  `cupos_totales` int(11) DEFAULT NULL,
  `guias_identificacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tours`
--

INSERT INTO `tours` (`id`, `nombre`, `ciudad`, `descripcion`, `precio`, `cupos_totales`, `guias_identificacion`) VALUES
(1, 'pepe', 'lol', 'es un tour', 432.00, 28, 123),
(2, 'lola', 'pro', 'es un tour', 432.00, 30, 123);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tours_has_reservas`
--

CREATE TABLE `tours_has_reservas` (
  `tour_id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `cantidad_personas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tours_has_reservas`
--

INSERT INTO `tours_has_reservas` (`tour_id`, `reserva_id`, `cantidad_personas`) VALUES
(2, 1, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`numero_documento`);

--
-- Indices de la tabla `guias`
--
ALTER TABLE `guias`
  ADD PRIMARY KEY (`identificacion`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_numero_documento` (`cliente_numero_documento`);

--
-- Indices de la tabla `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tours_guias1_idx` (`guias_identificacion`);

--
-- Indices de la tabla `tours_has_reservas`
--
ALTER TABLE `tours_has_reservas`
  ADD PRIMARY KEY (`tour_id`,`reserva_id`),
  ADD KEY `fk_tours_has_reservas_reservas1_idx` (`reserva_id`),
  ADD KEY `fk_tours_has_reservas_tours_idx` (`tour_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`cliente_numero_documento`) REFERENCES `clientes` (`numero_documento`);

--
-- Filtros para la tabla `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `fk_tours_guias1` FOREIGN KEY (`guias_identificacion`) REFERENCES `guias` (`identificacion`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `tours_has_reservas`
--
ALTER TABLE `tours_has_reservas`
  ADD CONSTRAINT `fk_tours_has_reservas_reservas1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_tours_has_reservas_tours` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
