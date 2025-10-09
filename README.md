# 🌙 Sistema ERP/POS - Moon Desarrollos

Sistema completo de gestión empresarial (ERP) y punto de venta (POS) desarrollado en PHP con integración a AFIP y MercadoPago.

## 🚀 Características

- ✅ Gestión de Productos, Clientes y Proveedores
- ✅ Sistema de Ventas y Compras
- ✅ Control de Stock e Inventario
- ✅ Cuenta Corriente de Clientes y Proveedores
- ✅ Integración con AFIP (Facturación Electrónica)
- ✅ Sistema de Cobros con MercadoPago
- ✅ Reportes y Estadísticas
- ✅ Control de Caja
- ✅ Múltiples Usuarios y Perfiles
- ✅ Presupuestos y Pedidos

## 📋 Requisitos

- PHP 7.4 o superior (recomendado PHP 8+)
- MySQL 5.7+ o MariaDB 10.3+
- Apache o Nginx
- Composer
- Extensiones PHP: PDO, PDO_MySQL, mbstring, GD, JSON

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git
cd TU_REPOSITORIO
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar base de datos

```bash
# Importar estructura
mysql -u usuario -p nombre_bd < base_datos/demo_db.sql
```

### 4. Configurar credenciales

```bash
# Copiar archivo de ejemplo
cp modelos/conexion.example.php modelos/conexion.php

# Editar con tus credenciales reales
nano modelos/conexion.php
```

**⚠️ IMPORTANTE:** Editar `conexion.php` y cambiar:
- `$hostDB` - Host de tu base de datos
- `$nameDB` - Nombre de tu base de datos
- `$userDB` - Usuario de MySQL
- `$passDB` - Contraseña de MySQL

### 5. Configurar permisos

```bash
chmod -R 755 logs
chmod -R 755 storage
chmod -R 755 vistas/img/usuarios
chmod -R 755 vistas/img/productos
```

### 6. Configurar MercadoPago (Opcional)

Editar las credenciales de MercadoPago en:
- `controladores/mercadopago.controlador.php`

O mejor aún, usar archivo `.env`:

```bash
cp .env.example .env
nano .env
```

## 🎨 Mejoras Recientes

### Sistema de Cobro Mejorado
- ✨ Diseño visual profesional con gradientes
- 🔔 Notificaciones de pago en navbar
- 📊 Dashboard de cuenta corriente
- 🔄 Webhooks automáticos de MercadoPago
- 📝 Auditoría completa de transacciones

### Seguridad
- 🔒 Protección de credenciales
- 🛡️ Validación de sesiones en AJAX
- 🔐 Sistema de passwords mejorado (pendiente)
- 🚫 Protección contra SQL injection (pendiente)

Ver documentación completa en: [`mejoras/README.md`](mejoras/README.md)

## 📁 Estructura del Proyecto

```
/
├── ajax/                  # Endpoints AJAX
├── controladores/         # Controladores MVC
├── modelos/              # Modelos MVC
├── vistas/               # Vistas (HTML/PHP)
│   ├── modulos/          # Módulos de vistas
│   ├── js/               # JavaScript
│   └── dist/             # Assets compilados
├── extensiones/          # Librerías externas
├── base_datos/           # Scripts SQL
├── mejoras/              # Documentación de mejoras
└── logs/                 # Archivos de log

```

## 🔐 Seguridad

**⚠️ NUNCA subir a GitHub:**
- Archivo `modelos/conexion.php` (credenciales de BD)
- Archivo `.env` (variables de entorno)
- Carpeta `logs/` (puede contener información sensible)
- Carpeta `controladores/facturacion/keys/` (claves AFIP)

Estos archivos están protegidos en `.gitignore`

## 👥 Usuarios por Defecto

Después de importar la base de datos:

```
Usuario: admin
Contraseña: admin123
```

**⚠️ Cambiar estas credenciales inmediatamente en producción**

## 📚 Documentación

- [Plan de Mejoras Completo](mejoras/README.md)
- [Guía MercadoPago](mejoras/GUIA-MERCADOPAGO.md)
- [Mejoras Visuales](mejoras/MEJORAS-VISUALES-COBRO.md)
- [Seguridad Crítica](mejoras/01-seguridad-critica.md)
- [Plan de Implementación](mejoras/07-plan-implementacion.md)

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abrir Pull Request

## 📝 Licencia

Este proyecto es privado. Todos los derechos reservados © Moon Desarrollos

## 💬 Soporte

Para soporte o consultas:
- Email: soporte@moondesarrollos.com
- Web: https://moondesarrollos.com

## 🎯 Roadmap

- [ ] Migración completa a PHP 8+
- [ ] Implementación de testing automatizado
- [ ] API RESTful para integraciones
- [ ] App móvil (React Native)
- [ ] Dashboard analytics avanzado
- [ ] Integración con más pasarelas de pago

---

**Desarrollado con ❤️ por Moon Desarrollos**

