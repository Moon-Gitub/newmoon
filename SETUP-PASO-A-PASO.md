# 🎯 Setup Paso a Paso - NewMoon ERP en Dokploy

Guía completa para deployar desde cero, sin asumir conocimientos previos.

---

## 📋 Antes de Empezar - Lo Que Necesitás

### ✅ Checklist de Requisitos

- [ ] Cuenta en Dokploy (servidor con Dokploy instalado)
- [ ] Repositorio en GitHub (ya lo tenés: Moon-Gitub/newmoon)
- [ ] Archivo SQL con la base de datos (backup o dump)
- [ ] Cuenta de MercadoPago (si vas a cobrar online)
- [ ] Dominio configurado (opcional, podés usar IP)

---

## 🚀 Paso 1: Crear MySQL en Dokploy

### 1.1 Acceder a Dokploy
```
https://tu-servidor-dokploy.com
```

### 1.2 Crear Servicio MySQL
1. Click en **"Add Service"** o **"New Service"**
2. Seleccionar: **MySQL** o **MariaDB**
3. Configurar:

```
Service Name: newmoon-mysql
MySQL Version: 8.0 o MariaDB 10.11

Database Configuration:
  ├─ Database Name: newmoon_db
  ├─ Username: newmoon_user
  └─ Password: [CLICK EN GENERAR PASSWORD SEGURO]
```

4. **⚠️ IMPORTANTE:** Copiar y guardar el password generado
5. Click en **"Create"** o **"Deploy"**
6. Esperar que el servicio esté **"Running"** (verde)

### 1.3 Anotar Datos de Conexión

```
DB_HOST=newmoon-mysql
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=el_password_que_generaste_y_copiaste
```

**Guardá esto, lo vas a necesitar en el Paso 3**

---

## 📦 Paso 2: Crear Aplicación NewMoon

### 2.1 Crear Nuevo Proyecto
1. En Dokploy Dashboard → **"New Project"** o **"Add Application"**
2. Configurar:

```
Application Name: newmoon-erp
Type: Dockerfile
```

### 2.2 Conectar con GitHub

```
Repository URL: https://github.com/Moon-Gitub/newmoon.git
Branch: main
Build Method: Dockerfile
Dockerfile Path: ./Dockerfile
```

### 2.3 Build Settings (Opcional)
```
Build Context: .
Build Args: (dejar vacío)
```

**NO DEPLOY TODAVÍA** - Primero hay que configurar variables de entorno

---

## ⚙️ Paso 3: Configurar Variables de Entorno

En la sección **"Environment Variables"** de tu proyecto, agregar:

### 3.1 Base de Datos (OBLIGATORIO)

Usar los datos del Paso 1.3:

```bash
DB_HOST=newmoon-mysql
DB_PORT=3306
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=el_password_del_paso_1
WAIT_FOR_DB=true
```

### 3.2 Aplicación (OBLIGATORIO)

```bash
APP_NAME=NewMoon ERP/POS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
TZ=America/Argentina/Buenos_Aires
```

**Nota:** Si todavía no tenés dominio, poné:
```bash
APP_URL=http://tu-ip-servidor
```

### 3.3 MercadoPago (OPCIONAL - Solo si vas a cobrar online)

#### Cómo obtener las credenciales:

1. Ir a: https://www.mercadopago.com.ar/developers
2. Login con tu cuenta de MercadoPago
3. Click en "Tus integraciones" o "Your integrations"
4. Click en "Credenciales" o "Credentials"
5. Verás dos modos:

**Modo TEST (para probar):**
```bash
MP_PUBLIC_KEY=TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MP_ACCESS_TOKEN=TEST-xxxxxxxxxxxx-xxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MP_MODE=test
```

**Modo PRODUCCIÓN (real, cobros reales):**
```bash
MP_PUBLIC_KEY=APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MP_ACCESS_TOKEN=APP_USR-xxxxxxxxxxxx-xxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MP_MODE=live
```

**Empezá con TEST**, cuando esté todo funcionando cambiás a LIVE.

### 3.4 Email (OPCIONAL - Para envío de emails)

#### Si usás Gmail:

**Primero, generar App Password:**
1. Ir a: https://myaccount.google.com/security
2. Activar "Verificación en 2 pasos" (si no está activada)
3. Buscar "Contraseñas de aplicaciones" o "App passwords"
4. Generar nueva contraseña
5. Seleccionar "Correo" y "Otro"
6. Copiar el código de 16 dígitos

