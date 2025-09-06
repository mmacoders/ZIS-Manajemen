<template>
  <div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Selamat datang, {{ authStore.user?.name }}</p>
      </div>
      
      <!-- Notification Bell Icon -->
      <div class="mt-4 sm:mt-0 relative">
        <button 
          @click="showNotifications = !showNotifications"
          class="p-2 rounded-full hover:bg-gray-100 relative"
          :class="{'bg-blue-50': showNotifications}"
        >
          <Bell class="w-6 h-6 text-gray-600" />
          <span 
            v-if="dashboardStore.getNotificationCount() > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ dashboardStore.getNotificationCount() }}
          </span>
        </button>
        
        <!-- Notification Dropdown -->
        <div 
          v-if="showNotifications"
          class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Notifikasi</h3>
          </div>
          
          <div class="max-h-96 overflow-y-auto">
            <!-- Pending Items Summary -->
            <div v-if="dashboardStore.getPendingItems().total > 0" class="p-4 border-b border-gray-100">
              <h4 class="font-medium text-gray-900 mb-2">Perlu Tindakan</h4>
              <div class="space-y-2">
                <div 
                  v-if="dashboardStore.getPendingItems().transactions > 0"
                  class="flex items-center justify-between p-2 bg-yellow-50 rounded"
                >
                  <span class="text-sm text-gray-700">Transaksi menunggu verifikasi</span>
                  <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ dashboardStore.getPendingItems().transactions }}
                  </span>
                </div>
                <div 
                  v-if="dashboardStore.getPendingItems().documents > 0"
                  class="flex items-center justify-between p-2 bg-yellow-50 rounded"
                >
                  <span class="text-sm text-gray-700">Dokumen menunggu proses</span>
                  <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ dashboardStore.getPendingItems().documents }}
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Recent Activities -->
            <div v-if="dashboardStore.getRecentActivities().length > 0">
              <div class="p-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-900">Aktivitas Terbaru</h4>
              </div>
              <div class="divide-y divide-gray-100">
                <div 
                  v-for="activity in dashboardStore.getRecentActivities()"
                  :key="activity.id"
                  class="p-4 hover:bg-gray-50"
                >
                  <p class="text-sm text-gray-900">{{ activity.message }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ activity.created_at }}</p>
                </div>
              </div>
            </div>
            
            <div v-else class="p-4 text-center text-gray-500 text-sm">
              Tidak ada notifikasi
            </div>
          </div>
          
          <div class="p-4 border-t border-gray-200 text-center">
            <button 
              @click="showNotifications = false"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="dashboardStore.isLoading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardStore.data">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total ZIS Terkumpul</p>
              <p class="text-2xl font-bold text-green-600">
                {{ formatCurrency(dashboardStore.data.summary?.total_zis_collected || 0) }}
              </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <DollarSign class="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Terdistribusi</p>
              <p class="text-2xl font-bold text-blue-600">
                {{ formatCurrency(dashboardStore.data.summary?.total_distributed || 0) }}
              </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Send class="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Muzakki</p>
              <p class="text-2xl font-bold text-purple-600">
                {{ dashboardStore.data.summary?.total_muzakki || 0 }}
              </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <Users class="w-6 h-6 text-purple-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Mustahiq</p>
              <p class="text-2xl font-bold text-orange-600">
                {{ dashboardStore.data.summary?.total_mustahiq || 0 }}
              </p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <UserCheck class="w-6 h-6 text-orange-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Transactions -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaksi Terbaru</h3>
          <div class="space-y-3">
            <div
              v-for="transaction in dashboardStore.data.recent_transactions?.slice(0, 5)"
              :key="transaction.id"
              class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0"
            >
              <div>
                <p class="font-medium text-gray-900">{{ transaction.muzakki?.nama }}</p>
                <p class="text-sm text-gray-600">{{ transaction.jenis_zis }} - {{ formatDate(transaction.tanggal_transaksi) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-green-600">{{ formatCurrency(transaction.jumlah) }}</p>
                <span :class="getStatusBadgeClass(transaction.status)">
                  {{ transaction.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Distributions -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Terbaru</h3>
          <div class="space-y-3">
            <div
              v-for="distribution in dashboardStore.data.recent_distributions?.slice(0, 5)"
              :key="distribution.id"
              class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0"
            >
              <div>
                <p class="font-medium text-gray-900">{{ distribution.mustahiq?.nama }}</p>
                <p class="text-sm text-gray-600">{{ distribution.program?.nama }} - {{ formatDate(distribution.tanggal_distribusi) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-blue-600">{{ formatCurrency(distribution.jumlah) }}</p>
                <span :class="getStatusBadgeClass(distribution.status)">
                  {{ distribution.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <DashboardCharts :charts-data="dashboardStore.data.charts" />
    </div>
    
    <!-- Empty State -->
    <div v-else class="flex items-center justify-center py-12">
      <div class="text-center">
        <p class="text-gray-500">Tidak ada data dashboard tersedia</p>
        <button 
          @click="dashboardStore.fetchDashboardData()"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Muat Ulang Data
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import { DollarSign, Send, Users, UserCheck, Bell } from 'lucide-vue-next'
import DashboardCharts from '@/components/charts/DashboardCharts.vue'

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const showNotifications = ref(false)

// Close notifications when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative') && showNotifications.value) {
    showNotifications.value = false
  }
}

onMounted(() => {
  dashboardStore.fetchDashboardData()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusBadgeClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'verified':
    case 'completed':
      return `${baseClass} bg-green-100 text-green-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'rejected':
    case 'cancelled':
      return `${baseClass} bg-red-100 text-red-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}
</script>