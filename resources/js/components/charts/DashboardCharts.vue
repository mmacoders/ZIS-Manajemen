<template>
  <div class="charts-section">
    <!-- Monthly Trends Chart -->
    <div class="card mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Tren Bulanan ZIS</h3>
        <div class="flex space-x-2">
          <button 
            @click="setChartPeriod('6months')"
            :class="chartPeriod === '6months' ? 'btn-primary-sm' : 'btn-secondary-sm'"
          >
            6 Bulan
          </button>
          <button 
            @click="setChartPeriod('12months')"
            :class="chartPeriod === '12months' ? 'btn-primary-sm' : 'btn-secondary-sm'"
          >
            12 Bulan
          </button>
        </div>
      </div>
      <div class="chart-container">
        <canvas ref="monthlyTrendsChart" width="800" height="300"></canvas>
      </div>
    </div>

    <!-- ZIS Types and Mustahiq Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- ZIS by Type -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ZIS per Jenis</h3>
        <div class="chart-container">
          <canvas ref="zisTypeChart" width="400" height="300"></canvas>
        </div>
      </div>

      <!-- Mustahiq by Category -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mustahiq per Kategori</h3>
        <div class="chart-container">
          <canvas ref="mustahiqCategoryChart" width="400" height="300"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Muzakki -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Muzakki</h3>
      <div class="chart-container">
        <canvas ref="topMuzakkiChart" width="800" height="300"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import Chart from 'chart.js/auto'

interface Props {
  chartsData?: any
}

const props = defineProps<Props>()

const monthlyTrendsChart = ref<HTMLCanvasElement>()
const zisTypeChart = ref<HTMLCanvasElement>()
const mustahiqCategoryChart = ref<HTMLCanvasElement>()
const topMuzakkiChart = ref<HTMLCanvasElement>()

const chartPeriod = ref('12months')
let charts: Chart[] = []

const colors = {
  primary: '#3B82F6',
  secondary: '#10B981',
  warning: '#F59E0B',
  danger: '#EF4444',
  info: '#06B6D4',
  purple: '#8B5CF6',
  pink: '#EC4899',
  indigo: '#6366F1'
}

const categoryColors = [
  '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
  '#06B6D4', '#8B5CF6', '#EC4899', '#6366F1'
]

onMounted(() => {
  nextTick(() => {
    initializeCharts()
  })
})

onUnmounted(() => {
  destroyCharts()
})

watch(() => props.chartsData, (newData) => {
  if (newData) {
    updateCharts()
  }
}, { deep: true })

watch(chartPeriod, () => {
  updateMonthlyTrendsChart()
})

const setChartPeriod = (period: string) => {
  chartPeriod.value = period
}

const initializeCharts = () => {
  createMonthlyTrendsChart()
  createZisTypeChart()
  createMustahiqCategoryChart()
  createTopMuzakkiChart()
}

const destroyCharts = () => {
  charts.forEach(chart => chart.destroy())
  charts = []
}

const updateCharts = () => {
  destroyCharts()
  nextTick(() => {
    initializeCharts()
  })
}

const createMonthlyTrendsChart = () => {
  if (!monthlyTrendsChart.value || !props.chartsData?.monthly_collection) return

  const monthlyData = props.chartsData.monthly_collection || []
  const distributionData = props.chartsData.monthly_distribution || []

  // Filter data based on period
  const filteredData = chartPeriod.value === '6months' 
    ? monthlyData.slice(-6) 
    : monthlyData.slice(-12)

  const filteredDistribution = chartPeriod.value === '6months'
    ? distributionData.slice(-6)
    : distributionData.slice(-12)

  const labels = filteredData.map((item: any) => {
    const date = new Date(item.year, item.month - 1)
    return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
  })

  const chart = new Chart(monthlyTrendsChart.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Pengumpulan ZIS',
          data: filteredData.map((item: any) => item.total),
          borderColor: colors.primary,
          backgroundColor: `${colors.primary}20`,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Distribusi ZIS',
          data: filteredDistribution.map((item: any) => item.total),
          borderColor: colors.secondary,
          backgroundColor: `${colors.secondary}20`,
          fill: true,
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Tren ${chartPeriod.value === '6months' ? '6' : '12'} Bulan Terakhir`
        },
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
              }).format(value)
            }
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false,
      },
    }
  })

  charts.push(chart)
}

const createZisTypeChart = () => {
  if (!zisTypeChart.value || !props.chartsData?.zis_by_type) return

  const data = props.chartsData.zis_by_type || []
  
  const chart = new Chart(zisTypeChart.value, {
    type: 'bar',
    data: {
      labels: data.map((item: any) => item.jenis_zis.toUpperCase()),
      datasets: [{
        label: 'Jumlah (Rp)',
        data: data.map((item: any) => item.total),
        backgroundColor: [
          colors.primary,
          colors.secondary,
          colors.warning,
          colors.info
        ],
        borderColor: [
          colors.primary,
          colors.secondary,
          colors.warning,
          colors.info
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
              }).format(value)
            }
          }
        }
      }
    }
  })

  charts.push(chart)
}

const createMustahiqCategoryChart = () => {
  if (!mustahiqCategoryChart.value || !props.chartsData?.mustahiq_by_category) return

  const data = props.chartsData.mustahiq_by_category || []
  
  const chart = new Chart(mustahiqCategoryChart.value, {
    type: 'doughnut',
    data: {
      labels: data.map((item: any) => item.kategori.charAt(0).toUpperCase() + item.kategori.slice(1)),
      datasets: [{
        data: data.map((item: any) => item.count),
        backgroundColor: categoryColors.slice(0, data.length),
        borderColor: '#ffffff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  })

  charts.push(chart)
}

const createTopMuzakkiChart = () => {
  if (!topMuzakkiChart.value || !props.chartsData?.top_muzakki) return

  const data = props.chartsData.top_muzakki || []
  
  const chart = new Chart(topMuzakkiChart.value, {
    type: 'bar',
    data: {
      labels: data.map((item: any) => item.muzakki?.nama || 'Unknown'),
      datasets: [{
        label: 'Total Kontribusi (Rp)',
        data: data.map((item: any) => item.total_contribution),
        backgroundColor: colors.purple,
        borderColor: colors.purple,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
              }).format(value)
            }
          }
        }
      }
    }
  })

  charts.push(chart)
}

const updateMonthlyTrendsChart = () => {
  const chart = charts.find(c => c.canvas === monthlyTrendsChart.value)
  if (chart) {
    chart.destroy()
    charts = charts.filter(c => c !== chart)
    createMonthlyTrendsChart()
  }
}
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
}

.btn-primary-sm {
  @apply px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors;
}

.btn-secondary-sm {
  @apply px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition-colors;
}

canvas {
  max-width: 100%;
  height: auto;
}
</style>