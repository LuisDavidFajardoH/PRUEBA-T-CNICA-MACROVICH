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
  padding: 1rem 1.2rem;
  border-bottom: 1px solid rgba(79, 70, 229, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  position: relative;
}

.chat-header::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.2), transparent);
}

.header-info h2 {
  margin: 0 0 0.1rem 0;
  font-size: 1rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1f2937, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-info p {
  margin: 0;
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
}

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #4f46e5;
  font-size: 0.7rem;
  font-weight: 600;
  background: rgba(79, 70, 229, 0.1);
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(79, 70, 229, 0.2);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.02);
  }
}

.typing-indicator :deep(.p-progress-spinner-circle) {
  stroke: #4f46e5;
}

@media (max-width: 768px) {
  .chat-header {
    padding: 1rem 1.5rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .header-info h2 {
    font-size: 1.2rem;
  }
  
  .header-info p {
    font-size: 0.8rem;
  }
  
  .typing-indicator {
    align-self: flex-end;
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
  }
}

@media (max-width: 640px) {
  .chat-header {
    padding: 1rem;
  }
  
  .header-info h2 {
    font-size: 1.1rem;
  }
}
</style>
