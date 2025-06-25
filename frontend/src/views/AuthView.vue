<template>
  <div class="auth-container">
    <div class="auth-background">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>
    </div>
    
    <div class="auth-card">
      <!-- Header -->
      <div class="auth-header">
        <div class="logo-container">
          <div class="logo-icon">
            <i class="pi pi-cloud"></i>
          </div>
          <div class="logo-ripple"></div>
        </div>
        <h1 class="auth-title">
          {{ isLogin ? 'Bienvenido de nuevo' : 'Crear cuenta' }}
        </h1>
        <p class="auth-subtitle">
          {{ isLogin ? 'Accede a tu asistente meteorológico inteligente' : 'Únete a la experiencia WeatherBot' }}
        </p>
      </div>

      <!-- Form -->
      <form class="auth-form" @submit.prevent="handleSubmit">
        <div class="form-fields">
          <!-- Name field (only for register) -->
          <div v-if="!isLogin" class="form-group">
            <label for="name" class="form-label">
              <i class="pi pi-user"></i>
              Nombre completo
            </label>
            <div class="input-container">
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="form-input"
                placeholder="Tu nombre completo"
              />
            </div>
          </div>

          <!-- Email field -->
          <div class="form-group">
            <label for="email" class="form-label">
              <i class="pi pi-envelope"></i>
              Correo electrónico
            </label>
            <div class="input-container">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="form-input"
                placeholder="tu@email.com"
              />
            </div>
          </div>

          <!-- Password field -->
          <div class="form-group">
            <label for="password" class="form-label">
              <i class="pi pi-lock"></i>
              Contraseña
            </label>
            <div class="input-container">
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                class="form-input"
                :placeholder="isLogin ? 'Tu contraseña' : 'Mínimo 8 caracteres'"
              />
            </div>
          </div>

          <!-- Confirm password field (only for register) -->
          <div v-if="!isLogin" class="form-group">
            <label for="password_confirmation" class="form-label">
              <i class="pi pi-shield"></i>
              Confirmar contraseña
            </label>
            <div class="input-container">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                class="form-input"
                placeholder="Repite tu contraseña"
              />
            </div>
          </div>
        </div>

        <!-- Error message -->
        <div v-if="authStore.error" class="error-message">
          <div class="error-content">
            <i class="pi pi-exclamation-triangle"></i>
            <span>{{ authStore.error }}</span>
          </div>
        </div>

        <!-- Submit button -->
        <div class="form-group">
          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="submit-button"
            :class="{ 'loading': authStore.isLoading }"
          >
            <span v-if="authStore.isLoading" class="loading-spinner">
              <i class="pi pi-spin pi-spinner"></i>
            </span>
            <span class="button-text">
              {{ authStore.isLoading 
                ? (isLogin ? 'Iniciando sesión...' : 'Creando cuenta...') 
                : (isLogin ? 'Iniciar sesión' : 'Crear cuenta') 
              }}
            </span>
          </button>
        </div>

        <!-- Toggle between login/register -->
        <div class="form-toggle">
          <button
            type="button"
            @click="toggleMode"
            class="toggle-button"
          >
            {{ isLogin 
              ? '¿No tienes cuenta? Regístrate aquí' 
              : '¿Ya tienes cuenta? Inicia sesión' 
            }}
          </button>
        </div>
      </form>

      <!-- Demo credentials -->
      <div v-if="isLogin" class="demo-credentials">
        <div class="demo-header">
          <i class="pi pi-info-circle"></i>
          <h3>Cuenta de demostración</h3>
        </div>
        <div class="demo-content">
          <div class="demo-item">
            <span class="demo-label">Email:</span>
            <span class="demo-value">demo@weatherbot.com</span>
          </div>
          <div class="demo-item">
            <span class="demo-label">Contraseña:</span>
            <span class="demo-value">password123</span>
          </div>
          <button
            type="button"
            @click="fillDemoCredentials"
            class="demo-button"
          >
            <i class="pi pi-bolt"></i>
            Usar credenciales de prueba
          </button>
        </div>
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

<style scoped>
/* Main container */
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 2rem 1rem;
}

/* Animated background */
.auth-background {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  overflow: hidden;
  z-index: -1;
}

/* Floating shapes animation */
.floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 120px;
  height: 120px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  bottom: 30%;
  left: 20%;
  animation-delay: 4s;
}

