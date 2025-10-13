<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Pengaturan</h1>
      <p class="text-gray-600">Kelola pengaturan akun Anda</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="p-6">
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex space-x-8">
            <button
              @click="activeTab = 'account'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'account'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Akun
            </button>
            <button
              @click="activeTab = 'notifications'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'notifications'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Notifikasi
            </button>
            <button
              @click="activeTab = 'appearance'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'appearance'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Tampilan
            </button>
          </nav>
        </div>

        <!-- Account Settings -->
        <div v-if="activeTab === 'account'">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Akun</h2>
          
          <div class="space-y-4">
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.twoFactorAuth"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Aktifkan autentikasi dua faktor</span>
              </label>
            </div>
            
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.emailNotifications"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Aktifkan notifikasi email</span>
              </label>
            </div>
            
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.smsNotifications"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Aktifkan notifikasi SMS</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Notification Settings -->
        <div v-if="activeTab === 'notifications'">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Notifikasi</h2>
          
          <div class="space-y-4">
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.notificationTypes.email"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Notifikasi email untuk transaksi baru</span>
              </label>
            </div>
            
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.notificationTypes.distribution"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Notifikasi untuk distribusi baru</span>
              </label>
            </div>
            
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="settings.notificationTypes.report"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Notifikasi untuk laporan bulanan</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Appearance Settings -->
        <div v-if="activeTab === 'appearance'">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Tampilan</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bahasa</label>
              <select
                v-model="settings.language"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="id">Bahasa Indonesia</option>
                <option value="en">English</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tema</label>
              <select
                v-model="settings.theme"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="light">Terang</option>
                <option value="dark">Gelap</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="saveSettings"
            :disabled="isSaving"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
          >
            <span v-if="isSaving" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Menyimpan...
            </span>
            <span v-else>Simpan Pengaturan</span>
          </button>
        </div>

        <div v-if="saveError" class="mt-4 bg-red-50 border border-red-200 rounded-md p-3">
          <div class="text-sm text-red-600">{{ saveError }}</div>
        </div>

        <div v-if="saveSuccess" class="mt-4 bg-green-50 border border-green-200 rounded-md p-3">
          <div class="text-sm text-green-600">{{ saveSuccess }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const activeTab = ref('account')
const isSaving = ref(false)
const saveError = ref('')
const saveSuccess = ref('')

const settings = ref({
  twoFactorAuth: true,
  emailNotifications: true,
  smsNotifications: false,
  notificationTypes: {
    email: true,
    distribution: true,
    report: false
  },
  language: 'id',
  theme: 'light'
})

const saveSettings = async () => {
  isSaving.value = true
  saveError.value = ''
  saveSuccess.value = ''

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    saveSuccess.value = 'Pengaturan berhasil disimpan'
  } catch (err: any) {
    saveError.value = 'Gagal menyimpan pengaturan'
  } finally {
    isSaving.value = false
  }
}
</script>