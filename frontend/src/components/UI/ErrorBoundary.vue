<template>
  <div class="error-boundary">
    <!-- Error Toast/Notification -->
    <Toast ref="toast" position="top-right" />
    
    <!-- Global Error Modal -->
    <Dialog
      v-model:visible="showErrorModal"
      modal
      :closable="true"
      :style="{ width: '32rem' }"
      class="error-modal"
    >
      <template #header>
        <div class="error-modal-header">
          <i class="pi pi-exclamation-triangle text-red-500"></i>
          <span>{{ currentError?.title || 'Error' }}</span>
        </div>
      </template>
      
      <div class="error-modal-content">
        <p class="error-message">{{ currentError?.message }}</p>
        
        <div v-if="currentError?.details" class="error-details">
          <Button
            @click="showDetails = !showDetails"
            :icon="showDetails ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
            text
            size="small"
            label="Detalles técnicos"
            class="details-toggle"
          />
          
          <div v-if="showDetails" class="details-content">
            <ScrollPanel style="width: 100%; height: 150px">
              <pre class="error-stack">{{ currentError.details }}</pre>
            </ScrollPanel>
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
      
      <template #footer>
        <div class="error-modal-footer">
          <Button
            v-if="currentError?.retryAction"
            @click="handleRetry"
            label="Reintentar"
            icon="pi pi-refresh"
            severity="secondary"
            outlined
          />
          <Button
            @click="closeErrorModal"
            label="Cerrar"
            icon="pi pi-times"
            severity="secondary"
          />
          <Button
            v-if="showReportButton"
            @click="reportError"
            label="Reportar Error"
            icon="pi pi-bug"
            severity="danger"
            outlined
          />
        </div>
      </template>
    </Dialog>
    
    <!-- Slot para el contenido principal -->
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, provide } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Toast from 'primevue/toast'
import ScrollPanel from 'primevue/scrollpanel'
import { useToast } from 'primevue/usetoast'

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

const props = defineProps<{
  showReportButton?: boolean
  globalErrorHandler?: boolean
}>()

const emit = defineEmits<{
  errorReported: [error: ErrorInfo]
  errorRetried: [error: ErrorInfo]
}>()

// State
const toast = useToast()
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
      showToastError(error)
      break
    case 'medium':
      showToastError(error)
      break
    case 'high':
    case 'critical':
      showModalError(error)
      break
    default:
      showToastError(error)
  }
}

const showToastError = (error: ErrorInfo) => {
  // Fallback if toast service is not available
  if (!toast || typeof toast.add !== 'function') {
    console.error('Toast Error:', error.title, error.message)
    // Show modal instead as fallback
    showModalError(error)
    return
  }
  
  const severity = error.type === 'validation' ? 'warn' : 'error'
  
  try {
    toast.add({
      severity,
      summary: error.title,
      detail: error.message,
      life: error.duration || (error.autoClose !== false ? 5000 : 0),
      closable: true
    })
  } catch (toastError) {
    console.error('Failed to show toast:', toastError)
    // Fallback to modal
    showModalError(error)
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
      
      // Show success message
      if (toast && typeof toast.add === 'function') {
        toast.add({
          severity: 'success',
          summary: 'Reintento exitoso',
          detail: 'La operación se completó correctamente',
          life: 3000
        })
      }
    } catch (retryError) {
      console.error('Retry failed:', retryError)
      if (toast && typeof toast.add === 'function') {
        toast.add({
          severity: 'error',
          summary: 'Reintento fallido',
          detail: 'No se pudo completar la operación',
          life: 5000
        })
      }
    }
  }
}

const reportError = () => {
  if (currentError.value) {
    emit('errorReported', currentError.value)
    
    if (toast && typeof toast.add === 'function') {
      toast.add({
        severity: 'info',
        summary: 'Error reportado',
        detail: 'Gracias por reportar este error. Lo revisaremos pronto.',
        life: 3000
      })
    }
    
    closeErrorModal()
  }
}

const clearErrorHistory = () => {
  errorHistory.value = []
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
})

// Expose methods
defineExpose({
  showError,
  clearErrorHistory,
  errorHistory
})
</script>

<style scoped>
.error-boundary {
  height: 100%;
  width: 100%;
}

.error-modal-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.error-modal-content {
  padding: 1rem 0;
}

.error-message {
  margin-bottom: 1rem;
  line-height: 1.6;
  color: #374151;
}

.error-details {
  margin: 1rem 0;
}

.details-toggle {
  margin-bottom: 0.5rem;
}

.details-content {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 0.375rem;
  padding: 0.5rem;
  margin-top: 0.5rem;
}

.error-stack {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.75rem;
  line-height: 1.4;
  color: #dc3545;
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
}

.error-actions {
  margin: 1rem 0;
  padding: 1rem;
  background: #f8f9fa;
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

.error-modal-footer {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

:deep(.p-toast) {
  z-index: 9999;
}

:deep(.p-dialog) {
  z-index: 9998;
}
</style>