.shape-4 {
  width: 100px;
  height: 100px;
  top: 10%;
  right: 25%;
  animation-delay: 1s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Auth card */
.auth-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2rem 4rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  max-width: 700px;
  min-height: auto;
  position: relative;
  overflow: hidden;
}

/* Auth header */
.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.logo-container {
  position: relative;
  display: inline-block;
  margin-bottom: 0.75rem;
}

.logo-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  position: relative;
  z-index: 2;
  box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
}

.logo-icon i {
  font-size: 1.6rem;
  color: white;
}

.logo-ripple {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 60px;
  height: 60px;
  border: 2px solid #4f46e5;
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: ripple 2s infinite;
  opacity: 0.6;
}

@keyframes ripple {
  0% {
    width: 60px;
    height: 60px;
    opacity: 0.6;
  }
  100% {
    width: 120px;
    height: 120px;
    opacity: 0;
  }
}

.auth-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
  background: linear-gradient(135deg, #1f2937, #4f46e5);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.auth-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  line-height: 1.4;
}

/* Form styles */
.auth-form {
  margin-top: 1.5rem;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  position: relative;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.4rem;
}

.form-label i {
  color: #4f46e5;
  font-size: 0.9rem;
}

.input-container {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 0.8rem 1.1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  background: #fafafa;
  transition: all 0.3s ease;
  outline: none;
}

.form-input:focus {
  border-color: #4f46e5;
  background: white;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  transform: translateY(-1px);
}

.form-input::placeholder {
  color: #9ca3af;
}

/* Error message */
.error-message {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 0.8rem;
  margin-bottom: 1rem;
}

.error-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #dc2626;
  font-size: 0.9rem;
  font-weight: 500;
}

.error-content i {
  font-size: 1.1rem;
}

/* Submit button */
.submit-button {
  width: 100%;
  padding: 0.8rem 1.3rem;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  margin-right: 0.5rem;
}

.button-text {
  position: relative;
  z-index: 1;
}

/* Toggle button */
.form-toggle {
  text-align: center;
  margin-top: 1rem;
}

.toggle-button {
  background: none;
  border: none;
  color: #4f46e5;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.toggle-button:hover {
  background: rgba(79, 70, 229, 0.1);
  color: #3730a3;
}

/* Demo credentials */
.demo-credentials {
  margin-top: 1.25rem;
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  border: 1px solid #bae6fd;
  border-radius: 14px;
  padding: 1rem;
  position: relative;
  overflow: hidden;
}

.demo-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.6rem;
}

.demo-header i {
  color: #0284c7;
  font-size: 1.1rem;
}

.demo-header h3 {
  color: #0c4a6e;
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.demo-content {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.demo-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 8px;
}

.demo-label {
  font-weight: 600;
  color: #0c4a6e;
  font-size: 0.85rem;
}

.demo-value {
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 0.85rem;
  color: #0284c7;
  background: rgba(2, 132, 199, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.demo-button {
  margin-top: 0.6rem;
  padding: 0.6rem 0.9rem;
  background: linear-gradient(135deg, #0284c7, #0369a1);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.demo-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
}

.demo-button i {
  font-size: 0.9rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .auth-card {
    max-width: 600px;
    padding: 1.8rem 3rem;
  }
}

@media (max-width: 640px) {
  .auth-container {
    padding: 1rem 0.5rem;
  }
  
  .auth-card {
    padding: 1.5rem 2rem;
    margin: 0.5rem;
    max-width: 95%;
  }
  
  .auth-title {
    font-size: 1.4rem;
  }
  
  .logo-icon {
    width: 50px;
    height: 50px;
  }
  
  .logo-icon i {
    font-size: 1.3rem;
  }
  
  .logo-ripple {
    width: 50px;
    height: 50px;
  }
}

@media (max-width: 480px) {
  .auth-card {
    padding: 1.3rem 1.5rem;
    max-width: 98%;
  }
  
  .auth-title {
    font-size: 1.3rem;
  }
  
  .form-input {
    padding: 0.7rem 0.9rem;
  }
  
  .submit-button {
    padding: 0.7rem 1.1rem;
  }
  
  .logo-icon {
    width: 45px;
    height: 45px;
  }
  
  .logo-icon i {
    font-size: 1.2rem;
  }
}

/* Animation for form entrance */
.auth-card {
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Input focus animation */
.form-input {
  position: relative;
}

.form-input:focus {
  animation: inputFocus 0.3s ease-out;
}

@keyframes inputFocus {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
  100% {
    transform: scale(1);
  }
}
</style>
