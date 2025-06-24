# Chatbot Meteorológico - Prueba Técnica

Un chatbot fullstack (Laravel + Vue.js) que responde consultas sobre el clima usando IA (Gemini) y datos en tiempo real de Open-Meteo, almacenando el historial en MySQL.

## 🎯 Características Principales

- **Chatbot Inteligente**: Integración con Gemini AI para respuestas naturales
- **Datos Meteorológicos**: API de Open-Meteo para información climática en tiempo real
- **Geocodificación**: Búsqueda automática de ubicaciones
- **Historial Persistente**: Almacenamiento de conversaciones en MySQL
- **Caché Inteligente**: Optimización de requests a APIs externas
- **API REST**: Endpoints robustos con autenticación
- **Testing**: Cobertura completa de pruebas unitarias



##
#### 1. **Infraestructura Base**
- ✅ **Laravel 12**: Framework PHP moderno
- ✅ **PHP 8.3**: Última versión estable
- ✅ **MySQL 9.3**: Base de datos relacional
- ✅ **Redis 8.0.2**: Caché y sesiones
- ✅ **Composer**: Gestión de dependencias

#### 2. **Base de Datos**
- ✅ **Migraciones**:
  - `users` - Usuarios con campos adicionales
  - `conversations` - Conversaciones con metadata
  - `messages` - Mensajes con soporte JSON
  - `weather_cache` - Caché de datos meteorológicos
- ✅ **Modelos Eloquent**: Relaciones, scopes, accessors/mutators
- ✅ **Factories**: Generación de datos para testing

#### 3. **Servicios de Negocio**

##### 🤖 **AIService (Gemini)**
- ✅ Integración con Gemini API
- ✅ System prompt optimizado para clima
- ✅ Function calling capability
- ✅ Detección de prompt injection
- ✅ Health checks y logging
- ✅ Facade pattern implementado

##### 🌤️ **WeatherService (Open-Meteo)**
- ✅ Integración con Open-Meteo API
- ✅ Geocoding para ubicaciones
- ✅ Sistema de caché inteligente
- ✅ Health checks y estadísticas
- ✅ Múltiples formatos de consulta

##### 💬 **ConversationService**
- ✅ Gestión completa de conversaciones
- ✅ Procesamiento de mensajes con IA
- ✅ Detección automática de consultas meteorológicas
- ✅ Búsqueda y estadísticas
- ✅ Manejo de contexto conversacional

#### 4. **API REST**
- ✅ **Laravel Sanctum**: Autenticación de API
- ✅ **Rate Limiting**: Protección contra abuso
- ✅ **CORS**: Configurado para frontend
- ✅ **Middleware**: Manejo de errores personalizado
- ✅ **Validación**: Request classes para entrada
- ✅ **Recursos**: Formateo consistente de respuestas

#### 5. **Controladores**
- ✅ **ChatController**: Gestión de conversaciones
- ✅ **WeatherController**: Consultas meteorológicas
- ✅ **AuthController**: Autenticación de usuarios

#### 6. **Testing**
- ✅ **18 Tests Unitarios** ejecutándose correctamente
- ✅ **40 Assertions** validadas
- ✅ **Cobertura completa** de servicios principales

### 🔄 **FRONTEND EN DESARROLLO**

#### Vue.js 3 + TypeScript
- 🚧 Componentes de chat
- 🚧 Interfaz de usuario
- 🚧 Integración con API
- 🚧 Estado global (Pinia)

### 📡 **API Endpoints**

#### Públicos
```
GET    /api/health                     # Health check general
GET    /api/weather/health             # Health check meteorológico
GET    /api/weather/public/current     # Clima actual
GET    /api/weather/public/forecast    # Pronóstico
GET    /api/weather/public/search      # Búsqueda de ubicaciones
```

#### Protegidos (Autenticación requerida)
```
GET    /api/user                       # Información del usuario
GET    /api/chat/conversations         # Listar conversaciones
POST   /api/chat/conversations         # Crear conversación
GET    /api/chat/conversations/{id}    # Obtener conversación
POST   /api/chat/conversations/{id}/messages  # Enviar mensaje
DELETE /api/chat/conversations/{id}    # Eliminar conversación
PATCH  /api/chat/conversations/{id}/archive   # Archivar conversación
GET    /api/chat/conversations/{id}/stats     # Estadísticas
GET    /api/chat/messages/search       # Buscar mensajes
GET    /api/chat/messages/recent       # Mensajes recientes
```

## 🚀 Instalación y Configuración

### Prerrequisitos
- PHP 8.3+
- Composer
- MySQL 9.3+
- Redis 8.0+
- Node.js 18+ (para frontend)

