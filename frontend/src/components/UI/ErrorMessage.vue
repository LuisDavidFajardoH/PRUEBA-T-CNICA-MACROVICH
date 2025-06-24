<template>
  <div v-if="error" class="error-container">
    <Message 
      :severity="severity" 
      :closable="closable"
      @close="$emit('close')"
    >
      {{ error }}
    </Message>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import Message from 'primevue/message'

defineProps<{
  error?: string
  severity?: 'error' | 'warn' | 'info' | 'success'
  closable?: boolean
}>()

defineEmits<{
  close: []
}>()
</script>

<style scoped>
.error-container {
  padding: 0 1.5rem;
}
</style>
