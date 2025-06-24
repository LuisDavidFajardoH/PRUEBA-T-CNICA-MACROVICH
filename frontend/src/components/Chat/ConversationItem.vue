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
      <span>{{ formatDate(conversation.lastMessageAt) }}</span>
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
import { defineProps, defineEmits } from 'vue'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'
import Button from 'primevue/button'

interface Conversation {
  id: string
  title?: string
  lastMessageAt: string
}

defineProps<{
  conversation: Conversation
  isActive: boolean
}>()

const emit = defineEmits<{
  select: []
  delete: []
}>()

const formatDate = (dateString: string) => {
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
</style>