**Luego, agregar variables:**
```bash
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=tu-email@gmail.com
MAIL_PASS=xxxx xxxx xxxx xxxx  # El código de 16 dígitos
MAIL_FROM=noreply@tudominio.com
```

#### Si usás otro proveedor:

**Outlook/Hotmail:**
```bash
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
```

**Yahoo:**
```bash
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
```

### 3.5 AFIP (OPCIONAL - Para facturación electrónica)

```bash
AFIP_CUIT=20123456789  # Tu CUIT real
AFIP_MODE=testing      # Empezar con testing
```

**Nota:** Para AFIP también necesitás certificados, eso se configura después.

---

## 💾 Paso 4: Configurar Volúmenes Persistentes

En la sección **"Volumes"** o **"Persistent Storage"**:

```
Volúmenes a crear:
├─ /var/www/html/logs
├─ /var/www/html/storage
├─ /var/www/html/vistas/img/usuarios
├─ /var/www/html/vistas/img/productos
└─ /var/www/html/vistas/img/empresa
```

**¿Por qué?** Para que no pierdas:
- Logs de la aplicación
- Imágenes de usuarios
- Imágenes de productos
- Logo de la empresa

---

## 🌐 Paso 5: Configurar Dominio (OPCIONAL)

### Si tenés dominio:

1. En tu proveedor de dominio (GoDaddy, Namecheap, etc.):
   - Crear registro A: `erp.tudominio.com` → IP de tu servidor Dokploy

2. En Dokploy:
   - Ir a sección **"Domains"**
   - Click en **"Add Domain"**
   - Ingresar: `erp.tudominio.com`
   - Habilitar: **"SSL/TLS"** o **"Let's Encrypt"**
   - Guardar

### Si NO tenés dominio:

- Podés acceder por IP: `http://tu-ip-servidor:puerto`
- Dokploy te asignará un puerto automáticamente

---

## 🚀 Paso 6: Deploy!

1. Verificar que:
   - ✅ MySQL está corriendo (verde)
   - ✅ Variables de entorno configuradas
   - ✅ Volúmenes configurados

2. Click en **"Deploy"** o **"Build & Deploy"**

3. Ver logs en tiempo real:
   - Se va a clonar el repo
   - Se va a construir la imagen Docker
   - Se va a instalar Composer
   - Se va a iniciar Apache

4. Esperar ~3-5 minutos

5. Cuando diga **"Running"** o **"Healthy"** → ¡Listo!

---

## 🗄️ Paso 7: Importar Base de Datos

Ahora la aplicación está corriendo pero la base de datos está **vacía**.

### ⚠️ NECESITÁS EL ARCHIVO SQL

**¿De dónde sale?**

1. **Si ya tenés el sistema corriendo en otro servidor:**
   ```bash
   # Exportar desde servidor actual
   mysqldump -u usuario -p nombre_bd > backup.sql
   ```

2. **Si es instalación nueva:**
   - Necesitás el archivo `demo_db.sql` del proyecto
   - O crear la estructura manualmente

### Método 1: Via Línea de Comandos (Dokploy)

1. Subir archivo SQL al servidor
2. En terminal de Dokploy:

```bash
# Importar
docker exec -i newmoon-mysql mysql \
  -u newmoon_user \
  -p'TU_PASSWORD' \
  newmoon_db < /ruta/al/backup.sql
```

### Método 2: Via phpMyAdmin

Si configuraste phpMyAdmin en el docker-compose:

1. Acceder a phpMyAdmin (puerto 8081 por defecto)
2. Login con:
   - Usuario: `newmoon_user`
   - Password: el que configuraste
3. Seleccionar base de datos: `newmoon_db`
4. Click en "Importar"
5. Seleccionar tu archivo `.sql`
6. Click en "Continuar"

### Método 3: Copiar y Ejecutar

```bash
# 1. Copiar SQL al contenedor
docker cp backup.sql newmoon-mysql:/tmp/

# 2. Importar
docker exec -i newmoon-mysql mysql \
  -u newmoon_user \
  -p'TU_PASSWORD' \
  newmoon_db < /tmp/backup.sql

# 3. Verificar
docker exec newmoon-mysql mysql \
  -u newmoon_user \
  -p'TU_PASSWORD' \
  newmoon_db \
  -e "SHOW TABLES;"
```

---

## ✅ Paso 8: Verificar que Todo Funciona

### 8.1 Acceder a la Aplicación

```
https://tu-dominio.com
o
http://tu-ip-servidor:puerto
```

### 8.2 Login por Defecto

```
Usuario: admin
Contraseña: admin123
```

### 8.3 Checklist de Verificación

