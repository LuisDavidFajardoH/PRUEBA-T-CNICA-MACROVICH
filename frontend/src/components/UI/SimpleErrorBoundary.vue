<template>
  <div class="simple-error-boundary">
    <!-- Simple Error Notifications -->
    <div v-if="notifications.length > 0" class="error-notifications">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'error-notification',
          `error-notification--${notification.severity}`
        ]"
      >
        <div class="error-notification__icon">
          <i :class="getNotificationIcon(notification.severity)"></i>
        </div>
        <div class="error-notification__content">
          <h4>{{ notification.title }}</h4>
          <p>{{ notification.message }}</p>
        </div>
        <button
          @click="removeNotification(notification.id)"
          class="error-notification__close"
        >
          <i class="pi pi-times"></i>
        </button>
      </div>
    </div>
    
    <!-- Global Error Modal -->
    <div v-if="showErrorModal" class="error-modal-overlay" @click="closeErrorModal">
      <div class="error-modal" @click.stop>
        <div class="error-modal__header">
          <div class="error-modal__title">
            <i class="pi pi-exclamation-triangle"></i>
            <span>{{ currentError?.title || 'Error' }}</span>
          </div>
          <button @click="closeErrorModal" class="error-modal__close">
            <i class="pi pi-times"></i>
          </button>
        </div>
        
        <div class="error-modal__body">
          <p>{{ currentError?.message }}</p>
          
          <div v-if="currentError?.details" class="error-details">
            <button
              @click="showDetails = !showDetails"
              class="details-toggle"
            >
              <i :class="showDetails ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
              {{ showDetails ? 'Ocultar detalles' : 'Ver detalles técnicos' }}
            </button>
            
            <div v-if="showDetails" class="details-content">
              <pre>{{ currentError.details }}</pre>
            </div>
          </div>
          
          <div v-if="currentError?.actions?.length" class="error-actions">
            <h4>Acciones sugeridas:</h4>
            <ul>
              <li v-for="action in currentError.actions" :key="action">
                {{ action }}
              </li>
            </ul>
          </div>
        </div>
        
        <div class="error-modal__footer">
          <button
            v-if="currentError?.retryAction"
            @click="handleRetry"
            class="btn btn--secondary"
          >
            <i class="pi pi-refresh"></i>
            Reintentar
          </button>
          <button @click="closeErrorModal" class="btn btn--primary">
            Cerrar
          </button>
          <button
            v-if="showReportButton"
            @click="reportError"
            class="btn btn--danger"
          >
            <i class="pi pi-bug"></i>
            Reportar Error
          </button>
        </div>
      </div>
    </div>
    
    <!-- Slot para el contenido principal -->
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, provide } from 'vue'

export interface ErrorInfo {
  id: string
  type: 'network' | 'validation' | 'api' | 'system' | 'unknown'
  severity: 'low' | 'medium' | 'high' | 'critical'
  title: string
  message: string
  details?: string
  timestamp: Date
  source?: string
  actions?: string[]
  retryAction?: () => Promise<void> | void
  autoClose?: boolean
  duration?: number
}

interface Notification extends ErrorInfo {
  timeoutId?: number
}

const props = defineProps<{
  showReportButton?: boolean
  globalErrorHandler?: boolean
}>()

const emit = defineEmits<{
  errorReported: [error: ErrorInfo]
  errorRetried: [error: ErrorInfo]
}>()

// State
const notifications = ref<Notification[]>([])
const showErrorModal = ref(false)
const showDetails = ref(false)
const currentError = ref<ErrorInfo | null>(null)
const errorHistory = ref<ErrorInfo[]>([])

// Methods
const showError = (error: ErrorInfo) => {
  // Add to history
  errorHistory.value.unshift(error)
  
  // Keep only last 10 errors
  if (errorHistory.value.length > 10) {
    errorHistory.value = errorHistory.value.slice(0, 10)
  }
  
  switch (error.severity) {
    case 'low':
    case 'medium':
      showNotification(error)
      break
    case 'high':
    case 'critical':
      showModalError(error)
      break
    default:
      showNotification(error)
  }
}

