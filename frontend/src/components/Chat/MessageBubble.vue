<template>
  <div :class="['message', `message-${message.role}`]">
    <div class="message-bubble">
      <div v-if="message.isLoading" class="loading-state">
        <div class="loading-dots">
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
        </div>
        <span>{{ loadingText || 'Escribiendo...' }}</span>
      </div>
      <div v-else class="message-content">
        <div class="message-text" v-html="formatMessage(message.content)"></div>
        <small>{{ formatTime(message.timestamp) }}</small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { format } from 'date-fns'

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

const formatMessage = (content: string): string => {
  // Convert line breaks to HTML
  let formatted = content.replace(/\n/g, '<br>')
  
  // Convert emoji-rich weather content to better formatted HTML
  // Temperature indicators
  formatted = formatted.replace(/(\d+\.?\d*°C)/g, '<strong>$1</strong>')
  
  // Weather conditions with emojis
  formatted = formatted.replace(/(🌤️|☀️|🌥️|☁️|🌧️|⛈️|🌩️|❄️|🌨️|🌦️|🌈)/g, '<span class="weather-emoji">$1</span>')
  
  // Percentage values (humidity, etc.)
  formatted = formatted.replace(/(\d+%)/g, '<strong>$1</strong>')
  
  // Wind speed
  formatted = formatted.replace(/(\d+\.?\d*\s*km\/h)/g, '<strong>$1</strong>')
  
  // Pressure values
  formatted = formatted.replace(/(\d+\.?\d*\s*hPa)/g, '<strong>$1</strong>')
  
  return formatted
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

.loading-dots {
  display: flex;
  gap: 0.25rem;
}

.loading-dots .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #64748b;
  animation: bounce 1.4s ease-in-out infinite both;
}

.loading-dots .dot:nth-child(1) {
  animation-delay: -0.32s;
}

.loading-dots .dot:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.message-content p {
  margin: 0 0 0.5rem 0;
  line-height: 1.5;
}

.message-text {
  line-height: 1.6;
  margin-bottom: 0.5rem;
}

.message-text strong {
  font-weight: 600;
}

.weather-emoji {
  font-size: 1.1em;
  margin: 0 2px;
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
