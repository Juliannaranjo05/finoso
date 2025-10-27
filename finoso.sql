-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-06-2025 a las 04:20:50
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
-- Base de datos: `finoso`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_descuento`
--

CREATE TABLE `codigo_descuento` (
  `id_codigo` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_codigo_descuento`
--

-- Tabla: usuario_codigo_descuento
-- Flujo de estados de un código de descuento:
-- 1. ASIGNADO (activo=1, id_reloj=NULL, fecha_usado=NULL, id_orden=NULL o ID de orden que lo generó)
-- 2. APLICADO (activo=0, id_reloj=X, fecha_usado=NOW(), id_orden=NULL) ← Se limpia id_orden al aplicar
-- 3. COMPRADO (activo=0, id_reloj=X, fecha_usado=fecha, id_orden=Y) ← Se asigna id_orden de la compra
CREATE TABLE `usuario_codigo_descuento` (
  `id_usuario_codigo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_codigo` int(11) NOT NULL,
  `id_reloj` int(11) DEFAULT NULL COMMENT 'ID del reloj al que se aplicó el código',
  `fecha_asignado` datetime DEFAULT current_timestamp(),
  `fecha_usado` datetime DEFAULT NULL COMMENT 'Fecha en que se aplicó (no cuando se compró)',
  `id_orden` int(11) DEFAULT NULL COMMENT 'NULL cuando está aplicado, se asigna cuando se compra',
  `veces_usado` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=disponible, 0=aplicado/usado',
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id_envio` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `dias_estimados` int(11) NOT NULL DEFAULT 3,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_envio`),
  UNIQUE KEY `ciudad_depto_unique` (`ciudad`, `departamento`),
  KEY `idx_departamento` (`departamento`),
  KEY `idx_ciudad` (`ciudad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `envios` (Ciudades principales de Colombia)
--

INSERT INTO `envios` (`departamento`, `ciudad`, `precio`, `dias_estimados`) VALUES
-- ANTIOQUIA
('Antioquia', 'Medellín', 8000.00, 2),
('Antioquia', 'Bello', 10000.00, 2),
('Antioquia', 'Itagüí', 10000.00, 2),
('Antioquia', 'Envigado', 10000.00, 2),
('Antioquia', 'Rionegro', 12000.00, 3),

-- CUNDINAMARCA
('Cundinamarca', 'Bogotá', 15000.00, 3),
('Cundinamarca', 'Soacha', 16000.00, 3),
('Cundinamarca', 'Chía', 16000.00, 3),
('Cundinamarca', 'Zipaquirá', 18000.00, 4),
('Cundinamarca', 'Facatativá', 18000.00, 4),

-- VALLE DEL CAUCA
('Valle del Cauca', 'Cali', 18000.00, 4),
('Valle del Cauca', 'Palmira', 20000.00, 4),
('Valle del Cauca', 'Buenaventura', 25000.00, 5),
('Valle del Cauca', 'Tuluá', 22000.00, 5),

-- ATLÁNTICO
('Atlántico', 'Barranquilla', 20000.00, 4),
('Atlántico', 'Soledad', 22000.00, 4),
('Atlántico', 'Malambo', 22000.00, 4),

-- BOLÍVAR
('Bolívar', 'Cartagena', 22000.00, 5),
('Bolívar', 'Magangué', 28000.00, 6),

-- SANTANDER
('Santander', 'Bucaramanga', 18000.00, 4),
('Santander', 'Floridablanca', 18000.00, 4),
('Santander', 'Girón', 20000.00, 4),
('Santander', 'Piedecuesta', 20000.00, 4),

-- NORTE DE SANTANDER
('Norte de Santander', 'Cúcuta', 25000.00, 5),
('Norte de Santander', 'Villa del Rosario', 26000.00, 5),

-- CALDAS
('Caldas', 'Manizales', 15000.00, 3),
('Caldas', 'Chinchiná', 17000.00, 4),

-- RISARALDA
('Risaralda', 'Pereira', 14000.00, 3),
('Risaralda', 'Dosquebradas', 15000.00, 3),

-- QUINDÍO
('Quindío', 'Armenia', 16000.00, 3),
('Quindío', 'Calarcá', 17000.00, 4),

-- TOLIMA
('Tolima', 'Ibagué', 18000.00, 4),
('Tolima', 'Espinal', 20000.00, 4),

-- HUILA
('Huila', 'Neiva', 20000.00, 4),
('Huila', 'Pitalito', 25000.00, 5),

-- NARIÑO
('Nariño', 'Pasto', 28000.00, 6),
('Nariño', 'Ipiales', 30000.00, 7),

-- CAUCA
('Cauca', 'Popayán', 20000.00, 4),

-- META
('Meta', 'Villavicencio', 18000.00, 4),
('Meta', 'Acacías', 22000.00, 5),

-- CÓRDOBA
('Córdoba', 'Montería', 25000.00, 5),

-- CESAR
('Cesar', 'Valledupar', 28000.00, 6),

-- MAGDALENA
('Magdalena', 'Santa Marta', 25000.00, 5),

-- LA GUAJIRA
('La Guajira', 'Riohacha', 30000.00, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `id_orden` int(11) NOT NULL,
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
  `comprobante_pago` varchar(255) DEFAULT NULL,
  `nombre_archivo_comprobante` varchar(255) DEFAULT NULL,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `comprobante_verificado` tinyint(1) DEFAULT 0,
  `monto_pagado` decimal(10,2) DEFAULT NULL COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)',
  `recordatorio_enviado` tinyint(1) DEFAULT 0 COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada',
  `transportadora` varchar(100) DEFAULT NULL COMMENT 'Empresa de transporte (SERVIENTREGA, etc)',
  `guia_envio` varchar(100) DEFAULT NULL COMMENT 'Número de guía de seguimiento',
  `fecha_envio` datetime DEFAULT NULL COMMENT 'Fecha y hora de envío del producto',
  `fecha_entrega_estimada` date DEFAULT NULL COMMENT 'Fecha estimada de entrega',
  `fecha_entrega` datetime DEFAULT NULL COMMENT 'Fecha y hora real de entrega'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalle`
--

CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reloj`
--

CREATE TABLE `reloj` (
  `id_reloj` int(11) NOT NULL,
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
  `vendido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reloj`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `nombre`, `descripcion`, `activa`) VALUES
(1, 'Richard Mille', 'Marca suiza de relojes de lujo conocida por sus innovaciones técnicas y materiales exóticos.', 1),
(2, 'Patek Philippe', 'Casa relojera suiza de prestigio, fabricante de relojes de alta gama desde 1839.', 1),
(3, 'Technomarine', 'Marca especializada en relojes deportivos y de buceo con diseño contemporáneo.', 1),
(4, 'Q&Q', 'Marca japonesa de relojes accesibles con buena calidad y diseño moderno.', 1);

INSERT INTO `reloj` (`id_reloj`, `marca`, `nombre`, `descripcion`, `precio`, `descuento`, `img`, `disponible`, `vendido`) VALUES
(1, 'Richard Mille', 'Rchrd Mll Calavera Blanco Circones Negros-Dorados', 'Diseño de calavera esqueleto con incrustaciones de circones negros y bisel dorado ionizado. Correa de acero pulido.', 135000.00, 0.11, 'img/Rchrd Mll Calavera Blanco Circones Negros-Dorados/Rchrd Mll Calavera Blanco Circones Negros-Dorados.png', 1, 0),
(2, 'Patek Philippe', 'Patk Phlppe Bicolor Dorado - Negro', 'Clásico bicolor con brazalete alternado en baño de oro y acero pulido. Tablero negro con marcadores luminiscentes y fechador a las 3', 125000.00, NULL, 'img/Patk Phlppe Bicolor Dorado - Negro/Patk Phlppe Bicolor Dorado - Negro.png', 1, 0),
(3, 'Technomarine', 'Tchmrn Mujer Circones Negro Tablero Negro-Dorado', 'Diseño elegante de tablero negro mate con bisel dorado ionizado e incrustaciones de circones facetados de alta pureza', 115000.00, 0.16, 'img/Tchmrn Mujer Circones Negro Tablero Negro-Dorado/Tchmrn Mujer Circones Negro Tablero Negro-Dorado.png', 1, 0),
(4, 'Q&Q', 'Q&Q hombre Bazel Plateado Tablero Blanco-Plateado', 'Bazeleado plateado en acero cromado, tablero blanco con subesferas decorativas y marcadores luminiscentes', 100000.00, NULL, 'img/Q&Q hombre Bazel Plateado Tablero Blanco-Plateado/Q&Q hombre Bazel Plateado Tablero Blanco-Plateado.png', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reset_tokens`
--

CREATE TABLE `reset_tokens` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `verificado` tinyint(1) DEFAULT 0,
  `token` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `contrasena`, `creado_en`, `verificado`, `token`) VALUES
