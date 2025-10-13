<template>
  <nav class="sticky top-0 w-full h-16 bg-white shadow-sm z-40 flex items-center justify-between px-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center space-x-2">
      <!-- Tombol Close Sidebar -->
      <button 
        @click="toggleSidebar"
        class="p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
      >
        <PanelRightOpen v-if="!isCollapsed" class="w-5 h-5" />
        <PanelRightClose v-else class="w-5 h-5" />
      </button>

      <Breadcrumbs />
    </div>

    <!-- Notifications and User Info -->
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <div class="relative">
        <button 
          @click="toggleNotifications"
          class="p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
        >
          <Bell class="w-5 h-5" />
          <span 
            v-if="unreadNotifications > 0"
            class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
          >
            {{ unreadNotifications }}
          </span>
        </button>
      </div>

      <!-- User Role and Info -->
      <div 
        class="relative profile-dropdown"
        @mouseenter="showProfileDropdown"
        @mouseleave="hideProfileDropdown"
      >
        <div class="flex items-center cursor-pointer">
          <div class="text-right mr-3 hidden md:block">
            <p class="text-sm font-medium text-gray-900">{{ authStore.user && authStore.user.name ? authStore.user.name : 'Pengguna' }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ authStore.user && authStore.user.role && authStore.user.role.name ? authStore.user.role.name : 'Peran' }}</p>
          </div>
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-semibold">
              {{ authStore.user && authStore.user.name ? authStore.user.name.charAt(0).toUpperCase() : 'U' }}
            </span>
          </div>
        </div>

        <!-- Profile Dropdown Menu -->
        <div 
          v-if="isProfileDropdownOpen"
          class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 transition-all duration-300 ease-in-out transform origin-top-right"
        >
          <button 
            @click="goToProfile"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            <User class="w-4 h-4 mr-2" />
            Profil
          </button>
          <button 
            @click="goToSettings"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            <Settings class="w-4 h-4 mr-2" />
            Pengaturan
          </button>
          <button 
            @click="logout"
            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
          >
            <LogOut class="w-4 h-4 mr-2" />
            Keluar
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { PanelRightOpen, PanelRightClose, Bell, Settings, User, LogOut } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const props = defineProps<{
  isCollapsed?: boolean
}>()

const authStore = useAuthStore()
const router = useRouter()

// Define emit for sidebar toggle
const emit = defineEmits(['toggle-sidebar'])

// Profile dropdown state
const isProfileDropdownOpen = ref(false)
let dropdownTimeout: number | null = null

const toggleSidebar = () => {
  emit('toggle-sidebar')
}

// Show profile dropdown with delay
const showProfileDropdown = () => {
  if (dropdownTimeout) {
    clearTimeout(dropdownTimeout)
    dropdownTimeout = null
  }
  isProfileDropdownOpen.value = true
}

// Hide profile dropdown with delay
const hideProfileDropdown = () => {
  dropdownTimeout = window.setTimeout(() => {
    isProfileDropdownOpen.value = false
    dropdownTimeout = null
  }, 300) // 300ms delay
}

// Profile settings
const goToProfile = () => {
  hideProfileDropdown()
  router.push('/profile')
}

// Go to settings
const goToSettings = () => {
  hideProfileDropdown()
  router.push('/settings')
}

// Logout
const logout = async () => {
  hideProfileDropdown()
  await authStore.logout()
  router.push('/login')
}

// Mock data for notifications - in a real app this would come from an API
const unreadNotifications = ref(3)

const toggleNotifications = () => {
  // In a real implementation, this would open a notification panel
  console.log('Toggle notifications')
}
</script>