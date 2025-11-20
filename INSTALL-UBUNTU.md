# 🐧 Instalación en Ubuntu (Sin Docker)

Guía para instalar NewMoon ERP/POS en Ubuntu usando instalación tradicional (LAMP).

---

## ⚡ Instalación Automática (Recomendado)

### Opción 1: Script Automático

```bash
# 1. Clonar el repositorio
git clone https://github.com/Moon-Gitub/newmoon.git
cd newmoon

# 2. Ejecutar script de instalación
sudo bash install-ubuntu.sh
```

**El script instalará automáticamente:**
- ✅ Apache 2.4
- ✅ MySQL/MariaDB
- ✅ PHP 8.1 y extensiones
- ✅ Composer
- ✅ phpMyAdmin (opcional)
- ✅ Configuración completa del proyecto
- ✅ Permisos correctos

**Tiempo estimado:** 10-15 minutos

---

## 🎯 Lo Que Hace el Script

### Paso 1: Actualiza el Sistema
```bash
sudo apt update && sudo apt upgrade -y
```

### Paso 2: Instala Apache
```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
```

### Paso 3: Instala MySQL/MariaDB
```bash
sudo apt install mariadb-server mariadb-client -y
```

### Paso 4: Instala PHP 8.1
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-gd php8.1-xml php8.1-curl php8.1-zip php8.1-intl php8.1-soap libapache2-mod-php8.1 -y
```

### Paso 5: Instala Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Paso 6: Configura MySQL
- Crea base de datos
- Crea usuario
- Asigna permisos

### Paso 7: Clona el Proyecto
```bash
cd /var/www
git clone https://github.com/Moon-Gitub/newmoon.git
```

### Paso 8: Instala Dependencias
```bash
cd newmoon/extensiones
composer install
```

### Paso 9: Configura Conexión a BD
- Crea `modelos/conexion.php` automáticamente

### Paso 10: Configura Permisos
```bash
sudo chown -R www-data:www-data /var/www/newmoon
sudo chmod -R 775 logs storage vistas/img
```

### Paso 11: Configura Apache
- Crea VirtualHost
- Habilita mod_rewrite
- Reinicia Apache

### Paso 12: Instala phpMyAdmin (Opcional)
- Instalación automática
- Configuración con Apache

### Paso 13: Importa Base de Datos
- Opcionalmente importa archivo SQL

---

## 📋 Requisitos Previos

- Ubuntu 20.04 LTS o superior
- Acceso sudo/root
- Conexión a internet
- Al menos 2GB de RAM
- 5GB de espacio en disco

---

## 🔧 Instalación Manual (Paso a Paso)

Si preferís instalar manualmente, seguí estos pasos:

### 1. Actualizar Sistema

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Instalar Apache

```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
```

### 3. Instalar MySQL

```bash
sudo apt install mariadb-server mariadb-client -y
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

### 4. Configurar MySQL

```bash
sudo mysql -u root
```

```sql
CREATE DATABASE newmoon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'newmoon_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON newmoon_db.* TO 'newmoon_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Instalar PHP 8.1

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-gd php8.1-xml php8.1-curl php8.1-zip php8.1-intl php8.1-soap libapache2-mod-php8.1 -y
```

### 6. Instalar Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 7. Clonar Proyecto

```bash
cd /var/www
sudo git clone https://github.com/Moon-Gitub/newmoon.git
cd newmoon
```

### 8. Instalar Dependencias

```bash
cd extensiones
sudo composer install
cd ..
```

### 9. Configurar Conexión a BD

```bash
sudo cp modelos/conexion.example.php modelos/conexion.php
sudo nano modelos/conexion.php
```

Editar valores:
```php
static public $hostDB = 'localhost';
static public $nameDB = 'newmoon_db';
static public $userDB = 'newmoon_user';
static public $passDB = 'tu_password_seguro';
```

### 10. Configurar Permisos

```bash
sudo mkdir -p logs storage vistas/img/usuarios vistas/img/productos vistas/img/empresa
sudo chown -R www-data:www-data /var/www/newmoon
sudo chmod -R 755 /var/www/newmoon
sudo chmod -R 775 logs storage vistas/img
```

### 11. Configurar Apache

```bash
sudo nano /etc/apache2/sites-available/newmoon.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/newmoon

    <Directory /var/www/newmoon>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/newmoon-error.log
    CustomLog ${APACHE_LOG_DIR}/newmoon-access.log combined
</VirtualHost>
```

