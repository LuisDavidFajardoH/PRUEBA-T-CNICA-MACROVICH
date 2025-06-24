<template>
  <div class="chat-sidebar">
    <div class="sidebar-header">
      <div class="app-branding">
        <i class="pi pi-cloud"></i>
        <div class="app-info">
          <h3>{{ appName }}</h3>
          <span>{{ appSubtitle }}</span>
        </div>
      </div>
      <Button 
        @click="$emit('newChat')"
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
        <ConversationItem
          v-for="conversation in conversations"
          :key="conversation.id"
          :conversation="conversation"
          :isActive="currentConversationId === conversation.id?.toString()"
          @select="$emit('selectConversation', conversation.id)"
          @delete="$emit('deleteConversation', conversation.id)"
        />
      </div>
    </div>
    
    <!-- User info footer -->
    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar">
          <i class="pi pi-user"></i>
        </div>
        <div class="user-details">
          <div class="user-name">{{ user?.name || 'Usuario' }}</div>
          <div class="user-email">{{ user?.email || '' }}</div>
        </div>
        <Button 
          @click="$emit('logout')"
          icon="pi pi-sign-out" 
          text
          rounded
          size="small"
          class="logout-btn"
          v-tooltip="'Cerrar sesión'"
          severity="secondary"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Button from 'primevue/button'
import ConversationItem from './ConversationItem.vue'

interface Conversation {
  id: string
  title?: string
  last_message_at?: string
  updated_at?: string
  messages_count?: number
}

interface User {
  id: string
  name: string
  email: string
}

defineProps<{
  conversations: Conversation[]
  currentConversationId?: string | null
  appName: string
  appSubtitle: string
  user?: User | null
}>()

defineEmits<{
  newChat: []
  selectConversation: [conversationId: string]
  deleteConversation: [conversationId: string]
  logout: []
}>()
</script>

<style scoped>
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

.sidebar-footer {
  border-top: 1px solid #e2e8f0;
  padding: 1rem;
  background: #f8fafc;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 2rem;
  height: 2rem;
  background: #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 0.875rem;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 0.75rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  color: #64748b;
  flex-shrink: 0;
}

.logout-btn:hover {
  background-color: #e2e8f0;
  color: #dc2626;
}

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
  
  .sidebar-footer {
    padding: 0.75rem;
  }
}
</style>
