<template>
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
      <WelcomeScreen
        v-if="!hasMessages"
        title="¡Hola! Soy tu asistente meteorológico"
        description="Puedes preguntarme sobre el clima actual, pronósticos, o cualquier información relacionada con el tiempo."
        avatarIcon="pi pi-cloud"
        :suggestions="welcomeSuggestions"
        @sendSuggestion="sendSampleMessage"
      />
      
      <MessagesList
        v-else
        :messages="messages"
      />
    </template>

    <template #error>
      <ErrorMessage
        :error="error"
        severity="error"
        :closable="false"
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
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'

// Components
import {
  ChatLayout,
  ChatSidebar,
  ChatHeader,
  WelcomeScreen,
  MessagesList,
  ErrorMessage,
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

// Methods
const handleSendMessage = async (message: string) => {
  await sendMessage(message)
}

const sendSampleMessage = async (message: string) => {
  await sendMessage(message)
}

const createNewChat = () => {
  createNewConversation()
  router.push('/')
}

const deleteConversation = async (conversationId: string) => {
  if (confirm('¿Estás seguro de que quieres eliminar esta conversación?')) {
    await deleteConv(conversationId)
  }
}

// Lifecycle
onMounted(async () => {
  // Load conversations list
  await loadConversations()
  
  // Load specific conversation if conversationId is provided
  if (props.conversationId) {
    await loadConversation(props.conversationId)
  }
})
</script>

<style scoped>
/* Estilos adicionales específicos para esta vista si es necesario */
</style>