```bash
sudo a2enmod rewrite
sudo a2dissite 000-default.conf
sudo a2ensite newmoon.conf
sudo systemctl restart apache2
```

### 12. Instalar phpMyAdmin (Opcional)

```bash
sudo apt install phpmyadmin -y
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin
sudo systemctl restart apache2
```

### 13. Importar Base de Datos

```bash
mysql -u newmoon_user -p newmoon_db < tu_backup.sql
```

---

## ✅ Verificación

### Acceder a la Aplicación

```
http://localhost
http://IP_DEL_SERVIDOR
```

### Acceder a phpMyAdmin

```
http://localhost/phpmyadmin
```

**Login:**
- Usuario: `newmoon_user`
- Password: el que configuraste

### Login en la Aplicación

```
Usuario: admin
Password: admin123
```

⚠️ **IMPORTANTE:** Cambiar password inmediatamente

---

## 🐛 Troubleshooting

### Error: Cannot connect to database

```bash
# Verificar MySQL
sudo systemctl status mariadb

# Ver logs
sudo tail -f /var/log/mysql/error.log
```

### Error: 403 Forbidden

```bash
# Verificar permisos
ls -la /var/www/newmoon

# Corregir permisos
sudo chown -R www-data:www-data /var/www/newmoon
```

### Error: 500 Internal Server Error

```bash
# Ver logs de Apache
sudo tail -f /var/log/apache2/newmoon-error.log

# Ver logs de PHP
sudo tail -f /var/log/apache2/error.log
```

### Composer no funciona

```bash
# Reinstalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Apache no inicia

```bash
# Ver estado
sudo systemctl status apache2

# Ver errores
sudo apache2ctl configtest

# Verificar puertos
sudo netstat -tulpn | grep :80
```

---

## 📝 Comandos Útiles

### Apache

```bash
# Iniciar
sudo systemctl start apache2

# Detener
sudo systemctl stop apache2

# Reiniciar
sudo systemctl restart apache2

# Ver estado
sudo systemctl status apache2

# Ver logs
sudo tail -f /var/log/apache2/newmoon-error.log
```

### MySQL

```bash
# Conectar
mysql -u newmoon_user -p

# Ver bases de datos
mysql -u newmoon_user -p -e "SHOW DATABASES;"

# Backup
mysqldump -u newmoon_user -p newmoon_db > backup.sql

# Restore
mysql -u newmoon_user -p newmoon_db < backup.sql
```

### PHP

```bash
# Ver versión
php -v

# Ver módulos
php -m

# Ver configuración
php -i | grep php.ini
```

---

## 🔒 Seguridad

### Cambiar Password de MySQL Root

```bash
sudo mysql_secure_installation
```

### Configurar Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Configurar SSL (Opcional)

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tu-dominio.com
```

---

## 📊 Optimización

### Configurar PHP

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Modificar:
```ini
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
date.timezone = America/Argentina/Buenos_Aires
```

### Configurar MySQL

```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Agregar:
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 256M
```

### Reiniciar Servicios

```bash
sudo systemctl restart apache2
sudo systemctl restart mariadb
```

---

## 📦 Actualización

### Actualizar Código

```bash
cd /var/www/newmoon
sudo git pull
cd extensiones
sudo composer install
sudo systemctl restart apache2
```

---

## 🆘 Soporte

Si tenés problemas con la instalación:

1. Verificar logs: `sudo tail -f /var/log/apache2/newmoon-error.log`
2. Verificar permisos: `ls -la /var/www/newmoon`
3. Verificar MySQL: `sudo systemctl status mariadb`
4. Contactar soporte: soporte@moondesarrollos.com

---

## ✨ Ventajas vs Desventajas

### ✅ Ventajas Instalación Manual

- Control total sobre la configuración
- No necesita Docker
- Familiar para administradores de sistemas tradicionales
- Fácil debuggear con herramientas del sistema

### ❌ Desventajas

- Setup más largo
- Requiere configuración manual
- No es portable entre sistemas
- Más difícil de replicar
- Puede interferir con otros proyectos

### 💡 Recomendación

Para desarrollo y producción, recomendamos **Docker** ([README-DOCKER.md](README-DOCKER.md)):
- Setup en minutos
- Portable
- Aislado
- Fácil de replicar

---

**Desarrollado con ❤️ por Moon Desarrollos**
