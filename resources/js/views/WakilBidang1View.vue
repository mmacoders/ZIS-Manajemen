<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard Wakil Bidang I - Pengumpulan</h1>
      <p class="text-gray-600">Fokus: Muzakki, Target dan Realisasi Penghimpunan</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardData">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          title="Total Muzakki"
          :value="dashboardData.summary.total_muzakki?.toLocaleString() || '0'"
          description="Donatur terdaftar"
          icon="UserGroupIcon"
          color="blue"
        />
        <StatCard
          title="Total UPZ"
          :value="dashboardData.summary.total_upz?.toLocaleString() || '0'"
          description="Unit Pengumpul Zakat"
          icon="BuildingOfficeIcon"
          color="green"
        />
        <StatCard
          title="Total ZIS Terkumpul"
          :value="formatCurrency(dashboardData.summary.total_zis_collected || 0)"
          description="Dana terkumpul"
          icon="CurrencyDollarIcon"
          color="yellow"
        />
        <StatCard
          title="Transaksi Pending"
          :value="dashboardData.summary.pending_transactions?.toLocaleString() || '0'"
          description="Menunggu verifikasi"
          icon="ClockIcon"
          color="red"
        />
      </div>

      <!-- Targets and Realizations -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Target vs Realisasi Penghimpunan</h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Target Penghimpunan</span>
                <span class="text-sm font-medium text-gray-700">{{ formatCurrency(dashboardData.targets.collection_target || 0) }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="bg-blue-600 h-2.5 rounded-full" 
                  :style="{ width: collectionProgress + '%' }">
                </div>
              </div>
              <div class="flex justify-between mt-1">
                <span class="text-xs text-gray-500">Realisasi: {{ formatCurrency(dashboardData.targets.collection_realization || 0) }}</span>
                <span class="text-xs text-gray-500">{{ collectionProgress }}%</span>
              </div>
            </div>
            
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Target Muzakki</span>
                <span class="text-sm font-medium text-gray-700">{{ dashboardData.targets.muzakki_target?.toLocaleString() || '0' }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="bg-green-600 h-2.5 rounded-full" 
                  :style="{ width: muzakkiProgress + '%' }">
                </div>
              </div>
              <div class="flex justify-between mt-1">
                <span class="text-xs text-gray-500">Realisasi: {{ dashboardData.targets.muzakki_realization?.toLocaleString() || '0' }}</span>
                <span class="text-xs text-gray-500">{{ muzakkiProgress }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
          <div class="space-y-3">
            <div
              v-for="activity in dashboardData.recent_activities"
              :key="activity.id"
              class="flex items-start py-2 border-b border-gray-100 last:border-b-0"
            >
              <div class="flex-shrink-0 mt-1">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <CurrencyDollarIcon class="w-4 h-4 text-blue-600" />
                </div>
              </div>
              <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                <p class="text-sm text-gray-500">{{ formatCurrency(activity.amount) }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(activity.date) }}</p>
              </div>
              <span :class="getStatusBadgeClass(activity.status)">
                {{ activity.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Penghimpunan (12 Bulan)</h3>
          <LineChart 
            :chart-data="monthlyCollectionChartData" 
            :chart-options="lineChartOptions" 
          />
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Komposisi ZIS</h3>
          <DoughnutChart 
            :chart-data="zisTypeChartData" 
            :chart-options="doughnutChartOptions" 
          />
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="flex items-center justify-center py-12">
      <div class="text-center">
        <p class="text-gray-500">Tidak ada data dashboard tersedia</p>
        <button 
          @click="fetchDashboardData"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Muat Ulang Data
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { 
  CurrencyDollarIcon, 
  UserGroupIcon, 
  BuildingOfficeIcon, 
  ClockIcon
} from '@heroicons/vue/24/solid'
import StatCard from '@/components/dashboard/StatCard.vue'
import LineChart from '@/components/charts/LineChart.vue'
import DoughnutChart from '@/components/charts/DoughnutChart.vue'

const dashboardData = ref<any>(null)
const isLoading = ref(false)

const fetchDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/wakil1/dashboard')
    dashboardData.value = response.data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    isLoading.value = false
  }
}

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
      return `${baseClass} bg-green-100 text-green-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'rejected':
      return `${baseClass} bg-red-100 text-red-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

// Computed properties for chart data
const monthlyCollectionChartData = computed(() => {
  if (!dashboardData.value?.charts?.monthly_collection) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.charts.monthly_collection
  const labels = data.map((item: any) => {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    return `${monthNames[item.month - 1]} ${item.year}`
  })
  
  const values = data.map((item: any) => item.total)
  
  return {
    labels,
    datasets: [
      {
        label: 'Penghimpunan (IDR)',
        data: values,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

const zisTypeChartData = computed(() => {
  if (!dashboardData.value?.charts?.zis_by_type) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.charts.zis_by_type
  const labels = data.map((item: any) => item.jenis_zis)
  const values = data.map((item: any) => item.total)
  
  return {
    labels,
    datasets: [
      {
        data: values,
        backgroundColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
          '#ef4444',
          '#8b5cf6'
        ]
      }
    ]
  }
})

// Progress calculations
const collectionProgress = computed(() => {
  if (!dashboardData.value?.targets) return 0
  const target = dashboardData.value.targets.collection_target || 0
  const realization = dashboardData.value.targets.collection_realization || 0
  return target > 0 ? Math.round((realization / target) * 100) : 0
})

const muzakkiProgress = computed(() => {
  if (!dashboardData.value?.targets) return 0
  const target = dashboardData.value.targets.muzakki_target || 0
  const realization = dashboardData.value.targets.muzakki_realization || 0
  return target > 0 ? Math.round((realization / target) * 100) : 0
})

// Chart options
const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      ticks: {
        callback: function(value: any) {
          return 'Rp' + (value / 1000000).toFixed(0) + 'jt'
        }
      }
    }
  }
}

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>