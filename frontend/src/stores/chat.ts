// # cGFuZ29saW4=
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Message, Conversation, ChatState, ChatMessage } from '@/types'
import axios from 'axios'

// Configure axios defaults
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true
})

// Add request interceptor for auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export const useChatStore = defineStore('chat', () => {
  // State
  const messages = ref<ChatMessage[]>([])
  const currentConversation = ref<Conversation | null>(null)
  const conversations = ref<Conversation[]>([])
  const isLoading = ref(false)
  const isTyping = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const hasMessages = computed(() => messages.value.length > 0)
  const lastMessage = computed(() => messages.value[messages.value.length - 1])

  // Actions
  const addMessage = (content: string, role: 'user' | 'assistant' = 'user', id?: string) => {
    const message: ChatMessage = {
      id: id || `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      content,
      role,
      timestamp: new Date(),
      isLoading: role === 'assistant' && !content
    }
    messages.value.push(message)
    return message
  }

  const updateMessage = (messageId: string, updates: Partial<ChatMessage>) => {
    const messageIndex = messages.value.findIndex(msg => msg.id === messageId)
    if (messageIndex !== -1) {
      messages.value[messageIndex] = { ...messages.value[messageIndex], ...updates }
    }
  }

  const sendMessage = async (content: string) => {
    if (!content.trim()) return

    error.value = null
    isLoading.value = true
    isTyping.value = true

    try {
      let response
      
      if (currentConversation.value?.id) {
        // Send message to existing conversation
        response = await api.post(`/chat/conversations/${currentConversation.value.id}/messages`, {
          content: content
        })
        
        const { data } = response.data
        
        // Add the user message if not already present
        if (data.user_message) {
          const userExists = messages.value.find(m => m.id === data.user_message.id.toString())
          if (!userExists) {
            addMessage(data.user_message.content, 'user', data.user_message.id.toString())
          }
        }
        
        // Add the AI response
        if (data.ai_message) {
          addMessage(data.ai_message.content, 'assistant', data.ai_message.id.toString())
        }
        
      } else {
        // Create new conversation with first message
        response = await api.post('/chat/conversations', {
          title: content.substring(0, 50) + (content.length > 50 ? '...' : ''),
          first_message: content
        })
        
        const { data } = response.data
        
        // Set the current conversation
        currentConversation.value = data
        
        // Clear existing messages and add all messages from the conversation
        messages.value = []
        
        if (data.messages && data.messages.length > 0) {
          data.messages.forEach((msg: any) => {
            addMessage(msg.content, msg.role, msg.id.toString())
          })
        }
      }

      // Refresh conversations list only if not already loading
      if (!isLoading.value) {
        loadConversations().catch(console.error)
      }

    } catch (err: any) {
      console.error('Error sending message:', err)
      error.value = err.response?.data?.message || 'Error al enviar el mensaje'
      
      // Add error message for user feedback
      addMessage('Lo siento, ocurrió un error al procesar tu mensaje. Por favor intenta nuevamente.', 'assistant')
      
    } finally {
      isLoading.value = false
      isTyping.value = false
    }
  }

  const clearMessages = () => {
    messages.value = []
    error.value = null
  }

  const loadConversations = async () => {
    try {
      const response = await api.get('/chat/conversations')
      conversations.value = response.data.data
    } catch (err: any) {
      console.error('Error loading conversations:', err)
      error.value = 'Error al cargar las conversaciones'
    }
  }

  const loadConversation = async (conversationId: string) => {
    try {
      isLoading.value = true
      const response = await api.get(`/chat/conversations/${conversationId}`)
      const conversation = response.data.data
      
      currentConversation.value = conversation
      
      // Convert messages to ChatMessage format
      messages.value = conversation.messages?.map((msg: any) => ({
        id: msg.id,
        content: msg.content,
        role: msg.role,
        timestamp: new Date(msg.created_at),
        isLoading: false
      })) || []
      
    } catch (err: any) {
      console.error('Error loading conversation:', err)
      error.value = 'Error al cargar la conversación'
    } finally {
      isLoading.value = false
    }
  }

  const createNewConversation = () => {
    currentConversation.value = null
    clearMessages()
  }

  const deleteConversation = async (conversationId: string) => {
    try {
      await api.delete(`/chat/conversations/${conversationId}`)
      conversations.value = conversations.value.filter(conv => conv.id !== conversationId)
      
      if (currentConversation.value?.id === conversationId) {
        createNewConversation()
      }
    } catch (err: any) {
      console.error('Error deleting conversation:', err)
      error.value = 'Error al eliminar la conversación'
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    messages,
    currentConversation,
    conversations,
    isLoading,
    isTyping,
    error,
    
    // Computed
    hasMessages,
    lastMessage,
    
    // Actions
    addMessage,
    updateMessage,
    sendMessage,
    clearMessages,
    clearError,
    loadConversations,
    loadConversation,
    createNewConversation,
    deleteConversation
  }
})
