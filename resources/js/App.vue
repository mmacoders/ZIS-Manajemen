<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'
import NotificationManager from '@/components/NotificationManager.vue'
import { onMounted } from 'vue'

const authStore = useAuthStore()

onMounted(() => {
  if (authStore.token && !authStore.user) {
    authStore.getMe()
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar v-if="authStore.isAuthenticated" />
    <main :class="authStore.isAuthenticated ? 'ml-64' : ''">
      <RouterView />
    </main>
    
    <!-- Real-time Notifications -->
    <NotificationManager v-if="authStore.isAuthenticated" />
  </div>
</template>