const showNotification = (error: ErrorInfo) => {
  const notification: Notification = { ...error }
  
  // Auto-close after duration
  if (error.autoClose !== false) {
    const duration = error.duration || 5000
    notification.timeoutId = window.setTimeout(() => {
      removeNotification(notification.id)
    }, duration)
  }
  
  notifications.value.push(notification)
  
  // Limit notifications
  if (notifications.value.length > 3) {
    const oldest = notifications.value.shift()
    if (oldest?.timeoutId) {
      clearTimeout(oldest.timeoutId)
    }
  }
}

const removeNotification = (id: string) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index >= 0) {
    const notification = notifications.value[index]
    if (notification.timeoutId) {
      clearTimeout(notification.timeoutId)
    }
    notifications.value.splice(index, 1)
  }
}

const showModalError = (error: ErrorInfo) => {
  currentError.value = error
  showErrorModal.value = true
  showDetails.value = false
}

const closeErrorModal = () => {
  showErrorModal.value = false
  currentError.value = null
}

const handleRetry = async () => {
  if (currentError.value?.retryAction) {
    try {
      await currentError.value.retryAction()
      emit('errorRetried', currentError.value)
      closeErrorModal()
      
      // Show success notification
      showNotification({
        id: `success-${Date.now()}`,
        type: 'system',
        severity: 'low',
        title: 'Éxito',
        message: 'La operación se completó correctamente',
        timestamp: new Date(),
        autoClose: true,
        duration: 3000
      })
    } catch (retryError) {
      console.error('Retry failed:', retryError)
      showNotification({
        id: `retry-failed-${Date.now()}`,
        type: 'system',
        severity: 'medium',
        title: 'Reintento fallido',
        message: 'No se pudo completar la operación',
        timestamp: new Date(),
        autoClose: true,
        duration: 5000
      })
    }
  }
}

const reportError = () => {
  if (currentError.value) {
    emit('errorReported', currentError.value)
    
    showNotification({
      id: `reported-${Date.now()}`,
      type: 'system',
      severity: 'low',
      title: 'Error reportado',
      message: 'Gracias por reportar este error. Lo revisaremos pronto.',
      timestamp: new Date(),
      autoClose: true,
      duration: 3000
    })
    
    closeErrorModal()
  }
}

const getNotificationIcon = (severity: string) => {
  switch (severity) {
    case 'low':
      return 'pi pi-info-circle'
    case 'medium':
      return 'pi pi-exclamation-triangle'
    case 'high':
    case 'critical':
      return 'pi pi-times-circle'
    default:
      return 'pi pi-info-circle'
  }
}

const clearErrorHistory = () => {
  errorHistory.value = []
  // Clear all notifications
  notifications.value.forEach(n => {
    if (n.timeoutId) clearTimeout(n.timeoutId)
  })
  notifications.value = []
}

// Global error handler
const handleGlobalError = (event: ErrorEvent) => {
  if (!props.globalErrorHandler) return
  
  const error: ErrorInfo = {
    id: `global-${Date.now()}`,
    type: 'system',
    severity: 'high',
    title: 'Error del Sistema',
    message: 'Se produjo un error inesperado en la aplicación',
    details: `${event.error?.stack || event.message}\nArchivo: ${event.filename}:${event.lineno}:${event.colno}`,
    timestamp: new Date(),
    source: 'global'
  }
  
  showError(error)
}

// Provide error context
provide('errorHandler', {
  showError,
  clearErrorHistory,
  errorHistory: errorHistory.value
})

// Lifecycle
onMounted(() => {
  if (props.globalErrorHandler) {
    window.addEventListener('error', handleGlobalError)
    
    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
      const error: ErrorInfo = {
        id: `promise-${Date.now()}`,
        type: 'system',
        severity: 'medium',
        title: 'Error de Promesa',
        message: 'Se produjo un error en una operación asíncrona',
        details: event.reason?.stack || event.reason?.toString() || 'Unknown promise rejection',
        timestamp: new Date(),
        source: 'promise'
      }
      
      showError(error)
    })
  }
})