- [ ] La página carga correctamente
- [ ] Login funciona
- [ ] Dashboard muestra datos
- [ ] Módulo de Productos funciona
- [ ] Módulo de Ventas funciona
- [ ] Módulo de Clientes funciona
- [ ] Imágenes se ven correctamente

---

## 🔒 Paso 9: Seguridad (IMPORTANTE)

### 9.1 Cambiar Contraseña de Admin

1. Login como admin
2. Ir a configuración de usuario
3. Cambiar contraseña
4. Logout y login con nueva contraseña

### 9.2 Crear Usuarios Adicionales

1. Ir a sección "Usuarios"
2. Crear usuarios con permisos específicos
3. NO usar admin para operaciones diarias

### 9.3 Verificar SSL

Si configuraste dominio:
- Verificar que el candado verde aparezca
- Forzar HTTPS en Dokploy

---

## 🔧 Paso 10: Configuración Adicional

### 10.1 Configurar Datos de Empresa

1. Login al sistema
2. Ir a "Empresa" o "Configuración"
3. Completar:
   - Razón social
   - CUIT
   - Dirección
   - Logo
   - Datos de contacto

### 10.2 Configurar MercadoPago (si aplica)

1. Ir a configuración de MercadoPago
2. Verificar que las credenciales están correctas
3. Hacer una venta de prueba con monto bajo

### 10.3 Configurar AFIP (si aplica)

1. Obtener certificado de AFIP
2. Subir certificado al servidor
3. Configurar en el sistema

---

## 🆘 Problemas Comunes

### "Cannot connect to database"

**Solución:**
```bash
# Verificar que MySQL está corriendo
docker ps | grep mysql

# Ver logs de MySQL
docker logs newmoon-mysql

# Verificar variables de entorno
# En Dokploy, revisar que DB_HOST, DB_USER, DB_PASSWORD sean correctos
```

### "500 Internal Server Error"

**Solución:**
```bash
# Ver logs de la aplicación en Dokploy
# O vía terminal:
docker logs newmoon-app

# Ver logs de Apache
docker exec newmoon-app tail -f /var/log/apache2/error.log
```

### "Page not found" o "404"

**Solución:**
- Verificar que el `.htaccess` existe
- Verificar que `mod_rewrite` está habilitado (ya incluido en Dockerfile)

### No puedo importar la BD

**Solución:**
```bash
# Verificar que el archivo SQL existe
ls -lh backup.sql

# Verificar permisos
chmod 644 backup.sql

# Intentar importar con verbose
docker exec -i newmoon-mysql mysql \
  -u newmoon_user \
  -p'PASSWORD' \
  --verbose \
  newmoon_db < backup.sql
```

---

## 📊 Backups Automáticos

### Configurar Backup Diario

Crear script en servidor:

```bash
#!/bin/bash
# /root/backup-newmoon.sh

BACKUP_DIR="/backups/newmoon"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup de BD
docker exec newmoon-mysql mysqldump \
  -u newmoon_user \
  -p'PASSWORD' \
  newmoon_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup de archivos subidos
docker cp newmoon-app:/var/www/html/vistas/img $BACKUP_DIR/img_$DATE/

# Limpiar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "img_*" -mtime +30 -exec rm -rf {} \;

echo "Backup completado: $DATE"
```

Agregar a cron:
```bash
# Ejecutar todos los días a las 3 AM
0 3 * * * /root/backup-newmoon.sh >> /var/log/backup-newmoon.log 2>&1
```

---

## 📞 Soporte

### Documentación
- **README.md** - Documentación principal del proyecto
- **README-DOCKER.md** - Guía completa de Docker
- **QUICKSTART-DOKPLOY.md** - Guía rápida

### Logs Importantes
```bash
# Logs de aplicación
docker logs -f newmoon-app

# Logs de MySQL
docker logs -f newmoon-mysql

# Logs de Apache
docker exec newmoon-app tail -f /var/log/apache2/error.log

# Logs de PHP (si existen)
docker exec newmoon-app tail -f /var/log/php_errors.log
```

### Contacto
- 📧 Email: soporte@moondesarrollos.com
- 🌐 Web: https://moondesarrollos.com

---

## ✨ ¡Listo!

Ahora tenés NewMoon ERP/POS corriendo en Dokploy.

### Próximos Pasos Sugeridos:

1. ✅ Hacer una venta de prueba
2. ✅ Cargar algunos productos reales
3. ✅ Configurar backup automático
4. ✅ Entrenar a usuarios
5. ✅ ¡Empezar a usar el sistema!

---

**Desarrollado con ❤️ por Moon Desarrollos**
