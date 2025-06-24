// # cGFuZ29saW4=
export interface Message {
  id: string
  conversation_id: string
  role: 'user' | 'assistant' | 'system'
  content: string
  metadata?: Record<string, any>
  tokens_used?: number
  processing_time?: number
  created_at: string
  updated_at: string
}

export interface Conversation {
  id: string | number
  user_id: string
  title: string
  is_active: boolean
  last_message_at: string
  created_at: string
  updated_at: string
  messages?: Message[]
  messages_count?: number
}

export interface User {
  id: string
  name: string
  email: string
  timezone: string
  preferred_language: string
  created_at: string
  updated_at: string
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
