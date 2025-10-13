<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatters'
import { Chart } from 'highcharts-vue'

// Define the highcharts component for local use
const HighchartsComponent = Chart

interface Wakil4Data {
  sop_compliance: {
    total_sop: number
    compliant: number
    non_compliant: number
    in_progress: number
  }
  hr_status: {
    total_staff: number
    active: number
    on_leave: number
    in_training: number
  }
  asset_status: {
    total_assets: number
    in_use: number
    under_maintenance: number
    retired: number
  }
  monthly_sop_trends: Array<{
    month: string
    compliant: number
    non_compliant: number
  }>
  top_asset_categories: Array<{
    category: string
    count: number
    value: number
  }>
}

const page = usePage()
const wakilData = ref<Wakil4Data | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

// Mock data for testing
const mockData: Wakil4Data = {
  sop_compliance: {
    total_sop: 45,
    compliant: 38,
    non_compliant: 5,
    in_progress: 2
  },
  hr_status: {
    total_staff: 65,
    active: 58,
    on_leave: 4,
    in_training: 3
  },
  asset_status: {
    total_assets: 120,
    in_use: 95,
    under_maintenance: 15,
    retired: 10
  },
  monthly_sop_trends: [
    { month: 'Jan', compliant: 32, non_compliant: 8 },
    { month: 'Feb', compliant: 34, non_compliant: 6 },
    { month: 'Mar', compliant: 35, non_compliant: 5 },
    { month: 'Apr', compliant: 36, non_compliant: 4 },
    { month: 'May', compliant: 37, non_compliant: 3 },
    { month: 'Jun', compliant: 37, non_compliant: 3 },
    { month: 'Jul', compliant: 38, non_compliant: 2 },
    { month: 'Aug', compliant: 38, non_compliant: 2 },
    { month: 'Sep', compliant: 38, non_compliant: 2 },
    { month: 'Oct', compliant: 38, non_compliant: 2 },
    { month: 'Nov', compliant: 38, non_compliant: 2 },
    { month: 'Dec', compliant: 38, non_compliant: 2 }
  ],
  top_asset_categories: [
    { category: 'Kendaraan', count: 15, value: 1500000000 },
    { category: 'Elektronik Kantor', count: 45, value: 800000000 },
    { category: 'Perabot Kantor', count: 30, value: 600000000 },
    { category: 'Peralatan IT', count: 20, value: 1200000000 },
    { category: 'Lainnya', count: 10, value: 300000000 }
  ]
}

// Chart configurations
const sopComplianceOptions = ref({
  chart: {
    type: 'column',
    height: 300
  },
  title: {
    text: 'Tren Kepatuhan SOP Bulanan',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: mockData.monthly_sop_trends.map(item => item.month),
    title: {
      text: null
    }
  },
  yAxis: {
    title: {
      text: 'Jumlah SOP',
      style: {
        fontSize: '12px'
      }
    }
  },
  tooltip: {
    formatter: function() {
      // @ts-ignore
      return `<b>${this.x}</b><br/>${this.series.name}: ${this.y}`
    }
  },
  series: [
    {
      name: 'Patuh',
      data: mockData.monthly_sop_trends.map(item => item.compliant),
      color: '#10B981'
    },
    {
      name: 'Tidak Patuh',
      data: mockData.monthly_sop_trends.map(item => item.non_compliant),
      color: '#EF4444'
    }
  ],
  credits: {
    enabled: false
  },
  legend: {
    enabled: true
  }
})

const assetByCategoryOptions = ref({
  chart: {
    type: 'pie',
    height: 300
  },
  title: {
    text: 'Aset per Kategori',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  tooltip: {
    pointFormatter: function() {
      // @ts-ignore
      return `<b>${this.name}</b>: ${this.y} unit (${this.percentage.toFixed(1)}%)`
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
    data: mockData.top_asset_categories.map(item => ({
      name: item.category,
      y: item.count
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
    console.error('Error fetching wakil 4 data:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Calculate compliance percentage
const compliancePercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.sop_compliance.compliant / wakilData.value.sop_compliance.total_sop) * 100
})

// Calculate active staff percentage
const activeStaffPercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.hr_status.active / wakilData.value.hr_status.total_staff) * 100
})

// Calculate asset utilization percentage
const assetUtilizationPercentage = computed(() => {
  if (!wakilData.value) return 0
  return (wakilData.value.asset_status.in_use / wakilData.value.asset_status.total_assets) * 100
})
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- SOP Compliance Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Kepatuhan SOP</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ compliancePercentage.toFixed(1) }}%</p>
            <p class="text-sm text-gray-500">{{ wakilData?.sop_compliance.compliant }}/{{ wakilData?.sop_compliance.total_sop }} SOP</p>
          </div>
        </div>
      </div>

      <!-- HR Status Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Status SDM</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ activeStaffPercentage.toFixed(1) }}%</p>
            <p class="text-sm text-gray-500">{{ wakilData?.hr_status.active }}/{{ wakilData?.hr_status.total_staff }} aktif</p>
          </div>
        </div>
      </div>

      <!-- Asset Utilization Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Utilisasi Aset</h3>
            <p class="text-2xl font-semibold text-gray-900">{{ assetUtilizationPercentage.toFixed(1) }}%</p>
            <p class="text-sm text-gray-500">{{ wakilData?.asset_status.in_use }}/{{ wakilData?.asset_status.total_assets }} aset</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- SOP Compliance Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="sopComplianceOptions"
          class="w-full"
        />
      </div>

      <!-- Asset by Category Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <HighchartsComponent
          :options="assetByCategoryOptions"
          class="w-full"
        />
      </div>
    </div>

    <!-- Detailed Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- SOP Details -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status SOP</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
              <span class="text-sm text-gray-600">Patuh</span>
            </div>
            <span class="font-medium">{{ wakilData?.sop_compliance.compliant }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div>
              <span class="text-sm text-gray-600">Tidak Patuh</span>
            </div>
            <span class="font-medium">{{ wakilData?.sop_compliance.non_compliant }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
              <span class="text-sm text-gray-600">Dalam Proses</span>
            </div>
            <span class="font-medium">{{ wakilData?.sop_compliance.in_progress }}</span>
          </div>
        </div>
      </div>

      <!-- HR Details -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status SDM</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
              <span class="text-sm text-gray-600">Aktif</span>
            </div>
            <span class="font-medium">{{ wakilData?.hr_status.active }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
              <span class="text-sm text-gray-600">Cuti</span>
            </div>
            <span class="font-medium">{{ wakilData?.hr_status.on_leave }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-purple-500 mr-2"></div>
              <span class="text-sm text-gray-600">Pelatihan</span>
            </div>
            <span class="font-medium">{{ wakilData?.hr_status.in_training }}</span>
          </div>
        </div>
      </div>

      <!-- Asset Details -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Aset</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
              <span class="text-sm text-gray-600">Digunakan</span>
            </div>
            <span class="font-medium">{{ wakilData?.asset_status.in_use }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
              <span class="text-sm text-gray-600">Perbaikan</span>
            </div>
            <span class="font-medium">{{ wakilData?.asset_status.under_maintenance }}</span>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-gray-500 mr-2"></div>
              <span class="text-sm text-gray-600">Dihapus</span>
            </div>
            <span class="font-medium">{{ wakilData?.asset_status.retired }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Asset Categories Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Kategori Aset Terbesar</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kategori
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah Unit
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nilai Total
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(asset, index) in wakilData?.top_asset_categories" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ asset.category }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ asset.count }} unit
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(asset.value) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>