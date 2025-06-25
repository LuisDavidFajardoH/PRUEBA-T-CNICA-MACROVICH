<template>
  <div 
    @click="$emit('click')"
    class="suggestion-card"
  >
    <div class="card-content">
      <h4>{{ title }}</h4>
      <p>{{ description }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'

defineProps<{
  title: string
  description: string
  message: string
}>()

defineEmits<{
  click: []
}>()
</script>

<style scoped>
.suggestion-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

.suggestion-card::before {
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

.suggestion-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.15);
  border-color: rgba(79, 70, 229, 0.3);
}

.suggestion-card:hover::before {
  opacity: 1;
}

.suggestion-card:active {
  transform: translateY(-1px);
}

.card-content {
  padding: 1rem;
  position: relative;
  z-index: 1;
}

.suggestion-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1f2937, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.3;
}

.suggestion-card p {
  margin: 0;
  font-size: 0.8rem;
  color: #6b7280;
  line-height: 1.4;
  font-weight: 500;
}

@media (max-width: 768px) {
  .card-content {
    padding: 1.25rem;
  }
  
  .suggestion-card h4 {
    font-size: 0.95rem;
  }
  
  .suggestion-card p {
    font-size: 0.8rem;
  }
}

@media (max-width: 640px) {
  .card-content {
    padding: 1rem;
  }
  
  .suggestion-card h4 {
    font-size: 0.9rem;
  }
  
  .suggestion-card p {
    font-size: 0.75rem;
  }
}
</style>
