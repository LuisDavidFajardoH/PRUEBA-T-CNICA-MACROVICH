import { inject, ref } from 'vue'
import type { ErrorInfo } from '@/components/UI/ErrorBoundary.vue'

interface ErrorHandler {
  showError: (error: ErrorInfo) => void
  clearErrorHistory: () => void
  errorHistory: ErrorInfo[]
}

export function useErrorHandler() {
  const errorHandler = inject<ErrorHandler>('errorHandler')
  const isLoading = ref(false)

  if (!errorHandler) {
    console.warn('ErrorHandler not provided. Make sure to wrap your app with ErrorBoundary component.')
  }

  // Helper function to create error info
  const createError = (
    message: string,
    options: Partial<ErrorInfo> = {}
  ): ErrorInfo => ({
    id: `error-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    type: 'unknown',
    severity: 'medium',
    title: 'Error',
    message,
    timestamp: new Date(),
    ...options
  })

  // Network error helper
  const handleNetworkError = (error: any, context?: string) => {
    let errorInfo: ErrorInfo

    if (error.response) {
      // API responded with error status
      const status = error.response.status
      const data = error.response.data

      errorInfo = createError(
        data?.message || `Error del servidor (${status})`,
        {
          type: 'api',
          severity: status >= 500 ? 'high' : 'medium',
          title: `Error ${status}`,
          details: JSON.stringify(data, null, 2),
          source: context,
          actions: getNetworkErrorActions(status)
        }
      )
    } else if (error.request) {
      // Network error (no response)
      errorInfo = createError(
        'No se pudo conectar con el servidor. Verifica tu conexión a internet.',
        {
          type: 'network',
          severity: 'high',
          title: 'Error de Conexión',
          details: error.message,
          source: context,
          actions: [
            'Verifica tu conexión a internet',
            'Intenta recargar la página',
            'Contacta soporte si el problema persiste'
          ]
        }
      )
    } else {
      // Other error
      errorInfo = createError(
        error.message || 'Error desconocido',
        {
          type: 'system',
          severity: 'medium',
          title: 'Error del Sistema',
          details: error.stack || error.toString(),
          source: context
        }
      )
    }

    errorHandler?.showError(errorInfo)
    return errorInfo
  }

  // Validation error helper
  const handleValidationError = (errors: Record<string, string[]> | string, title = 'Error de Validación') => {
    let message: string
    let details: string | undefined

    if (typeof errors === 'string') {
      message = errors
    } else {
      const errorMessages = Object.entries(errors)
        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
        .join('\n')
      
      message = 'Por favor corrige los siguientes errores:'
      details = errorMessages
    }

    const errorInfo = createError(message, {
      type: 'validation',
      severity: 'low',
      title,
      details,
      autoClose: false
    })

    errorHandler?.showError(errorInfo)
    return errorInfo
  }

  // Async operation wrapper with error handling
  const withErrorHandling = async <T>(
    operation: () => Promise<T>,
    options: {
      context?: string
      loadingRef?: any
      retryAction?: () => Promise<T>
      successMessage?: string
    } = {}
  ): Promise<T | null> => {
    const { context, loadingRef, retryAction, successMessage } = options
    
    try {
      if (loadingRef) {
        loadingRef.value = true
      } else {
        isLoading.value = true
      }

      const result = await operation()

      if (successMessage && errorHandler) {
        // Show success toast if needed
        console.log('Success:', successMessage)
      }

      return result
    } catch (error) {
      const errorInfo = handleNetworkError(error, context)
      
      if (retryAction) {
        errorInfo.retryAction = async () => {
          await withErrorHandling(retryAction, options)
        }
      }

      return null
    } finally {
      if (loadingRef) {
        loadingRef.value = false
      } else {
        isLoading.value = false
      }
    }
  }

  // Show custom error
  const showError = (message: string, options: Partial<ErrorInfo> = {}) => {
    const errorInfo = createError(message, options)
    errorHandler?.showError(errorInfo)
    return errorInfo
  }

  // Show success message
  const showSuccess = (message: string, title = 'Éxito') => {
    const successInfo = createError(message, {
      type: 'system',
      severity: 'low',
      title,
      autoClose: true,
      duration: 3000
    })
    
    // Convert to success type for display
    errorHandler?.showError({
      ...successInfo,
      // This would be handled differently in the ErrorBoundary component
    })
  }

  // Clear all errors
  const clearErrors = () => {
    errorHandler?.clearErrorHistory()
  }

  return {
    handleNetworkError,
    handleValidationError,
    withErrorHandling,
    showError,
    showSuccess,
    clearErrors,
    createError,
    isLoading
  }
}

// Helper function to get suggested actions based on HTTP status
function getNetworkErrorActions(status: number): string[] {
  switch (Math.floor(status / 100)) {
    case 4: // 4xx client errors
      if (status === 401) {
        return [
          'Inicia sesión nuevamente',
          'Verifica tus credenciales',
          'Contacta soporte si el problema persiste'
        ]
      }
      if (status === 403) {
        return [
          'Verifica que tengas permisos para esta acción',
          'Contacta al administrador del sistema'
        ]
      }
      if (status === 404) {
        return [
          'Verifica que la URL sea correcta',
          'El recurso podría haber sido eliminado',
          'Regresa a la página principal'
        ]
      }
      return [
        'Verifica los datos enviados',
        'Revisa que todos los campos estén completos',
        'Contacta soporte si el problema persiste'
      ]

    case 5: // 5xx server errors
      return [
        'El servidor está experimentando problemas',
        'Intenta nuevamente en unos minutos',
        'Contacta soporte si el problema persiste'
      ]

    default:
      return [
        'Intenta recargar la página',
        'Verifica tu conexión a internet',
        'Contacta soporte si el problema persiste'
      ]
  }
}

// Error boundary composable for handling component-level errors
export function useErrorBoundary() {
  const errorHandler = useErrorHandler()
  
  const handleComponentError = (error: Error, instance: any, errorInfo: string) => {
    console.error('Component error:', error, instance, errorInfo)
    
    errorHandler.showError(
      'Se produjo un error en un componente de la aplicación',
      {
        type: 'system',
        severity: 'medium',
        title: 'Error de Componente',
        details: `${error.stack}\n\nComponent Info: ${errorInfo}`,
        source: instance?.$options?.name || 'Unknown Component'
      }
    )
  }

  return {
    handleComponentError,
    ...errorHandler
  }
}
