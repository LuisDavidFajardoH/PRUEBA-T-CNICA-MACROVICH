<template>
  <div class="chat-container">
    <!-- Sidebar -->
    <div class="chat-sidebar">
      <div class="sidebar-header">
        <div class="app-branding">
          <i class="pi pi-cloud"></i>
          <div class="app-info">
            <h3>WeatherBot</h3>
            <span>Asistente Meteorológico</span>
          </div>
        </div>
        <Button 
          @click="createNewChat"
          icon="pi pi-plus" 
          text
          rounded
          size="small"
          class="new-chat-btn"
          v-tooltip="'Nueva conversación'"
        />
      </div>
      
      <div class="conversations-container">
        <div v-if="conversations.length === 0" class="empty-state">
          <i class="pi pi-comments"></i>
          <p>No hay conversaciones</p>
          <small>¡Empieza una nueva!</small>
        </div>
        
        <div v-else class="conversations-list">
          <div
            v-for="conversation in conversations"
            :key="conversation.id"
            @click="loadConversation(conversation.id)"
            :class="[
              'conversation-item',
              { 'active': currentConversation?.id === conversation.id }
            ]"
          >
            <div class="conversation-content">
              <h4>{{ conversation.title || 'Nueva conversación' }}</h4>
              <span>{{ formatDate(conversation.lastMessageAt) }}</span>
            </div>
            <Button
              @click.stop="deleteConversation(conversation.id)"
              icon="pi pi-trash"
              text
              rounded
              size="small"
              severity="danger"
              class="delete-btn"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="header-info">
          <h2>{{ currentConversation?.title || 'Nueva conversación' }}</h2>
          <p>Pregúntame sobre el clima en cualquier lugar del mundo</p>
        </div>
        
        <div v-if="isTyping" class="typing-indicator">
          <ProgressSpinner size="small" />
          <span>Escribiendo...</span>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="messages-container">
        <!-- Welcome Screen -->
        <div v-if="!hasMessages" class="welcome-screen">
          <div class="welcome-avatar">
            <i class="pi pi-cloud"></i>
          </div>
          <h3>¡Hola! Soy tu asistente meteorológico</h3>
          <p>Puedes preguntarme sobre el clima actual, pronósticos, o cualquier información relacionada con el tiempo.</p>
          
          <div class="suggestion-cards">
            <Card 
              @click="sendSampleMessage('¿Cómo está el clima en Madrid?')"
              class="suggestion-card"
            >
              <template #content>
                <h4>¿Cómo está el clima en Madrid?</h4>
                <p>Consulta el clima actual</p>
              </template>
            </Card>
            
            <Card 
              @click="sendSampleMessage('Pronóstico para Barcelona esta semana')"
              class="suggestion-card"
            >
              <template #content>
                <h4>Pronóstico semanal</h4>
                <p>Obtén el pronóstico extendido</p>
              </template>
            </Card>
          </div>
        </div>

        <!-- Messages -->
        <div v-else class="messages-list">
          <div
            v-for="message in messages"
            :key="message.id"
            :class="['message', `message-${message.role}`]"
          >
            <div class="message-bubble">
              <div v-if="message.isLoading" class="loading-state">
                <ProgressSpinner size="small" />
                <span>Cargando...</span>
              </div>
              <div v-else class="message-content">
                <p>{{ message.content }}</p>
                <small>{{ formatTime(message.timestamp) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="error-container">
        <Message severity="error" :closable="false">
          {{ error }}
        </Message>
      </div>

      <!-- Message Input -->
      <div class="input-container">
        <form @submit.prevent="handleSendMessage" class="message-form">
          <InputText
            v-model="messageInput"
            placeholder="Pregúntame sobre el clima..."
            :disabled="isLoading"
            class="message-input"
          />
          <Button
            type="submit"
            :disabled="!messageInput.trim() || isLoading"
            icon="pi pi-send"
            :loading="isLoading"
            class="send-btn"
          />
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

// PrimeVue Components
import Card from 'primevue/card'
import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import ScrollPanel from 'primevue/scrollpanel'
import Avatar from 'primevue/avatar'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'

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

<style scoped>
.chat-container {
  display: flex;
  height: 100vh;
  background-color: #f8fafc;
}

/* Sidebar Styles */
.chat-sidebar {
  width: 320px;
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.app-branding {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.app-branding i {
  font-size: 1.5rem;
  color: #3b82f6;
}

.app-info h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
}

.app-info span {
  font-size: 0.875rem;
  color: #64748b;
}

.new-chat-btn {
  color: #64748b;
}

.new-chat-btn:hover {
  background-color: #f1f5f9;
  color: #3b82f6;
}

.conversations-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #64748b;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #cbd5e1;
}

.empty-state p {
  margin: 0.5rem 0;
  font-weight: 500;
}

.empty-state small {
  color: #94a3b8;
}

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.conversation-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.conversation-item:hover {
  background-color: #f8fafc;
  border-color: #e2e8f0;
}

.conversation-item.active {
  background-color: #eff6ff;
  border-color: #bfdbfe;
}

.conversation-content {
  flex: 1;
}

.conversation-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
}

.conversation-content span {
  font-size: 0.75rem;
  color: #64748b;
}

.delete-btn {
  opacity: 0;
  transition: opacity 0.2s;
}

.conversation-item:hover .delete-btn {
  opacity: 1;
}

/* Main Chat Area */
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.chat-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-info h2 {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.header-info p {
  margin: 0;
  color: #64748b;
  font-size: 0.875rem;
}

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #3b82f6;
  font-size: 0.875rem;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

/* Welcome Screen */
.welcome-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.welcome-avatar {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.welcome-avatar i {
  font-size: 1.5rem;
  color: white;
}

.welcome-screen h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
}

.welcome-screen p {
  margin: 0 0 2rem 0;
  color: #64748b;
  line-height: 1.6;
}

.suggestion-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  width: 100%;
}

.suggestion-card {
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}

.suggestion-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.suggestion-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.suggestion-card p {
  margin: 0;
  font-size: 0.75rem;
  color: #64748b;
}

/* Messages */
.messages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 800px;
  margin: 0 auto;
}

