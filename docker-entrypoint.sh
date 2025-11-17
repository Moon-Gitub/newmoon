#!/bin/bash
set -e

echo "🌙 NewMoon ERP/POS - Iniciando contenedor..."

# Función para esperar a que MySQL esté listo
wait_for_mysql() {
    echo "⏳ Esperando conexión con MySQL..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        if mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; then
            echo "✅ MySQL está listo!"
            return 0
        fi

        echo "Intento $attempt/$max_attempts - MySQL no disponible aún..."
        sleep 2
        attempt=$((attempt + 1))
    done

    echo "❌ No se pudo conectar a MySQL después de $max_attempts intentos"
    return 1
}

# Crear archivo de conexión desde variables de entorno
create_connection_file() {
    echo "📝 Creando archivo de conexión a base de datos..."

    cat > /var/www/html/modelos/conexion.php <<EOF
<?php

/**
 * CONEXIÓN A BASE DE DATOS
 * Generado automáticamente desde variables de entorno
 */

class Conexion{

    static public \$hostDB = '${DB_HOST:-localhost}';
    static public \$nameDB = '${DB_NAME:-newmoon_db}';
    static public \$userDB = '${DB_USER:-root}';
    static public \$passDB = '${DB_PASSWORD:-}';
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
        \$host = '${DB_MOON_HOST:-107.161.23.241}';
        \$db = '${DB_MOON_NAME:-moondb}';
        \$user = '${DB_MOON_USER:-moonuser}';
        \$pass = '${DB_MOON_PASSWORD:-}';

        try {
            \$link = new PDO("mysql:host=\$host;dbname=\$db","\$user","\$pass");
            \$link->exec("set names utf8");
            return \$link;

        } catch (PDOException \$e) {
            error_log("Error de conexión Moon: " . \$e->getMessage());
            // No es crítico, retornar null
            return null;
        }
    }
}
EOF

    echo "✅ Archivo de conexión creado"
}

# Verificar permisos de directorios
check_permissions() {
    echo "🔒 Verificando permisos de directorios..."

    # Asegurar que los directorios existen y tienen permisos correctos
    local dirs=(
        "logs"
        "storage"
        "vistas/img/usuarios"
        "vistas/img/productos"
        "vistas/img/empresa"
    )

    for dir in "${dirs[@]}"; do
        if [ ! -d "/var/www/html/$dir" ]; then
            mkdir -p "/var/www/html/$dir"
        fi
        # Solo intentar cambiar permisos si somos root
        if [ "$(id -u)" -eq 0 ]; then
            chown -R www-data:www-data "/var/www/html/$dir"
            chmod -R 775 "/var/www/html/$dir"
        fi
    done

    echo "✅ Permisos verificados"
}

# Crear archivo parametros.php si no existe
create_parametros() {
    if [ ! -f "/var/www/html/parametros.php" ]; then
        echo "📝 Creando archivo de parámetros..."

        cat > /var/www/html/parametros.php <<'EOF'
<?php

/**
 * PARÁMETROS GLOBALES DE LA APLICACIÓN
 */

// URL Base de la aplicación
define('URL_BASE', getenv('APP_URL') ?: 'http://localhost');

// Configuración de MercadoPago
define('MP_PUBLIC_KEY', getenv('MP_PUBLIC_KEY') ?: '');
define('MP_ACCESS_TOKEN', getenv('MP_ACCESS_TOKEN') ?: '');

// Modo de la aplicación
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true' ? true : false);

// Configuración de email
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: '587');
define('MAIL_USER', getenv('MAIL_USER') ?: '');
define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@moondesarrollos.com');

// Timezone
date_default_timezone_set(getenv('TZ') ?: 'America/Argentina/Buenos_Aires');

EOF

        echo "✅ Archivo de parámetros creado"
    fi
}

# Main execution
main() {
    echo "=================================="
    echo "🌙 NewMoon ERP/POS"
    echo "=================================="

    # Crear archivo de parámetros
    create_parametros

    # Esperar a MySQL si está configurado
    if [ -n "${DB_HOST}" ] && [ "${WAIT_FOR_DB}" != "false" ]; then
        wait_for_mysql || {
            echo "⚠️  MySQL no disponible, pero continuando..."
        }
    fi

    # Crear archivo de conexión
    if [ -n "${DB_HOST}" ]; then
        create_connection_file
    else
        echo "⚠️  DB_HOST no configurado, saltando creación de archivo de conexión"
    fi

    # Verificar permisos
    check_permissions

    echo "=================================="
    echo "✅ Inicialización completada"
    echo "🚀 Iniciando Apache..."
    echo "=================================="

    # Ejecutar el comando original (apache2-foreground)
    exec "$@"
}

# Ejecutar main si es root, sino ejecutar directamente el comando
if [ "$(id -u)" -eq 0 ]; then
    # Ejecutar como root primero, luego cambiar a www-data
    main "$@"
else
    # Ya somos www-data, ejecutar directamente
    exec "$@"
fi
