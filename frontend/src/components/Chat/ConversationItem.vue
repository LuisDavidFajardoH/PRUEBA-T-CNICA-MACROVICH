<template>
  <div
    @click="$emit('select')"
    :class="[
      'conversation-item',
      { 'active': isActive }
    ]"
  >
    <div class="conversation-content">
      <h4>{{ conversation.title || 'Nueva conversación' }}</h4>
      <span>{{ formatDate(conversation.last_message_at || conversation.lastMessageAt || conversation.created_at) }}</span>
    </div>
    <Button
      @click.stop="handleDelete"
      icon="pi pi-trash"
      text
      rounded
      size="small"
      severity="danger"
      class="delete-btn"
    />
  </div>
</template>

<script setup lang="ts">
import { format } from 'date-fns'
import { es } from 'date-fns/locale'
import Button from 'primevue/button'

interface Conversation {
  id: string | number
  title?: string
  lastMessageAt?: string
  last_message_at?: string
  created_at?: string
}

defineProps<{
  conversation: Conversation
  isActive: boolean
}>()

const emit = defineEmits<{
  select: []
  delete: []
}>()

const formatDate = (dateString?: string) => {
  if (!dateString) return 'Ahora'
  const date = new Date(dateString)
  return format(date, 'dd MMM', { locale: es })
}

const handleDelete = () => {
  if (confirm('¿Estás seguro de que quieres eliminar esta conversación?')) {
    emit('delete')
  }
}
</script>

<style scoped>
.conversation-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  margin-bottom: 0.5rem;
  position: relative;
  overflow: hidden;
}

.conversation-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(124, 58, 237, 0.05));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.conversation-item:hover {
  background: rgba(255, 255, 255, 0.8);
  border-color: rgba(79, 70, 229, 0.2);
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.conversation-item:hover::before {
  opacity: 1;
}

.conversation-item.active {
  background: rgba(79, 70, 229, 0.1);
  border-color: rgba(79, 70, 229, 0.3);
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);
}

.conversation-item.active::before {
  opacity: 1;
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(124, 58, 237, 0.1));
}

.conversation-content {
  flex: 1;
  position: relative;
  z-index: 1;
}

.conversation-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.conversation-item.active .conversation-content h4 {
  color: #4f46e5;
  font-weight: 700;
}

.conversation-content span {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.conversation-item.active .conversation-content span {
  color: #7c3aed;
}

.delete-btn {
  opacity: 0;
  transition: all 0.3s ease;
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  width: 28px;
  height: 28px;
  position: relative;
  z-index: 2;
}

.delete-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #b91c1c;
  transform: scale(1.1);
}

.conversation-item:hover .delete-btn {
  opacity: 1;
}

@media (max-width: 768px) {
  .conversation-item {
    padding: 0.9rem;
  }
  
  .conversation-content h4 {
    font-size: 0.85rem;
  }
  
  .conversation-content span {
    font-size: 0.7rem;
  }
  
  .delete-btn {
    width: 26px;
    height: 26px;
  }
}
</style>
