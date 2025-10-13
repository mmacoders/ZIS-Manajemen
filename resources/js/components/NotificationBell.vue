<template>
  <div class="relative">
    <button 
      @click="toggleNotifications"
      class="p-1 rounded-full text-gray-600 hover:text-gray-900 focus:outline-none"
    >
      <BellIcon class="h-6 w-6" />
      <span 
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 block h-2 w-2 rounded-full ring-2 ring-white bg-red-400"
      ></span>
    </button>
    
    <!-- Notifications dropdown -->
    <div 
      v-if="showNotifications"
      class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50"
      style="max-height: 500px; overflow-y: auto;"
    >
      <div class="px-4 py-2 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-medium text-gray-900">Notifikasi</h3>
          <button 
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs text-blue-600 hover:text-blue-800"
          >
            Tandai semua dibaca
          </button>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="py-4 flex justify-center">
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
      </div>
      
      <!-- Notifications list -->
      <div v-else>
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
          :class="{ 'bg-blue-50': !notification.is_read }"
          @click="viewNotification(notification)"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 rounded-full flex items-center justify-center"
                :class="{
                  'bg-red-100 text-red-600': notification.priority === 'urgent',
                  'bg-yellow-100 text-yellow-600': notification.priority === 'high',
                  'bg-blue-100 text-blue-600': notification.priority === 'normal',
                  'bg-gray-100 text-gray-600': notification.priority === 'low'
                }">
                <ExclamationTriangleIcon v-if="notification.priority === 'urgent'" class="w-4 h-4" />
                <ExclamationCircleIcon v-else-if="notification.priority === 'high'" class="w-4 h-4" />
                <InformationCircleIcon v-else class="w-4 h-4" />
              </div>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ notification.message }}</p>
              <div class="mt-1 flex items-center justify-between">
                <p class="text-xs text-gray-400">{{ formatTime(notification.created_at) }}</p>
                <span 
                  v-if="!notification.is_read"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  Baru
                </span>
              </div>
              <div v-if="notification.due_date" class="mt-1">
                <p class="text-xs text-gray-500">
                  Tenggat: {{ formatDate(notification.due_date) }}
                  <span 
                    v-if="isOverdue(notification)"
                    class="text-red-600"
                  >
                    (Terlambat)
                  </span>
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- No notifications message -->
        <div v-if="notifications.length === 0" class="py-8 text-center text-gray-500">
          Tidak ada notifikasi
        </div>
        
        <!-- Load more button -->
        <div v-if="hasMore" class="px-4 py-2 text-center">
          <button 
            @click="loadMore"
            class="text-sm text-blue-600 hover:text-blue-800"
          >
            Muat lebih banyak
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { 
  BellIcon,
  ExclamationTriangleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/solid'

const showNotifications = ref(false)
const notifications = ref<any[]>([])
const unreadCount = ref(0)
const loading = ref(false)
const page = ref(1)
const hasMore = ref(true)

const authStore = useAuthStore()

const fetchNotifications = async () => {
  try {
    loading.value = true
    const response = await axios.get('/notifications', {
      params: {
        page: page.value,
        per_page: 10
      }
    })
    
    if (page.value === 1) {
      notifications.value = response.data.data
    } else {
      notifications.value = [...notifications.value, ...response.data.data]
    }
    
    hasMore.value = response.data.current_page < response.data.last_page
  } catch (error) {
    console.error('Error fetching notifications:', error)
  } finally {
    loading.value = false
  }
}

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/notifications/unread-count')
    unreadCount.value = response.data.count
  } catch (error) {
    console.error('Error fetching unread count:', error)
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    page.value = 1
    fetchNotifications()
  }
}

const loadMore = () => {
  if (!hasMore.value) return
  page.value++
  fetchNotifications()
}

const markAllAsRead = async () => {
  try {
    await axios.post('/notifications/mark-all-read')
    notifications.value = notifications.value.map(notification => ({
      ...notification,
      is_read: true
    }))
    unreadCount.value = 0
  } catch (error) {
    console.error('Error marking all as read:', error)
  }
}

const viewNotification = async (notification: any) => {
  // Mark as read if not already read
  if (!notification.is_read) {
    try {
      await axios.post(`/notifications/${notification.id}/mark-read`)
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }
  
  // Navigate to related item if exists
  if (notification.related_type && notification.related_id) {
    // This would typically navigate to the related item
    console.log('Navigate to:', notification.related_type, notification.related_id)
  }
}

const formatTime = (date: string) => {
  const now = new Date()
  const notificationDate = new Date(date)
  const diffInHours = Math.floor((now.getTime() - notificationDate.getTime()) / (1000 * 60 * 60))
  
  if (diffInHours < 1) {
    return 'Baru saja'
  } else if (diffInHours < 24) {
    return `${diffInHours} jam yang lalu`
  } else {
    return notificationDate.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short'
    })
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const isOverdue = (notification: any) => {
  if (!notification.due_date || notification.is_read) return false
  return new Date(notification.due_date) < new Date()
}

// Close notifications when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative') && showNotifications.value) {
    showNotifications.value = false
  }
}

// Poll for new notifications
let pollInterval: any

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchUnreadCount()
  
  // Poll for unread count every 30 seconds
  pollInterval = setInterval(() => {
    fetchUnreadCount()
  }, 30000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})
</script>