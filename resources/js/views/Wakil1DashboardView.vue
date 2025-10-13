<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatters'
import { Chart } from 'highcharts-vue'

// Define the highcharts component for local use
const HighchartsComponent = Chart

interface Wakil1Data {
  total_muzakki: number
  monthly_collection: Array<{
    month: string
    total: number
  }>
  collection_target: number
  collection_realization: number
  top_muzakki: Array<{
    nama: string
    total_contribution: number
  }>
  collection_by_type: Array<{
    jenis_zis: string
    total: number
  }>
}

const page = usePage()
const wakilData = ref<Wakil1Data | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

// Mock data for testing
const mockData: Wakil1Data = {
  total_muzakki: 1247,
  monthly_collection: [
    { month: 'Jan', total: 150000000 },
    { month: 'Feb', total: 180000000 },
    { month: 'Mar', total: 220000000 },
    { month: 'Apr', total: 190000000 },
    { month: 'May', total: 250000000 },
    { month: 'Jun', total: 210000000 },
    { month: 'Jul', total: 270000000 },
    { month: 'Aug', total: 240000000 },
    { month: 'Sep', total: 280000000 },
    { month: 'Oct', total: 300000000 },
    { month: 'Nov', total: 320000000 },
    { month: 'Dec', total: 350000000 }
  ],
  collection_target: 3000000000,
  collection_realization: 2850000000,
  top_muzakki: [
    { nama: 'Ahmad Santoso', total_contribution: 50000000 },
    { nama: 'Budi Prasetyo', total_contribution: 42000000 },
    { nama: 'Citra Dewi', total_contribution: 38000000 },
    { nama: 'Dedi Kurniawan', total_contribution: 35000000 },
    { nama: 'Eka Putri', total_contribution: 32000000 },
    { nama: 'Fajar Nugroho', total_contribution: 29000000 },
    { nama: 'Gina Marlina', total_contribution: 27000000 },
    { nama: 'Hadi Susanto', total_contribution: 25000000 },
    { nama: 'Indah Permata', total_contribution: 23000000 },
    { nama: 'Joko Widodo', total_contribution: 20000000 }
  ],
  collection_by_type: [
    { jenis_zis: 'Zakat', total: 1500000000 },
    { jenis_zis: 'Infaq', total: 1000000000 },
    { jenis_zis: 'Sedekah', total: 350000000 }
  ]
}

// Chart configurations
const collectionTrendOptions = ref({
  chart: {
    type: 'column',
    height: 300
  },
  title: {
    text: 'Tren Pengumpulan Bulanan',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: mockData.monthly_collection.map(item => item.month),
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
    name: 'Pengumpulan',
    data: mockData.monthly_collection.map(item => item.total),
    color: '#3B82F6'
  }],
  credits: {
    enabled: false
  },
  legend: {
    enabled: false
  }
})

const collectionByTypeOptions = ref({
  chart: {
    type: 'pie',
    height: 300
  },
  title: {
    text: 'Komposisi Pengumpulan',
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
    data: mockData.collection_by_type.map(item => ({
      name: item.jenis_zis,
      y: item.total
    })),
    colors: ['#3B82F6', '#10B981', '#F59E0B']
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
    console.error('Error fetching wakil 1 data:', err)
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
  return (wakilData.value.collection_realization / wakilData.value.collection_target) * 100
})
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Muzakki Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Total Muzakki</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ wakilData?.total_muzakki.toLocaleString('id-ID') || '0' }}</p>
          </div>
        </div>
      </div>

      <!-- Collection Target Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Target Pengumpulan</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.collection_target || 0) }}</p>
          </div>
        </div>
      </div>

      <!-- Collection Realization Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Realisasi Pengumpulan</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(wakilData?.collection_realization || 0) }}</p>
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
      <!-- Collection Trend Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="collectionTrendOptions"
          class="w-full"
        />
      </div>

      <!-- Collection by Type Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="collectionByTypeOptions"
          class="w-full"
        />
      </div>
    </div>

    <!-- Top Muzakki Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Muzakki</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama Muzakki
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total Kontribusi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(muzakki, index) in wakilData?.top_muzakki" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ muzakki.nama }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(muzakki.total_contribution) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>