#!/bin/bash

# 🚀 Script de Inicio Rápido - Weather Chatbot
# Ejecuta backend y frontend simultáneamente

echo "🌤️  Iniciando Weather Chatbot..."
echo "================================"

# Función para limpiar procesos al salir
cleanup() {
    echo ""
    echo "🛑 Deteniendo servicios..."
    kill $BACKEND_PID $FRONTEND_PID 2>/dev/null
    wait $BACKEND_PID $FRONTEND_PID 2>/dev/null
    echo "✅ Servicios detenidos"
    exit 0
}

# Capturar Ctrl+C para limpieza
trap cleanup SIGINT SIGTERM

# Verificar que los directorios existan
if [ ! -d "backend" ]; then
    echo "❌ Directorio 'backend' no encontrado"
    exit 1
fi

if [ ! -d "frontend" ]; then
    echo "❌ Directorio 'frontend' no encontrado"
    exit 1
fi

# Verificar que las dependencias estén instaladas
if [ ! -d "backend/vendor" ]; then
    echo "⚠️  Dependencias de backend no instaladas. Ejecuta primero: ./install.sh"
    exit 1
fi

if [ ! -d "frontend/node_modules" ]; then
    echo "⚠️  Dependencias de frontend no instaladas. Ejecuta primero: ./install.sh"
    exit 1
fi

# Iniciar backend
echo "🔧 Iniciando backend (Laravel) en puerto 8000..."
cd backend
php artisan serve --host=0.0.0.0 --port=8000 &
BACKEND_PID=$!
cd ..

# Esperar un poco para que el backend se inicie
sleep 3

# Iniciar frontend
echo "🎨 Iniciando frontend (Vue.js) en puerto 5173..."
cd frontend
npm run dev &
FRONTEND_PID=$!
cd ..

# Mostrar información
echo ""
echo "🎉 ¡Servicios iniciados exitosamente!"
echo "===================================="
echo ""
echo "🌐 URLs disponibles:"
echo "- Frontend: http://localhost:5173"
echo "- Backend API: http://localhost:8000/api"
echo "- Health Check: http://localhost:8000/api/health"
echo ""
echo "👤 Usuario Demo:"
echo "- Email: demo@weatherbot.com"
echo "- Password: password123"
echo ""
echo "🔄 Los servicios están ejecutándose..."
echo "💡 Presiona Ctrl+C para detener ambos servicios"
echo ""

# Esperar indefinidamente
wait
