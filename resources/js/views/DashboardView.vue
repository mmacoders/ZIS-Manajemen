<template>
  <div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Selamat Datang {{ authStore && authStore.user ? authStore.user.name : 'Pengguna' }}</h1>
        <!-- Tanggal Sekarang -->
        <p class="text-sm lg:text-sm text-gray-600">
          {{ currentDate }}
        </p>
      </div>
      
      <!-- User Profile and Notification Section -->
      
    </div>

    <!-- Loading State -->
    <div v-if="dashboardStore.isLoading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardStore.data">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          v-for="(card, index) in statCards"
          :key="index"
          :title="card.title"
          :value="card.value"
          :description="card.description"
          :icon="card.icon"
          :color="card.color"
        />
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
                <p class="font-medium text-gray-900 text-sm lg:text-base">{{ transaction.muzakki?.nama }}</p>
                <p class="text-sm lg:text-base text-gray-600">{{ transaction.jenis_zis }} - {{ formatDate(transaction.tanggal_transaksi) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-green-600 text-sm lg:text-base">{{ formatCurrency(transaction.jumlah) }}</p>
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
                <p class="font-medium text-gray-900 text-sm lg:text-base">{{ distribution.mustahiq?.nama }}</p>
                <p class="text-sm lg:text-base text-gray-600">{{ distribution.program?.nama }} - {{ formatDate(distribution.tanggal_distribusi) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-blue-600 text-sm lg:text-base">{{ formatCurrency(distribution.jumlah) }}</p>
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
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard.ts'
import { 
  CurrencyDollarIcon, 
  ArrowPathIcon, 
  UserGroupIcon, 
  UsersIcon,
  BellIcon
} from '@heroicons/vue/24/solid'
import DashboardCharts from '@/components/charts/DashboardCharts.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import { BreadcrumbItem } from '@/types'

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const showNotifications = ref(false)

// waktu sekarang (format: Tanggal Bulan, Tahun)
const currentDate = ref(
  new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
)

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
  
  // Add debugging to see what data is being fetched
  dashboardStore.$subscribe((mutation, state) => {
    console.log('Dashboard store updated:', state)
    if (state.data?.charts) {
      console.log('Chart data received:', state.data.charts)
    }
  })
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

// Stat cards data computed from dashboard store
const statCards = computed(() => {
  if (!dashboardStore.data?.summary) return []
  
  const summary = dashboardStore.data.summary
  
  return [
    {
      title: 'Total ZIS',
      value: formatCurrency(summary.total_zis_collected || 0),
      description: 'Zis Terkumpul',
      icon: CurrencyDollarIcon,
      color: 'blue' as const
    },
    {
      title: 'Total Terdistribusi',
      value: formatCurrency(summary.total_distributed || 0),
      description: 'Dana Distribusi',
      icon: ArrowPathIcon,
      color: 'green' as const
    },
    {
      title: 'Total Muzakki',
      value: summary.total_muzakki?.toLocaleString() || '0',
      description: 'Donatur terdaftar',
      icon: UserGroupIcon,
      color: 'yellow' as const
    },
    {
      title: 'Total Mustahiq',
      value: summary.total_mustahiq?.toLocaleString() || '0',
      description: 'Penerima manfaat',
      icon: UsersIcon,
      color: 'red' as const
    }
  ]
})
</script>