<template>
  <div class="chat-header">
    <div class="header-info">
      <h2>{{ title }}</h2>
      <p>{{ subtitle }}</p>
    </div>
    
    <div v-if="isTyping" class="typing-indicator">
      <ProgressSpinner size="small" />
      <span>{{ typingText }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import ProgressSpinner from 'primevue/progressspinner'

defineProps<{
  title: string
  subtitle?: string
  isTyping?: boolean
  typingText?: string
}>()
</script>

<style scoped>
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

@media (max-width: 768px) {
  .chat-header {
    padding: 1rem;
  }
}
</style>
