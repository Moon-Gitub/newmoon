# 🐳 NewMoon ERP/POS - Guía de Deployment con Docker y Dokploy

Esta guía te ayudará a deployar el sistema NewMoon ERP/POS usando Docker y Dokploy.

## 📋 Tabla de Contenidos

- [Requisitos](#requisitos)
- [Deployment en Dokploy](#deployment-en-dokploy)
- [Desarrollo Local](#desarrollo-local)
- [Variables de Entorno](#variables-de-entorno)
- [Gestión de Base de Datos](#gestión-de-base-de-datos)
- [Troubleshooting](#troubleshooting)
- [Comandos Útiles](#comandos-útiles)

---

## 🎯 Requisitos

### Para Dokploy:
- Servidor con Dokploy instalado
- Repositorio Git configurado
- Dominio configurado (opcional)

### Para desarrollo local:
- Docker 20.10+
- Docker Compose 2.0+
- Git

---

## 🚀 Deployment en Dokploy

### Paso 1: Configurar el Proyecto en Dokploy

1. **Acceder a Dokploy Dashboard**
   - Ingresa a tu instancia de Dokploy: `https://tu-dokploy.com`

2. **Crear Nuevo Proyecto**
   - Click en "New Project"
   - Nombre: `newmoon-erp`
   - Tipo: **Dockerfile**

3. **Configurar Repository**
   ```
   Repository URL: https://github.com/TU_USUARIO/newmoon.git
   Branch: main
   Dockerfile Path: ./Dockerfile
   ```

### Paso 2: Configurar Base de Datos MySQL

En Dokploy, crea un servicio de MySQL:

1. **Crear servicio MySQL**
   - Click en "Add Service"
   - Tipo: **MySQL/MariaDB**
   - Nombre: `newmoon-mysql`

2. **Configurar MySQL**
   ```
   Database Name: newmoon_db
   User: newmoon_user
   Password: [GENERAR PASSWORD SEGURO]
   Root Password: [GENERAR PASSWORD SEGURO]
   ```

3. **Configurar Volúmenes**
   - Mapear `/var/lib/mysql` para persistencia

### Paso 3: Configurar Variables de Entorno

En la configuración del proyecto en Dokploy, agregar las siguientes variables:

#### 🗄️ Base de Datos
```bash
DB_HOST=newmoon-mysql
DB_PORT=3306
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=tu_password_seguro
WAIT_FOR_DB=true
```

#### 🌐 Aplicación
```bash
APP_NAME=NewMoon ERP/POS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
TZ=America/Argentina/Buenos_Aires
```

#### 💳 MercadoPago
```bash
MP_PUBLIC_KEY=APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MP_ACCESS_TOKEN=APP_USR-xxxxxxxxxxxx-xxxxxx-xxxxxx
MP_MODE=live
```

#### 📧 Email (Opcional)
```bash
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=tu-email@gmail.com
MAIL_PASS=tu-app-password
MAIL_FROM=noreply@tudominio.com
```

#### 🧾 AFIP
```bash
AFIP_MODE=production
AFIP_CUIT=20123456789
```

### Paso 4: Configurar Volúmenes Persistentes

En Dokploy, configurar los siguientes volúmenes para persistir datos:

```yaml
Volúmenes:
  - /var/www/html/logs
  - /var/www/html/storage
  - /var/www/html/vistas/img/usuarios
  - /var/www/html/vistas/img/productos
  - /var/www/html/vistas/img/empresa
  - /var/www/html/controladores/facturacion/keys
```

### Paso 5: Configurar Dominio y SSL

1. **Dominio**
   - En Dokploy, ir a "Domains"
   - Agregar tu dominio: `erp.tudominio.com`
   - Habilitar HTTPS automático con Let's Encrypt

2. **Health Check**
   - Path: `/`
   - Interval: 30s
   - Timeout: 10s

### Paso 6: Deploy

1. Click en **"Deploy"**
2. Dokploy automáticamente:
   - ✅ Clonará el repositorio
   - ✅ Construirá la imagen Docker
   - ✅ Instalará dependencias con Composer
   - ✅ Configurará Apache y PHP
   - ✅ Iniciará la aplicación

3. **Monitorear logs**:
   - Ver logs en tiempo real en Dokploy Dashboard
   - Verificar que no haya errores

### Paso 7: Importar Base de Datos

Una vez desplegada la aplicación:

#### Opción A: Via phpMyAdmin (si está habilitado)
```
1. Acceder a phpMyAdmin
2. Seleccionar base de datos 'newmoon_db'
3. Importar -> Seleccionar archivo SQL
4. Ejecutar
```

#### Opción B: Via Terminal en Dokploy
```bash
# Acceder al contenedor de MySQL
docker exec -i newmoon-mysql mysql -u newmoon_user -p newmoon_db < backup.sql
```

#### Opción C: Via comando en el contenedor de la app
```bash
# Conectarse al contenedor
docker exec -it [container-id] bash

# Importar SQL
mysql -h newmoon-mysql -u newmoon_user -p newmoon_db < /path/to/dump.sql
```

### Paso 8: Verificación Post-Deploy

1. **Acceder a la aplicación**
   - URL: `https://tu-dominio.com`

2. **Login por defecto**
   ```
   Usuario: admin
   Contraseña: admin123
   ```
   ⚠️ **CAMBIAR INMEDIATAMENTE EN PRODUCCIÓN**

3. **Verificar funcionalidades**:
   - ✅ Login funciona
   - ✅ Dashboard carga correctamente
   - ✅ Módulos de ventas, productos, clientes
   - ✅ Integración con MercadoPago (si configurado)

---

## 💻 Desarrollo Local

### Opción 1: Docker Compose (Recomendado)

```bash
# 1. Clonar repositorio
git clone https://github.com/TU_USUARIO/newmoon.git
cd newmoon

# 2. Copiar archivo de variables de entorno
cp .env.example .env

# 3. Editar .env con tus valores
nano .env

# 4. Iniciar servicios
docker-compose up -d

# 5. Ver logs
docker-compose logs -f app

# 6. Acceder a la aplicación
# http://localhost:8080
```

### Opción 2: Solo Docker

```bash
# 1. Construir imagen
docker build -t newmoon-erp .

# 2. Ejecutar contenedor
docker run -d \
  --name newmoon-app \
  -p 8080:80 \
  -e DB_HOST=tu-mysql-host \
  -e DB_NAME=newmoon_db \
  -e DB_USER=newmoon_user \
  -e DB_PASSWORD=tu_password \
  newmoon-erp

# 3. Ver logs
docker logs -f newmoon-app
```

### Importar Base de Datos en Desarrollo

```bash
# Con docker-compose
docker-compose exec mysql mysql -u newmoon_user -p newmoon_db < base_datos/demo_db.sql

# O con phpMyAdmin
# Acceder a http://localhost:8081
# Importar archivo SQL manualmente
```

---

## 🔐 Variables de Entorno

Todas las variables disponibles en `.env.example`:

### Variables Críticas (OBLIGATORIAS)

```bash
# Base de Datos
DB_HOST=mysql              # Host de MySQL
DB_NAME=newmoon_db         # Nombre de la BD
DB_USER=newmoon_user       # Usuario
DB_PASSWORD=password       # Contraseña

# Aplicación
APP_URL=https://tu-url.com # URL pública
```

### Variables Opcionales

Ver archivo `.env.example` completo para todas las opciones disponibles.

---

## 🗄️ Gestión de Base de Datos

### Backup Manual

```bash
# Backup completo
docker exec newmoon-mysql mysqldump \
  -u newmoon_user \
  -p newmoon_db \
  > backup-$(date +%Y%m%d).sql

# Comprimir backup
gzip backup-$(date +%Y%m%d).sql
```

### Restore desde Backup

```bash
# Descomprimir si está comprimido
gunzip backup-20240101.sql.gz

# Restaurar
docker exec -i newmoon-mysql mysql \
  -u newmoon_user \
  -p newmoon_db \
  < backup-20240101.sql
```

### Backup Automático en Dokploy

Configurar un cron job en Dokploy:

```bash
# Crear script de backup
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)

docker exec newmoon-mysql mysqldump \
  -u newmoon_user \
  -pPASSWORD \
  newmoon_db | gzip > $BACKUP_DIR/newmoon_$DATE.sql.gz

# Mantener solo últimos 30 días
find $BACKUP_DIR -name "newmoon_*.sql.gz" -mtime +30 -delete
```

---

## 🔧 Troubleshooting

### Error: No se puede conectar a MySQL

```bash
# Verificar que MySQL esté corriendo
docker ps | grep mysql

# Ver logs de MySQL
docker logs newmoon-mysql

# Verificar conectividad desde app
docker exec newmoon-app ping mysql
```

### Error: Permisos de archivos

```bash
# Entrar al contenedor
docker exec -it newmoon-app bash

# Verificar permisos
ls -la logs/
ls -la storage/

# Corregir permisos (como root)
chown -R www-data:www-data logs storage vistas/img
```

### Error: Composer dependencies

```bash
# Rebuild forzado
docker-compose build --no-cache app
docker-compose up -d
```

### Ver logs detallados

```bash
# Logs de la aplicación
docker logs -f --tail=100 newmoon-app

# Logs de MySQL
docker logs -f --tail=100 newmoon-mysql

# Logs de Apache dentro del contenedor
docker exec newmoon-app tail -f /var/log/apache2/error.log
```

### Resetear completamente

```bash
# Detener y eliminar todo
docker-compose down -v

# Eliminar volúmenes (⚠️ ESTO BORRA LA BD)
docker volume prune

# Reconstruir
docker-compose up -d --build
```

---

## 📝 Comandos Útiles

### Docker Compose

```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Ver logs
docker-compose logs -f [servicio]

# Reconstruir imagen
docker-compose build [servicio]

# Ejecutar comando en contenedor
docker-compose exec app bash

# Ver estado de servicios
docker-compose ps
```

### Docker directo

```bash
# Listar contenedores
docker ps

# Entrar a contenedor
docker exec -it [container-id] bash

# Ver logs
docker logs -f [container-id]

# Copiar archivos
docker cp archivo.sql [container-id]:/tmp/

# Inspeccionar contenedor
docker inspect [container-id]
```

### MySQL en contenedor

```bash
# Conectarse a MySQL
docker exec -it newmoon-mysql mysql -u root -p

# Verificar bases de datos
docker exec newmoon-mysql mysql -u root -p -e "SHOW DATABASES;"

# Verificar usuarios
docker exec newmoon-mysql mysql -u root -p -e "SELECT User, Host FROM mysql.user;"
```

---

## 📊 Monitoreo en Producción

### Health Checks

El Dockerfile incluye health checks automáticos:

```bash
# Ver estado de health check
docker inspect --format='{{json .State.Health}}' newmoon-app | jq
```

### Recursos

```bash
# Ver uso de recursos
docker stats newmoon-app newmoon-mysql

# Ver uso de disco
docker system df

# Limpiar recursos no usados
docker system prune -a
```

---

## 🔒 Seguridad

### Checklist de Seguridad

- [ ] Cambiar contraseñas por defecto
- [ ] Configurar HTTPS con SSL
- [ ] Usar variables de entorno para secretos
- [ ] No exponer puerto 3306 de MySQL en producción
- [ ] Configurar backups automáticos
- [ ] Mantener Docker y imágenes actualizadas
- [ ] Revisar logs regularmente

### Actualizar contraseñas

```bash
# En MySQL
docker exec -it newmoon-mysql mysql -u root -p

ALTER USER 'newmoon_user'@'%' IDENTIFIED BY 'nuevo_password_seguro';
FLUSH PRIVILEGES;
```

---

## 🆘 Soporte

### Logs importantes

```bash
# Logs de aplicación
/var/www/html/logs/

# Logs de Apache
/var/log/apache2/error.log
/var/log/apache2/access.log

# Logs de PHP
/var/log/php_errors.log
```

### Contacto

- 📧 Email: soporte@moondesarrollos.com
- 🌐 Web: https://moondesarrollos.com
- 📚 Documentación: Ver README.md principal

---

## 🎉 ¡Listo!

Tu sistema NewMoon ERP/POS debería estar corriendo. Accede a la URL configurada y comienza a usar el sistema.

**Usuarios por defecto:**
```
Usuario: admin
Contraseña: admin123
```

⚠️ **Recuerda cambiar las credenciales inmediatamente en producción**

---

**Desarrollado con ❤️ por Moon Desarrollos**
