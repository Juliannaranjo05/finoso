-- ============================================
-- ACTUALIZACIÓN TABLA usuario_codigo_descuento
-- Fecha: 27 de octubre de 2025
-- ============================================

-- 1. Vaciar tabla porque los registros actuales no tienen id_reloj
TRUNCATE TABLE usuario_codigo_descuento;

-- 2. Agregar campo id_reloj para saber en qué reloj se aplicó el código
ALTER TABLE usuario_codigo_descuento 
ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;

-- 3. Agregar foreign key para mantener integridad referencial
ALTER TABLE usuario_codigo_descuento 
ADD CONSTRAINT fk_usuario_codigo_reloj 
FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) 
ON DELETE SET NULL;

-- 4. Eliminar tabla temporal que no se debe usar
DROP TABLE IF EXISTS descuento_aplicado_reloj;

-- ============================================
-- RESULTADO FINAL:
-- usuario_codigo_descuento ahora tiene:
-- - id_usuario_codigo
-- - id_usuario
-- - id_codigo
-- - id_reloj (NUEVO) ✅
-- - fecha_asignado
-- - fecha_usado
-- - id_orden
-- - veces_usado
-- - activo
-- - notas
-- ============================================

-- ACTUALIZACIÓN TABLA usuario_codigo_descuento
-- Fecha: 27 de octubre de 2025
-- ============================================

-- 1. Vaciar tabla porque los registros actuales no tienen id_reloj
TRUNCATE TABLE usuario_codigo_descuento;

-- 2. Agregar campo id_reloj para saber en qué reloj se aplicó el código
ALTER TABLE usuario_codigo_descuento 
ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;

-- 3. Agregar foreign key para mantener integridad referencial
ALTER TABLE usuario_codigo_descuento 
ADD CONSTRAINT fk_usuario_codigo_reloj 
FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) 
ON DELETE SET NULL;

-- 4. Eliminar tabla temporal que no se debe usar
DROP TABLE IF EXISTS descuento_aplicado_reloj;

-- ============================================
-- RESULTADO FINAL:
-- usuario_codigo_descuento ahora tiene:
-- - id_usuario_codigo
-- - id_usuario
-- - id_codigo
-- - id_reloj (NUEVO) ✅
-- - fecha_asignado
-- - fecha_usado
-- - id_orden
-- - veces_usado
-- - activo
-- - notas
-- ============================================

