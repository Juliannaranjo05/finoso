-- Arreglar registros de códigos aplicados que tienen id_orden
-- Cuando un código se aplica a un reloj, id_orden debe ser NULL
-- Solo cuando se compra, se asigna el id_orden de la nueva compra

UPDATE usuario_codigo_descuento 
SET id_orden = NULL
WHERE activo = 0 
  AND id_reloj IS NOT NULL
  AND fecha_usado IS NOT NULL
  AND id_orden IS NOT NULL;

-- Verificar el cambio
SELECT * FROM usuario_codigo_descuento WHERE id_reloj IS NOT NULL;

