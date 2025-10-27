-- ============================================
-- LIMPIEZA COMPLETA DE BASE DE DATOS
-- Elimina campos y tablas innecesarias
-- ============================================

-- 1. ELIMINAR TABLA AUDITORIA_PAGOS (experimental, nunca usada)
DROP TABLE IF EXISTS `auditoria_pagos`;

-- 2. ELIMINAR FOREIGN KEY Y ÍNDICE de codigo_descuento_id
ALTER TABLE `orden` DROP FOREIGN KEY IF EXISTS `orden_ibfk_2`;
ALTER TABLE `orden` DROP KEY IF EXISTS `codigo_descuento_id`;

-- 3. ELIMINAR CAMPOS INNECESARIOS DE ORDEN
ALTER TABLE `orden` 
  DROP COLUMN IF EXISTS `codigo_descuento_id`,
  DROP COLUMN IF EXISTS `intentos_pago`,
  DROP COLUMN IF EXISTS `fecha_ultima_subida`;

-- 4. ELIMINAR CAMPO CANTIDAD DE ORDEN_DETALLE
ALTER TABLE `orden_detalle` 
  DROP COLUMN IF EXISTS `cantidad`;

-- 5. CAMBIAR DEFAULT DE MONTO_PAGADO (de 0.00 a NULL, más semántico)
ALTER TABLE `orden` 
  MODIFY COLUMN `monto_pagado` decimal(10,2) DEFAULT NULL 
  COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)';

-- ============================================
-- RESUMEN
-- ============================================
-- Tablas eliminadas: 1 (auditoria_pagos)
-- Campos eliminados de orden: 3 (codigo_descuento_id, intentos_pago, fecha_ultima_subida)
-- Campos eliminados de orden_detalle: 1 (cantidad)
-- Campos modificados: 1 (orden.monto_pagado DEFAULT NULL)
-- ============================================

-- LIMPIEZA COMPLETA DE BASE DE DATOS
-- Elimina campos y tablas innecesarias
-- ============================================

-- 1. ELIMINAR TABLA AUDITORIA_PAGOS (experimental, nunca usada)
DROP TABLE IF EXISTS `auditoria_pagos`;

-- 2. ELIMINAR FOREIGN KEY Y ÍNDICE de codigo_descuento_id
ALTER TABLE `orden` DROP FOREIGN KEY IF EXISTS `orden_ibfk_2`;
ALTER TABLE `orden` DROP KEY IF EXISTS `codigo_descuento_id`;

-- 3. ELIMINAR CAMPOS INNECESARIOS DE ORDEN
ALTER TABLE `orden` 
  DROP COLUMN IF EXISTS `codigo_descuento_id`,
  DROP COLUMN IF EXISTS `intentos_pago`,
  DROP COLUMN IF EXISTS `fecha_ultima_subida`;

-- 4. ELIMINAR CAMPO CANTIDAD DE ORDEN_DETALLE
ALTER TABLE `orden_detalle` 
  DROP COLUMN IF EXISTS `cantidad`;

-- 5. CAMBIAR DEFAULT DE MONTO_PAGADO (de 0.00 a NULL, más semántico)
ALTER TABLE `orden` 
  MODIFY COLUMN `monto_pagado` decimal(10,2) DEFAULT NULL 
  COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)';

-- ============================================
-- RESUMEN
-- ============================================
-- Tablas eliminadas: 1 (auditoria_pagos)
-- Campos eliminados de orden: 3 (codigo_descuento_id, intentos_pago, fecha_ultima_subida)
-- Campos eliminados de orden_detalle: 1 (cantidad)
-- Campos modificados: 1 (orden.monto_pagado DEFAULT NULL)
-- ============================================

