<template>
  <SimpleErrorBoundary @errorReported="handleErrorReported" @errorRetried="handleErrorRetry">
    <ChatLayout>
      <template #sidebar>
        <ChatSidebar
          :conversations="chatStore.conversations?.map(conv => ({...conv, id: conv.id.toString()}))"
          :currentConversationId="chatStore.currentConversation?.id?.toString() || null"
          :user="authStore.user"
          appName="WeatherBot"
          appSubtitle="Asistente Meteorológico"
          @newChat="createNewChat"
          @selectConversation="loadConversationSafely"
          @deleteConversation="deleteConversation"
          @logout="handleLogout"
        />
      </template>

      <template #header>
        <ChatHeader
          :title="chatStore.currentConversation?.title || 'Nueva conversación'"
          subtitle="Consulta el clima actual y pronósticos con datos meteorológicos reales"
          :isTyping="chatStore.isTyping"
          typingText="Escribiendo..."
        />
      </template>

      <template #messages>
        <!-- Error state for when there's a critical error loading messages -->
        <EmptyErrorState
          v-if="criticalError"
          :title="criticalError.title"
          :description="criticalError.message"
          :type="criticalError.type"
          :suggestions="criticalError.suggestions"
          :primaryAction="criticalError.primaryAction"
          :secondaryAction="criticalError.secondaryAction"
        />
        
        <!-- Welcome screen -->
        <WelcomeScreen
          v-else-if="!chatStore.hasMessages"
          title="¡Hola! Soy tu asistente meteorológico inteligente"
          description="Consulta el clima actual, pronósticos extendidos y datos meteorológicos de cualquier ciudad del mundo. Todas mis respuestas están basadas en información real y actualizada."
          avatarIcon="pi pi-cloud"
          :suggestions="welcomeSuggestions"
          @sendSuggestion="sendSampleMessage"
        />
        
        <!-- Messages list -->
        <MessagesList
          v-else
          :messages="chatStore.messages"
        />
      </template>

      <template #error>
        <!-- Inline error for non-critical errors -->
        <ErrorState
          v-if="chatStore.error && !criticalError"
          :error="chatStore.error"
          title="Error temporal"
          variant="error"
          :closable="true"
          :actions="errorActions"
          @close="chatStore.clearError"
        />
      </template>

      <template #input>
        <MessageInput
          placeholder="Pregúntame sobre el clima de cualquier ciudad del mundo..."
          :disabled="chatStore.isLoading"
          :loading="chatStore.isLoading"
          submitIcon="pi pi-send"
          @submit="handleSendMessage"
        />
      </template>
    </ChatLayout>
  </SimpleErrorBoundary>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import { useErrorHandler } from '@/composables/useErrorHandler'

// Components
import {
  ChatLayout,
  ChatSidebar,
  ChatHeader,
  WelcomeScreen,
  MessagesList,
  SimpleErrorBoundary,
  ErrorState,
  EmptyErrorState,
  MessageInput
} from '@/components'

// Props
const props = defineProps<{
  conversationId?: string
}>()

// Composables
const route = useRoute()
const router = useRouter()
const chatStore = useChatStore()
const authStore = useAuthStore()
const { handleNetworkError, withErrorHandling, showError, clearErrors } = useErrorHandler()

// Local state for error handling
const criticalError = ref<any>(null)

// Destructure store
const {
  messages,
  currentConversation,
  conversations,
  isLoading,
  isTyping,
  error,
  hasMessages,
  sendMessage,
  loadConversations,
  loadConversation,
  createNewConversation,
  deleteConversation: deleteConv
} = chatStore

// Welcome suggestions
  const welcomeSuggestions = computed(() => [
    {
      id: '1',
      title: '¿Cómo está el clima en Madrid?',
      description: 'Consulta el clima actual con datos reales',
      message: '¿Cómo está el clima en Madrid?'
    },
    {
      id: '2',
      title: 'Pronóstico de 7 días para Barcelona',
      description: 'Obtén el pronóstico extendido para Barcelona',
      message: 'Dame el pronóstico de 7 días para Barcelona'
    },
    {
      id: '3',
      title: 'Temperatura en París',
      description: 'Información meteorológica actualizada de París',
      message: '¿Cuál es la temperatura actual en París?'
    },
    {
      id: '4',
      title: 'Clima en Nueva York',
      description: 'Consulta datos meteorológicos de cualquier ciudad del mundo',
      message: '¿Qué tiempo hace en Nueva York?'
    }
  ])

// Error actions for inline errors
const errorActions = computed(() => [
  {
    label: 'Reintentar',
    handler: () => retryLastAction(),
    icon: 'pi pi-refresh',
    severity: 'primary' as const
  },
  {
    label: 'Limpiar',
    handler: () => {
      chatStore.clearError()
      clearErrors()
      criticalError.value = null
    },
    icon: 'pi pi-times',
    severity: 'secondary' as const
  }
])

// Methods
const handleSendMessage = async (message: string) => {
  try {
    await chatStore.sendMessage(message)
  } catch (error) {
    console.error('Error sending message:', error)
    // Error is already handled in the store
  }
}

const sendSampleMessage = async (message: string) => {
  await handleSendMessage(message)
}

const createNewChat = async () => {
  try {
    chatStore.createNewConversation()
    router.push('/')
  } catch (error) {
    console.error('Error creating new chat:', error)
  }
}

const deleteConversation = async (conversationId: string) => {
  if (confirm('¿Estás seguro de que quieres eliminar esta conversación?')) {
    try {
      await chatStore.deleteConversation(conversationId)
    } catch (error) {
      console.error('Error deleting conversation:', error)
    }
  }
}

const loadConversationSafely = async (conversationId: string) => {
  try {
    await chatStore.loadConversation(conversationId)
  } catch (error) {
    console.error('Error loading conversation:', error)
  }
}

const loadConversationsSafely = async () => {
  try {
    await chatStore.loadConversations()
  } catch (error) {
    console.error('Error loading conversations:', error)
    // Show critical error state if no conversations exist
    if (!chatStore.conversations?.length) {
      criticalError.value = {
        type: 'network',
        title: 'No se pudieron cargar las conversaciones',
        message: 'Hubo un problema al conectar con el servidor. Por favor, verifica tu conexión a internet.',
        suggestions: [
          'Verifica tu conexión a internet',
          'Recarga la página',
          'Intenta más tarde'
        ],
        primaryAction: {
          label: 'Recargar',
          handler: () => {
            criticalError.value = null
            loadConversationsSafely()
          },
          icon: 'pi pi-refresh'
        },
        secondaryAction: {
          label: 'Crear nueva conversación',
          handler: () => {
            criticalError.value = null
            createNewChat()
          },
          icon: 'pi pi-plus'
        }
      }
    }
  }
}

const retryLastAction = () => {
  // Implement retry logic based on last action
  // This is a placeholder - you'd implement based on your store structure
  console.log('Retrying last action...')
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/auth')
}

// Error event handlers
const handleErrorReported = (error: any) => {
  console.log('Error reported:', error)
  // You could send this to your error reporting service
}

const handleErrorRetry = (error: any) => {
  console.log('Error retry successful:', error)
}

// Lifecycle
onMounted(async () => {
  // Initialize authentication (handled by router guard, but ensure user is loaded)
  if (authStore.token && !authStore.user) {
    await authStore.initializeAuth()
  }
  
  // Load conversations list with error handling
  await loadConversationsSafely()
  
  // Load specific conversation if conversationId is provided
  if (props.conversationId) {
    await loadConversationSafely(props.conversationId)
  }
})
</script>

