#!/bin/bash

# 🚀 Script de Instalación Automática - Weather Chatbot
# Prueba Técnica Macrovich

echo "🌤️  Iniciando instalación del Weather Chatbot..."
echo "================================================="

# Verificar prerrequisitos
echo "🔍 Verificando prerrequisitos..."

# Verificar PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP no encontrado. Por favor instala PHP 8.3+"
    exit 1
fi

# Verificar Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer no encontrado. Por favor instala Composer"
    exit 1
fi

# Verificar Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js no encontrado. Por favor instala Node.js 18+"
    exit 1
fi

# Verificar MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL no encontrado. Por favor instala MySQL 8.0+"
    exit 1
fi

echo "✅ Todos los prerrequisitos están instalados"

# 1. Configurar Backend
echo ""
echo "🔧 Configurando Backend (Laravel)..."
cd backend

# Instalar dependencias
echo "📦 Instalando dependencias PHP..."
composer install --no-interaction

# Crear .env si no existe
if [ ! -f .env ]; then
    echo "📝 Creando archivo de configuración..."
    cp .env.example .env
    php artisan key:generate --no-interaction
fi

# Crear base de datos
echo "🗄️  Configurando base de datos..."
read -p "Ingresa tu usuario de MySQL (default: root): " db_user
db_user=${db_user:-root}

read -s -p "Ingresa tu password de MySQL: " db_pass
echo

# Crear base de datos
mysql -u "$db_user" -p"$db_pass" -e "CREATE DATABASE IF NOT EXISTS weather_chatbot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Base de datos creada exitosamente"
    
    # Actualizar .env con credenciales
    sed -i.bak "s/DB_USERNAME=root/DB_USERNAME=$db_user/" .env
    sed -i.bak "s/DB_PASSWORD=/DB_PASSWORD=$db_pass/" .env
    sed -i.bak "s/DB_DATABASE=weather_chatbot/DB_DATABASE=weather_chatbot/" .env
    sed -i.bak "s/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/" .env
else
    echo "❌ Error al crear la base de datos. Verifica las credenciales."
    exit 1
fi

# Ejecutar migraciones
echo "🚀 Ejecutando migraciones..."
php artisan migrate --no-interaction

# Crear usuario demo
echo "👤 Creando usuario demo..."
php artisan db:seed --class=DemoUserSeeder --no-interaction

# 2. Configurar Frontend
echo ""
echo "🎨 Configurando Frontend (Vue.js)..."
cd ../frontend

# Instalar dependencias
echo "📦 Instalando dependencias Node.js..."
npm install

# Crear .env si no existe
if [ ! -f .env ]; then
    echo "📝 Creando configuración del frontend..."
    cp .env.example .env
fi

# 3. Configurar API de Gemini
echo ""
echo "🤖 Configuración de Gemini AI..."
echo "Para obtener tu clave API de Gemini:"
echo "1. Ve a: https://makersuite.google.com/app/apikey"
echo "2. Inicia sesión con tu cuenta de Google"
echo "3. Haz clic en 'Create API Key'"
echo "4. Copia la clave generada"
echo ""

read -p "Ingresa tu clave API de Gemini: " gemini_key

if [ ! -z "$gemini_key" ]; then
    cd ../backend
    sed -i.bak "s/GEMINI_API_KEY=your_gemini_api_key_here/GEMINI_API_KEY=$gemini_key/" .env
    echo "✅ Clave de Gemini configurada"
else
    echo "⚠️  Clave de Gemini no configurada. Recuerda agregarla al archivo .env"
fi

# 4. Verificar instalación
echo ""
echo "🧪 Verificando instalación..."
cd ../backend

# Cache de configuración
php artisan config:cache --no-interaction

# Test de conectividad
echo "🔍 Probando conectividad..."
php artisan migrate:status > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Conexión a base de datos exitosa"
else
    echo "❌ Error de conexión a base de datos"
fi

# 5. Instrucciones finales
echo ""
echo "🎉 ¡Instalación completada!"
echo "=========================="
echo ""
echo "📋 Para iniciar la aplicación:"
echo ""
echo "Terminal 1 - Backend:"
echo "cd backend && php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "Terminal 2 - Frontend:"
echo "cd frontend && npm run dev"
echo ""
echo "🌐 Acceso:"
echo "- Frontend: http://localhost:5173"
echo "- Backend API: http://localhost:8000/api"
echo ""
echo "👤 Usuario Demo:"
echo "- Email: demo@weatherbot.com"
echo "- Password: password123"
echo ""
echo "🤖 Si tienes problemas con Gemini AI:"
echo "php artisan test:gemini"
echo ""
echo "📚 Consulta el README.md para más información"
echo ""

