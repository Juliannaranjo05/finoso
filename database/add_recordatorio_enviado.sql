-- Agregar columna para trackear recordatorios enviados
-- Esta columna evita enviar múltiples recordatorios a la misma orden rechazada

ALTER TABLE orden 
ADD COLUMN recordatorio_enviado TINYINT(1) DEFAULT 0 COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada';

