import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const data = ref<any>(null)
  const isLoading = ref(false)

  const fetchDashboardData = async () => {
    try {
      isLoading.value = true
      const response = await axios.get('/dashboard')
      data.value = response.data
      return response.data
    } catch (error) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const getNotificationCount = () => {
    if (!data.value || !data.value.notifications) return 0
    return data.value.notifications.unread_count || 0
  }

  const getPendingItems = () => {
    if (!data.value || !data.value.notifications) return { transactions: 0, documents: 0, total: 0 }
    return data.value.notifications.pending_items || { transactions: 0, documents: 0, total: 0 }
  }

  const getRecentActivities = () => {
    if (!data.value || !data.value.notifications) return []
    return data.value.notifications.recent_activities || []
  }

  return {
    data,
    isLoading,
    fetchDashboardData,
    getNotificationCount,
    getPendingItems,
    getRecentActivities
  }
})