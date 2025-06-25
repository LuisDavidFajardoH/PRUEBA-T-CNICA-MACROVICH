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
  width: 260px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  margin: 0.5rem 0 0.5rem 0.5rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
  z-index: 2;
}

.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid rgba(79, 70, 229, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
}

.app-branding {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.app-branding i {
  font-size: 1.3rem;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  filter: drop-shadow(0 2px 4px rgba(79, 70, 229, 0.2));
}

.app-info h3 {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1f2937, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.app-info span {
  font-size: 0.7rem;
  color: #6b7280;
  font-weight: 500;
}

.new-chat-btn {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.new-chat-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
}

.conversations-container {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.3);
}

.conversations-container::-webkit-scrollbar {
  width: 6px;
}

.conversations-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

.conversations-container::-webkit-scrollbar-thumb {
  background: rgba(79, 70, 229, 0.3);
  border-radius: 3px;
}

.conversations-container::-webkit-scrollbar-thumb:hover {
  background: rgba(79, 70, 229, 0.5);
}

.empty-state {
  text-align: center;
  padding: 1.5rem;
  color: #6b7280;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 0.8rem;
  background: linear-gradient(135deg, #cbd5e1, #9ca3af);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.empty-state p {
  margin: 0.4rem 0;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.empty-state small {
  color: #9ca3af;
  font-weight: 500;
  font-size: 0.75rem;
}

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.sidebar-footer {
  border-top: 1px solid rgba(79, 70, 229, 0.1);
  padding: 0.6rem;
  background: rgba(248, 250, 252, 0.8);
  backdrop-filter: blur(10px);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.3rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.user-info:hover {
  background: rgba(255, 255, 255, 0.9);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-avatar {
  width: 1.7rem;
  height: 1.7rem;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.7rem;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.75rem;
  font-weight: 600;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 0.7rem;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

.logout-btn {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 6px;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

@media (max-width: 1024px) {
  .chat-sidebar {
    width: 240px;
  }
}

@media (max-width: 768px) {
  .chat-sidebar {
    width: 100%;
    max-width: 100%;
    margin: 0;
    border-radius: 0;
    height: auto;
    max-height: 25vh;
    order: 1;
    flex-shrink: 0;
  }
  
  .sidebar-header {
    padding: 0.6rem 1rem;
  }
  
  .app-info h3 {
    font-size: 0.85rem;
  }
  
  .app-info span {
    font-size: 0.65rem;
  }
  
  .conversations-container {
    padding: 0.5rem 1rem;
    max-height: 120px;
  }
  
  .sidebar-footer {
    padding: 0.5rem 1rem;
  }
  
  .user-name {
    font-size: 0.7rem;
  }
  
  .user-email {
    font-size: 0.65rem;
  }
}

@media (max-width: 640px) {
  .chat-sidebar {
    width: 100%;
    max-width: 100%;
    margin: 0;
    border-radius: 0;
    max-height: 20vh;
    background: rgba(255, 255, 255, 0.98);
  }
  
  .sidebar-header {
    padding: 0.5rem 0.8rem;
    flex-wrap: nowrap;
    gap: 0.5rem;
  }
  
  .app-branding {
    gap: 0.4rem;
    flex: 1;
  }
  
  .app-branding i {
    font-size: 1.1rem;
  }
  
  .app-info h3 {
    font-size: 0.8rem;
  }
  
  .app-info span {
    font-size: 0.6rem;
  }
  
  .new-chat-btn {
    width: 28px;
    height: 28px;
    flex-shrink: 0;
  }
  
  .conversations-container {
    padding: 0.4rem 0.8rem;
    max-height: 80px;
  }
  
  .conversations-list {
    gap: 0.2rem;
  }
  
  .empty-state {
    padding: 0.8rem;
  }
  
  .empty-state i {
    font-size: 1.2rem;
    margin-bottom: 0.4rem;
  }
  
  .empty-state p {
    font-size: 0.75rem;
    margin: 0.2rem 0;
  }
  
  .empty-state small {
    font-size: 0.65rem;
  }
  
  .sidebar-footer {
    padding: 0.4rem 0.8rem;
  }
  
  .user-info {
    padding: 0.3rem;
    gap: 0.4rem;
  }
  
  .user-avatar {
    width: 1.4rem;
    height: 1.4rem;
    font-size: 0.6rem;
  }
  
  .user-name {
    font-size: 0.65rem;
  }
  
  .user-email {
    font-size: 0.55rem;
  }
  
  .logout-btn {
    width: 22px;
    height: 22px;
  }
}

@media (max-width: 480px) {
  .chat-sidebar {
    max-height: 15vh;
    border-radius: 0;
  }
  
  .sidebar-header {
    padding: 0.4rem 0.6rem;
  }
  
  .app-info h3 {
    font-size: 0.75rem;
  }
  
  .app-info span {
    display: none;
  }
  
  .new-chat-btn {
    width: 24px;
    height: 24px;
  }
  
  .conversations-container {
    max-height: 60px;
    padding: 0.3rem 0.6rem;
  }
  
  .sidebar-footer {
    padding: 0.3rem 0.6rem;
  }
  
  .user-info {
    flex-direction: row;
    align-items: center;
    gap: 0.3rem;
    text-align: left;
    padding: 0.2rem;
  }
  
  .user-details {
    text-align: left;
  }
  
  .user-avatar {
    width: 1.2rem;
    height: 1.2rem;
    font-size: 0.55rem;
  }
  
  .user-name {
    font-size: 0.6rem;
  }
  
  .user-email {
    font-size: 0.5rem;
  }
  
  .logout-btn {
    align-self: center;
    width: 20px;
    height: 20px;
  }
}
</style>
