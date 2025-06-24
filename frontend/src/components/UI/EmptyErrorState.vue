<template>
  <div class="empty-error-state">
    <div class="empty-error-state__illustration">
      <div class="error-illustration">
        <div class="error-illustration__circle">
          <i :class="iconClass"></i>
        </div>
        <div class="error-illustration__lines">
          <div class="line line--1"></div>
          <div class="line line--2"></div>
          <div class="line line--3"></div>
        </div>
      </div>
    </div>
    
    <div class="empty-error-state__content">
      <h2>{{ title }}</h2>
      <p>{{ description }}</p>
      
      <div v-if="suggestions?.length" class="suggestions">
        <h3>¿Qué puedes hacer?</h3>
        <ul>
          <li v-for="suggestion in suggestions" :key="suggestion">
            {{ suggestion }}
          </li>
        </ul>
      </div>
      
      <div class="empty-error-state__actions">
        <Button
          v-if="primaryAction"
          @click="primaryAction.handler"
          :label="primaryAction.label"
          :icon="primaryAction.icon"
          :severity="primaryAction.severity || 'primary'"
          class="primary-action"
        />
        
        <Button
          v-if="secondaryAction"
          @click="secondaryAction.handler"
          :label="secondaryAction.label"
          :icon="secondaryAction.icon"
          :severity="secondaryAction.severity || 'secondary'"
          outlined
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Button from 'primevue/button'

interface ActionButton {
  label: string
  handler: () => void
  icon?: string
  severity?: 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'danger'
}

const props = defineProps<{
  title: string
  description: string
  type?: 'error' | 'network' | 'not-found' | 'unauthorized' | 'maintenance'
  suggestions?: string[]
  primaryAction?: ActionButton
  secondaryAction?: ActionButton
}>()

const iconClass = computed(() => {
  switch (props.type) {
    case 'network':
      return 'pi pi-wifi text-4xl text-red-400'
    case 'not-found':
      return 'pi pi-search text-4xl text-gray-400'
    case 'unauthorized':
      return 'pi pi-lock text-4xl text-yellow-400'
    case 'maintenance':
      return 'pi pi-wrench text-4xl text-blue-400'
    default:
      return 'pi pi-exclamation-triangle text-4xl text-red-400'
  }
})
</script>

<style scoped>
.empty-error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 2rem;
  text-align: center;
}

.empty-error-state__illustration {
  margin-bottom: 2rem;
  position: relative;
}

.error-illustration {
  position: relative;
  width: 120px;
  height: 120px;
}

.error-illustration__circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 20px auto;
  position: relative;
  z-index: 2;
  box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2);
}

.error-illustration__lines {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.line {
  position: absolute;
  background: linear-gradient(90deg, transparent, #fca5a5, transparent);
  border-radius: 2px;
  animation: float 3s ease-in-out infinite;
}

.line--1 {
  width: 60px;
  height: 2px;
  top: 20px;
  left: 10px;
  animation-delay: 0s;
}

.line--2 {
  width: 40px;
  height: 2px;
  top: 50px;
  right: 5px;
  animation-delay: 1s;
}

.line--3 {
  width: 50px;
  height: 2px;
  bottom: 25px;
  left: 15px;
  animation-delay: 2s;
}

@keyframes float {
  0%, 100% {
    opacity: 0.3;
    transform: translateY(0px);
  }
  50% {
    opacity: 0.8;
    transform: translateY(-10px);
  }
}

.empty-error-state__content {
  max-width: 500px;
}

.empty-error-state__content h2 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}

.empty-error-state__content p {
  margin: 0 0 2rem 0;
  font-size: 1rem;
  color: #6b7280;
  line-height: 1.6;
}

.suggestions {
  margin: 2rem 0;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  text-align: left;
}

.suggestions h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.suggestions ul {
  margin: 0;
  padding-left: 1.25rem;
}

.suggestions li {
  margin-bottom: 0.5rem;
  color: #4b5563;
  line-height: 1.5;
}

.suggestions li:last-child {
  margin-bottom: 0;
}

.empty-error-state__actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.primary-action {
  min-width: 120px;
}

/* Responsive */
@media (max-width: 640px) {
  .empty-error-state {
    padding: 1rem;
    min-height: 300px;
  }
  
  .empty-error-state__illustration {
    margin-bottom: 1.5rem;
  }
  
  .error-illustration {
    width: 100px;
    height: 100px;
  }
  
  .error-illustration__circle {
    width: 60px;
    height: 60px;
  }
  
  .empty-error-state__content h2 {
    font-size: 1.25rem;
  }
  
  .empty-error-state__actions {
    flex-direction: column;
    align-items: center;
  }
  
  .primary-action {
    width: 100%;
    max-width: 200px;
  }
}
</style>
