# Makefile para NewMoon ERP/POS
# Simplifica comandos comunes de Docker

.PHONY: help build up down restart logs shell mysql backup restore clean

# Variables
COMPOSE=docker-compose
APP_CONTAINER=newmoon-app
DB_CONTAINER=newmoon-mysql

help: ## Mostrar esta ayuda
	@echo "NewMoon ERP/POS - Comandos disponibles:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Construir las imágenes Docker
	$(COMPOSE) build --no-cache

up: ## Iniciar todos los servicios
	$(COMPOSE) up -d
	@echo "✅ Servicios iniciados!"
	@echo "🌐 Aplicación: http://localhost:8080"
	@echo "📊 phpMyAdmin: http://localhost:8081"

down: ## Detener todos los servicios
	$(COMPOSE) down
	@echo "✅ Servicios detenidos"

restart: ## Reiniciar todos los servicios
	$(COMPOSE) restart
	@echo "✅ Servicios reiniciados"

logs: ## Ver logs de todos los servicios
	$(COMPOSE) logs -f

logs-app: ## Ver logs solo de la aplicación
	$(COMPOSE) logs -f app

logs-db: ## Ver logs solo de MySQL
	$(COMPOSE) logs -f mysql

shell: ## Abrir shell en el contenedor de la aplicación
	$(COMPOSE) exec app bash

shell-root: ## Abrir shell como root en el contenedor
	$(COMPOSE) exec -u root app bash

mysql: ## Conectarse a MySQL CLI
	$(COMPOSE) exec mysql mysql -u newmoon_user -p newmoon_db

ps: ## Ver estado de contenedores
	$(COMPOSE) ps

stats: ## Ver estadísticas de recursos
	docker stats $(APP_CONTAINER) $(DB_CONTAINER)

backup: ## Crear backup de la base de datos
	@mkdir -p backups
	@echo "📦 Creando backup..."
	$(COMPOSE) exec -T mysql mysqldump -u newmoon_user -pnewmoon_password newmoon_db > backups/backup-$$(date +%Y%m%d-%H%M%S).sql
	@echo "✅ Backup creado en backups/"

restore: ## Restaurar backup (usar: make restore FILE=backups/backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "❌ Error: Especificar archivo con FILE=ruta/archivo.sql"; \
		exit 1; \
	fi
	@echo "📥 Restaurando backup $(FILE)..."
	$(COMPOSE) exec -T mysql mysql -u newmoon_user -pnewmoon_password newmoon_db < $(FILE)
	@echo "✅ Backup restaurado"

clean: ## Limpiar contenedores, volúmenes y cache
	@echo "⚠️  Esto eliminará TODOS los datos. ¿Continuar? [y/N]" && read ans && [ $${ans:-N} = y ]
	$(COMPOSE) down -v
	docker system prune -f
	@echo "✅ Sistema limpiado"

rebuild: ## Rebuild completo (down, build, up)
	$(COMPOSE) down
	$(COMPOSE) build --no-cache
	$(COMPOSE) up -d
	@echo "✅ Rebuild completado"

install: ## Setup inicial (copiar .env y levantar servicios)
	@if [ ! -f .env ]; then \
		echo "📝 Creando archivo .env desde .env.example..."; \
		cp .env.example .env; \
		echo "⚠️  Editar .env con tus valores antes de continuar"; \
		exit 1; \
	fi
	$(MAKE) build
	$(MAKE) up
	@echo ""
	@echo "✅ Instalación completada!"
	@echo "🌐 Acceder a: http://localhost:8080"
	@echo "👤 Usuario: admin"
	@echo "🔑 Password: admin123"
	@echo ""
	@echo "⚠️  Importar base de datos con: make restore FILE=tu_backup.sql"

dev: ## Modo desarrollo con logs en vivo
	$(COMPOSE) up

test-connection: ## Verificar conexión entre servicios
	@echo "🔍 Verificando conexión MySQL..."
	$(COMPOSE) exec app ping -c 3 mysql
	@echo "🔍 Verificando conexión a BD..."
	$(COMPOSE) exec app mysql -h mysql -u newmoon_user -pnewmoon_password -e "SELECT 1;" newmoon_db

composer-install: ## Instalar dependencias de Composer
	$(COMPOSE) exec app composer install -d /var/www/html/extensiones

composer-update: ## Actualizar dependencias de Composer
	$(COMPOSE) exec app composer update -d /var/www/html/extensiones

permissions: ## Corregir permisos de archivos
	$(COMPOSE) exec -u root app chown -R www-data:www-data logs storage vistas/img
	$(COMPOSE) exec -u root app chmod -R 775 logs storage vistas/img
	@echo "✅ Permisos corregidos"

# Comandos para Dokploy
dokploy-test: ## Probar build para Dokploy
	docker build -t newmoon-erp-test .
	@echo "✅ Build exitoso. Imagen lista para Dokploy"

dokploy-run: ## Ejecutar imagen como en Dokploy
	docker run -d \
		--name newmoon-test \
		-p 8080:80 \
		-e DB_HOST=host.docker.internal \
		-e DB_NAME=newmoon_db \
		-e DB_USER=newmoon_user \
		-e DB_PASSWORD=password \
		newmoon-erp-test
	@echo "✅ Contenedor de prueba iniciado en http://localhost:8080"

dokploy-stop: ## Detener contenedor de prueba
	docker stop newmoon-test && docker rm newmoon-test

# Default
.DEFAULT_GOAL := help
