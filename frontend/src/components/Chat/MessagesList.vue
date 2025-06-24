<template>
  <div class="messages-list">
    <MessageBubble
      v-for="message in messages"
      :key="message.id"
      :message="message"
    />
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import MessageBubble from './MessageBubble.vue'

interface Message {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: Date
  isLoading?: boolean
}

defineProps<{
  messages: Message[]
}>()
</script>

<style scoped>
.messages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 800px;
  margin: 0 auto;
}
</style>
