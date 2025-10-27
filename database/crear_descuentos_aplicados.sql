-- Tabla para guardar descuentos aplicados a relojes específicos
CREATE TABLE IF NOT EXISTS `descuento_aplicado_reloj` (
  `id_descuento_aplicado` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_reloj` INT(11) NOT NULL,
  `id_codigo` INT(11) NOT NULL,
  `precio_original` DECIMAL(10,2) NOT NULL,
  `porcentaje_descuento` DECIMAL(5,2) NOT NULL,
  `precio_con_descuento` DECIMAL(10,2) NOT NULL,
  `fecha_aplicado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expira_en` DATETIME NULL DEFAULT NULL COMMENT 'Expira en 24 horas',
  `usado_en_orden` INT(11) NULL DEFAULT NULL COMMENT 'ID de orden donde se usó',
  PRIMARY KEY (`id_descuento_aplicado`),
  UNIQUE KEY `unico_usuario_reloj` (`id_usuario`, `id_reloj`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_reloj` (`id_reloj`),
  KEY `idx_codigo` (`id_codigo`),
  KEY `idx_usado` (`usado_en_orden`),
  CONSTRAINT `fk_descuento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_descuento_reloj` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE CASCADE,
  CONSTRAINT `fk_descuento_codigo` FOREIGN KEY (`id_codigo`) REFERENCES `codigo_descuento` (`id_codigo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

