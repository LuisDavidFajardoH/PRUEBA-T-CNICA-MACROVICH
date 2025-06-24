<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar with conversations -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
      <!-- Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-semibold text-gray-900">WeatherBot</h1>
          <button
            @click="createNewChat"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors"
            title="Nueva conversación"
          >
            <PlusIcon class="w-5 h-5" />
          </button>
        </div>
        <p class="text-sm text-gray-500 mt-1">Tu asistente meteorológico con IA</p>
      </div>

      <!-- Conversations list -->
      <div class="flex-1 overflow-y-auto">
        <div class="p-2">
          <div
            v-for="conversation in conversations"
            :key="conversation.id"
            @click="loadConversation(conversation.id)"
            :class="[
              'p-3 rounded-lg cursor-pointer transition-colors mb-2',
              currentConversation?.id === conversation.id
                ? 'bg-blue-50 border border-blue-200'
                : 'hover:bg-gray-50'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium text-gray-900 truncate">
                  {{ conversation.title || 'Nueva conversación' }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                  {{ formatDate(conversation.lastMessageAt) }}
                </p>
              </div>
              <button
                @click.stop="deleteConversation(conversation.id)"
                class="p-1 text-gray-400 hover:text-red-500 transition-colors"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <!-- Empty state -->
          <div v-if="conversations.length === 0" class="text-center py-8">
            <ChatBubbleLeftRightIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-sm text-gray-500">No hay conversaciones aún</p>
            <p class="text-xs text-gray-400 mt-1">¡Empieza una nueva conversación!</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main chat area -->
    <div class="flex-1 flex flex-col">
      <!-- Chat header -->
      <div class="bg-white border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              {{ currentConversation?.title || 'Nueva conversación' }}
            </h2>
            <p class="text-sm text-gray-500">
              Pregúntame sobre el clima en cualquier lugar del mundo
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <div v-if="isTyping" class="flex items-center text-sm text-blue-600">
              <div class="flex space-x-1 mr-2">
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
              Escribiendo...
            </div>
          </div>
        </div>
      </div>

      <!-- Messages area -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4">
        <!-- Welcome message when no messages -->
        <div v-if="!hasMessages" class="text-center py-12">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CloudIcon class="w-8 h-8 text-blue-600" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">¡Hola! Soy tu asistente meteorológico</h3>
          <p class="text-gray-600 mb-4 max-w-md mx-auto">
            Puedes preguntarme sobre el clima actual, pronósticos, o cualquier información relacionada con el tiempo.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-2xl mx-auto">
            <button
              @click="sendSampleMessage('¿Cómo está el clima en Madrid?')"
              class="p-3 bg-white border border-gray-200 rounded-lg text-left hover:bg-gray-50 transition-colors"
            >
              <div class="text-sm font-medium text-gray-900">¿Cómo está el clima en Madrid?</div>
              <div class="text-xs text-gray-500 mt-1">Consulta el clima actual</div>
            </button>
            <button
              @click="sendSampleMessage('Pronóstico para Barcelona esta semana')"
              class="p-3 bg-white border border-gray-200 rounded-lg text-left hover:bg-gray-50 transition-colors"
            >
              <div class="text-sm font-medium text-gray-900">Pronóstico semanal</div>
              <div class="text-xs text-gray-500 mt-1">Obtén el pronóstico extendido</div>
            </button>
          </div>
        </div>

        <!-- Messages -->
        <div
          v-for="message in messages"
          :key="message.id"
          :class="[
            'flex',
            message.role === 'user' ? 'justify-end' : 'justify-start'
          ]"
        >
          <div
            :class="[
              'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
              message.role === 'user'
                ? 'bg-blue-600 text-white'
                : 'bg-white border border-gray-200 text-gray-900'
            ]"
          >
            <div v-if="message.isLoading" class="flex items-center space-x-2">
              <div class="flex space-x-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
            </div>
            <div v-else>
              <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
              <p
                :class="[
                  'text-xs mt-1',
                  message.role === 'user' ? 'text-blue-100' : 'text-gray-500'
                ]"
              >
                {{ formatTime(message.timestamp) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error message -->
      <div v-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 mx-4">
        <div class="flex">
          <ExclamationTriangleIcon class="w-5 h-5 text-red-400" />
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Message input -->
      <div class="bg-white border-t border-gray-200 p-4">
        <form @submit.prevent="handleSendMessage" class="flex space-x-3">
          <div class="flex-1">
            <input
              v-model="messageInput"
              type="text"
              placeholder="Pregúntame sobre el clima..."
              :disabled="isLoading"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
          </div>
          <button
            type="submit"
            :disabled="!messageInput.trim() || isLoading"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
          >
            <PaperAirplaneIcon class="w-5 h-5" />
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'
import {
  PlusIcon,
  TrashIcon,
  ChatBubbleLeftRightIcon,
  CloudIcon,
  PaperAirplaneIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps<{
  conversationId?: string
}>()

// Composables
const route = useRoute()
const router = useRouter()
const chatStore = useChatStore()

// Reactive references
const messageInput = ref('')

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

// Methods
const handleSendMessage = async () => {
  if (!messageInput.value.trim()) return
  
  const message = messageInput.value
  messageInput.value = ''
  
  await sendMessage(message)
}

const sendSampleMessage = async (message: string) => {
  messageInput.value = message
  await handleSendMessage()
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

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return format(date, 'dd MMM', { locale: es })
}

const formatTime = (date: Date) => {
  return format(date, 'HH:mm')
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
