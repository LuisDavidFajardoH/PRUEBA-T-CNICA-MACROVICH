<template>
  <div :class="['message', `message-${message.role}`]">
    <div class="message-bubble">
      <div v-if="message.isLoading" class="loading-state">
        <ProgressSpinner size="small" />
        <span>{{ loadingText }}</span>
      </div>
      <div v-else class="message-content">
        <p>{{ message.content }}</p>
        <small>{{ formatTime(message.timestamp) }}</small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import { format } from 'date-fns'
import ProgressSpinner from 'primevue/progressspinner'

interface Message {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: Date
  isLoading?: boolean
}

defineProps<{
  message: Message
  loadingText?: string
}>()

const formatTime = (date: Date) => {
  return format(date, 'HH:mm')
}
</script>

<style scoped>
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

@media (max-width: 768px) {
  .message-bubble {
    max-width: 85%;
  }
}
</style>
