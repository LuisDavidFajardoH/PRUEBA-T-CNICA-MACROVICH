<template>
  <div class="welcome-screen">
    <div class="welcome-avatar">
      <i :class="avatarIcon"></i>
    </div>
    <h3>{{ title }}</h3>
    <p>{{ description }}</p>
    
    <div class="suggestion-cards" v-if="suggestions.length > 0">
      <SuggestionCard
        v-for="suggestion in suggestions"
        :key="suggestion.id"
        :title="suggestion.title"
        :description="suggestion.description"
        :message="suggestion.message"
        @click="$emit('sendSuggestion', suggestion.message)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import SuggestionCard from './SuggestionCard.vue'

interface Suggestion {
  id: string
  title: string
  description: string
  message: string
}

defineProps<{
  title: string
  description: string
  avatarIcon?: string
  suggestions: Suggestion[]
}>()

defineEmits<{
  sendSuggestion: [message: string]
}>()
</script>

<style scoped>
.welcome-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.welcome-avatar {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.welcome-avatar i {
  font-size: 1.5rem;
  color: white;
}

.welcome-screen h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
}

.welcome-screen p {
  margin: 0 0 2rem 0;
  color: #64748b;
  line-height: 1.6;
}

.suggestion-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  width: 100%;
}

@media (max-width: 768px) {
  .suggestion-cards {
    grid-template-columns: 1fr;
  }
}
</style>
