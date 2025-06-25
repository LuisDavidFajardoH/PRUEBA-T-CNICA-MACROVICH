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
  padding: 1rem 1.2rem;
  border-top: 1px solid rgba(79, 70, 229, 0.1);
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  position: relative;
}

.input-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.2), transparent);
}

.message-form {
  display: flex;
  gap: 0.5rem;
  max-width: 600px;
  margin: 0 auto;
  align-items: center;
}

.message-input {
  flex: 1;
  padding: 0.7rem 1rem;
  border: 2px solid rgba(79, 70, 229, 0.1);
  border-radius: 12px;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  outline: none;
}

.message-input:focus {
  border-color: #4f46e5;
  background: white;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  transform: translateY(-1px);
}

.message-input::placeholder {
  color: #9ca3af;
  font-weight: 500;
}

.send-btn {
  padding: 0.8rem 1rem;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border: none;
  border-radius: 12px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
  min-width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
}

.send-btn:active:not(:disabled) {
  transform: translateY(0);
}

.send-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
}

@media (max-width: 768px) {
  .input-container {
    padding: 0.6rem 1rem;
  }
  
  .message-form {
    max-width: 100%;
    gap: 0.4rem;
  }
  
  .message-input {
    padding: 0.5rem 0.7rem;
    font-size: 0.8rem;
    border-radius: 8px;
  }
  
  .send-btn {
    padding: 0.5rem;
    min-width: 36px;
    height: 36px;
    border-radius: 8px;
  }
}

@media (max-width: 640px) {
  .input-container {
    padding: 0.5rem 0.8rem;
  }
  
  .message-form {
    gap: 0.3rem;
  }
  
  .message-input {
    padding: 0.45rem 0.6rem;
    font-size: 0.75rem;
    border-radius: 6px;
  }
  
  .send-btn {
    border-radius: 6px;
    min-width: 32px;
    height: 32px;
    padding: 0.4rem;
  }
}

@media (max-width: 480px) {
  .input-container {
    padding: 0.4rem 0.6rem;
  }
  
  .message-input {
    padding: 0.4rem 0.5rem;
    font-size: 0.7rem;
  }
  
  .send-btn {
    min-width: 28px;
    height: 28px;
    border-radius: 4px;
  }
}
</style>
