<template>
  <Card 
    @click="$emit('click')"
    class="suggestion-card"
  >
    <template #content>
      <h4>{{ title }}</h4>
      <p>{{ description }}</p>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import Card from 'primevue/card'

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
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}

.suggestion-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.suggestion-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.suggestion-card p {
  margin: 0;
  font-size: 0.75rem;
  color: #64748b;
}
</style>