.message {
  display: flex;
}

.message-user {
  justify-content: flex-end;
}

.message-assistant {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 70%;
  padding: 1rem;
  border-radius: 1rem;
  position: relative;
}

.message-user .message-bubble {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  border-bottom-right-radius: 0.25rem;
}

.message-assistant .message-bubble {
  background: #f1f5f9;
  color: #1e293b;
  border-bottom-left-radius: 0.25rem;
}

.loading-state {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
}

.message-content p {
  margin: 0 0 0.5rem 0;
  line-height: 1.5;
}

.message-content small {
  opacity: 0.7;
  font-size: 0.75rem;
}

.message-user .message-content small {
  color: #bfdbfe;
}

.message-assistant .message-content small {
  color: #64748b;
}

/* Error Container */
.error-container {
  padding: 0 1.5rem;
}

/* Input Container */
.input-container {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: white;
}

.message-form {
  display: flex;
  gap: 0.75rem;
  max-width: 800px;
  margin: 0 auto;
}

.message-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.message-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.send-btn {
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border: none;
  border-radius: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.send-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
  .chat-sidebar {
    width: 280px;
  }
  
  .sidebar-header {
    padding: 1rem;
  }
  
  .conversations-container {
    padding: 0.75rem;
  }
  
  .chat-header {
    padding: 1rem;
  }
  
  .messages-container {
    padding: 1rem;
  }
  
  .input-container {
    padding: 1rem;
  }
  
  .message-bubble {
    max-width: 85%;
  }
  
  .suggestion-cards {
    grid-template-columns: 1fr;
  }
}
</style>