// # cGFuZ29saW4=
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Message, Conversation, ChatState, ChatMessage } from '@/types'
import axios from 'axios'

export const useChatStore = defineStore('chat', () => {
  // State
  const messages = ref<ChatMessage[]>([])
  const currentConversation = ref<Conversation | null>(null)
  const conversations = ref<Conversation[]>([])
  const isLoading = ref(false)
  const isTyping = ref(false)
  const error = ref<string | null>(null)

  // API base URL - this will be configured later
  const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

  // Computed
  const hasMessages = computed(() => messages.value.length > 0)
  const lastMessage = computed(() => messages.value[messages.value.length - 1])

  // Actions
  const addMessage = (content: string, role: 'user' | 'assistant' = 'user') => {
    const message: ChatMessage = {
      id: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      content,
      role,
      timestamp: new Date(),
      isLoading: role === 'assistant'
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
    
    // Add user message
    addMessage(content, 'user')
    
    // Add loading assistant message
    const assistantMessage = addMessage('', 'assistant')
    isLoading.value = true
    isTyping.value = true

    try {
      const response = await axios.post(`${API_BASE_URL}/chat`, {
        message: content,
        conversation_id: currentConversation.value?.id
      })

      const { data } = response.data

      // Update assistant message with response
      updateMessage(assistantMessage.id, {
        content: data.response,
        isLoading: false
      })

      // Update current conversation if returned
      if (data.conversation) {
        currentConversation.value = data.conversation
      }

    } catch (err: any) {
      console.error('Error sending message:', err)
      error.value = err.response?.data?.message || 'Error al enviar el mensaje'
      
      // Update assistant message with error
      updateMessage(assistantMessage.id, {
        content: 'Lo siento, ocurrió un error al procesar tu mensaje. Por favor intenta nuevamente.',
        isLoading: false
      })
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
      const response = await axios.get(`${API_BASE_URL}/conversations`)
      conversations.value = response.data.data
    } catch (err: any) {
      console.error('Error loading conversations:', err)
      error.value = 'Error al cargar las conversaciones'
    }
  }

  const loadConversation = async (conversationId: string) => {
    try {
      isLoading.value = true
      const response = await axios.get(`${API_BASE_URL}/conversations/${conversationId}`)
      const conversation = response.data.data
      
      currentConversation.value = conversation
      
      // Convert messages to ChatMessage format
      messages.value = conversation.messages?.map((msg: Message) => ({
        id: msg.id,
        content: msg.content,
        role: msg.role,
        timestamp: new Date(msg.createdAt),
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
      await axios.delete(`${API_BASE_URL}/conversations/${conversationId}`)
      conversations.value = conversations.value.filter(conv => conv.id !== conversationId)
      
      if (currentConversation.value?.id === conversationId) {
        createNewConversation()
      }
    } catch (err: any) {
      console.error('Error deleting conversation:', err)
      error.value = 'Error al eliminar la conversación'
    }
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
    loadConversations,
    loadConversation,
    createNewConversation,
    deleteConversation
  }
})
