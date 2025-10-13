<script setup lang="ts">
// Import Vue functions with type assertions to avoid TypeScript errors
// @ts-ignore
import { ref, watch, onMounted } from 'vue'
import Highcharts from 'highcharts'
// Import the Chart component from highcharts-vue for local registration
import { Chart } from 'highcharts-vue'

interface Props {
  chartsData?: any
}

// Define the highcharts component for local use
const HighchartsComponent = Chart

const props = defineProps<Props>()
const isClient = ref(false)

const chartPeriod = ref('12months')

// Mock data for testing
const mockChartData = {
  monthly_collection: [
    { year: 2024, month: 1, total: 15000000 },
    { year: 2024, month: 2, total: 18000000 },
    { year: 2024, month: 3, total: 22000000 },
    { year: 2024, month: 4, total: 19000000 },
    { year: 2024, month: 5, total: 25000000 },
    { year: 2024, month: 6, total: 21000000 },
    { year: 2024, month: 7, total: 27000000 },
    { year: 2024, month: 8, total: 24000000 },
    { year: 2024, month: 9, total: 28000000 },
    { year: 2024, month: 10, total: 30000000 },
    { year: 2024, month: 11, total: 32000000 },
    { year: 2024, month: 12, total: 35000000 }
  ],
  monthly_distribution: [
    { year: 2024, month: 1, total: 12000000 },
    { year: 2024, month: 2, total: 15000000 },
    { year: 2024, month: 3, total: 18000000 },
    { year: 2024, month: 4, total: 16000000 },
    { year: 2024, month: 5, total: 20000000 },
    { year: 2024, month: 6, total: 17000000 },
    { year: 2024, month: 7, total: 22000000 },
    { year: 2024, month: 8, total: 19000000 },
    { year: 2024, month: 9, total: 23000000 },
    { year: 2024, month: 10, total: 25000000 },
    { year: 2024, month: 11, total: 27000000 },
    { year: 2024, month: 12, total: 30000000 }
  ],
  zis_by_type: [
    { jenis_zis: 'zakat', total: 50000000, count: 120 },
    { jenis_zis: 'infaq', total: 30000000, count: 80 },
    { jenis_zis: 'sedekah', total: 15000000, count: 60 }
  ],
  mustahiq_by_category: [
    { kategori: 'fakir', count: 45 },
    { kategori: 'miskin', count: 38 },
    { kategori: 'amil', count: 12 },
    { kategori: 'muallaf', count: 8 },
    { kategori: 'riqab', count: 5 },
    { kategori: 'gharimin', count: 15 },
    { kategori: 'sabilillah', count: 22 },
    { kategori: 'ibnusabil', count: 10 }
  ],
  top_muzakki: [
    { muzakki: { nama: 'Ahmad Santoso' }, total_contribution: 5000000 },
    { muzakki: { nama: 'Budi Prasetyo' }, total_contribution: 4200000 },
    { muzakki: { nama: 'Citra Dewi' }, total_contribution: 3800000 },
    { muzakki: { nama: 'Dedi Kurniawan' }, total_contribution: 3500000 },
    { muzakki: { nama: 'Eka Putri' }, total_contribution: 3200000 },
    { muzakki: { nama: 'Fajar Nugroho' }, total_contribution: 2900000 },
    { muzakki: { nama: 'Gina Marlina' }, total_contribution: 2700000 },
    { muzakki: { nama: 'Hadi Susanto' }, total_contribution: 2500000 },
    { muzakki: { nama: 'Indah Permata' }, total_contribution: 2300000 },
    { muzakki: { nama: 'Joko Widodo' }, total_contribution: 2000000 }
  ]
}

const setChartPeriod = (period: string) => {
  chartPeriod.value = period
}

