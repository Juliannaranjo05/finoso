# 🔐 CONFIGURACIÓN DE CREDENCIALES

## 📋 **CREDENCIALES PROTEGIDAS**

Por seguridad, las credenciales reales **NO** están incluidas en el repositorio. Este documento explica cómo configurarlas correctamente.

---

## 🔑 **CREDENCIALES QUE NECESITAS**

### **1️⃣ TWILIO (WhatsApp)**

**Archivo:** `config/twilio_config.php`

```
TWILIO_ACCOUNT_SID: [CONTACTA AL PROPIETARIO PARA OBTENER LAS CREDENCIALES]
TWILIO_AUTH_TOKEN: [CONTACTA AL PROPIETARIO PARA OBTENER LAS CREDENCIALES]
TWILIO_WHATSAPP_FROM: whatsapp:+14155238886
ADMIN_WHATSAPP: 57XXXXXXXXX
```

### **2️⃣ WOMPI (Pagos)**

**Archivo:** `informacion/php/wompi_config.php`

```
WOMPI_PUBLIC_KEY: [CONTACTA AL PROPIETARIO PARA OBTENER LAS CREDENCIALES]
WOMPI_PRIVATE_KEY: [CONTACTA AL PROPIETARIO PARA OBTENER LAS CREDENCIALES]
WOMPI_EVENTS_SECRET: [CONTACTA AL PROPIETARIO PARA OBTENER LAS CREDENCIALES]
```

---

## 🚀 **INSTALACIÓN (Primera vez)**

### **Paso 1: Configurar Twilio**

```bash
# Copiar archivo de ejemplo
cp config/twilio_config.example.php config/twilio_config.php

# Editar y agregar tus credenciales
nano config/twilio_config.php  # o usa tu editor favorito
```

Reemplaza:
```php
define('TWILIO_ACCOUNT_SID', 'TU_ACCOUNT_SID_AQUI');
define('TWILIO_AUTH_TOKEN', 'TU_AUTH_TOKEN_AQUI');
define('ADMIN_WHATSAPP', '57XXXXXXXXX');
```

Por tus credenciales reales (ver CREDENCIALES_BACKUP.txt)

---

### **Paso 2: Configurar Wompi**

```bash
# Copiar archivo de ejemplo
cp informacion/php/wompi_config.example.php informacion/php/wompi_config.php

# Editar y agregar tus credenciales
nano informacion/php/wompi_config.php
```

Reemplaza:
```php
define('WOMPI_PUBLIC_KEY', 'pub_prod_XXXXXXXXXXXXXXXXXXXXXXXX');
define('WOMPI_PRIVATE_KEY', 'prv_prod_XXXXXXXXXXXXXXXXXXXXXXXX');
define('WOMPI_EVENTS_SECRET', 'prod_integrity_XXXXXXXXXXXXXXXXXXXXXXXX');
```

Por tus credenciales reales (ver CREDENCIALES_BACKUP.txt)

---

## ⚠️ **IMPORTANTE: .gitignore**

Los archivos con credenciales reales están protegidos por `.gitignore`:

```gitignore
# Archivos de configuración con credenciales reales
config/twilio_config.php
informacion/php/wompi_config.php
```

**NUNCA** elimines estas líneas del `.gitignore`

---

## 🔄 **DESPLEGAR EN SERVIDOR (Producción)**

### **Opción 1: Manual**

1. Conéctate al servidor vía SSH/FTP
2. Crea los archivos de configuración:
   ```bash
   nano /ruta/a/finoso/config/twilio_config.php
   nano /ruta/a/finoso/informacion/php/wompi_config.php
   ```
3. Copia y pega las credenciales desde este documento

---

### **Opción 2: Script de despliegue**

```bash
#!/bin/bash
# deploy.sh

# Subir código (sin credenciales)
git push origin main

# Conectar al servidor y configurar credenciales
ssh usuario@servidor << 'EOF'
  cd /ruta/a/finoso
  
  # Actualizar código
  git pull origin main
  
  # Copiar archivos de ejemplo y editarlos manualmente
  cp config/twilio_config.example.php config/twilio_config.php
  cp informacion/php/wompi_config.example.php informacion/php/wompi_config.php
  
  # ⚠️ EDITAR MANUALMENTE con las credenciales reales (ver CREDENCIALES_BACKUP.txt)
  nano config/twilio_config.php
  nano informacion/php/wompi_config.php

  # Permisos
  chmod 600 config/twilio_config.php
  chmod 600 informacion/php/wompi_config.php
EOF
```

---

## ✅ **VERIFICAR CONFIGURACIÓN**

### **Test Twilio:**

```php
<?php
require_once 'config/twilio_config.php';

if (verificarConfiguracionTwilio()) {
    echo "✅ Twilio configurado correctamente\n";
    echo "Account SID: " . substr(TWILIO_ACCOUNT_SID, 0, 10) . "...\n";
} else {
    echo "❌ Error: Twilio no configurado\n";
}
?>
```

### **Test Wompi:**

```php
<?php
require_once 'informacion/php/wompi_config.php';

if (WOMPI_PUBLIC_KEY !== 'pub_prod_XXXXXXXXXXXXXXXXXXXXXXXX') {
    echo "✅ Wompi configurado correctamente\n";
    echo "Public Key: " . substr(WOMPI_PUBLIC_KEY, 0, 15) . "...\n";
} else {
    echo "❌ Error: Wompi no configurado\n";
}
?>
```

---

## 📝 **ARCHIVOS EN EL REPOSITORIO**

| Archivo | Descripción | En Git |
|---------|-------------|--------|
| `config/twilio_config.example.php` | Ejemplo sin credenciales | ✅ Sí |
| `config/twilio_config.php` | **Con credenciales reales** | ❌ NO |
| `informacion/php/wompi_config.example.php` | Ejemplo sin credenciales | ✅ Sí |
| `informacion/php/wompi_config.php` | **Con credenciales reales** | ❌ NO |

---

## 🔒 **SEGURIDAD**

### **Buenas prácticas:**

1. ✅ **NUNCA** commits los archivos con credenciales reales
2. ✅ Usa `.gitignore` para proteger archivos sensibles
3. ✅ Cambia las credenciales si se exponen accidentalmente
4. ✅ Usa permisos restrictivos en el servidor: `chmod 600`
5. ✅ No compartas las credenciales por correo o chat sin cifrar

### **Si las credenciales se exponen:**

1. **Twilio:**
   - Ve a https://console.twilio.com/
   - Settings > API Keys
   - Revoca el Auth Token comprometido
   - Genera uno nuevo

2. **Wompi:**
   - Ve a https://comercios.wompi.co/
   - Configuración > API Keys
   - Genera nuevas llaves
   - Actualiza en todos los servidores

---

## 🆘 **SOPORTE**

Si tienes problemas con la configuración:

1. Verifica que los archivos estén en las rutas correctas
2. Revisa los permisos de los archivos (deben ser legibles por PHP)
3. Revisa los logs de error de PHP
4. Verifica que las credenciales estén copiadas correctamente (sin espacios extra)

---

**✅ Configuración completa!** 🎉

Ahora puedes hacer `git push` sin exponer tus credenciales.

