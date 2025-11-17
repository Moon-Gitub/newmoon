# Multi-stage Dockerfile para NewMoon ERP/POS
# Optimizado para Dokploy deployment

# ==================================
# Stage 1: Composer Dependencies
# ==================================
FROM composer:2 AS composer-build

WORKDIR /app

# Copiar archivos de composer
COPY extensiones/composer.json extensiones/composer.lock* ./extensiones/

# Instalar dependencias de producción
RUN cd extensiones && \
    composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ==================================
# Stage 2: Production Image
# ==================================
FROM php:8.1-apache

# Metadata
LABEL maintainer="Moon Desarrollos"
LABEL description="Sistema ERP/POS - NewMoon"
LABEL version="1.0"

# Variables de entorno por defecto
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV TZ=America/Argentina/Buenos_Aires

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    git \
    unzip \
    curl \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Configurar extensiones GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    intl \
    mbstring \
    opcache \
    soap \
    exif

# Habilitar módulos de Apache
RUN a2enmod rewrite headers expires deflate

# Configurar PHP para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.validate_timestamps=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=50M'; \
    echo 'post_max_size=50M'; \
    echo 'max_execution_time=300'; \
    echo 'max_input_time=300'; \
    echo 'date.timezone=America/Argentina/Buenos_Aires'; \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Configurar Apache
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    ServerAdmin admin@moondesarrollos.com'; \
    echo '    DocumentRoot /var/www/html'; \
    echo '    <Directory /var/www/html>'; \
    echo '        Options -Indexes +FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf

# Crear usuario www-data con permisos correctos
RUN usermod -u 1000 www-data && \
    groupmod -g 1000 www-data

# Crear directorios necesarios
WORKDIR /var/www/html

# Copiar código de la aplicación
COPY --chown=www-data:www-data . /var/www/html/

# Copiar dependencias de composer desde stage anterior
COPY --from=composer-build --chown=www-data:www-data /app/extensiones/vendor /var/www/html/extensiones/vendor

# Crear directorios con permisos correctos
RUN mkdir -p \
    logs \
    storage \
    vistas/img/usuarios \
    vistas/img/productos \
    vistas/img/empresa \
    controladores/facturacion/keys \
    && chown -R www-data:www-data \
    logs \
    storage \
    vistas/img \
    controladores/facturacion/keys \
    && chmod -R 775 \
    logs \
    storage \
    vistas/img \
    controladores/facturacion/keys

# Copiar script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Exponer puerto
EXPOSE 80

# Usuario para ejecutar
USER www-data

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