// Format data for monthly trends (stacked area chart)
const getMonthlyTrendsData = (dataToUse: any) => {
  const monthlyData = Array.isArray(dataToUse.monthly_collection) 
    ? dataToUse.monthly_collection 
    : []
    
  const distributionData = Array.isArray(dataToUse.monthly_distribution) 
    ? dataToUse.monthly_distribution 
    : []

  // Filter data based on period
  const filteredData = chartPeriod.value === '6months' 
    ? monthlyData.slice(-6) 
    : monthlyData.slice(-12)

  const filteredDistribution = chartPeriod.value === '6months'
    ? distributionData.slice(-6)
    : distributionData.slice(-12)

  // Format for Highcharts
  const categories = filteredData.map((item: any) => {
    const date = new Date(item.year, item.month - 1)
    return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
  })

  const collectionData = filteredData.map((item: any) => item.total || 0)
  const distributionDataFormatted = filteredDistribution.map((item: any) => item.total || 0)

  return {
    categories,
    series: [
      {
        name: 'Pengumpulan ZIS',
        data: collectionData,
        color: '#3B82F6'
      },
      {
        name: 'Distribusi ZIS',
        data: distributionDataFormatted,
        color: '#10B981'
      }
    ]
  }
}

// Format data for ZIS by type (linechart with data tables)
const getZisByTypeData = (dataToUse: any) => {
  const data = Array.isArray(dataToUse.zis_by_type) 
    ? dataToUse.zis_by_type 
    : []
  
  // Sort data by total in descending order
  const sortedData = [...data].sort((a, b) => (b.total || 0) - (a.total || 0))
  
  return {
    categories: sortedData.map((item: any) => (item.jenis_zis || '').toUpperCase()),
    series: [{
      name: 'Jumlah (Rp)',
      data: sortedData.map((item: any) => item.total || 0),
      color: '#3B82F6'
    }],
    tableData: sortedData
  }
}

// Format data for Mustahiq by category (pie chart)
const getMustahiqByCategoryData = (dataToUse: any) => {
  const data = Array.isArray(dataToUse.mustahiq_by_category) 
    ? dataToUse.mustahiq_by_category 
    : []
  
  // Format for pie chart
  const seriesData = data.map((item: any) => ({
    name: item.kategori || 'Unknown',
    y: item.count || 0
  }))
  
  return {
    series: [{
      name: 'Jumlah Mustahiq',
      data: seriesData,
      colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#8B5CF6', '#EC4899', '#6366F1']
    }]
  }
}

// Format data for Top 10 Muzakki (bar chart)
const getTopMuzakkiData = (dataToUse: any) => {
  const data = Array.isArray(dataToUse.top_muzakki) 
    ? dataToUse.top_muzakki 
    : []
  
  // Sort by contribution in descending order
  const sortedData = [...data].sort((a, b) => (b.total_contribution || 0) - (a.total_contribution || 0))
  
  return {
    categories: sortedData.map((item: any) => item.muzakki?.nama || 'Unknown'),
    series: [{
      name: 'Total Kontribusi (Rp)',
      data: sortedData.map((item: any) => item.total_contribution || 0),
      color: '#8B5CF6'
    }]
  }
}

