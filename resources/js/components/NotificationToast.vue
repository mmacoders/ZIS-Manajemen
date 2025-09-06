<template>
  <div v-if="isVisible" class="notification-container">
    <div 
      :class="notificationClasses"
      class="notification-card"
      @click="handleClick"
    >
      <div class="flex items-start">
        <div class="notification-icon">
          <Bell v-if="notification.type === 'new_transaction'" class="w-5 h-5" />
          <Send v-else-if="notification.type === 'distribution_completed'" class="w-5 h-5" />
          <Info v-else class="w-5 h-5" />
        </div>
        
        <div class="flex-1 ml-3">
          <p class="notification-title">{{ getTitle() }}</p>
          <p class="notification-message">{{ notification.message }}</p>
          <p class="notification-time">{{ formatTime(notification.timestamp) }}</p>
        </div>
        
        <button 
          @click.stop="dismiss"
          class="notification-close"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Bell, Send, Info, X } from 'lucide-vue-next'

interface Props {
  notification: {
    id?: string
    type: string
    message: string
    timestamp: string
    [key: string]: any
  }
  autoClose?: boolean
  duration?: number
}

const props = withDefaults(defineProps<Props>(), {
  autoClose: true,
  duration: 5000
})

const emit = defineEmits<{
  close: [id: string]
  click: [notification: any]
}>()

const isVisible = ref(true)

const notificationClasses = computed(() => {
  const baseClasses = 'notification-card transition-all duration-300 ease-in-out transform'
  
  switch (props.notification.type) {
    case 'new_transaction':
      return `${baseClasses} notification-success`
    case 'distribution_completed':
      return `${baseClasses} notification-info`
    default:
      return `${baseClasses} notification-default`
  }
})

onMounted(() => {
  if (props.autoClose) {
    setTimeout(() => {
      dismiss()
    }, props.duration)
  }
})

const getTitle = () => {
  switch (props.notification.type) {
    case 'new_transaction':
      return 'Transaksi ZIS Baru'
    case 'distribution_completed':
      return 'Distribusi Selesai'
    default:
      return 'Notifikasi'
  }
}

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  
  if (diff < 60000) {
    return 'Baru saja'
  } else if (diff < 3600000) {
    return `${Math.floor(diff / 60000)} menit yang lalu`
  } else if (diff < 86400000) {
    return `${Math.floor(diff / 3600000)} jam yang lalu`
  } else {
    return date.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const dismiss = () => {
  isVisible.value = false
  setTimeout(() => {
    emit('close', props.notification.id || Date.now().toString())
  }, 300)
}

const handleClick = () => {
  emit('click', props.notification)
}
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  max-width: 400px;
  min-width: 300px;
}

.notification-card {
  @apply bg-white border border-gray-200 rounded-lg shadow-lg p-4 cursor-pointer;
  animation: slideIn 0.3s ease-out;
}

.notification-success {
  @apply border-l-4 border-l-green-500;
}

.notification-info {
  @apply border-l-4 border-l-blue-500;
}

.notification-default {
  @apply border-l-4 border-l-gray-500;
}

.notification-icon {
  @apply flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full;
}

.notification-success .notification-icon {
  @apply bg-green-100 text-green-600;
}

.notification-info .notification-icon {
  @apply bg-blue-100 text-blue-600;
}

.notification-default .notification-icon {
  @apply bg-gray-100 text-gray-600;
}

.notification-title {
  @apply font-semibold text-gray-900 text-sm;
}

.notification-message {
  @apply text-gray-700 text-sm mt-1;
}

.notification-time {
  @apply text-gray-500 text-xs mt-1;
}

.notification-close {
  @apply flex-shrink-0 ml-2 text-gray-400 hover:text-gray-600 transition-colors;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>