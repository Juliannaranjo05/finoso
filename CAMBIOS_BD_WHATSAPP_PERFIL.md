# 📊 CAMBIOS EN BASE DE DATOS - FINOSO

## ✅ CAMBIOS APLICADOS Y ACTUALIZADOS EN `finoso.sql`

### **Tabla `orden`** - NUEVOS CAMPOS AGREGADOS:

#### **1. Sistema de Recuperación de Pagos:**
```sql
monto_pagado DECIMAL(10,2) DEFAULT 0 
  COMMENT 'Monto real que pagó el cliente (puede ser menor al total)'

intentos_pago INT DEFAULT 0 
  COMMENT 'Número de intentos de pago/resubida de comprobante'

fecha_ultima_subida DATETIME DEFAULT NULL 
  COMMENT 'Última vez que se subió o actualizó el comprobante'
```

**Propósito:** Permite el flujo de recuperación cuando un cliente paga menos de lo debido.

---

#### **2. Sistema de Recordatorios WhatsApp:**
```sql
recordatorio_enviado TINYINT(1) DEFAULT 0 
  COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada'
```

**Propósito:** Evita enviar múltiples recordatorios a la misma orden rechazada.

---

#### **3. Sistema de Envíos y Rastreo:**
```sql
transportadora VARCHAR(100) DEFAULT NULL 
  COMMENT 'Empresa de transporte (SERVIENTREGA, etc)'

guia_envio VARCHAR(100) DEFAULT NULL 
  COMMENT 'Número de guía de seguimiento'

fecha_envio DATETIME DEFAULT NULL 
  COMMENT 'Fecha y hora de envío del producto'

fecha_entrega_estimada DATE DEFAULT NULL 
  COMMENT 'Fecha estimada de entrega'

fecha_entrega DATETIME DEFAULT NULL 
  COMMENT 'Fecha y hora real de entrega'
```

**Propósito:** Tracking completo de envíos y notificaciones automáticas.

---

## 📁 ARCHIVOS SQL INDIVIDUALES (en carpeta `database/`)

Estos archivos **YA ESTÁN APLICADOS** en tu BD actual, pero ahora también están en `finoso.sql`:

1. ✅ `agregar_monto_pagado.sql` → Campos de recuperación de pagos
2. ✅ `add_recordatorio_enviado.sql` → Campo de recordatorios
3. ✅ `update_orden_table.sql` → Campos de envío

---

## 🎯 ESTADO ACTUAL

### ✅ **finoso.sql** - ACTUALIZADO
- Contiene TODOS los campos nuevos
- Listo para importar en una BD limpia

### ✅ **Tu BD actual** - YA TIENE LOS CAMPOS
- Los campos ya fueron agregados mediante los scripts individuales
- No necesitas ejecutar nada adicional

---

## 🔄 SI NECESITAS REINSTALAR LA BD

### **Opción 1: Base de datos limpia**
```bash
# Usar finoso.sql (YA INCLUYE TODO)
mysql -u root -p finoso < finoso.sql
```

### **Opción 2: BD existente (solo agregar campos faltantes)**
```bash
# Si por alguna razón necesitas agregar los campos manualmente
mysql -u root -p finoso < database/agregar_monto_pagado.sql
mysql -u root -p finoso < database/add_recordatorio_enviado.sql
mysql -u root -p finoso < database/update_orden_table.sql
```

---

## 📝 RESUMEN DE FUNCIONALIDADES

| Campo | Funcionalidad | Usado en |
|-------|---------------|----------|
| `monto_pagado` | Recuperación de pagos parciales | `admin/php/acciones.php` |
| `intentos_pago` | Contador de reintentos | Sistema de recuperación |
| `fecha_ultima_subida` | Tracking de comprobantes | Sistema de auditoría |
| `recordatorio_enviado` | Control de recordatorios | `admin/php/recordatorio_orden_rechazada.php` |
| `transportadora` | Empresa de envío | Notificaciones WhatsApp |
| `guia_envio` | Número de rastreo | Cliente puede rastrear |
| `fecha_envio` | Cuando se envió | Notificación automática |
| `fecha_entrega_estimada` | Estimación de llegada | Mostrar al cliente |
| `fecha_entrega` | Entrega real | Notificación de entrega |

---

## ⚠️ IMPORTANTE

- ✅ **finoso.sql** ahora es la versión completa y actualizada
- ✅ Todos los cambios están documentados con comentarios
- ✅ Compatible con MySQL 5.7+ y MariaDB 10.2+
- ✅ Los scripts individuales en `database/` son OPCIONALES (solo para agregar campos a BD existente)

---

**Última actualización:** 25 de octubre de 2025
**Versión BD:** 2.0 (con WhatsApp + Perfil de Usuario)

