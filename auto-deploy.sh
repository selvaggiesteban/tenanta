#!/bin/bash

echo "🚀 Iniciando Despliegue Maestro de Tenanta..."

# 1. Cargar variables de entorno
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | xargs)
fi

# 2. Levantar infraestructura Docker
echo "📦 Construyendo contenedores..."
docker-compose up -d --build

# 3. Instalación de dependencias dentro del contenedor
echo "📥 Instalando dependencias de PHP..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader

# 4. Migraciones y Base de Datos
echo "🗄️ Actualizando esquemas de base de datos..."
docker-compose exec -T app php artisan migrate --force

# 5. Carga de Inquilinos (Importación Masiva)
echo "👥 Cargando base de inquilinos desde el archivo unificado..."
docker-compose exec -T app php artisan db:seed --class=TenantBulkImportSeeder --force

# 6. Optimización de Laravel
echo "⚡ Optimizando cache..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# 7. Permisos de carpetas
echo "🔐 Ajustando permisos de almacenamiento..."
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

echo "✅ DESPLIEGUE COMPLETADO EXITOSAMENTE."
echo "🌍 Accede a: http://72.60.59.25"
