<template>
  <div class="input-container">
    <form @submit.prevent="handleSubmit" class="message-form">
      <InputText
        v-model="inputValue"
        :placeholder="placeholder"
        :disabled="disabled"
        class="message-input"
        @keydown.enter.prevent="handleSubmit"
      />
      <Button
        type="submit"
        :disabled="!inputValue.trim() || disabled"
        :icon="submitIcon"
        :loading="loading"
        class="send-btn"
      />
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits } from 'vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const props = defineProps<{
  placeholder?: string
  disabled?: boolean
  loading?: boolean
  submitIcon?: string
}>()

const emit = defineEmits<{
  submit: [message: string]
}>()

const inputValue = ref('')

const handleSubmit = () => {
  if (!inputValue.value.trim() || props.disabled) return
  
  const message = inputValue.value
  inputValue.value = ''
  emit('submit', message)
}
</script>

<style scoped>
.input-container {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: white;
}

.message-form {
  display: flex;
  gap: 0.75rem;
  max-width: 800px;
  margin: 0 auto;
}

.message-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.message-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.send-btn {
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border: none;
  border-radius: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.send-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

@media (max-width: 768px) {
  .input-container {
    padding: 1rem;
  }
}
</style>