### Backend (Laravel)

1. **Clonar el repositorio**:
```bash
git clone <repository-url>
cd PRUEBA-T-CNICA-MACROVICH/backend
```

2. **Instalar dependencias**:
```bash
composer install
```

3. **Configurar entorno**:
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**:
```bash
# Crear base de datos 'weather_chatbot'
mysql -u root -p -e "CREATE DATABASE weather_chatbot;"

# Ejecutar migraciones
php artisan migrate
```

5. **Configurar variables de entorno**:
```env
# Base de datos
DB_DATABASE=weather_chatbot
DB_USERNAME=root
DB_PASSWORD=

# APIs externas
GEMINI_API_KEY=your_gemini_api_key_here
OPENMETEO_BASE_URL=https://api.open-meteo.com/v1
GEOCODING_BASE_URL=https://geocoding-api.open-meteo.com/v1

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Frontend
FRONTEND_URL=http://localhost:5173
```

6. **Iniciar servidor**:
```bash
php artisan serve
```

### Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

## 🧪 Testing

### Ejecutar todos los tests:
```bash
cd backend
php artisan test
```

### Tests específicos:
```bash
# Tests de WeatherService
php artisan test --filter WeatherServiceTest

# Tests de ConversationService
php artisan test --filter ConversationServiceTest
```

### Resultado actual:
```
Tests:    18 passed (40 assertions)
Duration: ~5.67s
```

## 🛠️ Comandos Útiles

### Backend
```bash
# Limpiar caché
php artisan cache:clear

# Ver rutas disponibles
php artisan route:list

# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Generar factories
php artisan make:factory ModelFactory

# Ejecutar seeders
php artisan db:seed
```

### Desarrollo
```bash
# Iniciar servidor con hot reload
php artisan serve --host=0.0.0.0 --port=8000

# Monitorear logs
tail -f storage/logs/laravel.log

# Ejecutar tests en modo watch
php artisan test --parallel
```

## 📊 Métricas del Proyecto

### Estructura de Archivos
```
backend/
├── app/
│   ├── Http/Controllers/Api/     # Controladores API
│   ├── Http/Requests/           # Validación de entrada
│   ├── Http/Resources/          # Formateo de respuestas
│   ├── Models/                  # Modelos Eloquent
│   ├── Services/                # Lógica de negocio
│   ├── Facades/                 # Facades personalizadas
│   └── Providers/               # Service providers
├── database/
│   ├── migrations/              # Migraciones de BD
│   └── factories/               # Factories para testing
├── tests/
│   ├── Unit/                    # Tests unitarios
│   └── Feature/                 # Tests de integración
└── routes/
    ├── api.php                  # Rutas de API
    └── web.php                  # Rutas web
```

### Estadísticas Actuales
- **Archivos Creados**: ~20 archivos principales
- **Líneas de Código**: ~2,500 líneas
- **Tests**: 18 tests, 40 assertions ✅
- **API Endpoints**: 15+ endpoints funcionales
- **Tablas de BD**: 6 tablas con relaciones
- **Servicios**: 3 servicios principales implementados

## 🔮 Próximos Pasos

### Fase 2: Jobs y Colas
- [ ] `ProcessAIResponse` Job para respuestas asíncronas
- [ ] `FetchWeatherData` Job para actualización de caché
- [ ] Configuración de colas con Redis

### Fase 3: Frontend Avanzado
- [ ] Componentes de chat en tiempo real
- [ ] WebSockets para chat en vivo
- [ ] PWA capabilities
- [ ] Responsive design

### Fase 4: DevOps
- [ ] Docker containerization
- [ ] CI/CD pipeline
- [ ] Deployment automation
- [ ] Monitoring y logging

### Fase 5: Optimización
- [ ] Performance optimization
- [ ] Caching strategies
- [ ] Database indexing
- [ ] API rate limiting refinement

## 🤝 Contribución

Este es un proyecto de prueba técnica desarrollado siguiendo las mejores prácticas de Laravel y Vue.js, con enfoque en:

- ✅ **Arquitectura limpia** (MVC + Services)
- ✅ **Testing exhaustivo** (TDD approach)
- ✅ **Documentación completa**
- ✅ **Seguridad** (Sanctum, CORS, Validation)
- ✅ **Performance** (Caching, Rate limiting)
- ✅ **Escalabilidad** (Queue system ready)

## 📄 Licencia

Este proyecto es parte de una prueba técnica para Macrovich.

---

**Desarrollado con ❤️ usando Laravel 12 + Vue.js 3**