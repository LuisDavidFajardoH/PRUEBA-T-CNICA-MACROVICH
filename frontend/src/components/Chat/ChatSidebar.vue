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
          :isActive="currentConversationId === conversation.id"
          @select="$emit('selectConversation', conversation.id)"
          @delete="$emit('deleteConversation', conversation.id)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import Button from 'primevue/button'
import ConversationItem from './ConversationItem.vue'

interface Conversation {
  id: string
  title?: string
  lastMessageAt: string
}

defineProps<{
  conversations: Conversation[]
  currentConversationId?: string
  appName?: string
  appSubtitle?: string
}>()

defineEmits<{
  newChat: []
  selectConversation: [id: string]
  deleteConversation: [id: string]
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
}
</style>
