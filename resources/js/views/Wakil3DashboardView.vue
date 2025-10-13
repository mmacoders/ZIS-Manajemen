<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatters'
import { Chart } from 'highcharts-vue'

// Define the highcharts component for local use
const HighchartsComponent = Chart

interface Wakil3Data {
  total_budget: number
  budget_absorption: number
  budget_deviation: number
  report_status: {
    submitted: number
    pending: number
    approved: number
    rejected: number
  }
  monthly_absorption: Array<{
    month: string
    planned: number
    realized: number
  }>
  top_expenditures: Array<{
    category: string
    amount: number
  }>
}

const page = usePage()
const wakilData = ref<Wakil3Data | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

// Mock data for testing
const mockData: Wakil3Data = {
  total_budget: 5000000000,
  budget_absorption: 4250000000,
  budget_deviation: -750000000,
  report_status: {
    submitted: 24,
    pending: 3,
    approved: 18,
    rejected: 3
  },
  monthly_absorption: [
    { month: 'Jan', planned: 400000000, realized: 380000000 },
    { month: 'Feb', planned: 400000000, realized: 420000000 },
    { month: 'Mar', planned: 450000000, realized: 430000000 },
    { month: 'Apr', planned: 400000000, realized: 390000000 },
    { month: 'May', planned: 450000000, realized: 460000000 },
    { month: 'Jun', planned: 400000000, realized: 380000000 },
    { month: 'Jul', planned: 450000000, realized: 440000000 },
    { month: 'Aug', planned: 400000000, realized: 410000000 },
    { month: 'Sep', planned: 450000000, realized: 430000000 },
    { month: 'Oct', planned: 400000000, realized: 390000000 },
    { month: 'Nov', planned: 450000000, realized: 420000000 },
    { month: 'Dec', planned: 400000000, realized: 380000000 }
  ],
  top_expenditures: [
    { category: 'Operasional Kantor', amount: 1200000000 },
    { category: 'Program Pemberdayaan', amount: 1000000000 },
    { category: 'Infrastruktur', amount: 800000000 },
    { category: 'SDM dan Pelatihan', amount: 600000000 },
    { category: 'Administrasi dan Laporan', amount: 450000000 }
  ]
}

// Chart configurations
const budgetAbsorptionOptions = ref({
  chart: {
    type: 'column',
    height: 300
  },
  title: {
    text: 'Penyerapan Anggaran Bulanan',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: mockData.monthly_absorption.map(item => item.month),
    title: {
      text: null
    }
  },
  yAxis: {
    title: {
      text: 'Jumlah (Rp)',
      style: {
        fontSize: '12px'
      }
    },
    labels: {
      formatter: function() {
        // @ts-ignore
        return 'Rp' + this.value.toLocaleString('id-ID')
      }
    }
  },
  tooltip: {
    formatter: function() {
      // @ts-ignore
      return `<b>${this.x}</b><br/>${this.series.name}: Rp${this.y.toLocaleString('id-ID')}`
    }
  },
  series: [
    {
      name: 'Rencana',
      data: mockData.monthly_absorption.map(item => item.planned),
      color: '#3B82F6'
    },
    {
      name: 'Realisasi',
      data: mockData.monthly_absorption.map(item => item.realized),
      color: '#10B981'
    }
  ],
  credits: {
    enabled: false
  },
  legend: {
    enabled: true
  }
})

const expenditureByCategoryOptions = ref({
  chart: {
    type: 'pie',
    height: 300
  },
  title: {
    text: 'Komposisi Pengeluaran',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  tooltip: {
    pointFormatter: function() {
      // @ts-ignore
      return `<b>${this.name}</b>: Rp${this.y.toLocaleString('id-ID')} (${this.percentage.toFixed(1)}%)`
    }
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
      }
    }
  },
  series: [{
    name: 'Jumlah',
    data: mockData.top_expenditures.map(item => ({
      name: item.category,
      y: item.amount
    })),
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#06B6D4']
  }],
  credits: {
    enabled: false
  }
})

const fetchData = async () => {
  try {
    loading.value = true
    // In a real implementation, this would fetch data from an API
    // For now, we'll use mock data
    wakilData.value = mockData
  } catch (err) {
    error.value = 'Gagal memuat data dashboard'
    console.error('Error fetching wakil 3 data:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Calculate absorption percentage
const absorptionPercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.budget_absorption / wakilData.value.total_budget) * 100
})

// Calculate deviation percentage
const deviationPercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.budget_deviation / wakilData.value.total_budget) * 100
})
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Budget Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Total Anggaran</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.total_budget || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Budget Absorption Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Penyerapan Anggaran</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.budget_absorption || 0) }}</p>
            <p class="text-sm text-gray-500">{{ absorptionPercentage.toFixed(1) }}% dari total anggaran</p>
          </div>
        </div>
      </div>

      <!-- Budget Deviation Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Deviasi Anggaran</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.budget_deviation || 0) }}</p>
            <p class="text-sm text-gray-500">{{ deviationPercentage.toFixed(1) }}% dari total anggaran</p>
          </div>
        </div>
      </div>

      <!-- Report Status Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Status Laporan</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ wakilData?.report_status.approved }}/{{ wakilData?.report_status.submitted }}</p>
            <p class="text-sm text-gray-500">Disetujui/Diajukan</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Budget Absorption Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="budgetAbsorptionOptions"
          class="w-full"
        />
      </div>

      <!-- Expenditure by Category Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="expenditureByCategoryOptions"
          class="w-full"
        />
      </div>
    </div>

    <!-- Report Status Details -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500">Diajukan</p>
            <p class="text-xl font-semibold text-gray-900">{{ wakilData?.report_status.submitted || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500">Menunggu</p>
            <p class="text-xl font-semibold text-gray-900">{{ wakilData?.report_status.pending || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500">Disetujui</p>
            <p class="text-xl font-semibold text-gray-900">{{ wakilData?.report_status.approved || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-red-100 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-500">Ditolak</p>
            <p class="text-xl font-semibold text-gray-900">{{ wakilData?.report_status.rejected || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Expenditures Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengeluaran Terbesar</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kategori
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(expenditure, index) in wakilData?.top_expenditures" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ expenditure.category }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(expenditure.amount) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>