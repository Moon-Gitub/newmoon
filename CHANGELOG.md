# Changelog

Todos los cambios importantes del proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [1.1.0] - 2024-11-17

### 🎉 Añadido

#### Soporte Completo para Docker y Dokploy

- **Dockerfile multi-stage** optimizado para producción
  - PHP 8.1 con Apache
  - Instalación automática de dependencias con Composer
  - Configuración de PHP con opcache para mejor performance
  - Health checks integrados
  - Permisos correctos configurados automáticamente
  - Usuario www-data para seguridad

- **docker-compose.yml** para desarrollo local
  - Servicio de aplicación PHP/Apache
  - MySQL/MariaDB 10.11
  - phpMyAdmin (opcional, perfil development)
  - Volúmenes persistentes configurados
  - Red interna configurada

- **Script de entrypoint (docker-entrypoint.sh)**
  - Espera automática hasta que MySQL esté listo
  - Generación automática de `modelos/conexion.php` desde variables de entorno
  - Configuración automática de permisos de carpetas
  - Creación de archivo `parametros.php` con configuración

- **Configuración de MySQL optimizada** (`docker/mysql/my.cnf`)
  - UTF8MB4 por defecto
  - Performance tuning
  - Query cache habilitado
  - Timezone configurado para Argentina

#### Archivos de Configuración

- **.env.example** - Template completo de variables de entorno
  - Base de datos (principal y Moon)
  - Configuración de aplicación
  - Credenciales de MercadoPago
  - Configuración de email
  - Configuración de AFIP
  - PHP settings

- **.dockerignore** - Optimización del build Docker
  - Excluye archivos innecesarios
  - Reduce tamaño de imagen
  - Protege archivos sensibles

- **Makefile** - Comandos útiles para desarrollo
  - `make install` - Setup inicial
  - `make up/down` - Iniciar/detener servicios
  - `make logs` - Ver logs
  - `make backup/restore` - Gestión de BD
  - `make shell` - Acceso a contenedor
  - Y más... (`make help`)

#### Documentación Completa

- **README-DOCKER.md** - Guía completa de Docker
  - Deployment paso a paso en Dokploy
  - Desarrollo local con Docker Compose
  - Gestión de base de datos
  - Troubleshooting detallado
  - Comandos útiles
  - Seguridad

- **QUICKSTART-DOKPLOY.md** - Inicio rápido
  - Deploy en 10 minutos
  - Variables mínimas necesarias
  - Checklist post-deploy

- **SETUP-PASO-A-PASO.md** - Guía completa para principiantes
  - 10 pasos detallados
  - Explicación de cada variable de entorno
  - Cómo obtener credenciales de MercadoPago, Gmail, etc.
  - Importación de base de datos
  - Configuración de seguridad
  - Backups automáticos
  - Troubleshooting

- **INDICE-DOCUMENTACION.md** - Navegación de toda la documentación
  - Guía por caso de uso
  - Índice de todos los documentos
  - Comandos rápidos
  - Flujos de trabajo recomendados

#### Actualización de Documentación Principal

- **README.md** actualizado
  - Sección prominente de Docker (recomendado)
  - Reorganización de contenido
  - Estructura del proyecto actualizada
  - Roadmap actualizado con Docker como completado
  - Enlaces a toda la documentación nueva

### 🔧 Cambiado

- **.gitignore** actualizado
  - Agregadas reglas para Docker
  - Archivos de entorno local
  - Volúmenes de Docker
  - docker-compose.override.yml

### 🎯 Características del Sistema Docker

#### Ventajas

✅ **Setup automático** - Base de datos y aplicación configuradas desde variables de entorno
✅ **Sin dependencias** - No necesita PHP, Apache, MySQL instalados localmente
✅ **Portabilidad** - Funciona igual en desarrollo, staging y producción
✅ **Aislamiento** - No interfiere con otros proyectos
✅ **Fácil escalabilidad** - Ready para Dokploy, Railway, Render, etc.
✅ **Desarrollo rápido** - `make install` y listo
✅ **Backups integrados** - `make backup` para exportar BD

#### Plataformas Soportadas

- ✅ Dokploy (deployment automático desde GitHub)
- ✅ Docker Compose (desarrollo local)
- ✅ Cualquier plataforma que soporte Docker (Railway, Render, etc.)
- ✅ Instalación tradicional (cPanel, servidores manuales)

### 📦 Archivos Nuevos

```
Dockerfile
docker-compose.yml
docker-entrypoint.sh
.dockerignore
.env.example
Makefile
docker/mysql/my.cnf
README-DOCKER.md
QUICKSTART-DOKPLOY.md
SETUP-PASO-A-PASO.md
INDICE-DOCUMENTACION.md
CHANGELOG.md (este archivo)
```

### 🔒 Seguridad

- Variables de entorno separadas del código
- Secrets nunca commiteados al repositorio
- Configuración automática desde variables de entorno en runtime
- Usuario no-root (www-data) en contenedor
- Health checks para monitoreo

### 📚 Documentación Total

El proyecto ahora cuenta con **8 documentos** completos:
1. README.md (actualizado)
2. README-DOCKER.md (nuevo)
3. QUICKSTART-DOKPLOY.md (nuevo)
4. SETUP-PASO-A-PASO.md (nuevo)
5. INDICE-DOCUMENTACION.md (nuevo)
6. CHANGELOG.md (nuevo)
7. SOLUCION-ERRORES.md (existente)
8. mejoras/* (documentación de mejoras anteriores)

---

## [1.0.0] - 2024-11-XX (Anterior)

### Añadido

#### Sistema de Cobros con MercadoPago
- Integración completa con MercadoPago
- Webhooks automáticos
- Sistema de notificaciones
- Dashboard de cuenta corriente

#### Mejoras Visuales
- Diseño profesional con gradientes
- Notificaciones en navbar
- Mejora de interfaz de usuario

#### cPanel Deployment
- Script de instalación de Composer para cPanel
- Configuración de deployment automático
- Archivo .cpanel.yml

#### AFIP
- Integración con AFIP para facturación electrónica
- Soporte para diferentes tipos de comprobantes
- Sistema de certificados

#### Funcionalidades Core
- Gestión de productos, clientes y proveedores
- Sistema de ventas y compras
- Control de stock e inventario
- Cuenta corriente de clientes y proveedores
- Reportes y estadísticas
- Control de caja
- Múltiples usuarios y perfiles
- Presupuestos y pedidos

### Seguridad
- Protección de credenciales
- Validación de sesiones en AJAX
- .gitignore configurado
- Separación de archivos de configuración

---

## Tipos de Cambios

- `Added` - Para funcionalidades nuevas
- `Changed` - Para cambios en funcionalidades existentes
- `Deprecated` - Para funcionalidades que serán removidas
- `Removed` - Para funcionalidades removidas
- `Fixed` - Para corrección de bugs
- `Security` - Para vulnerabilidades de seguridad

---

## [Unreleased]

### Planificado

- [ ] API RESTful para integraciones
- [ ] Sistema de testing automatizado
- [ ] App móvil (React Native)
- [ ] Dashboard analytics avanzado
- [ ] Integración con más pasarelas (Stripe, PayPal)
- [ ] Sistema de notificaciones push
- [ ] Multi-tenancy (SaaS)
- [ ] Migración completa a PHP 8.1+
- [ ] Autenticación 2FA

---

**Desarrollado con ❤️ por Moon Desarrollos**
