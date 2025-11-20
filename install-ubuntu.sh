#!/bin/bash

# ============================================
# Script de Instalación NewMoon ERP/POS
# Para Ubuntu (Sin Docker)
# ============================================

set -e  # Salir si hay error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funciones de utilidad
print_header() {
    echo -e "\n${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Verificar que se ejecuta como root o con sudo
check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Este script debe ejecutarse con sudo"
        echo "Uso: sudo bash install-ubuntu.sh"
        exit 1
    fi
}

# Obtener el usuario real (no root)
REAL_USER=${SUDO_USER:-$USER}
REAL_HOME=$(eval echo ~$REAL_USER)

print_header "🌙 NewMoon ERP/POS - Instalación en Ubuntu"
echo "Usuario: $REAL_USER"
echo "Home: $REAL_HOME"
echo ""

# Verificar permisos
check_root

# ============================================
# 1. ACTUALIZAR SISTEMA
# ============================================
print_header "📦 Paso 1: Actualizando Sistema"

apt update
apt upgrade -y

print_success "Sistema actualizado"

# ============================================
# 2. INSTALAR APACHE
# ============================================
print_header "🌐 Paso 2: Instalando Apache"

apt install apache2 -y
systemctl start apache2
systemctl enable apache2

print_success "Apache instalado y habilitado"

# ============================================
# 3. INSTALAR MYSQL/MARIADB
# ============================================
print_header "🗄️  Paso 3: Instalando MySQL/MariaDB"

apt install mariadb-server mariadb-client -y
systemctl start mariadb
systemctl enable mariadb

print_success "MySQL/MariaDB instalado y habilitado"

# ============================================
# 4. INSTALAR PHP 8.1
# ============================================
print_header "🐘 Paso 4: Instalando PHP 8.1"

# Verificar si el repositorio PPA está disponible
if ! dpkg -l | grep -q php8.1; then
    print_info "Agregando repositorio PPA de PHP..."
    apt install software-properties-common -y
    add-apt-repository ppa:ondrej/php -y
    apt update
fi

# Instalar PHP y extensiones
apt install -y \
    php8.1 \
    php8.1-mysql \
    php8.1-mbstring \
    php8.1-gd \
    php8.1-xml \
    php8.1-curl \
    php8.1-zip \
    php8.1-intl \
    php8.1-soap \
    libapache2-mod-php8.1

print_success "PHP 8.1 y extensiones instaladas"

# ============================================
# 5. INSTALAR COMPOSER
# ============================================
print_header "📦 Paso 5: Instalando Composer"

if ! command -v composer &> /dev/null; then
    cd /tmp
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_success "Composer instalado"
else
    print_info "Composer ya está instalado"
fi

composer --version

# ============================================
# 6. INSTALAR GIT
# ============================================
print_header "📥 Paso 6: Verificando Git"

if ! command -v git &> /dev/null; then
    apt install git -y
    print_success "Git instalado"
else
    print_info "Git ya está instalado"
fi

# ============================================
# 7. CONFIGURAR MYSQL
# ============================================
print_header "🔐 Paso 7: Configurando MySQL"

# Solicitar datos de base de datos
echo ""
print_info "Configuración de Base de Datos"
echo ""

read -p "Nombre de la base de datos [newmoon_db]: " DB_NAME
DB_NAME=${DB_NAME:-newmoon_db}

read -p "Usuario de la base de datos [newmoon_user]: " DB_USER
DB_USER=${DB_USER:-newmoon_user}

read -sp "Contraseña para $DB_USER: " DB_PASS
echo ""

if [ -z "$DB_PASS" ]; then
    print_error "La contraseña no puede estar vacía"
    exit 1
fi

# Crear base de datos y usuario
print_info "Creando base de datos y usuario..."

mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

print_success "Base de datos '$DB_NAME' y usuario '$DB_USER' creados"

# ============================================
# 8. CLONAR PROYECTO
# ============================================
print_header "📂 Paso 8: Clonando Proyecto"

PROJECT_DIR="/var/www/newmoon"

if [ -d "$PROJECT_DIR" ]; then
    print_warning "El directorio $PROJECT_DIR ya existe"
    read -p "¿Desea eliminarlo y volver a clonar? (s/N): " CONFIRM
    if [ "$CONFIRM" = "s" ] || [ "$CONFIRM" = "S" ]; then
        rm -rf $PROJECT_DIR
    else
        print_info "Saltando clonación"
        PROJECT_DIR="$PROJECT_DIR"
    fi
fi

