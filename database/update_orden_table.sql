-- ============================================
-- ACTUALIZACIÓN TABLA ORDEN PARA WHATSAPP
-- ============================================
-- Este script agrega las columnas necesarias para
-- el sistema de notificaciones WhatsApp
-- ============================================

USE finoso_db;

-- Verificar estructura actual
DESCRIBE orden;

-- Agregar columnas si no existen
ALTER TABLE orden 
ADD COLUMN IF NOT EXISTS transportadora VARCHAR(100) DEFAULT NULL COMMENT 'Empresa de transporte (SERVIENTREGA, etc)',
ADD COLUMN IF NOT EXISTS guia_envio VARCHAR(100) DEFAULT NULL COMMENT 'Número de guía de seguimiento',
ADD COLUMN IF NOT EXISTS fecha_envio DATETIME DEFAULT NULL COMMENT 'Fecha y hora de envío del producto',
ADD COLUMN IF NOT EXISTS fecha_entrega_estimada DATE DEFAULT NULL COMMENT 'Fecha estimada de entrega',
ADD COLUMN IF NOT EXISTS fecha_entrega DATETIME DEFAULT NULL COMMENT 'Fecha y hora real de entrega';

-- Verificar que se agregaron correctamente
DESCRIBE orden;

-- Ver estructura completa
SHOW CREATE TABLE orden;

-- ============================================
-- IMPORTANTE:
-- Ejecutar este script SOLO UNA VEZ
-- Si ya tienes estas columnas, puedes omitir este paso
-- ============================================

SELECT 'Base de datos actualizada correctamente para WhatsApp' AS status;

