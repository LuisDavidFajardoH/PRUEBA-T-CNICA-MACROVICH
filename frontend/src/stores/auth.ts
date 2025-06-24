import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

// Configure axios instance for auth
const authApi = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true
})

export interface User {
  id: string
  name: string
  email: string
  timezone?: string
  language?: string
  preferences?: any
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  timezone?: string
  language?: string
}

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  const setAuthToken = (newToken: string) => {
    token.value = newToken
    localStorage.setItem('auth_token', newToken)
    // Update axios default headers
    authApi.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  const clearAuth = () => {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
    delete authApi.defaults.headers.common['Authorization']
  }

  const register = async (userData: RegisterData) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await authApi.post('/auth/register', userData)
      const { data } = response.data

      setAuthToken(data.token)
      user.value = data.user

      return { success: true, user: data.user }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Error al registrar usuario'
      error.value = errorMessage
      console.error('Registration error:', err)
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  const login = async (credentials: LoginCredentials) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await authApi.post('/auth/login', credentials)
      const { data } = response.data

      setAuthToken(data.token)
      user.value = data.user

      return { success: true, user: data.user }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Error al iniciar sesión'
      error.value = errorMessage
      console.error('Login error:', err)
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await authApi.post('/auth/logout', {}, {
          headers: { Authorization: `Bearer ${token.value}` }
        })
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      clearAuth()
    }
  }

  const fetchUser = async () => {
    try {
      if (!token.value) return false

      const response = await authApi.get('/auth/me', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      
      user.value = response.data.data
      return true
    } catch (err) {
      console.error('Fetch user error:', err)
      clearAuth()
      return false
    }
  }

  const initializeAuth = async () => {
    if (token.value) {
      authApi.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      await fetchUser()
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Computed
    isAuthenticated,
    
    // Actions
    register,
    login,
    logout,
    fetchUser,
    initializeAuth,
    clearError
  }
})
