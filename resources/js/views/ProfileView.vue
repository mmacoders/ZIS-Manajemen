<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Profil Pengguna</h1>
      <p class="text-gray-600">Kelola informasi profil Anda</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="p-6">
        <div class="flex items-center mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
            <span class="text-white text-xl font-semibold">
              {{ authStore.user?.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div class="ml-4">
            <h2 class="text-lg font-medium text-gray-900">{{ authStore.user?.name }}</h2>
            <p class="text-gray-500">{{ authStore.user?.email }}</p>
            <p class="text-sm text-gray-500 capitalize">{{ authStore.user?.role?.name }}</p>
          </div>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          <div>
            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Saat Ini</label>
            <input
              id="currentPassword"
              v-model="form.currentPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
            <input
              id="newPassword"
              v-model="form.newPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3">
            <div class="text-sm text-red-600">{{ error }}</div>
          </div>

          <div v-if="success" class="bg-green-50 border border-green-200 rounded-md p-3">
            <div class="text-sm text-green-600">{{ success }}</div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="isLoading"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
              </span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const isLoading = ref(false)
const error = ref('')
const success = ref('')

// Initialize form with user data
onMounted(() => {
  if (authStore.user) {
    form.value.name = authStore.user.name || ''
    form.value.email = authStore.user.email || ''
  }
})

const updateProfile = async () => {
  isLoading.value = true
  error.value = ''
  success.value = ''

  try {
    // Validate password confirmation
    if (form.value.newPassword && form.value.newPassword !== form.value.confirmPassword) {
      throw new Error('Konfirmasi kata sandi tidak cocok')
    }

    // In a real implementation, you would call an API to update the user profile
    // For now, we'll just simulate the update
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Update the user data in the store
    if (authStore.user) {
      authStore.user.name = form.value.name
      authStore.user.email = form.value.email
    }
    
    success.value = 'Profil berhasil diperbarui'
  } catch (err: any) {
    error.value = err.message || 'Gagal memperbarui profil'
  } finally {
    isLoading.value = false
  }
}
</script>