# 🚀 Inicio Rápido - Dokploy

Guía rápida para deployar NewMoon ERP/POS en Dokploy en 10 minutos.

## ⚡ Pasos Rápidos

### 1️⃣ Preparar en GitHub

```bash
# Ya está listo en tu repo!
# El Dockerfile y configuración están incluidos
```

### 2️⃣ Crear MySQL en Dokploy

1. En Dokploy Dashboard → **"Add Service"**
2. Seleccionar: **MySQL/MariaDB**
3. Configurar:
   ```
   Name: newmoon-mysql
   Database: newmoon_db
   Username: newmoon_user
   Password: [GENERAR PASSWORD SEGURO]
   ```
4. Click **"Deploy"**

### 3️⃣ Crear Aplicación en Dokploy

1. Dashboard → **"New Project"**
2. Configurar:
   ```
   Name: newmoon-erp
   Type: Dockerfile
   Repository: https://github.com/TU_USUARIO/newmoon.git
   Branch: main
   Dockerfile Path: ./Dockerfile
   ```

### 4️⃣ Variables de Entorno (Mínimas)

En la sección **"Environment Variables"** agregar:

```bash
# Base de Datos (OBLIGATORIO)
DB_HOST=newmoon-mysql
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=el_password_que_configuraste
WAIT_FOR_DB=true

# Aplicación (OBLIGATORIO)
APP_URL=https://tu-dominio.com
APP_ENV=production
TZ=America/Argentina/Buenos_Aires

# MercadoPago (si vas a usar cobros online)
MP_PUBLIC_KEY=tu_public_key
MP_ACCESS_TOKEN=tu_access_token
MP_MODE=live
```

### 5️⃣ Configurar Volúmenes Persistentes

En **"Volumes"**, agregar:

```
/var/www/html/logs
/var/www/html/storage
/var/www/html/vistas/img/usuarios
/var/www/html/vistas/img/productos
```

### 6️⃣ Configurar Dominio

1. En **"Domains"** → **"Add Domain"**
2. Ingresar: `erp.tudominio.com`
3. Habilitar: **Auto SSL (Let's Encrypt)**

### 7️⃣ Deploy!

1. Click en **"Deploy"**
2. Esperar ~3-5 minutos
3. Monitorear logs en tiempo real

### 8️⃣ Importar Base de Datos

Una vez deployed, importar tu SQL:

```bash
# Opción 1: Via Dokploy Terminal
docker exec -i newmoon-mysql mysql -u newmoon_user -p newmoon_db < tu_backup.sql

# Opción 2: Via phpMyAdmin (si está habilitado)
# Acceder y subir el archivo SQL
```

### 9️⃣ Acceder a la Aplicación

```
URL: https://tu-dominio.com
Usuario: admin
Contraseña: admin123
```

⚠️ **IMPORTANTE: Cambiar password inmediatamente!**

---

## 🎯 Variables de Entorno Completas

Si necesitas todas las opciones, copia esto:

```bash
# === BASE DE DATOS ===
DB_HOST=newmoon-mysql
DB_PORT=3306
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=tu_password_seguro
WAIT_FOR_DB=true

# === APLICACIÓN ===
APP_NAME=NewMoon ERP/POS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.tudominio.com
TZ=America/Argentina/Buenos_Aires

# === MERCADOPAGO ===
MP_PUBLIC_KEY=APP_USR-xxxxxxxx
MP_ACCESS_TOKEN=APP_USR-xxxxxxxx
MP_MODE=live

# === EMAIL (Opcional) ===
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=tu-email@gmail.com
MAIL_PASS=tu-app-password
MAIL_FROM=noreply@tudominio.com

# === AFIP ===
AFIP_MODE=production
AFIP_CUIT=20123456789
```

---

## 📝 Checklist Post-Deploy

- [ ] Aplicación carga correctamente
- [ ] Login funciona (admin/admin123)
- [ ] Cambiar contraseña de admin
- [ ] Crear usuarios adicionales
- [ ] Configurar datos de empresa
- [ ] Probar módulo de ventas
- [ ] Probar módulo de productos
- [ ] Verificar integración MercadoPago
- [ ] Configurar backup automático
- [ ] SSL habilitado y funcionando

---

## 🆘 Problemas Comunes

### "Cannot connect to database"
```bash
# Verificar que MySQL esté corriendo
# Verificar las credenciales en variables de entorno
# Verificar que DB_HOST apunte al servicio correcto
```

### "500 Internal Server Error"
```bash
# Ver logs en Dokploy
# Verificar permisos de carpetas
# Verificar que las dependencias de Composer se instalaron
```

### "Page not found"
```bash
# Verificar que mod_rewrite esté habilitado (ya incluido en Dockerfile)
# Verificar archivo .htaccess
```

---

## 📚 Documentación Completa

Para más detalles, ver:
- **[README-DOCKER.md](README-DOCKER.md)** - Guía completa
- **[README.md](README.md)** - Documentación del proyecto
- **[.env.example](.env.example)** - Todas las variables disponibles

---

## 💬 Soporte

¿Problemas? Contacta a:
- 📧 soporte@moondesarrollos.com
- 🌐 https://moondesarrollos.com

---

**¡Listo para usar! 🎉**
