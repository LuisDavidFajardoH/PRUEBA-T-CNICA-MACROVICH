<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-lg bg-blue-100">
          <i class="pi pi-cloud text-blue-600 text-2xl"></i>
        </div>
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          {{ isLogin ? 'Inicia sesión' : 'Crear cuenta' }}
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ isLogin ? 'Accede a tu asistente meteorológico' : 'Únete a WeatherBot' }}
        </p>
      </div>

      <!-- Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <!-- Name field (only for register) -->
          <div v-if="!isLogin">
            <label for="name" class="block text-sm font-medium text-gray-700">
              Nombre completo
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Tu nombre completo"
            />
          </div>

          <!-- Email field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Correo electrónico
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="tu@email.com"
            />
          </div>

          <!-- Password field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Contraseña
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              :placeholder="isLogin ? 'Tu contraseña' : 'Mínimo 8 caracteres'"
            />
          </div>

          <!-- Confirm password field (only for register) -->
          <div v-if="!isLogin">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmar contraseña
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Repite tu contraseña"
            />
          </div>
        </div>

        <!-- Error message -->
        <div v-if="authStore.error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <i class="pi pi-exclamation-triangle text-red-400 mr-2"></i>
            <div class="text-sm text-red-700">
              {{ authStore.error }}
            </div>
          </div>
        </div>

        <!-- Submit button -->
        <div>
          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="authStore.isLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <i class="pi pi-spin pi-spinner text-blue-300"></i>
            </span>
            {{ authStore.isLoading 
              ? (isLogin ? 'Iniciando sesión...' : 'Creando cuenta...') 
              : (isLogin ? 'Iniciar sesión' : 'Crear cuenta') 
            }}
          </button>
        </div>

        <!-- Toggle between login/register -->
        <div class="text-center">
          <button
            type="button"
            @click="toggleMode"
            class="text-sm text-blue-600 hover:text-blue-500"
          >
            {{ isLogin 
              ? '¿No tienes cuenta? Regístrate' 
              : '¿Ya tienes cuenta? Inicia sesión' 
            }}
          </button>
        </div>

        <!-- Demo credentials -->
        <div v-if="isLogin" class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <div class="text-center">
            <h3 class="text-sm font-medium text-blue-900 mb-2">
              Credenciales de prueba
            </h3>
            <div class="text-xs text-blue-700 space-y-1">
              <p><strong>Email:</strong> demo@weatherbot.com</p>
              <p><strong>Contraseña:</strong> password123</p>
            </div>
            <button
              type="button"
              @click="fillDemoCredentials"
              class="mt-2 text-xs text-blue-600 hover:text-blue-500 underline"
            >
              Llenar automáticamente
            </button>
          </div>
        </div>
      </form>

      <!-- Demo credentials -->
      <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Cuenta de prueba:</h3>
        <p class="text-xs text-gray-600">
          Email: demo@weatherbot.com<br>
          Contraseña: password123
        </p>
        <button
          @click="fillDemoCredentials"
          class="mt-2 text-xs text-blue-600 hover:text-blue-500"
        >
          Usar credenciales de prueba
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// State
const isLogin = ref(true)
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  language: 'es'
})

// Methods
const toggleMode = () => {
  isLogin.value = !isLogin.value
  authStore.clearError()
  resetForm()
}

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
}

const fillDemoCredentials = () => {
  form.email = 'demo@weatherbot.com'
  form.password = 'password123'
  if (!isLogin.value) {
    form.name = 'Usuario Demo'
    form.password_confirmation = 'password123'
  }
}

const handleSubmit = async () => {
  authStore.clearError()

  let result
  if (isLogin.value) {
    result = await authStore.login({
      email: form.email,
      password: form.password
    })
  } else {
    result = await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      timezone: form.timezone,
      language: form.language
    })
  }

  if (result.success) {
    router.push('/')
  }
}
</script>
