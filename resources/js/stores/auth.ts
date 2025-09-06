import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

// Configure axios defaults
axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(null)
  const token = ref<string | null>(localStorage.getItem('token') || null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value)

  // Set token in axios headers
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  const login = async (credentials: { email: string; password: string }) => {
    try {
      isLoading.value = true
      const response = await axios.post('/login', credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      
      if (token.value) {
        localStorage.setItem('token', token.value)
      }
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      return response.data
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await axios.post('/logout')
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    }
  }

  const getMe = async () => {
    try {
      if (!token.value) return null
      
      const response = await axios.get('/me')
      user.value = response.data.user
      return response.data.user
    } catch (error) {
      await logout()
      throw error
    }
  }

  // Initialize user data if token exists
  if (token.value && !user.value) {
    getMe().catch((error) => {
      console.error('Failed to initialize user data:', error)
      logout()
    })
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    login,
    logout,
    getMe
  }
})