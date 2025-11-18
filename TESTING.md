# 🧪 Plan de Testing - NewMoon ERP/POS

Guía para probar todos los componentes del sistema de forma incremental.

---

## 📋 Estrategia de Testing

Vamos a probar **componente por componente**, validando que funcione antes de continuar.

---

## 🎯 Fase 1: Testing Local con Docker

### Test 1.1: Validar Archivos Docker ✅

**Objetivo:** Verificar que todos los archivos necesarios existen.

```bash
# Ejecutar desde la raíz del proyecto
ls -la Dockerfile
ls -la docker-compose.yml
ls -la docker-entrypoint.sh
ls -la .dockerignore
ls -la .env.example
ls -la Makefile
ls -la docker/mysql/my.cnf
```

**Resultado esperado:** Todos los archivos deben existir
**Si falla:** Verificar que estés en la rama correcta

---

### Test 1.2: Validar Sintaxis de Docker ✅

**Objetivo:** Verificar que el Dockerfile y docker-compose son válidos.

```bash
# Validar docker-compose.yml
docker-compose config

# Validar Dockerfile (dry-run)
docker build --no-cache --target composer-build -t test-composer .
```

**Resultado esperado:**
- `docker-compose config` muestra la configuración sin errores
- El build del stage composer-build debe completarse sin errores

**Si falla:**
- Verificar sintaxis YAML
- Verificar que Docker esté corriendo

---

### Test 1.3: Configurar Variables de Entorno ✅

**Objetivo:** Preparar archivo .env para testing local.

```bash
# Copiar template
cp .env.example .env

# Editar .env
nano .env
```

**Configuración mínima para testing:**
```bash
# Base de Datos
DB_HOST=mysql
DB_PORT=3306
DB_NAME=newmoon_db
DB_USER=newmoon_user
DB_PASSWORD=test_password_123
WAIT_FOR_DB=true

# Aplicación
APP_NAME=NewMoon ERP/POS
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
TZ=America/Argentina/Buenos_Aires

# MercadoPago (opcional para testing básico)
MP_PUBLIC_KEY=
MP_ACCESS_TOKEN=
MP_MODE=test
```

**Resultado esperado:** Archivo `.env` creado con valores de testing
**Si falla:** Verificar permisos de escritura

---

### Test 1.4: Build de Imagen Docker 🔨

**Objetivo:** Construir la imagen Docker completa.

```bash
# Opción 1: Con Make
make build

# Opción 2: Con Docker Compose
docker-compose build --no-cache

# Ver progreso
# Debería ver:
# - Descarga de imagen PHP 8.1
# - Instalación de dependencias del sistema
# - Instalación de extensiones PHP
# - Configuración de Apache
# - Instalación de Composer
# - Instalación de dependencias PHP
```

**Tiempo estimado:** 5-10 minutos (primera vez)

**Resultado esperado:**
```
Successfully built [image-id]
Successfully tagged newmoon-app:latest
```

**Si falla:**
- Verificar conexión a internet
- Verificar espacio en disco: `docker system df`
- Ver logs completos para identificar error

---

### Test 1.5: Iniciar Servicio MySQL 🗄️

**Objetivo:** Levantar solo MySQL primero.

```bash
# Iniciar solo MySQL
docker-compose up -d mysql

# Ver logs
docker-compose logs -f mysql

# Esperar mensaje:
# "mysqld: ready for connections"
```

**Verificación:**
```bash
# Ver que esté corriendo
docker-compose ps

# Debería mostrar:
# mysql ... Up ... 3306/tcp
```

**Conectarse a MySQL para probar:**
```bash
docker-compose exec mysql mysql -u root -ptest_password_123 -e "SHOW DATABASES;"

# Debería listar:
# - information_schema
# - mysql
# - performance_schema
# - newmoon_db  ← Esta es nuestra BD
```

**Resultado esperado:** MySQL corriendo y respondiendo
**Si falla:**
- Ver logs: `docker-compose logs mysql`
- Verificar puerto 3306 no está en uso: `lsof -i :3306`

---

### Test 1.6: Iniciar Aplicación 🚀

**Objetivo:** Levantar el contenedor de la aplicación.

```bash
# Iniciar aplicación
docker-compose up -d app

# Ver logs en tiempo real
docker-compose logs -f app

# Buscar mensajes:
# "🌙 NewMoon ERP/POS - Iniciando contenedor..."
# "⏳ Esperando conexión con MySQL..."
# "✅ MySQL está listo!"
# "✅ Inicialización completada"
# "🚀 Iniciando Apache..."
```

**Verificación:**
```bash
# Ver que esté corriendo
docker-compose ps

# Ambos contenedores deben estar "Up"
```

**Resultado esperado:**
- Aplicación inicia sin errores
- Se conecta a MySQL exitosamente
- Apache inicia correctamente

**Si falla:**
- Ver logs completos: `docker-compose logs app`
- Verificar que MySQL esté corriendo primero
- Verificar variables de entorno en .env