if [ ! -d "$PROJECT_DIR" ]; then
    print_info "Clonando repositorio..."
    cd /var/www

    # Solicitar URL del repositorio
    read -p "URL del repositorio [https://github.com/Moon-Gitub/newmoon.git]: " REPO_URL
    REPO_URL=${REPO_URL:-https://github.com/Moon-Gitub/newmoon.git}

    git clone $REPO_URL newmoon
    print_success "Proyecto clonado en $PROJECT_DIR"
fi

cd $PROJECT_DIR

# ============================================
# 9. INSTALAR DEPENDENCIAS DE COMPOSER
# ============================================
print_header "📦 Paso 9: Instalando Dependencias PHP"

if [ -d "extensiones" ]; then
    cd extensiones
    print_info "Ejecutando composer install..."
    composer install --no-dev --optimize-autoloader
    cd ..
    print_success "Dependencias instaladas"
else
    print_warning "Directorio extensiones/ no encontrado"
fi

# ============================================
# 10. CONFIGURAR ARCHIVO DE CONEXIÓN
# ============================================
print_header "🔧 Paso 10: Configurando Conexión a BD"

# Crear archivo de conexión
cat > modelos/conexion.php <<EOF
<?php

/**
 * CONEXIÓN A BASE DE DATOS
 * Generado automáticamente por install-ubuntu.sh
 */

class Conexion{

    static public \$hostDB = 'localhost';
    static public \$nameDB = '$DB_NAME';
    static public \$userDB = '$DB_USER';
    static public \$passDB = '$DB_PASS';
    static public \$charset = 'UTF8MB4';

    static public function getDatosConexion(){
        return array(
            'host' => self::\$hostDB,
            'db' => self::\$nameDB,
            'user' => self::\$userDB,
            'pass' => self::\$passDB,
            'charset' => self::\$charset
        );
    }

    static public function conectar(){
        \$host = self::\$hostDB;
        \$db = self::\$nameDB;
        \$user = self::\$userDB;
        \$pass = self::\$passDB;

        try {
            \$link = new PDO("mysql:host=\$host;dbname=\$db","\$user","\$pass");
            \$link->exec("set names utf8");
            return \$link;

        } catch (PDOException \$e) {
            error_log("Error de conexión: " . \$e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    // CONECTAR A BD MOON PARA VER ESTADO CLIENTE
    static public function conectarMoon(){
        // Configurar si es necesario
        return null;
    }
}
EOF

print_success "Archivo modelos/conexion.php creado"

# ============================================
# 11. CONFIGURAR PERMISOS
# ============================================
print_header "🔒 Paso 11: Configurando Permisos"

# Crear directorios si no existen
mkdir -p logs storage vistas/img/usuarios vistas/img/productos vistas/img/empresa controladores/facturacion/keys

# Configurar permisos
chown -R www-data:www-data $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 775 logs storage vistas/img controladores/facturacion/keys

print_success "Permisos configurados"

# ============================================
# 12. CONFIGURAR APACHE
# ============================================
print_header "⚙️  Paso 12: Configurando Apache"

# Habilitar mod_rewrite
a2enmod rewrite

# Crear configuración del sitio
cat > /etc/apache2/sites-available/newmoon.conf <<EOF
<VirtualHost *:80>
    ServerName localhost
    ServerAdmin admin@localhost
    DocumentRoot $PROJECT_DIR

    <Directory $PROJECT_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/newmoon-error.log
    CustomLog \${APACHE_LOG_DIR}/newmoon-access.log combined
</VirtualHost>
EOF

# Deshabilitar sitio por defecto y habilitar newmoon
a2dissite 000-default.conf 2>/dev/null || true
a2ensite newmoon.conf

# Reiniciar Apache
systemctl restart apache2

print_success "Apache configurado"

# ============================================
# 13. CONFIGURAR PHP
# ============================================
print_header "🐘 Paso 13: Configurando PHP"

# Configurar php.ini
PHP_INI="/etc/php/8.1/apache2/php.ini"

if [ -f "$PHP_INI" ]; then
    # Backup
    cp $PHP_INI ${PHP_INI}.backup

    # Modificar configuraciones
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' $PHP_INI
    sed -i 's/post_max_size = .*/post_max_size = 50M/' $PHP_INI
    sed -i 's/memory_limit = .*/memory_limit = 512M/' $PHP_INI
    sed -i 's/max_execution_time = .*/max_execution_time = 300/' $PHP_INI
    sed -i 's/;date.timezone =.*/date.timezone = America\/Argentina\/Buenos_Aires/' $PHP_INI

    print_success "PHP configurado"
else
    print_warning "No se encontró $PHP_INI"
fi

# Reiniciar Apache para aplicar cambios
systemctl restart apache2

# ============================================
# 14. INSTALAR PHPMYADMIN
# ============================================
print_header "📊 Paso 14: Instalando phpMyAdmin"

echo ""
print_info "¿Desea instalar phpMyAdmin?"
read -p "Instalar phpMyAdmin? (S/n): " INSTALL_PMA
INSTALL_PMA=${INSTALL_PMA:-s}

if [ "$INSTALL_PMA" = "s" ] || [ "$INSTALL_PMA" = "S" ]; then
    print_info "Instalando phpMyAdmin..."

    # Preconfigurar respuestas para instalación no interactiva
    echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/app-password-confirm password $DB_PASS" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/mysql/admin-pass password " | debconf-set-selections
    echo "phpmyadmin phpmyadmin/mysql/app-pass password $DB_PASS" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections

    # Instalar phpMyAdmin
    DEBIAN_FRONTEND=noninteractive apt install -y phpmyadmin

    # Habilitar configuración de phpMyAdmin en Apache
    ln -sf /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
    a2enconf phpmyadmin

    # Reiniciar Apache
    systemctl restart apache2

    print_success "phpMyAdmin instalado"
    print_info "Acceso: http://localhost/phpmyadmin"
else
    print_info "Instalación de phpMyAdmin saltada"
fi

# ============================================
# 15. IMPORTAR BASE DE DATOS
# ============================================
print_header "📥 Paso 15: Importar Base de Datos"

echo ""
print_info "¿Tenés un archivo SQL para importar?"
read -p "Ruta al archivo SQL (Enter para saltar): " SQL_FILE

if [ -n "$SQL_FILE" ] && [ -f "$SQL_FILE" ]; then
    print_info "Importando $SQL_FILE..."
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < "$SQL_FILE"
    print_success "Base de datos importada"
else
    print_warning "Importación de BD saltada. Importá manualmente con:"
    echo "mysql -u $DB_USER -p $DB_NAME < tu_archivo.sql"
fi

# ============================================
# RESUMEN FINAL
# ============================================
print_header "🎉 Instalación Completada"

echo -e "${GREEN}┌─────────────────────────────────────────────────────┐${NC}"
echo -e "${GREEN}│  ✅ NewMoon ERP/POS instalado exitosamente         │${NC}"
echo -e "${GREEN}└─────────────────────────────────────────────────────┘${NC}"
echo ""
echo -e "${BLUE}📍 Ubicación del proyecto:${NC} $PROJECT_DIR"
echo -e "${BLUE}🗄️  Base de datos:${NC} $DB_NAME"
echo -e "${BLUE}👤 Usuario BD:${NC} $DB_USER"
echo ""
echo -e "${BLUE}🌐 Acceso:${NC}"
echo -e "   Aplicación: http://localhost"
echo -e "   phpMyAdmin: http://localhost/phpmyadmin"
echo -e "   IP: http://$(hostname -I | awk '{print $1}')"
echo ""
echo -e "${YELLOW}👥 Login por defecto:${NC}"
echo -e "   Usuario: admin"
echo -e "   Contraseña: admin123"
echo -e "   ${RED}⚠️  Cambiar inmediatamente en producción${NC}"
echo ""
echo -e "${BLUE}📋 Logs:${NC}"
echo -e "   Apache: tail -f /var/log/apache2/newmoon-error.log"
echo -e "   App: tail -f $PROJECT_DIR/logs/"
echo ""
echo -e "${BLUE}🔧 Comandos útiles:${NC}"
echo -e "   Reiniciar Apache: sudo systemctl restart apache2"
echo -e "   Ver estado: sudo systemctl status apache2"
echo -e "   Ver logs: tail -f /var/log/apache2/newmoon-error.log"
echo ""

# Guardar credenciales en archivo
CREDS_FILE="$REAL_HOME/newmoon-credentials.txt"
cat > $CREDS_FILE <<EOF
NewMoon ERP/POS - Credenciales
==============================

Base de Datos
-------------
Host: localhost
Database: $DB_NAME
User: $DB_USER
Password: $DB_PASS

Aplicación
----------
URL: http://localhost
Directorio: $PROJECT_DIR

Login Default
-------------
Usuario: admin
Password: admin123
⚠️  CAMBIAR INMEDIATAMENTE

Generado: $(date)
EOF

chown $REAL_USER:$REAL_USER $CREDS_FILE
chmod 600 $CREDS_FILE

print_success "Credenciales guardadas en: $CREDS_FILE"

echo ""
print_info "¡Instalación completa! Abrí tu navegador en http://localhost"
echo ""