// Chart configurations
const monthlyTrendsChartOptions = ref<Highcharts.Options>({
  chart: {
    type: 'area',
    height: 300
  },
  title: {
    text: 'Tren Bulanan ZIS',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: [],
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
  plotOptions: {
    area: {
      stacking: 'normal',
      lineWidth: 2,
      marker: {
        enabled: true,
        radius: 4
      }
    }
  },
  series: [],
  credits: {
    enabled: false
  },
  legend: {
    enabled: true
  }
})

const zisByTypeChartOptions = ref<Highcharts.Options>({
  chart: {
    type: 'line',
    height: 300
  },
  title: {
    text: 'ZIS per Jenis',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: [],
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
  series: [],
  credits: {
    enabled: false
  },
  legend: {
    enabled: false
  }
})

const mustahiqCategoryChartOptions = ref<Highcharts.Options>({
  chart: {
    type: 'pie',
    height: 300
  },
  title: {
    text: 'Mustahiq per Kategori',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  tooltip: {
    pointFormatter: function() {
      // @ts-ignore
      return `<b>${this.name}</b>: ${this.y} mustahiq`
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
  series: [],
  credits: {
    enabled: false
  }
})

const topMuzakkiChartOptions = ref<Highcharts.Options>({
  chart: {
    type: 'bar',
    height: 300
  },
  title: {
    text: 'Top 10 Muzakki',
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  },
  xAxis: {
    categories: [],
    title: {
      text: null
    }
  },
  yAxis: {
    title: {
      text: 'Total Kontribusi (Rp)',
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
  plotOptions: {
    bar: {
      borderRadius: 4,
      dataLabels: {
        enabled: true,
        formatter: function() {
          // @ts-ignore
          return 'Rp' + this.y.toLocaleString('id-ID')
        }
      }
    }
  },
  series: [],
  credits: {
    enabled: false
  },
  legend: {
    enabled: false
  }
})

// Update chart data when props change
watch(() => props.chartsData, (newData) => {
  const dataToUse = newData || mockChartData
  
  try {
    // Update monthly trends chart
    const monthlyData = getMonthlyTrendsData(dataToUse)
    monthlyTrendsChartOptions.value.xAxis = {
      categories: monthlyData.categories
    }
    monthlyTrendsChartOptions.value.series = monthlyData.series
    
    // Update ZIS by type chart
    const zisData = getZisByTypeData(dataToUse)
    zisByTypeChartOptions.value.xAxis = {
      categories: zisData.categories
    }
    zisByTypeChartOptions.value.series = zisData.series
    
    // Update Mustahiq by category chart
    const mustahiqData = getMustahiqByCategoryData(dataToUse)
    mustahiqCategoryChartOptions.value.series = mustahiqData.series
    
    // Update Top Muzakki chart
    const muzakkiData = getTopMuzakkiData(dataToUse)
    topMuzakkiChartOptions.value.xAxis = {
      categories: muzakkiData.categories
    }
    topMuzakkiChartOptions.value.series = muzakkiData.series
  } catch (error) {
    console.error('Error updating chart data:', error)
  }
}, { deep: true, immediate: true })

// Update monthly trends chart when period changes
watch(chartPeriod, () => {
  const dataToUse = props.chartsData || mockChartData
  const monthlyData = getMonthlyTrendsData(dataToUse)
  
  monthlyTrendsChartOptions.value.xAxis = {
    categories: monthlyData.categories
  }
  monthlyTrendsChartOptions.value.series = monthlyData.series
  
  // Update title based on period
  const periodText = chartPeriod.value === '6months' ? '6 Bulan Terakhir' : '12 Bulan Terakhir'
  monthlyTrendsChartOptions.value.title = {
    text: `Tren ${periodText}`,
    style: {
      fontSize: '16px',
      fontWeight: 'bold'
    }
  }
})

// Initialize charts on mount
onMounted(() => {
  isClient.value = true
})

// Format currency for data tables
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Monthly Trends Chart -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Tren Bulanan ZIS</h3>
        <div class="flex space-x-2">
          <button 
            @click="setChartPeriod('6months')"
            :class="[
              'px-3 py-1 text-sm rounded-md',
              chartPeriod === '6months' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            6 Bulan
          </button>
          <button 
            @click="setChartPeriod('12months')"
            :class="[
              'px-3 py-1 text-sm rounded-md',
              chartPeriod === '12months' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            12 Bulan
          </button>
        </div>
      </div>
      <HighchartsComponent
        v-if="isClient"
        :options="monthlyTrendsChartOptions" 
        class="w-full"
      />
      <div v-else class="h-64 flex items-center justify-center">
        <p class="text-gray-500">Memuat chart...</p>
      </div>
    </div>

    <!-- ZIS by Type Chart with Data Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">ZIS per Jenis</h3>
      <HighchartsComponent
        v-if="isClient"
        :options="zisByTypeChartOptions" 
        class="w-full mb-4"
      />
      <div v-else class="h-64 flex items-center justify-center">
        <p class="text-gray-500">Memuat chart...</p>
      </div>
      
      <!-- Data Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jenis ZIS
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah (Rp)
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah Transaksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in (props.chartsData?.zis_by_type || mockChartData.zis_by_type)" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 uppercase">
                {{ item.jenis_zis }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(item.total) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.count }} transaksi
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mustahiq by Category Chart -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Mustahiq per Kategori</h3>
      <HighchartsComponent
        v-if="isClient"
        :options="mustahiqCategoryChartOptions" 
        class="w-full"
      />
      <div v-else class="h-64 flex items-center justify-center">
        <p class="text-gray-500">Memuat chart...</p>
      </div>
    </div>

    <!-- Top 10 Muzakki Chart -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Muzakki</h3>
      <HighchartsComponent
        v-if="isClient"
        :options="topMuzakkiChartOptions" 
        class="w-full"
      />
      <div v-else class="h-64 flex items-center justify-center">
        <p class="text-gray-500">Memuat chart...</p>
      </div>
    </div>
  </div>
</template>
