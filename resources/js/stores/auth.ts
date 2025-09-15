import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(null)
  const token = ref<string | null>(localStorage.getItem('token') || null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value)

  // Function to set token in axios headers
  const setAxiosToken = (tokenValue: string | null) => {
    if (tokenValue) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`
    } else {
      delete axios.defaults.headers.common['Authorization']
    }
  }

  // Set token in axios headers initially
  setAxiosToken(token.value)

  // Watch for token changes and update axios headers
  watch(token, (newToken) => {
    setAxiosToken(newToken)
  })

  const login = async (credentials: { email: string; password: string }) => {
    try {
      isLoading.value = true
      // Now that bootstrap.js is properly imported, axios should have the correct baseURL
      console.log('Making login request with configured axios')
      console.log('Axios config:', {
        baseURL: axios.defaults.baseURL,
        headers: axios.defaults.headers
      })
      
      const response = await axios.post('/login', credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      
      if (token.value) {
        localStorage.setItem('token', token.value)
      }
      setAxiosToken(token.value)
      
      return response.data
    } catch (error: any) {
      console.error('Login error:', error)
      console.error('Error response:', error.response)
      console.error('Error status:', error.response?.status)
      console.error('Error data:', error.response?.data)
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
      setAxiosToken(null)
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