#!/bin/bash

# ============================================
# Script para Actualizar Composer en Servidor
# ============================================

echo "🔧 Actualizando Composer y Dependencias..."
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar directorio
if [ ! -d "extensiones" ]; then
    echo -e "${RED}❌ Error: Directorio 'extensiones' no encontrado${NC}"
    echo "   Asegúrate de estar en el directorio raíz del proyecto"
    exit 1
fi

cd extensiones

echo -e "${YELLOW}📦 Eliminando vendor y composer.lock antiguos...${NC}"
rm -rf vendor
rm -f composer.lock

echo -e "${YELLOW}📥 Instalando dependencias...${NC}"
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ ¡Composer actualizado correctamente!${NC}"
    echo ""
    echo "📋 Verificación de plataforma:"
    composer check-platform-reqs
else
    echo ""
    echo -e "${RED}❌ Error al actualizar Composer${NC}"
    echo "   Intenta manualmente:"
    echo "   cd extensiones"
    echo "   composer install --ignore-platform-reqs"
    exit 1
fi

echo ""
echo -e "${GREEN}🎉 Proceso completado${NC}"

