<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard Wakil Bidang III - Keuangan</h1>
      <p class="text-gray-600">Fokus: Serapan Anggaran, Deviasi, dan Status Laporan</p>
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
          title="Total Anggaran"
          :value="formatCurrency(dashboardData.summary.total_budget || 0)"
          description="Anggaran tersedia"
          icon="BanknotesIcon"
          color="blue"
        />
        <StatCard
          title="Total Penerimaan"
          :value="formatCurrency(dashboardData.summary.total_receipts || 0)"
          description="Dana masuk"
          icon="ArrowDownCircleIcon"
          color="green"
        />
        <StatCard
          title="Total Penyaluran"
          :value="formatCurrency(dashboardData.summary.total_distributions || 0)"
          description="Dana tersalurkan"
          icon="ArrowUpCircleIcon"
          color="red"
        />
        <StatCard
          title="Utilisasi Anggaran"
          :value="dashboardData.summary.budget_utilization + '%'"
          description="Tingkat penyerapan"
          icon="ChartBarIcon"
          color="yellow"
        />
      </div>

      <!-- Budget Analysis -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Analisis Anggaran Tahunan</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-600">Total Anggaran</span>
              <span class="font-medium">{{ formatCurrency(dashboardData.budget_analysis.annual_summary.total_budget) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Realisasi</span>
              <span class="font-medium">{{ formatCurrency(dashboardData.budget_analysis.annual_summary.total_realization) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tingkat Penyerapan</span>
              <span class="font-medium" :class="getUtilizationClass(dashboardData.budget_analysis.annual_summary.absorption_rate)">
                {{ dashboardData.budget_analysis.annual_summary.absorption_rate }}%
              </span>
            </div>
            <div class="pt-2">
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full" 
                  :class="getUtilizationBarClass(dashboardData.budget_analysis.annual_summary.absorption_rate)"
                  :style="{ width: dashboardData.budget_analysis.annual_summary.absorption_rate + '%' }">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Laporan SPJ</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-600">Total SPJ</span>
              <span class="font-medium">{{ dashboardData.reports_status.total_spj }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Disetujui</span>
              <span class="font-medium text-green-600">{{ dashboardData.reports_status.approved_spj }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Pending</span>
              <span class="font-medium text-yellow-600">{{ dashboardData.reports_status.pending_spj }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Ditolak</span>
              <span class="font-medium text-red-600">{{ dashboardData.reports_status.rejected_spj }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Dikirim</span>
              <span class="font-medium text-blue-600">{{ dashboardData.reports_status.submitted_spj }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Absorption Chart -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Penyerapan Anggaran Bulanan</h3>
        <LineChart 
          :chart-data="monthlyAbsorptionChartData" 
          :chart-options="lineChartOptions" 
        />
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
                <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-blue-600" />
              </div>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
              <p class="text-sm text-gray-500">{{ formatCurrency(activity.amount) }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(activity.date) }}</p>
            </div>
          </div>
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
  BanknotesIcon, 
  ArrowDownCircleIcon, 
  ArrowUpCircleIcon, 
  ChartBarIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ClipboardDocumentListIcon
} from '@heroicons/vue/24/solid'
import StatCard from '@/components/dashboard/StatCard.vue'
import LineChart from '@/components/charts/LineChart.vue'

const dashboardData = ref<any>(null)
const isLoading = ref(false)

const fetchDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/wakil3/dashboard')
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

const getUtilizationClass = (rate: number) => {
  if (rate >= 80) return 'text-green-600'
  if (rate >= 60) return 'text-yellow-600'
  return 'text-red-600'
}

const getUtilizationBarClass = (rate: number) => {
  if (rate >= 80) return 'bg-green-600'
  if (rate >= 60) return 'bg-yellow-600'
  return 'bg-red-600'
}

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'rkat':
      return ClipboardDocumentListIcon
    case 'fund_receipt':
      return ArrowDownCircleIcon
    case 'spj':
      return DocumentTextIcon
    default:
      return CurrencyDollarIcon
  }
}

// Computed properties for chart data
const monthlyAbsorptionChartData = computed(() => {
  if (!dashboardData.value?.budget_analysis?.monthly_absorption) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.budget_analysis.monthly_absorption
  const labels = data.map((item: any) => {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    return monthNames[item.month - 1]
  })
  
  const budgetValues = data.map((item: any) => item.budget)
  const realizationValues = data.map((item: any) => item.realization)
  
  return {
    labels,
    datasets: [
      {
        label: 'Anggaran (IDR)',
        data: budgetValues,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Realisasi (IDR)',
        data: realizationValues,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

// Chart options
const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const
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

onMounted(() => {
  fetchDashboardData()
})
</script>