// Cleanup
onBeforeUnmount(() => {
  if (props.globalErrorHandler) {
    window.removeEventListener('error', handleGlobalError)
  }
  
  // Clear all timeouts
  notifications.value.forEach(n => {
    if (n.timeoutId) clearTimeout(n.timeoutId)
  })
})

// Expose methods
defineExpose({
  showError,
  clearErrorHistory,
  errorHistory
})
</script>

<style scoped>
.simple-error-boundary {
  height: 100%;
  width: 100%;
  position: relative;
}

/* Notifications */
.error-notifications {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 400px;
}

.error-notification {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  background: white;
  border-left: 4px solid;
  animation: slideIn 0.3s ease-out;
}

.error-notification--low {
  border-left-color: #3b82f6;
}

.error-notification--medium {
  border-left-color: #f59e0b;
}

.error-notification--high,
.error-notification--critical {
  border-left-color: #ef4444;
}

.error-notification__icon {
  flex-shrink: 0;
  font-size: 1.25rem;
}

.error-notification--low .error-notification__icon {
  color: #3b82f6;
}

.error-notification--medium .error-notification__icon {
  color: #f59e0b;
}

.error-notification--high .error-notification__icon,
.error-notification--critical .error-notification__icon {
  color: #ef4444;
}

.error-notification__content {
  flex: 1;
  min-width: 0;
}

.error-notification__content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
}

.error-notification__content p {
  margin: 0;
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1.4;
}

.error-notification__close {
  flex-shrink: 0;
  background: none;
  border: none;
  padding: 0.25rem;
  cursor: pointer;
  color: #9ca3af;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.error-notification__close:hover {
  color: #6b7280;
  background: rgba(0, 0, 0, 0.05);
}

/* Modal */
.error-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9998;
  padding: 1rem;
}

.error-modal {
  background: white;
  border-radius: 0.5rem;
  max-width: 500px;
  width: 100%;
  max-height: 80vh;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.error-modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.error-modal__title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #1f2937;
}

.error-modal__title i {
  color: #ef4444;
  font-size: 1.25rem;
}

.error-modal__close {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #6b7280;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.error-modal__close:hover {
  color: #374151;
  background: rgba(0, 0, 0, 0.05);
}

.error-modal__body {
  padding: 1.5rem;
  max-height: 50vh;
  overflow-y: auto;
}

.error-modal__body p {
  margin: 0 0 1rem 0;
  line-height: 1.6;
  color: #374151;
}

.error-details {
  margin: 1rem 0;
}

.details-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #6b7280;
  font-size: 0.875rem;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.details-toggle:hover {
  color: #374151;
  background: rgba(0, 0, 0, 0.05);
}

.details-content {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 0.375rem;
  border: 1px solid #e5e7eb;
}

.details-content pre {
  margin: 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.75rem;
  line-height: 1.4;
  white-space: pre-wrap;
  word-break: break-all;
  color: #6b7280;
}

.error-actions {
  margin: 1rem 0;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.375rem;
  border-left: 4px solid #3b82f6;
}

.error-actions h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
}

.error-actions ul {
  margin: 0;
  padding-left: 1.25rem;
}

.error-actions li {
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.error-modal__footer {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid;
}

.btn--primary {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.btn--primary:hover {
  background: #2563eb;
  border-color: #2563eb;
}

.btn--secondary {
  background: white;
  color: #374151;
  border-color: #d1d5db;
}

.btn--secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn--danger {
  background: white;
  color: #ef4444;
  border-color: #ef4444;
}

.btn--danger:hover {
  background: #fef2f2;
  border-color: #dc2626;
}

/* Animations */
@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 640px) {
  .error-notifications {
    top: 0.5rem;
    right: 0.5rem;
    left: 0.5rem;
    max-width: none;
  }
  
  .error-modal {
    margin: 0.5rem;
  }
  
  .error-modal__footer {
    flex-direction: column;
  }
}
</style>
