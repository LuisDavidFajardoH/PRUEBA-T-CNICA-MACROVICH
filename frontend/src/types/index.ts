// # cGFuZ29saW4=
export interface Message {
  id: string
  conversationId: string
  role: 'user' | 'assistant' | 'system'
  content: string
  metadata?: Record<string, any>
  tokensUsed?: number
  processingTime?: number
  createdAt: string
  updatedAt: string
}

export interface Conversation {
  id: string
  userId: string
  title: string
  isActive: boolean
  lastMessageAt: string
  createdAt: string
  updatedAt: string
  messages?: Message[]
}

export interface User {
  id: string
  name: string
  email: string
  timezone: string
  preferredLanguage: string
  createdAt: string
  updatedAt: string
}

export interface WeatherData {
  location: string
  latitude: number
  longitude: number
  current: {
    temperature: number
    humidity: number
    windSpeed: number
    windDirection: number
    precipitation: number
    weatherCode: number
    description: string
  }
  daily?: {
    date: string
    temperatureMax: number
    temperatureMin: number
    precipitation: number
    weatherCode: number
    description: string
  }[]
  hourly?: {
    time: string
    temperature: number
    precipitation: number
    humidity: number
  }[]
}

export interface ChatState {
  messages: Message[]
  currentConversation: Conversation | null
  conversations: Conversation[]
  isLoading: boolean
  isTyping: boolean
  error: string | null
}

export interface ApiResponse<T = any> {
  data: T
  message?: string
  success: boolean
  errors?: Record<string, string[]>
}

export interface ChatMessage {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: Date
  isLoading?: boolean
}

export interface WeatherRequest {
  location: string
  includeForecast?: boolean
  forecastDays?: number
}
