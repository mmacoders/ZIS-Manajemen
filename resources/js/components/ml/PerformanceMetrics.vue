<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Metrik Kinerja & Analisis Efisiensi</h3>
      <div class="text-sm text-gray-500">
        Skor Keseluruhan: {{ getOverallScore() }}/100
      </div>
    </div>

    <!-- Performance Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Efisiensi Pengumpulan</p>
            <p class="text-2xl font-bold text-blue-600">{{ Math.round(efficiency?.donation_collection_efficiency || 0) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Rasio donatur aktif</p>
          </div>
          <div class="p-2 bg-blue-100 rounded-lg">
            <TrendingUp class="w-6 h-6 text-blue-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Efisiensi Distribusi</p>
            <p class="text-2xl font-bold text-green-600">{{ Math.round(efficiency?.distribution_efficiency || 0) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Mustahiq yang dilayani</p>
          </div>
          <div class="p-2 bg-green-100 rounded-lg">
            <Target class="w-6 h-6 text-green-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Utilisasi Sumber Daya</p>
            <p class="text-2xl font-bold text-purple-600">{{ Math.round(efficiency?.resource_utilization_score || 0) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Rasio distribusi dana</p>
          </div>
          <div class="p-2 bg-purple-100 rounded-lg">
            <Zap class="w-6 h-6 text-purple-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Skor Kepuasan</p>
            <p class="text-2xl font-bold text-orange-600">{{ Math.round(efficiency?.beneficiary_satisfaction_score || 0) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Estimasi kualitas layanan</p>
          </div>
          <div class="p-2 bg-orange-100 rounded-lg">
            <Award class="w-6 h-6 text-orange-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="bg-white rounded-lg shadow p-6">
      <h4 class="text-lg font-medium text-gray-900 mb-6">Indikator Kinerja Utama</h4>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <h5 class="text-md font-medium text-gray-700 mb-4">Metrik Keuangan</h5>
          <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Total Donasi</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(performance?.total_donations || 0) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Total Distribusi</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(performance?.total_distributions || 0) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Efisiensi Dana</span>
              <span class="font-medium" :class="getEfficiencyClass(performance?.efficiency_percentage)">
                {{ Math.round(performance?.efficiency_percentage || 0) }}%
              </span>
            </div>
          </div>
        </div>
        
        <div>
          <h5 class="text-md font-medium text-gray-700 mb-4">Metrik Operasional</h5>
          <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Donatur Aktif</span>
              <span class="font-medium text-gray-900">{{ performance?.active_donors_count || 0 }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Mustahiq Aktif</span>
              <span class="font-medium text-gray-900">{{ performance?.active_beneficiaries_count || 0 }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
              <span class="text-sm text-gray-600">Tingkat Pertumbuhan</span>
              <span class="font-medium" :class="getGrowthClass(trends?.growth_rate)">
                {{ formatGrowth(trends?.growth_rate) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Trends Analysis Chart -->
    <div class="bg-white rounded-lg shadow p-6">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Analisis Tren Bulanan</h4>
      <div class="h-64" ref="trendsChartContainer">
        <canvas ref="trendsChart"></canvas>
      </div>
    </div>

    <!-- Efficiency Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Efficiency Gauge Charts -->
      <div class="bg-white rounded-lg shadow p-6">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Rincian Efisiensi</h4>
        <div class="space-y-6">
          <div v-for="metric in efficiencyMetrics" :key="metric.name" class="">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-700">{{ metric.name }}</span>
              <span class="text-sm font-bold" :class="metric.color">{{ metric.value }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="metric.bgColor"
                :style="{ width: metric.value + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Seasonality Analysis -->
      <div class="bg-white rounded-lg shadow p-6">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Pola Musiman</h4>
        <div v-if="trends?.seasonality" class="space-y-3">
          <div v-for="(amount, month) in trends.seasonality" :key="month" class="flex items-center justify-between py-2">
            <div class="flex items-center">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                <span class="text-xs font-medium text-blue-600">{{ month }}</span>
              </div>
              <span class="text-sm text-gray-700">{{ getMonthName(month) }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium text-gray-900">{{ formatCurrency(amount) }}</div>
              <div class="text-xs text-gray-500">{{ getSeasonalIndicator(amount, trends.seasonality) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Insights -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
      <div class="flex items-start">
        <BarChart3 class="w-6 h-6 text-blue-600 mt-1 mr-3" />
        <div>
          <h4 class="text-lg font-medium text-blue-800 mb-2">Wawasan Kinerja</h4>
          <ul class="space-y-2 text-sm text-blue-700">
            <li>• Efisiensi sistem keseluruhan: {{ getOverallScore() }}/100</li>
            <li v-if="performance?.efficiency_percentage >= 80">
              • Tingkat pemanfaatan dana yang sangat baik {{ Math.round(performance.efficiency_percentage) }}%
            </li>
            <li v-else-if="performance?.efficiency_percentage >= 60">
              • Pemanfaatan dana yang baik dengan ruang untuk perbaikan ({{ Math.round(performance.efficiency_percentage) }}%)
            </li>
            <li v-else>
              • Pemanfaatan dana perlu perhatian ({{ Math.round(performance?.efficiency_percentage || 0) }}%)
            </li>
            <li v-if="trends?.growth_rate > 0">
              • Tren pertumbuhan positif {{ Math.round(trends.growth_rate) }}% per tahun
            </li>
            <li v-else-if="trends?.growth_rate < 0">
              • Tren menurun {{ Math.round(Math.abs(trends.growth_rate)) }}% perlu perhatian
            </li>
            <li v-else>
              • Kinerja stabil dengan pola yang konsisten
            </li>
            <li>• {{ performance?.active_donors_count || 0 }} donatur aktif yang berkontribusi secara teratur</li>
            <li>• {{ performance?.active_beneficiaries_count || 0 }} mustahiq yang menerima dukungan</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { TrendingUp, Target, Zap, Award, BarChart3 } from 'lucide-vue-next'
import Chart from 'chart.js/auto'

interface PerformanceMetrics {
  total_donations: number
  total_distributions: number
  efficiency_percentage: number
  active_donors_count: number
  active_beneficiaries_count: number
}

interface EfficiencyScores {
  donation_collection_efficiency: number
  distribution_efficiency: number
  beneficiary_satisfaction_score: number
  resource_utilization_score: number
}

interface TrendAnalysis {
  monthly_trends: any[]
  growth_rate: number
  seasonality: Record<string, number>
}

const props = defineProps<{
  performance: PerformanceMetrics | null
  efficiency: EfficiencyScores | null
  trends: TrendAnalysis | null
}>()

const trendsChartContainer = ref<HTMLDivElement>()
const trendsChart = ref<HTMLCanvasElement>()
const chart = ref<Chart | null>(null)

const efficiencyMetrics = ref([
  {
    name: 'Pengumpulan Donasi',
    value: Math.round(props.efficiency?.donation_collection_efficiency || 0),
    color: 'text-blue-600',
    bgColor: 'bg-blue-500'
  },
  {
    name: 'Efisiensi Distribusi',
    value: Math.round(props.efficiency?.distribution_efficiency || 0),
    color: 'text-green-600',
    bgColor: 'bg-green-500'
  },
  {
    name: 'Utilisasi Sumber Daya',
    value: Math.round(props.efficiency?.resource_utilization_score || 0),
    color: 'text-purple-600',
    bgColor: 'bg-purple-500'
  },
  {
    name: 'Skor Kepuasan',
    value: Math.round(props.efficiency?.beneficiary_satisfaction_score || 0),
    color: 'text-orange-600',
    bgColor: 'bg-orange-500'
  }
])

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatGrowth = (rate: number): string => {
  if (!rate) return '0%'
  const sign = rate > 0 ? '+' : ''
  return `${sign}${Math.round(rate)}%`
}

const getOverallScore = (): number => {
  if (!props.efficiency) return 0
  
  const scores = [
    props.efficiency.donation_collection_efficiency,
    props.efficiency.distribution_efficiency,
    props.efficiency.resource_utilization_score,
    props.efficiency.beneficiary_satisfaction_score
  ]
  
  return Math.round(scores.reduce((sum, score) => sum + score, 0) / scores.length)
}

const getEfficiencyClass = (percentage: number): string => {
  if (percentage >= 80) return 'text-green-600'
  if (percentage >= 60) return 'text-yellow-600'
  return 'text-red-600'
}

const getGrowthClass = (rate: number): string => {
  if (rate > 0) return 'text-green-600'
  if (rate < 0) return 'text-red-600'
  return 'text-gray-600'
}

const getMonthName = (month: string | number): string => {
  const monthNames = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
  ]
  return monthNames[parseInt(month.toString()) - 1] || 'Tidak Diketahui'
}

const getSeasonalIndicator = (amount: number, seasonality: Record<string, number>): string => {
  const amounts = Object.values(seasonality)
  const avg = amounts.reduce((sum, val) => sum + val, 0) / amounts.length
  
  if (amount > avg * 1.2) return 'Puncak'
  if (amount < avg * 0.8) return 'Rendah'
  return 'Normal'
}

const createTrendsChart = async () => {
  if (!trendsChart.value || !props.trends?.monthly_trends) return
  
  await nextTick()
  
  if (chart.value) {
    chart.value.destroy()
  }
  
  const ctx = trendsChart.value.getContext('2d')
  if (!ctx) return
  
  const data = props.trends.monthly_trends
  
  chart.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => `${item.year}-${item.month.toString().padStart(2, '0')}`),
      datasets: [
        {
          label: 'Jumlah Total',
          data: data.map(item => item.total_amount),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Jumlah Transaksi',
          data: data.map(item => item.transaction_count),
          borderColor: 'rgb(16, 185, 129)',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          yAxisID: 'y1'
        },
        {
          label: 'Rata-rata',
          data: data.map(item => item.avg_amount),
          borderColor: 'rgb(245, 158, 11)',
          backgroundColor: 'rgba(245, 158, 11, 0.1)',
          tension: 0.4,
          yAxisID: 'y2'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index'
      },
      scales: {
        y: {
          type: 'linear',
          display: true,
          position: 'left'
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          grid: {
            drawOnChartArea: false
          }
        },
        y2: {
          type: 'linear',
          display: false
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              if (context.datasetIndex === 0) {
                return 'Total: ' + formatCurrency(context.parsed.y)
              } else if (context.datasetIndex === 1) {
                return 'Jumlah: ' + context.parsed.y + ' transaksi'
              } else {
                return 'Rata-rata: ' + formatCurrency(context.parsed.y)
              }
            }
          }
        }
      }
    }
  })
}

onMounted(() => {
  if (props.trends?.monthly_trends) {
    createTrendsChart()
  }
})
</script>