---

### Test 1.7: Verificar Conectividad 🔌

**Objetivo:** Probar que la app puede acceder a MySQL.

```bash
# Entrar al contenedor de la app
docker-compose exec app bash

# Dentro del contenedor, probar conexión MySQL
ping -c 3 mysql

# Probar conexión a la BD
mysql -h mysql -u newmoon_user -ptest_password_123 newmoon_db -e "SELECT 1 as test;"

# Debería mostrar:
# +------+
# | test |
# +------+
# |    1 |
# +------+

# Salir del contenedor
exit
```

**Resultado esperado:** Conexión exitosa entre contenedores
**Si falla:** Verificar red Docker: `docker network ls`

---

### Test 1.8: Verificar Archivo de Conexión Auto-generado 📝

**Objetivo:** Verificar que el entrypoint creó modelos/conexion.php

```bash
# Ver el archivo generado
docker-compose exec app cat /var/www/html/modelos/conexion.php

# Debería mostrar:
# - hostDB = mysql
# - nameDB = newmoon_db
# - userDB = newmoon_user
# - passDB = test_password_123
```

**Resultado esperado:** Archivo creado con valores correctos desde .env
**Si falla:**
- Ver logs del entrypoint: `docker-compose logs app | grep "Creando archivo"`
- Verificar variables de entorno: `docker-compose exec app env | grep DB_`

---

### Test 1.9: Verificar Permisos de Carpetas 🔒

**Objetivo:** Validar que las carpetas tienen permisos correctos.

```bash
# Ver permisos
docker-compose exec app ls -la logs/
docker-compose exec app ls -la storage/
docker-compose exec app ls -la vistas/img/

# Todos deberían ser:
# drwxrwxr-x ... www-data www-data
```

**Resultado esperado:** Permisos 775 y owner www-data
**Si falla:** Ejecutar: `make permissions`

---

### Test 1.10: Acceder desde el Navegador 🌐

**Objetivo:** Verificar que el servidor web responde.

```bash
# La aplicación debería estar en:
http://localhost:8080
```

**Probar:**
1. Abrir navegador
2. Ir a `http://localhost:8080`

**Resultado esperado (SIN base de datos aún):**
- ✅ Apache responde (no error 502/503)
- ⚠️ Puede dar error de base de datos vacía (normal, no la importamos aún)
- ⚠️ Puede mostrar página en blanco o error PHP (normal sin datos)

**Lo importante es que NO de:**
- ❌ "Connection refused" → revisar si app está corriendo
- ❌ "502 Bad Gateway" → revisar logs de Apache
- ❌ "This site can't be reached" → verificar puerto 8080

---

### Test 1.11: Verificar Logs de Apache/PHP 📋

**Objetivo:** Ver si hay errores de PHP o Apache.

```bash
# Logs de Apache (errores)
docker-compose exec app tail -f /var/log/apache2/error.log

# Logs de acceso
docker-compose exec app tail -f /var/log/apache2/access.log

# Presionar Ctrl+C para salir
```

**Buscar:**
- ✅ Requests llegando al servidor
- ⚠️ Errores de base de datos (normal sin importar SQL)
- ❌ Errores de sintaxis PHP (investigar)

---

## 🗄️ Fase 2: Base de Datos

### Test 2.1: Preparar Dump SQL 📦

**Objetivo:** Tener un archivo SQL para importar.

**Opciones:**

**A) Si tenés un backup existente:**
```bash
# Copiar a la raíz del proyecto
cp /ruta/a/backup.sql ./newmoon_backup.sql
```

**B) Si es instalación nueva, crear BD de prueba:**
```sql
-- Crear archivo: test_schema.sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    email VARCHAR(255),
    password VARCHAR(255)
);

INSERT INTO usuarios (nombre, email, password) VALUES
('Admin Test', 'admin@test.com', 'test123');
```

---

### Test 2.2: Importar Base de Datos 📥

**Objetivo:** Cargar datos en MySQL.

```bash
# Opción 1: Con Make (recomendado)
make restore FILE=newmoon_backup.sql

# Opción 2: Manual
docker-compose exec -T mysql mysql \
  -u newmoon_user \
  -ptest_password_123 \
  newmoon_db < newmoon_backup.sql

# Ver progreso (puede tomar varios minutos)
```

**Verificar importación:**
```bash
# Ver tablas importadas
docker-compose exec mysql mysql \
  -u newmoon_user \
  -ptest_password_123 \
  newmoon_db \
  -e "SHOW TABLES;"

# Contar registros de usuarios
docker-compose exec mysql mysql \
  -u newmoon_user \
  -ptest_password_123 \
  newmoon_db \
  -e "SELECT COUNT(*) FROM usuarios;"
```

**Resultado esperado:**
- Tablas importadas sin errores
- Datos presentes en tablas

**Si falla:**
- Ver tamaño del archivo: `ls -lh newmoon_backup.sql`
- Verificar sintaxis SQL
- Intentar importar en partes si es muy grande

