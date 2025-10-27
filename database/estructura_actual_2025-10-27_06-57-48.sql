-- Estructura de Base de Datos: finoso
-- Generado: 2025-10-27 06:57:48
-- Tablas: 12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_pagos`
--

DROP TABLE IF EXISTS `auditoria_pagos`;
CREATE TABLE `auditoria_pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp_intento` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_cliente` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `monto_esperado` decimal(10,2) DEFAULT NULL,
  `monto_recibido` decimal(10,2) DEFAULT NULL,
  `hash_archivo` varchar(64) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

DROP TABLE IF EXISTS `carrito`;
CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_agregado` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_carrito`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_reloj` (`id_reloj`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_descuento`
--

DROP TABLE IF EXISTS `codigo_descuento`;
CREATE TABLE `codigo_descuento` (
  `id_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) DEFAULT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  PRIMARY KEY (`id_codigo`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_reloj` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `calificacion` int(1) NOT NULL DEFAULT 5,
  `comentario` text NOT NULL,
  `fecha_comentario` datetime DEFAULT current_timestamp(),
  `aprobado` tinyint(1) DEFAULT 0,
  `fecha_aprobacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_comentario`),
  KEY `fk_comentarios_reloj` (`id_reloj`),
  KEY `fk_comentarios_usuario` (`id_usuario`),
  KEY `idx_aprobado` (`aprobado`),
  KEY `idx_fecha` (`fecha_comentario`),
  CONSTRAINT `fk_comentarios_reloj` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE CASCADE,
  CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

DROP TABLE IF EXISTS `envios`;
CREATE TABLE `envios` (
  `id_envio` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `dias_estimados` int(11) NOT NULL DEFAULT 3,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_envio`),
  UNIQUE KEY `ciudad_depto_unique` (`ciudad`,`departamento`),
  KEY `idx_departamento` (`departamento`),
  KEY `idx_ciudad` (`ciudad`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

DROP TABLE IF EXISTS `marca`;
CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_marca`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

DROP TABLE IF EXISTS `orden`;
CREATE TABLE `orden` (
  `id_orden` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `barrio` varchar(100) DEFAULT NULL,
  `referencias` text DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `costo_envio` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'pendiente',
  `codigo_descuento_id` int(11) DEFAULT NULL,
  `comprobante_pago` varchar(255) DEFAULT NULL,
  `nombre_archivo_comprobante` varchar(255) DEFAULT NULL,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `comprobante_verificado` tinyint(1) DEFAULT 0,
  `transportadora` varchar(100) DEFAULT NULL COMMENT 'Empresa de transporte',
  `guia_envio` varchar(100) DEFAULT NULL COMMENT 'Número de guía',
  `fecha_envio` datetime DEFAULT NULL COMMENT 'Fecha de envío',
  `fecha_entrega_estimada` date DEFAULT NULL COMMENT 'Fecha estimada',
  `fecha_entrega` datetime DEFAULT NULL COMMENT 'Fecha real de entrega',
  `recordatorio_enviado` tinyint(1) DEFAULT 0 COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada',
  `monto_pagado` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto real que pagó el cliente (puede ser menor al total)',
  `intentos_pago` int(11) DEFAULT 0 COMMENT 'Número de intentos de pago/resubida de comprobante',
  `fecha_ultima_subida` datetime DEFAULT NULL COMMENT 'Última vez que se subió o actualizó el comprobante',
  PRIMARY KEY (`id_orden`),
  KEY `id_usuario` (`id_usuario`),
  KEY `codigo_descuento_id` (`codigo_descuento_id`),
  CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`codigo_descuento_id`) REFERENCES `codigo_descuento` (`id_codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalle`
--

DROP TABLE IF EXISTS `orden_detalle`;
CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_orden_detalle`),
  KEY `id_orden` (`id_orden`),
  KEY `id_reloj` (`id_reloj`),
  CONSTRAINT `orden_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id_orden`),
  CONSTRAINT `orden_detalle_ibfk_2` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reloj`
--

DROP TABLE IF EXISTS `reloj`;
CREATE TABLE `reloj` (
  `id_reloj` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(100) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `eslabones` varchar(100) DEFAULT NULL,
  `tipo_bisel` varchar(50) DEFAULT NULL,
  `movimiento` varchar(100) DEFAULT NULL,
  `pulsera` varchar(100) DEFAULT NULL,
  `peso` varchar(50) DEFAULT NULL,
  `resistencia_agua` varchar(50) DEFAULT NULL,
  `precio` decimal(12,2) DEFAULT NULL,
  `descuento` decimal(5,2) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `img_lateral` varchar(255) DEFAULT NULL,
  `img_detalle` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `vendido` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_reloj`),
  KEY `id_marca` (`marca`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reset_tokens`
--

DROP TABLE IF EXISTS `reset_tokens`;
CREATE TABLE `reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `expira` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `verificado` tinyint(1) DEFAULT 0,
  `token` varchar(32) DEFAULT NULL,
  `rol` enum('usuario','administrador') DEFAULT 'usuario',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_codigo_descuento`
--

DROP TABLE IF EXISTS `usuario_codigo_descuento`;
CREATE TABLE `usuario_codigo_descuento` (
  `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_codigo` int(11) NOT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `fecha_asignado` datetime DEFAULT current_timestamp(),
  `fecha_usado` datetime DEFAULT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `veces_usado` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id_usuario_codigo`),
  UNIQUE KEY `unico_usuario_codigo` (`id_usuario`,`id_codigo`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_codigo` (`id_codigo`),
  KEY `id_orden` (`id_orden`),
  KEY `idx_activo` (`activo`),
  KEY `fk_usuario_codigo_reloj` (`id_reloj`),
  CONSTRAINT `fk_usuario_codigo_reloj` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE SET NULL,
  CONSTRAINT `usuario_codigo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `usuario_codigo_ibfk_2` FOREIGN KEY (`id_codigo`) REFERENCES `codigo_descuento` (`id_codigo`) ON DELETE CASCADE,
  CONSTRAINT `usuario_codigo_ibfk_3` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id_orden`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

