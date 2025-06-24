<template>
  <div v-if="error" :class="['error-state', `error-state--${variant}`, { 'error-state--compact': compact }]">
    <div class="error-state__icon">
      <i :class="iconClass"></i>
    </div>
    
    <div class="error-state__content">
      <h3 v-if="title" class="error-state__title">{{ title }}</h3>
      <p class="error-state__message">{{ message || error }}</p>
      
      <div v-if="showDetails && details" class="error-state__details">
        <Button
          @click="detailsVisible = !detailsVisible"
          :icon="detailsVisible ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
          text
          size="small"
          :label="detailsVisible ? 'Ocultar detalles' : 'Ver detalles'"
        />
        
        <div v-if="detailsVisible" class="error-state__details-content">
          <pre>{{ details }}</pre>
        </div>
      </div>
      
      <div v-if="actions?.length" class="error-state__actions">
        <Button
          v-for="action in actions"
          :key="action.label"
          @click="action.handler"
          :label="action.label"
          :icon="action.icon"
          :severity="action.severity || 'secondary'"
          :outlined="action.outlined !== false"
          size="small"
          class="error-state__action"
        />
      </div>
    </div>
    
    <Button
      v-if="closable"
      @click="$emit('close')"
      icon="pi pi-times"
      text
      rounded
      size="small"
      class="error-state__close"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import Button from 'primevue/button'

interface ErrorAction {
  label: string
  handler: () => void
  icon?: string
  severity?: 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'danger'
  outlined?: boolean
}

const props = defineProps<{
  error?: string
  title?: string
  message?: string
  details?: string
  variant?: 'error' | 'warning' | 'info'
  compact?: boolean
  showDetails?: boolean
  closable?: boolean
  actions?: ErrorAction[]
}>()

defineEmits<{
  close: []
}>()

const detailsVisible = ref(false)

const iconClass = computed(() => {
  const baseClass = 'text-2xl'
  switch (props.variant) {
    case 'warning':
      return `pi pi-exclamation-triangle ${baseClass} text-yellow-500`
    case 'info':
      return `pi pi-info-circle ${baseClass} text-blue-500`
    default:
      return `pi pi-times-circle ${baseClass} text-red-500`
  }
})
</script>

<style scoped>
.error-state {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-radius: 0.5rem;
  border: 1px solid;
  background: white;
  position: relative;
}

.error-state--error {
  border-color: #fecaca;
  background: #fef2f2;
}

.error-state--warning {
  border-color: #fed7aa;
  background: #fffbeb;
}

.error-state--info {
  border-color: #bfdbfe;
  background: #eff6ff;
}

.error-state--compact {
  padding: 1rem;
  gap: 0.75rem;
}

.error-state--compact .error-state__title {
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.error-state--compact .error-state__message {
  font-size: 0.875rem;
}

.error-state__icon {
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
  padding-top: 0.125rem;
}

.error-state__content {
  flex: 1;
  min-width: 0;
}

.error-state__title {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
}

.error-state__message {
  margin: 0 0 1rem 0;
  color: #4b5563;
  line-height: 1.5;
}

.error-state--compact .error-state__message {
  margin-bottom: 0.5rem;
}

.error-state__details {
  margin: 1rem 0;
}

.error-state__details-content {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 0.375rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.error-state__details-content pre {
  margin: 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.75rem;
  line-height: 1.4;
  white-space: pre-wrap;
  word-break: break-all;
  color: #6b7280;
}

.error-state__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
}

.error-state--compact .error-state__actions {
  margin-top: 0.5rem;
}

.error-state__action {
  flex-shrink: 0;
}

.error-state__close {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  color: #6b7280;
}

.error-state__close:hover {
  color: #374151;
}

/* Responsive */
@media (max-width: 640px) {
  .error-state {
    flex-direction: column;
    text-align: center;
  }
  
  .error-state__icon {
    align-self: center;
    padding-top: 0;
  }
  
  .error-state__actions {
    justify-content: center;
  }
  
  .error-state__close {
    position: static;
    align-self: flex-end;
    margin-top: -0.5rem;
  }
}
</style>
