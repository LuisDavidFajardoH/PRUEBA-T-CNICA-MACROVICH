# Componentes del Chat

Esta documentación describe los componentes reutilizables creados para la interfaz de chat.

## Estructura de Componentes

```
src/components/
├── Chat/
│   ├── ChatSidebar.vue        # Barra lateral con conversaciones
│   ├── ConversationItem.vue   # Item individual de conversación
│   ├── ChatHeader.vue         # Cabecera del chat
│   ├── WelcomeScreen.vue      # Pantalla de bienvenida
│   ├── SuggestionCard.vue     # Tarjeta de sugerencia
│   ├── MessagesList.vue      # Lista de mensajes
│   ├── MessageBubble.vue      # Burbuja individual de mensaje
│   └── MessageInput.vue       # Input para enviar mensajes
├── UI/
│   ├── ErrorMessage.vue       # Componente para mostrar errores
│   └── LoadingSpinner.vue     # Spinner de carga
├── Layout/
│   └── ChatLayout.vue         # Layout principal del chat
└── index.ts                   # Archivo de exportación
```

## Componentes de Chat

### ChatSidebar
Barra lateral que contiene la lista de conversaciones y el branding de la aplicación.

**Props:**
- `conversations: Conversation[]` - Lista de conversaciones
- `currentConversationId?: string` - ID de la conversación actual
- `appName?: string` - Nombre de la aplicación (default: "WeatherBot")
- `appSubtitle?: string` - Subtítulo de la aplicación

**Eventos:**
- `newChat` - Se emite al hacer clic en nueva conversación
- `selectConversation(id: string)` - Se emite al seleccionar una conversación
- `deleteConversation(id: string)` - Se emite al eliminar una conversación

### ConversationItem
Item individual para mostrar una conversación en la sidebar.

**Props:**
- `conversation: Conversation` - Datos de la conversación
- `isActive: boolean` - Si la conversación está activa

**Eventos:**
- `select` - Se emite al hacer clic en la conversación
- `delete` - Se emite al eliminar la conversación

### ChatHeader
Cabecera del área principal del chat.

**Props:**
- `title: string` - Título del chat
- `subtitle?: string` - Subtítulo del chat
- `isTyping?: boolean` - Si se está escribiendo
- `typingText?: string` - Texto del indicador de escritura

### WelcomeScreen
Pantalla de bienvenida que se muestra cuando no hay mensajes.

**Props:**
- `title: string` - Título de bienvenida
- `description: string` - Descripción
- `avatarIcon?: string` - Clase del icono del avatar
- `suggestions: Suggestion[]` - Lista de sugerencias

**Eventos:**
- `sendSuggestion(message: string)` - Se emite al hacer clic en una sugerencia

### SuggestionCard
Tarjeta de sugerencia para la pantalla de bienvenida.

**Props:**
- `title: string` - Título de la sugerencia
- `description: string` - Descripción de la sugerencia
- `message: string` - Mensaje que se enviará

**Eventos:**
- `click` - Se emite al hacer clic en la tarjeta

### MessagesList
Contenedor para la lista de mensajes.

**Props:**
- `messages: Message[]` - Lista de mensajes

### MessageBubble
Burbuja individual para mostrar un mensaje.

**Props:**
- `message: Message` - Datos del mensaje
- `loadingText?: string` - Texto del estado de carga

### MessageInput
Input para escribir y enviar mensajes.

**Props:**
- `placeholder?: string` - Placeholder del input
- `disabled?: boolean` - Si el input está deshabilitado
- `loading?: boolean` - Si está en estado de carga
- `submitIcon?: string` - Icono del botón de envío

**Eventos:**
- `submit(message: string)` - Se emite al enviar un mensaje

## Componentes UI

### ErrorMessage
Componente para mostrar mensajes de error.

**Props:**
- `error?: string` - Mensaje de error
- `severity?: 'error' | 'warn' | 'info' | 'success'` - Tipo de mensaje
- `closable?: boolean` - Si se puede cerrar

**Eventos:**
- `close` - Se emite al cerrar el mensaje

### LoadingSpinner
Spinner de carga reutilizable.

**Props:**
- `size?: string` - Tamaño del spinner
- `text?: string` - Texto acompañante
- `centered?: boolean` - Si debe estar centrado

## Layout

### ChatLayout
Layout principal que organiza los slots del chat.

**Slots:**
- `sidebar` - Contenido de la barra lateral
- `header` - Cabecera del chat
- `messages` - Área de mensajes
- `error` - Mensajes de error
- `input` - Input de mensajes

## Interfaces TypeScript

```typescript
interface Conversation {
  id: string
  title?: string
  lastMessageAt: string
}

interface Message {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: Date
  isLoading?: boolean
}

interface Suggestion {
  id: string
  title: string
  description: string
  message: string
}
```

## Uso

### Importación
```typescript
import {
  ChatLayout,
  ChatSidebar,
  ChatHeader,
  WelcomeScreen,
  MessagesList,
  ErrorMessage,
  MessageInput
} from '@/components'
```

### Ejemplo de uso completo
Ver `ChatView.vue` para un ejemplo completo de cómo usar todos los componentes juntos.

## Características

✅ **Totalmente reutilizables** - Cada componente puede usarse independientemente  
✅ **TypeScript** - Tipado completo con interfaces  
✅ **Responsive** - Diseño adaptable a móviles  
✅ **Modular** - Separación clara de responsabilidades  
✅ **Accesible** - Eventos y props semánticamente correctos  
✅ **Personalizable** - Props para customizar apariencia y comportamiento
