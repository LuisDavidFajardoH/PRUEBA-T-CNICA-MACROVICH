<template>
  <SimpleErrorBoundary @errorReported="handleErrorReported" @errorRetried="handleErrorRetry">
    <ChatLayout>
      <template #sidebar>
        <ChatSidebar
          :conversations="conversations"
          :currentConversationId="currentConversation?.id"
          appName="WeatherBot"
          appSubtitle="Asistente Meteorológico"
          @newChat="createNewChat"
          @selectConversation="loadConversation"
          @deleteConversation="deleteConversation"
        />
      </template>

      <template #header>
        <ChatHeader
          :title="currentConversation?.title || 'Nueva conversación'"
          subtitle="Pregúntame sobre el clima en cualquier lugar del mundo"
          :isTyping="isTyping"
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
          v-else-if="!hasMessages"
          title="¡Hola! Soy tu asistente meteorológico"
          description="Puedes preguntarme sobre el clima actual, pronósticos, o cualquier información relacionada con el tiempo."
          avatarIcon="pi pi-cloud"
          :suggestions="welcomeSuggestions"
          @sendSuggestion="sendSampleMessage"
        />
        
        <!-- Messages list -->
        <MessagesList
          v-else
          :messages="messages"
        />
      </template>

      <template #error>
        <!-- Inline error for non-critical errors -->
        <ErrorState
          v-if="error && !criticalError"
          :error="error"
          title="Error temporal"
          variant="error"
          :closable="true"
          :actions="errorActions"
          @close="clearError"
        />
      </template>

      <template #input>
        <MessageInput
          placeholder="Pregúntame sobre el clima..."
          :disabled="isLoading"
          :loading="isLoading"
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
    description: 'Consulta el clima actual',
    message: '¿Cómo está el clima en Madrid?'
  },
  {
    id: '2',
    title: 'Pronóstico semanal',
    description: 'Obtén el pronóstico extendido',
    message: 'Pronóstico para Barcelona esta semana'
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
    handler: () => clearError(),
    icon: 'pi pi-times',
    severity: 'secondary' as const
  }
])

// Methods
const handleSendMessage = async (message: string) => {
  await withErrorHandling(
    () => sendMessage(message),
    {
      context: 'sending message',
      successMessage: 'Mensaje enviado correctamente'
    }
  )
}

const sendSampleMessage = async (message: string) => {
  await handleSendMessage(message)
}

const createNewChat = async () => {
  await withErrorHandling(
    async () => {
      createNewConversation()
      router.push('/')
    },
    {
      context: 'creating new chat'
    }
  )
}

const deleteConversation = async (conversationId: string) => {
  if (confirm('¿Estás seguro de que quieres eliminar esta conversación?')) {
    await withErrorHandling(
      () => deleteConv(conversationId),
      {
        context: 'deleting conversation',
        successMessage: 'Conversación eliminada correctamente'
      }
    )
  }
}

const loadConversationSafely = async (conversationId: string) => {
  await withErrorHandling(
    () => loadConversation(conversationId),
    {
      context: 'loading conversation',
      retryAction: () => loadConversation(conversationId)
    }
  )
}

const loadConversationsSafely = async () => {
  const result = await withErrorHandling(
    () => loadConversations(),
    {
      context: 'loading conversations',
      retryAction: () => loadConversations()
    }
  )

  // If loading conversations fails critically, show empty error state
  if (!result && !conversations.value?.length) {
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

const clearError = () => {
  // Clear both store error and critical error
  if (chatStore.clearError) {
    chatStore.clearError()
  }
  criticalError.value = null
  clearErrors()
}

const retryLastAction = () => {
  // Implement retry logic based on last action
  // This is a placeholder - you'd implement based on your store structure
  console.log('Retrying last action...')
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
  // Load conversations list with error handling
  await loadConversationsSafely()
  
  // Load specific conversation if conversationId is provided
  if (props.conversationId) {
    await loadConversationSafely(props.conversationId)
  }
})
</script>

