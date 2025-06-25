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
  padding: 1.5rem;
}

.welcome-avatar {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.2rem;
  box-shadow: 0 15px 35px rgba(79, 70, 229, 0.3);
  position: relative;
  animation: float 3s ease-in-out infinite;
}

.welcome-avatar::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 4rem;
  height: 4rem;
  border: 2px solid #4f46e5;
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: ripple 2s infinite;
  opacity: 0.6;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes ripple {
  0% {
    width: 4rem;
    height: 4rem;
    opacity: 0.6;
  }
  100% {
    width: 6rem;
    height: 6rem;
    opacity: 0;
  }
}

.welcome-avatar i {
  font-size: 1.6rem;
  color: white;
  position: relative;
  z-index: 2;
}

.welcome-screen h3 {
  margin: 0 0 0.8rem 0;
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1f2937, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.welcome-screen p {
  margin: 0 0 1.5rem 0;
  color: #6b7280;
  line-height: 1.5;
  font-size: 0.9rem;
  font-weight: 500;
  max-width: 480px;
}

.suggestion-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  width: 100%;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .welcome-screen {
    padding: 1.5rem;
  }
  
  .welcome-avatar {
    width: 4rem;
    height: 4rem;
  }
  
  .welcome-avatar i {
    font-size: 1.6rem;
  }
  
  .welcome-screen h3 {
    font-size: 1.5rem;
  }
  
  .welcome-screen p {
    font-size: 1rem;
  }
  
  .suggestion-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

@media (max-width: 640px) {
  .welcome-screen {
    padding: 1rem;
  }
  
  .welcome-avatar {
    width: 3.5rem;
    height: 3.5rem;
  }
  
  .welcome-avatar i {
    font-size: 1.4rem;
  }
  
  .welcome-screen h3 {
    font-size: 1.3rem;
  }
  
  .welcome-screen p {
    font-size: 0.95rem;
  }
}
</style>
