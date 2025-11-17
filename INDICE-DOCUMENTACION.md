# 📚 Índice Completo de Documentación - NewMoon ERP/POS

Guía de navegación para toda la documentación del proyecto.

---

## 🚀 Para Empezar

### ¿Primera vez con el proyecto?

1. **Lee primero:** [README.md](README.md) - Visión general del proyecto
2. **Elige tu método de instalación:**
   - 🐳 **Docker (Recomendado):** [QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md)
   - 💻 **Manual:** [README.md - Instalación Manual](README.md#instalación-manual-tradicional)

---

## 🐳 Deployment y Docker

### Guías de Deployment

| Documento | Descripción | Para quién | Tiempo |
|-----------|-------------|------------|--------|
| **[QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md)** | Inicio rápido en Dokploy | Usuarios con experiencia básica | 10 min |
| **[SETUP-PASO-A-PASO.md](SETUP-PASO-A-PASO.md)** | Guía completa paso a paso | Principiantes sin experiencia previa | 30 min |
| **[README-DOCKER.md](README-DOCKER.md)** | Documentación técnica completa | Desarrolladores y DevOps | Referencia |

### Archivos de Configuración

| Archivo | Propósito |
|---------|-----------|
| [Dockerfile](Dockerfile) | Configuración de imagen Docker multi-stage |
| [docker-compose.yml](docker-compose.yml) | Orquestación para desarrollo local |
| [docker-entrypoint.sh](docker-entrypoint.sh) | Script de inicialización del contenedor |
| [.dockerignore](.dockerignore) | Archivos excluidos del build Docker |
| [.env.example](.env.example) | Template de variables de entorno |
| [Makefile](Makefile) | Comandos útiles (`make help`) |

---

## 📖 Documentación por Caso de Uso

### 🎯 Quiero deployar en producción

**Opción 1: Dokploy (Recomendado)**
1. [QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md) - Inicio rápido
2. [SETUP-PASO-A-PASO.md](SETUP-PASO-A-PASO.md) - Guía detallada
3. [.env.example](.env.example) - Variables necesarias

**Opción 2: Servidor propio con Docker**
1. [README-DOCKER.md](README-DOCKER.md) - Sección "Deployment"
2. [docker-compose.yml](docker-compose.yml) - Adaptar para producción

**Opción 3: Instalación tradicional (cPanel, etc.)**
1. [README.md](README.md) - Sección "Instalación Manual"
2. [.cpanel.yml](.cpanel.yml) - Configuración cPanel

### 💻 Quiero desarrollar localmente

1. [README-DOCKER.md - Desarrollo Local](README-DOCKER.md#desarrollo-local)
2. [docker-compose.yml](docker-compose.yml) - Levantar servicios
3. [Makefile](Makefile) - Comandos útiles (`make help`)
4. [.env.example](.env.example) - Copiar a `.env` y configurar

### 🔧 Quiero configurar integraciones

**MercadoPago:**
1. [SETUP-PASO-A-PASO.md - Sección MercadoPago](SETUP-PASO-A-PASO.md#33-mercadopago-opcional---solo-si-vas-a-cobrar-online)
2. [mejoras/GUIA-MERCADOPAGO.md](mejoras/GUIA-MERCADOPAGO.md) - Guía completa

**AFIP:**
1. [SETUP-PASO-A-PASO.md - Sección AFIP](SETUP-PASO-A-PASO.md#35-afip-opcional---para-facturación-electrónica)

**Email:**
1. [SETUP-PASO-A-PASO.md - Sección Email](SETUP-PASO-A-PASO.md#34-email-opcional---para-envío-de-emails)

### 🗄️ Quiero gestionar la base de datos

**Importar/Exportar:**
- [README-DOCKER.md - Gestión de Base de Datos](README-DOCKER.md#gestión-de-base-de-datos)
- [SETUP-PASO-A-PASO.md - Paso 7](SETUP-PASO-A-PASO.md#paso-7-importar-base-de-datos)

**Backups:**
- [README-DOCKER.md - Backups](README-DOCKER.md#backup-manual)
- [SETUP-PASO-A-PASO.md - Backups Automáticos](SETUP-PASO-A-PASO.md#backups-automáticos)
- [Makefile](Makefile) - Comando `make backup`

### 🐛 Tengo un problema

**Troubleshooting general:**
1. [README-DOCKER.md - Troubleshooting](README-DOCKER.md#troubleshooting)
2. [SETUP-PASO-A-PASO.md - Problemas Comunes](SETUP-PASO-A-PASO.md#problemas-comunes)
3. [SOLUCION-ERRORES.md](SOLUCION-ERRORES.md) - Errores conocidos

**Ver logs:**
```bash
# Con Make
make logs        # Todos los logs
make logs-app    # Solo aplicación
make logs-db     # Solo MySQL

# Con Docker Compose
docker-compose logs -f app
docker-compose logs -f mysql
```

---

## 🎨 Mejoras y Desarrollo

### Documentación de Mejoras

| Documento | Contenido |
|-----------|-----------|
| [mejoras/README.md](mejoras/README.md) | Plan completo de mejoras |
| [mejoras/GUIA-MERCADOPAGO.md](mejoras/GUIA-MERCADOPAGO.md) | Integración MercadoPago |
| [mejoras/MEJORAS-VISUALES-COBRO.md](mejoras/MEJORAS-VISUALES-COBRO.md) | Mejoras visuales sistema de cobro |
| [mejoras/01-seguridad-critica.md](mejoras/01-seguridad-critica.md) | Seguridad crítica |
| [mejoras/07-plan-implementacion.md](mejoras/07-plan-implementacion.md) | Plan de implementación |

---

## 🔐 Seguridad

### Variables de Entorno y Secrets

**NUNCA commitear:**
- `.env` (variables de entorno reales)
- `modelos/conexion.php` (credenciales de BD)
- `controladores/facturacion/keys/*` (certificados AFIP)
- `logs/*` (pueden contener info sensible)

**Usar en su lugar:**
- [.env.example](.env.example) - Template sin valores reales
- [modelos/conexion.example.php](modelos/conexion.example.php) - Template de conexión

### Checklist de Seguridad

Ver [SETUP-PASO-A-PASO.md - Paso 9: Seguridad](SETUP-PASO-A-PASO.md#paso-9-seguridad-importante)

---

## 📋 Comandos Rápidos

### Docker Compose

```bash
# Iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Rebuild
docker-compose build --no-cache
```

### Makefile (Recomendado)

```bash
# Ver todos los comandos
make help

# Setup inicial
make install

# Iniciar servicios
make up

# Ver logs
make logs

# Backup de BD
make backup

# Restaurar backup
make restore FILE=backup.sql

# Shell en contenedor
make shell
```

### Git

```bash
# Clonar proyecto
git clone https://github.com/Moon-Gitub/newmoon.git

# Ver estado
git status

# Actualizar desde remoto
git pull origin main
```

---

## 🔄 Flujo de Trabajo Recomendado

### Para Desarrollo

```
1. git clone [repo]
2. cp .env.example .env
3. nano .env  # Configurar
4. make install
5. Importar SQL
6. http://localhost:8080
```

### Para Producción (Dokploy)

```
1. Crear MySQL en Dokploy
2. Crear app desde GitHub
3. Configurar variables de entorno
4. Deploy
5. Importar SQL
6. Configurar dominio
7. Verificar funcionamiento
```

---

## 📞 Soporte y Ayuda

### ¿Dónde buscar ayuda?

1. **Primero:** Buscar en [README-DOCKER.md - Troubleshooting](README-DOCKER.md#troubleshooting)
2. **Segundo:** Ver [SETUP-PASO-A-PASO.md - Problemas Comunes](SETUP-PASO-A-PASO.md#problemas-comunes)
3. **Tercero:** Revisar [SOLUCION-ERRORES.md](SOLUCION-ERRORES.md)
4. **Si nada funciona:** Contactar soporte

### Información de Contacto

- 📧 Email: soporte@moondesarrollos.com
- 🌐 Web: https://moondesarrollos.com
- 📦 GitHub Issues: https://github.com/Moon-Gitub/newmoon/issues

---

## 🗂️ Mapa del Repositorio

```
newmoon/
│
├── 📖 DOCUMENTACIÓN PRINCIPAL
│   ├── README.md                    ← Empieza aquí
│   ├── INDICE-DOCUMENTACION.md      ← Estás aquí
│   ├── QUICKSTART-DOKPLOY.md        ← Deploy rápido
│   ├── SETUP-PASO-A-PASO.md         ← Guía completa
│   ├── README-DOCKER.md             ← Referencia Docker
│   └── SOLUCION-ERRORES.md          ← Troubleshooting
│
├── 🐳 DOCKER
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── docker-entrypoint.sh
│   ├── .dockerignore
│   ├── .env.example
│   ├── Makefile
│   └── docker/
│       └── mysql/my.cnf
│
├── 💻 CÓDIGO FUENTE
│   ├── index.php
│   ├── ajax/
│   ├── controladores/
│   ├── modelos/
│   ├── vistas/
│   └── extensiones/
│
├── 📊 BASE DE DATOS
│   └── base_datos/
│
└── 📚 MEJORAS Y DOCS ADICIONALES
    └── mejoras/
        ├── README.md
        ├── GUIA-MERCADOPAGO.md
        └── ...
```

---

## 🎯 Atajos Rápidos

### Documentación por Tiempo Disponible

**Tengo 5 minutos:**
- [README.md](README.md) - Visión general

**Tengo 15 minutos:**
- [QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md) - Deploy rápido

**Tengo 1 hora:**
- [SETUP-PASO-A-PASO.md](SETUP-PASO-A-PASO.md) - Setup completo

**Soy desarrollador:**
- [README-DOCKER.md](README-DOCKER.md) - Documentación técnica
- [Makefile](Makefile) - `make help`

---

## ✅ Checklist de Lectura

Para un deployment exitoso, asegurate de haber leído:

- [ ] [README.md](README.md) - Introducción
- [ ] [QUICKSTART-DOKPLOY.md](QUICKSTART-DOKPLOY.md) o [SETUP-PASO-A-PASO.md](SETUP-PASO-A-PASO.md)
- [ ] [.env.example](.env.example) - Variables necesarias
- [ ] [README-DOCKER.md - Variables de Entorno](README-DOCKER.md#variables-de-entorno)
- [ ] [SETUP-PASO-A-PASO.md - Seguridad](SETUP-PASO-A-PASO.md#paso-9-seguridad-importante)

---

**Última actualización:** 2024
**Versión:** 1.0.0

**Desarrollado con ❤️ por Moon Desarrollos**
