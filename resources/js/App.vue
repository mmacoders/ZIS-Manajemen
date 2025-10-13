<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Sidebar from '@/components/Sidebar.vue'
import Navbar from '@/components/Navbar.vue'
import NotificationManager from '@/components/NotificationManager.vue'

const authStore = useAuthStore()

// Track sidebar collapsed state
const isSidebarCollapsed = ref(false)
const sidebarRef = ref<InstanceType<typeof Sidebar> | null>(null)

// Update sidebar state when it changes
const onSidebarCollapse = (collapsed: boolean) => {
  isSidebarCollapsed.value = collapsed
}

// Toggle sidebar from navbar
const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value
}

onMounted(() => {
  if (authStore.token && !authStore.user) {
    authStore.getMe()
  }
})

// Compute the main content margin based on sidebar state
const mainContentClass = computed(() => {
  const classes = []
  if (authStore.isAuthenticated) {
    // Adjust left margin based on sidebar state
    classes.push(isSidebarCollapsed.value ? 'ml-20' : 'ml-64') // 20 for collapsed, 64 for expanded
  }
  return classes.join(' ')
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Sidebar 
      v-if="authStore.isAuthenticated" 
      ref="sidebarRef"
      :collapsed="isSidebarCollapsed"
      @update:collapsed="onSidebarCollapse"
    />
    <main :class="mainContentClass">
      <Navbar 
        v-if="authStore.isAuthenticated" 
        :is-collapsed="isSidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
      />
      <RouterView />
    </main>
    
    <!-- Real-time Notifications -->
    <NotificationManager v-if="authStore.isAuthenticated" />
  </div>
</template>