(1, 'JulianADM', 'juliannaranjo58@gmail.com', '$2y$10$SYZD.7SZmZ9WWqJBq5.Vv.Wr.Y65acmqhaLOKKjJFqJHNckZkyWB6', '2025-06-15 01:58:40', 1, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_reloj` (`id_reloj`);

--
-- Indices de la tabla `codigo_descuento`
--
ALTER TABLE `codigo_descuento`
  ADD PRIMARY KEY (`id_codigo`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `usuario_codigo_descuento`
--
ALTER TABLE `usuario_codigo_descuento`
  ADD PRIMARY KEY (`id_usuario_codigo`),
  ADD UNIQUE KEY `unico_usuario_codigo` (`id_usuario`,`id_codigo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_codigo` (`id_codigo`),
  ADD KEY `id_reloj` (`id_reloj`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD PRIMARY KEY (`id_orden_detalle`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_reloj` (`id_reloj`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `reloj`
--
ALTER TABLE `reloj`
  ADD PRIMARY KEY (`id_reloj`),
  ADD KEY `id_marca` (`marca`);

--
-- Indices de la tabla `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `codigo_descuento`
--
ALTER TABLE `codigo_descuento`
  MODIFY `id_codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_codigo_descuento`
--
ALTER TABLE `usuario_codigo_descuento`
  MODIFY `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  MODIFY `id_orden_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reloj`
--
ALTER TABLE `reloj`
  MODIFY `id_reloj` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reset_tokens`
--
ALTER TABLE `reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`);

--
-- Filtros para la tabla `usuario_codigo_descuento`
--
ALTER TABLE `usuario_codigo_descuento`
  ADD CONSTRAINT `usuario_codigo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_codigo_ibfk_2` FOREIGN KEY (`id_codigo`) REFERENCES `codigo_descuento` (`id_codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_codigo_ibfk_3` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuario_codigo_ibfk_4` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id_orden`) ON DELETE SET NULL;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `orden_detalle`
--
ALTER TABLE `orden_detalle`
  ADD CONSTRAINT `orden_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id_orden`),
  ADD CONSTRAINT `orden_detalle_ibfk_2` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`);

--
-- Filtros para la tabla `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Agregar campo rol a la tabla usuario
--
ALTER TABLE `usuario` ADD COLUMN `rol` ENUM('usuario', 'administrador') DEFAULT 'usuario';

--
-- Crear un usuario administrador por defecto
-- Usuario: admin
-- Contraseña: admin123
--
INSERT INTO `usuario` (`nombre`, `correo`, `contrasena`, `verificado`, `rol`) 
VALUES ('admin', 'admin@finoso.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'administrador')
ON DUPLICATE KEY UPDATE `rol` = 'administrador', `contrasena` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

--
-- Actualizar usuarios existentes para que tengan rol 'usuario' por defecto
--
UPDATE `usuario` SET `rol` = 'usuario' WHERE `rol` IS NULL;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Volcado de datos para la tabla `comentarios` (comentarios de ejemplo)
--

INSERT INTO `comentarios` (`id_reloj`, `id_usuario`, `nombre_usuario`, `calificacion`, `comentario`, `aprobado`, `fecha_aprobacion`) VALUES
(1, NULL, 'Carlos M.', 5, 'Excelente reloj, la calidad se nota desde el primer momento. El envío fue súper rápido y el empaque muy elegante.', 1, NOW()),
(1, NULL, 'Ana L.', 4, 'Muy bonito, solo que esperaba que fuera un poco más grande. Pero en general estoy satisfecha con la compra.', 1, NOW()),
(2, NULL, 'Roberto S.', 5, 'Increíble calidad, se ve mucho más caro de lo que cuesta. Definitivamente recomiendo Finoso.', 1, NOW()),
(2, NULL, 'María G.', 5, 'Perfecto en todos los aspectos. El servicio al cliente fue excepcional y el producto superó mis expectativas.', 1, NOW());

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;