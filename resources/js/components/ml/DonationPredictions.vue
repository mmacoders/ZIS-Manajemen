<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Prediksi Donasi</h3>
      <div class="flex space-x-2">
        <select 
          v-model="selectedMonths" 
          @change="updatePredictions"
          class="form-select text-sm"
        >
          <option value="1">1 Bulan Berikutnya</option>
          <option value="3">3 Bulan Berikutnya</option>
          <option value="6">6 Bulan Berikutnya</option>
          <option value="12">12 Bulan Berikutnya</option>
        </select>
      </div>
    </div>

    <!-- Main Prediction Card -->
    <div v-if="data" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
          <p class="text-sm font-medium text-gray-600">Prediksi Bulan Depan</p>
          <p class="text-3xl font-bold text-blue-600 mt-2">
            {{ formatCurrency(data.predicted_amount) }}
          </p>
          <p class="text-sm text-gray-500 mt-1">
            Kepercayaan: {{ data.confidence }}%
          </p>
        </div>
        
        <div class="text-center">
          <p class="text-sm font-medium text-gray-600">Arah Tren</p>
          <div class="flex items-center justify-center mt-2">
            <TrendingUp v-if="data.trend === 'increasing'" class="w-8 h-8 text-green-500" />
            <TrendingDown v-else-if="data.trend === 'decreasing'" class="w-8 h-8 text-red-500" />
            <Minus v-else class="w-8 h-8 text-gray-500" />
            <span class="ml-2 text-lg font-semibold capitalize">{{ data.trend === 'increasing' ? 'Meningkat' : data.trend === 'decreasing' ? 'Menurun' : 'Stabil' }}</span>
          </div>
        </div>
        
        <div class="text-center">
          <p class="text-sm font-medium text-gray-600">Akurasi Model</p>
          <p class="text-3xl font-bold text-purple-600 mt-2">
            {{ Math.round(data.r_squared * 100) }}%
          </p>
          <p class="text-sm text-gray-500 mt-1">Skor R²</p>
        </div>
      </div>
    </div>

    <!-- Extended Predictions -->
    <div v-if="extendedPredictions.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 border-b">
        <h4 class="text-lg font-medium text-gray-900">Prediksi Tambahan</h4>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Bulan
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah Prediksi
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kepercayaan
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tren
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="prediction in extendedPredictions" :key="prediction.month">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatMonth(prediction.month) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(prediction.predicted_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getConfidenceClass(prediction.confidence)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ prediction.confidence }}%
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center">
                  <TrendingUp v-if="prediction.trend === 'increasing'" class="w-4 h-4 text-green-500 mr-1" />
                  <TrendingDown v-else-if="prediction.trend === 'decreasing'" class="w-4 h-4 text-red-500 mr-1" />
                  <Minus v-else class="w-4 h-4 text-gray-500 mr-1" />
                  <span class="capitalize">{{ prediction.trend === 'increasing' ? 'Meningkat' : prediction.trend === 'decreasing' ? 'Menurun' : 'Stabil' }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Historical Accuracy Chart -->
    <div v-if="data?.historical_data" class="bg-white rounded-lg shadow p-6">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Tren Donasi Historis</h4>
      <div class="h-64" ref="chartContainer">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>

    <!-- Insights and Recommendations -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
      <div class="flex items-start">
        <Lightbulb class="w-6 h-6 text-yellow-600 mt-1 mr-3" />
        <div>
          <h4 class="text-lg font-medium text-yellow-800 mb-2">Wawasan Prediksi</h4>
          <ul class="space-y-2 text-sm text-yellow-700">
            <li v-if="data?.trend === 'increasing'">
              • Donasi diprediksi akan meningkat sebesar {{ Math.abs(data.slope).toLocaleString() }} per bulan
            </li>
            <li v-else-if="data?.trend === 'decreasing'">
              • Donasi diprediksi akan menurun sebesar {{ Math.abs(data.slope).toLocaleString() }} per bulan
            </li>
            <li v-else>
              • Donasi diperkirakan tetap stabil dengan variasi minimal
            </li>
            <li>
              • Tingkat kepercayaan model: {{ getConfidenceLevel(data?.confidence) }}
            </li>
            <li v-if="data?.r_squared > 0.7">
              • Korelasi kuat terdeteksi dalam data historis (R² = {{ (data.r_squared * 100).toFixed(1) }}%)
            </li>
            <li v-else-if="data?.r_squared > 0.5">
              • Korelasi sedang dalam data historis (R² = {{ (data.r_squared * 100).toFixed(1) }}%)
            </li>
            <li v-else>
              • Korelasi rendah menunjukkan variabilitas tinggi dalam pola donasi
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'
import { TrendingUp, TrendingDown, Minus, Lightbulb } from 'lucide-vue-next'
import axios from 'axios'
import Chart from 'chart.js/auto'

interface DonationPrediction {
  predicted_amount: number
  confidence: number
  trend: string
  historical_data: any[]
  slope: number
  r_squared: number
}

interface ExtendedPrediction {
  month: string
  predicted_amount: number
  confidence: number
  trend: string
}

const props = defineProps<{
  data: DonationPrediction | null
}>()

const selectedMonths = ref(3)
const extendedPredictions = ref<ExtendedPrediction[]>([])
const chartContainer = ref<HTMLDivElement>()
const chartCanvas = ref<HTMLCanvasElement>()
const chart = ref<Chart | null>(null)

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatMonth = (monthStr: string): string => {
  const [year, month] = monthStr.split('-')
  return new Date(parseInt(year), parseInt(month) - 1).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long'
  })
}

const getConfidenceClass = (confidence: number): string => {
  if (confidence >= 80) return 'bg-green-100 text-green-800'
  if (confidence >= 60) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getConfidenceLevel = (confidence: number): string => {
  if (confidence >= 80) return 'Tinggi'
  if (confidence >= 60) return 'Sedang'
  return 'Rendah'
}

const updatePredictions = async () => {
  try {
    const response = await axios.get(`/api/ml-analytics/predictions/donation-extended/${selectedMonths.value}`)
    if (response.data.success) {
      extendedPredictions.value = response.data.data
    }
  } catch (err) {
    console.error('Kesalahan memuat prediksi tambahan:', err)
  }
}

const createChart = async () => {
  if (!chartCanvas.value || !props.data?.historical_data) return
  
  await nextTick()
  
  if (chart.value) {
    chart.value.destroy()
  }
  
  const ctx = chartCanvas.value.getContext('2d')
  if (!ctx) return
  
  const data = props.data.historical_data
  
  chart.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => `${item.year}-${item.month.toString().padStart(2, '0')}`),
      datasets: [
        {
          label: 'Donasi Aktual',
          data: data.map(item => item.actual_amount),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Donasi Prediksi',
          data: data.map(item => item.predicted_amount),
          borderColor: 'rgb(16, 185, 129)',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          borderDash: [5, 5],
          tension: 0.4
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
          ticks: {
            callback: function(value) {
              return formatCurrency(value as number)
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              return formatCurrency(context.parsed.y)
            }
          }
        }
      }
    }
  })
}

watch(() => props.data, () => {
  if (props.data?.historical_data) {
    createChart()
  }
})

onMounted(() => {
  updatePredictions()
})
</script>