<template>
  <div class="loading-container" :class="{ 'centered': centered }">
    <ProgressSpinner :size="size" />
    <span v-if="text" class="loading-text">{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import ProgressSpinner from 'primevue/progressspinner'

defineProps<{
  size?: string
  text?: string
  centered?: boolean
}>()
</script>

<style scoped>
.loading-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.loading-container.centered {
  justify-content: center;
  padding: 2rem;
}

.loading-text {
  color: #64748b;
  font-size: 0.875rem;
}
</style>
