-- Agregar columna para guardar el monto que realmente pagó el cliente
ALTER TABLE orden ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente (puede ser menor al total)';

-- Agregar columna para rastrear intentos de recuperación
ALTER TABLE orden ADD COLUMN intentos_pago INT DEFAULT 0 COMMENT 'Número de intentos de pago/resubida de comprobante';

-- Agregar columna para fecha de última actualización de comprobante
ALTER TABLE orden ADD COLUMN fecha_ultima_subida DATETIME DEFAULT NULL COMMENT 'Última vez que se subió o actualizó el comprobante';