---

### Test 2.3: Acceder a la Aplicación con Datos ✅

**Objetivo:** Login en la aplicación.

```bash
# Abrir navegador
http://localhost:8080
```

**Probar login:**
```
Usuario: admin
Password: admin123
(o los que hayas configurado en tu BD)
```

**Resultado esperado:**
- ✅ Pantalla de login aparece
- ✅ Login exitoso
- ✅ Dashboard carga
- ✅ Módulos funcionan

**Si falla:**
- Ver logs PHP: `docker-compose exec app tail -f /var/log/apache2/error.log`
- Verificar datos en BD
- Verificar sesiones PHP

---

## 🧰 Fase 3: Funcionalidades

### Test 3.1: Navegación Básica 🧭

**Probar cada módulo:**
```
✅ Dashboard
✅ Productos
✅ Clientes
✅ Ventas
✅ Compras
✅ Reportes
```

**Anotar cualquier error que aparezca**

---

### Test 3.2: Subir Imágenes 📸

**Objetivo:** Verificar permisos de escritura.

```
1. Ir a Productos
2. Crear/Editar producto
3. Subir imagen
4. Guardar
```

**Verificar:**
```bash
# Ver si la imagen se guardó
docker-compose exec app ls -la vistas/img/productos/

# Debería aparecer la imagen subida
```

---

### Test 3.3: phpMyAdmin (Opcional) 🗃️

**Objetivo:** Acceder a la BD con interfaz gráfica.

```bash
# Iniciar phpMyAdmin
docker-compose --profile development up -d phpmyadmin

# Acceder
http://localhost:8081

# Login:
Server: mysql
Username: newmoon_user
Password: test_password_123
```

---

## 🎯 Fase 4: Testing Avanzado

### Test 4.1: Health Check 🏥

```bash
# Ver estado de health check
docker inspect newmoon-app | grep -A 10 Health

# Debería mostrar "healthy"
```

---

### Test 4.2: Restart del Sistema 🔄

```bash
# Detener todo
docker-compose down

# Iniciar de nuevo
docker-compose up -d

# Verificar que todo vuelve a funcionar
```

**Resultado esperado:**
- Datos persisten (no se pierden)
- Aplicación vuelve a funcionar

---

### Test 4.3: Backup 💾

```bash
# Crear backup
make backup

# Debería crear archivo en backups/backup-YYYYMMDD-HHMMSS.sql

# Verificar
ls -lh backups/
```

---

## 🚀 Fase 5: Deployment en Dokploy

**Solo continuar si TODOS los tests locales pasaron ✅**

### Test 5.1: Push Final a GitHub

```bash
# Asegurar que todo está commiteado
git status

# Si hay cambios
git add -A
git commit -m "test: validar configuración local antes de deployment"
git push
```

---

### Test 5.2: Deployment en Dokploy

Seguir: [QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md)

---

## 📊 Checklist de Testing

### ✅ Fase 1: Docker Local
- [ ] Test 1.1: Archivos existen
- [ ] Test 1.2: Sintaxis válida
- [ ] Test 1.3: .env configurado
- [ ] Test 1.4: Build exitoso
- [ ] Test 1.5: MySQL corriendo
- [ ] Test 1.6: App corriendo
- [ ] Test 1.7: Conectividad OK
- [ ] Test 1.8: conexion.php generado
- [ ] Test 1.9: Permisos OK
- [ ] Test 1.10: Navegador responde
- [ ] Test 1.11: Logs sin errores críticos

### ✅ Fase 2: Base de Datos
- [ ] Test 2.1: SQL preparado
- [ ] Test 2.2: Importación exitosa
- [ ] Test 2.3: Login funciona

### ✅ Fase 3: Funcionalidades
- [ ] Test 3.1: Navegación OK
- [ ] Test 3.2: Upload de imágenes
- [ ] Test 3.3: phpMyAdmin (opcional)

### ✅ Fase 4: Avanzado
- [ ] Test 4.1: Health check
- [ ] Test 4.2: Restart OK
- [ ] Test 4.3: Backup funciona

### ✅ Fase 5: Producción
- [ ] Test 5.1: Push a GitHub
- [ ] Test 5.2: Deploy en Dokploy

---

## 🆘 Si Algo Falla

1. **No avances al siguiente test**
2. **Anota el error exacto**
3. **Revisa la sección "Si falla" del test**
4. **Ve a:** [README-DOCKER.md - Troubleshooting](README-DOCKER.md#troubleshooting)
5. **O consulta:** [SETUP-PASO-A-PASO.md - Problemas Comunes](SETUP-PASO-A-PASO.md#problemas-comunes)

---

## 📝 Registro de Testing

Ir anotando resultados:

```
Test 1.1: ✅ OK
Test 1.2: ✅ OK
Test 1.3: ✅ OK
Test 1.4: ⚠️ Warning en composer install (no crítico)
Test 1.5: ✅ OK
...
```

---

**¡Éxito en el testing! 🎉**
