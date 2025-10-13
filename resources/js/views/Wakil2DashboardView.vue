<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatters'
import { Chart } from 'highcharts-vue'

// Define the highcharts component for local use
const HighchartsComponent = Chart

interface Wakil2Data {
  total_mustahiq: number
  monthly_distribution: Array<{
    month: string
    total: number
  }>
  distribution_target: number
  distribution_realization: number
  top_programs: Array<{
    nama: string
    total_distribution: number
  }>
  distribution_by_category: Array<{
    kategori: string
    total: number
  }>
}

const page = usePage()
const wakilData = ref<Wakil2Data | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

// Mock data for testing
const mockData: Wakil2Data = {
  total_mustahiq: 856,
  monthly_distribution: [
    { month: 'Jan', total: 120000000 },
    { month: 'Feb', total: 150000000 },
    { month: 'Mar', total: 180000000 },
    { month: 'Apr', total: 160000000 },
    { month: 'May', total: 200000000 },
    { month: 'Jun', total: 170000000 },
    { month: 'Jul', total: 220000000 },
    { month: 'Aug', total: 190000000 },
    { month: 'Sep', total: 230000000 },
    { month: 'Oct', total: 250000000 },
    { month: 'Nov', total: 270000000 },
    { month: 'Dec', total: 300000000 }
  ],
  distribution_target: 2500000000,
  distribution_realization: 2350000000,
  top_programs: [
    { nama: 'Program Pemberdayaan Ekonomi', total_distribution: 800000000 },
    { nama: 'Bantuan Kemanusiaan', total_distribution: 650000000 },
    { nama: 'Pendidikan dan Beasiswa', total_distribution: 450000000 },
    { nama: 'Kesehatan Masyarakat', total_distribution: 300000000 },
    { nama: 'Infrastruktur Dasar', total_distribution: 150000000 }
  ],
  distribution_by_category: [
    { kategori: 'Fakir', total: 500000000 },
    { kategori: 'Miskin', total: 450000000 },
    { kategori: 'Amil', total: 100000000 },
    { kategori: 'Muallaf', total: 200000000 },
    { kategori: 'Riqab', total: 150000000 },
    { kategori: 'Gharimin', total: 300000000 },
    { kategori: 'Sabilillah', total: 400000000 },
    { kategori: 'Ibnusabil', total: 250000000 }
  ]
}

// Chart configurations
const distributionTrendOptions = ref({
  chart: {
    type: 'column',
    height: 300
  },
  title: {
    text: 'Tren Distribusi Bulanan',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: mockData.monthly_distribution.map(item => item.month),
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
  series: [{
    name: 'Distribusi',
    data: mockData.monthly_distribution.map(item => item.total),
    color: '#10B981'
  }],
  credits: {
    enabled: false
  },
  legend: {
    enabled: false
  }
})

const distributionByCategoryOptions = ref({
  chart: {
    type: 'pie',
    height: 300
  },
  title: {
    text: 'Komposisi Distribusi per Kategori',
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
    data: mockData.distribution_by_category.map(item => ({
      name: item.kategori,
      y: item.total
    })),
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#8B5CF6', '#EC4899', '#6366F1']
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
    console.error('Error fetching wakil 2 data:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Calculate realization percentage
const realizationPercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.distribution_realization / wakilData.value.distribution_target) * 100
})
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Mustahiq Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Total Mustahiq</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ wakilData?.total_mustahiq.toLocaleString('id-ID') || '0' }}</p>
          </div>
        </div>
      </div>

      <!-- Distribution Target Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Target Distribusi</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.distribution_target || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Distribution Realization Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Realisasi Distribusi</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.distribution_realization || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Realization Percentage Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Persentase Realisasi</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ realizationPercentage.toFixed(1) }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Distribution Trend Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="distributionTrendOptions"
          class="w-full"
        />
      </div>

      <!-- Distribution by Category Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="distributionByCategoryOptions"
          class="w-full"
        />
      </div>
    </div>

    <!-- Top Programs Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Program Teratas</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama Program
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total Distribusi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(program, index) in wakilData?.top_programs" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ program.nama }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(program.total_distribution) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>