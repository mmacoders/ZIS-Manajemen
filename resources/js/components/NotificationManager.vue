<template>
  <div class="notification-manager">
    <NotificationToast
      v-for="notification in notifications"
      :key="notification.id"
      :notification="notification"
      @close="removeNotification"
      @click="handleNotificationClick"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import NotificationToast from './NotificationToast.vue'
import Pusher from 'pusher-js'

interface Notification {
  id: string
  type: string
  message: string
  timestamp: string
  [key: string]: any
}

const notifications = ref<Notification[]>([])
let pusher: Pusher | null = null

onMounted(() => {
  initializePusher()
})

onUnmounted(() => {
  if (pusher) {
    pusher.disconnect()
  }
})

const initializePusher = () => {
  // For development, we'll use a mock implementation
  // In production, replace with actual Pusher configuration
  
  if (import.meta.env.VITE_PUSHER_APP_KEY) {
    pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
      encrypted: true,
      authEndpoint: '/api/broadcasting/auth',
      auth: {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      }
    })

    // Subscribe to public channels
    const transactionChannel = pusher.subscribe('zis-transactions')
    const distributionChannel = pusher.subscribe('distributions')

    // Listen for transaction events
    transactionChannel.bind('transaction.created', (data: any) => {
      addNotification({
        id: generateId(),
        type: 'new_transaction',
        message: data.message,
        timestamp: data.timestamp,
        ...data
      })
    })

    // Listen for distribution events
    distributionChannel.bind('distribution.completed', (data: any) => {
      addNotification({
        id: generateId(),
        type: 'distribution_completed',
        message: data.message,
        timestamp: data.timestamp,
        ...data
      })
    })

    // Subscribe to private channels based on user role
    const userRole = getCurrentUserRole()
    if (userRole) {
      const privateChannel = pusher.subscribe(`private-${userRole}-notifications`)
      
      privateChannel.bind('transaction.created', (data: any) => {
        addNotification({
          id: generateId(),
          type: 'new_transaction',
          message: data.message,
          timestamp: data.timestamp,
          ...data
        })
      })

      privateChannel.bind('distribution.completed', (data: any) => {
        addNotification({
          id: generateId(),
          type: 'distribution_completed',
          message: data.message,
          timestamp: data.timestamp,
          ...data
        })
      })
    }
  } else {
    // Mock notifications for development
    setTimeout(() => {
      addNotification({
        id: generateId(),
        type: 'new_transaction',
        message: 'Demo: Transaksi ZIS baru dari Ahmad Rizki sebesar Rp 500,000',
        timestamp: new Date().toISOString()
      })
    }, 3000)

    setTimeout(() => {
      addNotification({
        id: generateId(),
        type: 'distribution_completed',
        message: 'Demo: Distribusi Bantuan Pendidikan kepada Siti Fatimah sebesar Rp 1,000,000 telah selesai',
        timestamp: new Date().toISOString()
      })
    }, 8000)
  }
}

const getCurrentUserRole = (): string | null => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user.role?.name || null
  } catch {
    return null
  }
}

const addNotification = (notification: Notification) => {
  notifications.value.unshift(notification)
  
  // Limit to 5 notifications
  if (notifications.value.length > 5) {
    notifications.value = notifications.value.slice(0, 5)
  }
}

const removeNotification = (id: string) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

const handleNotificationClick = (notification: Notification) => {
  // Handle navigation based on notification type
  switch (notification.type) {
    case 'new_transaction':
      // Navigate to transactions page
      console.log('Navigate to transactions:', notification)
      break
    case 'distribution_completed':
      // Navigate to distributions page
      console.log('Navigate to distributions:', notification)
      break
    default:
      console.log('Notification clicked:', notification)
  }
}

const generateId = (): string => {
  return Date.now().toString() + Math.random().toString(36).substr(2, 9)
}

// Expose methods for external use
defineExpose({
  addNotification,
  removeNotification
})
</script>

<style scoped>
.notification-manager {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  pointer-events: none;
}

.notification-manager > * {
  pointer-events: auto;
  margin-bottom: 10px;
}
</style>