#!/bin/bash

# ==============================================================================
# 🚀 Tenanta: High-Performance Deployment Script (Hostinger VPS)
# ==============================================================================

set -e # Detener el script ante cualquier error

echo "----------------------------------------------------------------"
echo "🌐 Desplegando en: srv1569965.hstgr.cloud (72.60.59.25)"
echo "----------------------------------------------------------------"

# 1. Cargar variables de entorno
if [ -f .env ]; then
    echo "🔑 Cargando variables de entorno..."
    export $(cat .env | grep -v '#' | xargs)
else
    echo "⚠️ Error: Archivo .env no encontrado."
    exit 1
fi

# 2. Gestión de Contenedores Docker
echo "📦 Construyendo y levantando contenedores (Caddy, PHP, MySQL, Redis)..."
docker-compose up -d --build

# 3. Instalación de dependencias
echo "📥 Instalando dependencias de PHP (Producción)..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader

# 4. Base de Datos
echo "🗄️ Ejecutando migraciones y seeders..."
docker-compose exec -T app php artisan migrate --force
docker-compose exec -T app php artisan db:seed --class=TenantBulkImportSeeder --force

# 5. Optimización de Performance (Laravel)
echo "⚡ Optimizando caché de la aplicación..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache
docker-compose exec -T app php artisan event:cache

# 6. Permisos de Sistema
echo "🔐 Configurando permisos de carpetas críticas..."
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec -T app chmod -R 775 storage bootstrap/cache

# 7. Frontend Assets
if [ -d "node_modules" ]; then
    echo "🎨 Compilando assets de frontend (Vite)..."
    npm run build
fi

echo "----------------------------------------------------------------"
echo "✅ DESPLIEGUE COMPLETADO EXITOSAMENTE."
echo "🌍 URL: http://72.60.59.25"
echo "----------------------------------------------------------------"
