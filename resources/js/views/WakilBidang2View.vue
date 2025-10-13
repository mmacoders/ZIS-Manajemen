<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard Wakil Bidang II - Distribusi</h1>
      <p class="text-gray-600">Fokus: Mustahiq, Penyaluran, dan Outcome Program</p>
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
          title="Total Mustahiq"
          :value="dashboardData.summary.total_mustahiq?.toLocaleString() || '0'"
          description="Penerima manfaat"
          icon="UsersIcon"
          color="red"
        />
        <StatCard
          title="Total Program"
          :value="dashboardData.summary.total_programs?.toLocaleString() || '0'"
          description="Program aktif"
          icon="ClipboardDocumentListIcon"
          color="blue"
        />
        <StatCard
          title="Total Terdistribusi"
          :value="formatCurrency(dashboardData.summary.total_distributed || 0)"
          description="Dana tersalurkan"
          icon="ArrowPathIcon"
          color="green"
        />
        <StatCard
          title="Distribusi Pending"
          :value="dashboardData.summary.pending_distributions?.toLocaleString() || '0'"
          description="Menunggu penyaluran"
          icon="ClockIcon"
          color="yellow"
        />
      </div>

      <!-- Program Outcomes -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Outcome Program</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distribusi</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="program in dashboardData.program_outcomes" :key="program.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ program.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ program.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ program.distributions_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(program.target_amount) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(program.total_distributed) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full" 
                        :style="{ width: program.completion_percentage + '%' }">
                      </div>
                    </div>
                    <span class="text-sm text-gray-500">{{ program.completion_percentage }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Penyaluran (12 Bulan)</h3>
          <BarChart 
            :chart-data="monthlyDistributionChartData" 
            :chart-options="barChartOptions" 
          />
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi per Program</h3>
          <DoughnutChart 
            :chart-data="distributionByProgramChartData" 
            :chart-options="doughnutChartOptions" 
          />
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
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                <ArrowPathIcon class="w-4 h-4 text-green-600" />
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
  UsersIcon, 
  ClipboardDocumentListIcon, 
  ArrowPathIcon, 
  ClockIcon
} from '@heroicons/vue/24/solid'
import StatCard from '@/components/dashboard/StatCard.vue'
import BarChart from '@/components/charts/BarChart.vue'
import DoughnutChart from '@/components/charts/DoughnutChart.vue'

const dashboardData = ref<any>(null)
const isLoading = ref(false)

const fetchDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/wakil2/dashboard')
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
    case 'completed':
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
const monthlyDistributionChartData = computed(() => {
  if (!dashboardData.value?.charts?.monthly_distribution) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.charts.monthly_distribution
  const labels = data.map((item: any) => {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    return `${monthNames[item.month - 1]} ${item.year}`
  })
  
  const values = data.map((item: any) => item.total)
  
  return {
    labels,
    datasets: [
      {
        label: 'Penyaluran (IDR)',
        data: values,
        backgroundColor: '#10b981'
      }
    ]
  }
})

const distributionByProgramChartData = computed(() => {
  if (!dashboardData.value?.charts?.distribution_by_program) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.charts.distribution_by_program
  const labels = data.map((item: any) => item.program_name)
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

// Chart options
const barChartOptions = {
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