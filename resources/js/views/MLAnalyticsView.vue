<template>
  <div class="min-h-screen bg-gray-50 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Analitik Machine Learning</h1>
          <p class="mt-2 text-gray-600">Analitik lanjutan dan prediksi menggunakan machine learning</p>
        </div>
        
        <div class="mt-4 sm:mt-0 flex space-x-3">
          <button 
            @click="runAIAnalysis"
            :disabled="isAnalyzing"
            class="btn btn-primary"
          >
            <Zap :class="{'animate-spin': isAnalyzing}" class="w-4 h-4 mr-2" />
            Analisis AI
          </button>
          
          <button 
            @click="refreshData"
            :disabled="isLoading"
            class="btn btn-secondary"
          >
            <RefreshCw :class="{'animate-spin': isLoading}" class="w-4 h-4 mr-2" />
            Segarkan
          </button>
          
          <button 
            @click="clearCache"
            class="btn btn-outline"
          >
            <Database class="w-4 h-4 mr-2" />
            Hapus Cache
          </button>
        </div>
      </div>
    </div>

    <!-- AI Analysis Results Notification -->
    <div v-if="aiAnalysisResults" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="flex items-start">
        <Zap class="w-5 h-5 text-blue-500 mt-0.5 mr-2" />
        <div>
          <h5 class="text-sm font-medium text-blue-800">Analisis AI Selesai</h5>
          <p class="text-sm text-blue-700 mt-1">
            Analisis selesai pada {{ formatDateTime(aiAnalysisResults.analysis_time) }}. 
            Ditemukan {{ getFraudAlertCountFromResults(aiAnalysisResults.data) }} masalah potensial.
          </p>
          <button @click="viewFraudResults" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
            Lihat Detail
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardData" class="space-y-8">
      <!-- Performance Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <TrendingUp class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Akurasi Prediksi</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ Math.round(modelStats?.average_accuracy || 0) }}%
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <Target class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Prediksi</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ modelStats?.total_predictions || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <Zap class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Skor Efisiensi</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ Math.round(dashboardData.efficiency_scores?.resource_utilization_score || 0) }}%
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <AlertTriangle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Peringatan Penipuan</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ getFraudAlertCount() }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="w-5 h-5 mr-2 inline" />
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Donation Predictions -->
          <div v-if="activeTab === 'predictions'">
            <DonationPredictions :data="dashboardData.donation_prediction" />
          </div>

          <!-- Donor Analysis -->
          <div v-if="activeTab === 'donors'">
            <DonorAnalysis :data="dashboardData.donor_patterns" />
          </div>

          <!-- Beneficiary Predictions -->
          <div v-if="activeTab === 'beneficiaries'">
            <BeneficiaryPredictions :data="dashboardData.beneficiary_needs" />
          </div>

          <!-- Fraud Detection -->
          <div v-if="activeTab === 'fraud'">
            <FraudDetection :data="dashboardData.fraud_detection" />
          </div>

          <!-- Performance Metrics -->
          <div v-if="activeTab === 'performance'">
            <PerformanceMetrics 
              :performance="dashboardData.performance_metrics"
              :efficiency="dashboardData.efficiency_scores"
              :trends="dashboardData.trend_analysis"
            />
          </div>

          <!-- Recommendations -->
          <div v-if="activeTab === 'recommendations'">
            <RecommendationsView :data="dashboardData.recommendations" />
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <AlertTriangle class="w-12 h-12 text-red-500 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Kesalahan Memuat Analitik</h3>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <button @click="loadDashboard" class="btn btn-primary">
        Coba Lagi
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import {
  RefreshCw, Database, TrendingUp, Target, Zap, AlertTriangle,
  BarChart3, Users, Shield, Activity, Award, Lightbulb
} from 'lucide-vue-next'

// Components
import DonationPredictions from '@/components/ml/DonationPredictions.vue'
import DonorAnalysis from '@/components/ml/DonorAnalysis.vue'
import BeneficiaryPredictions from '@/components/ml/BeneficiaryPredictions.vue'
import FraudDetection from '@/components/ml/FraudDetection.vue'
import PerformanceMetrics from '@/components/ml/PerformanceMetrics.vue'
import RecommendationsView from '@/components/ml/RecommendationsView.vue'

// State
const isLoading = ref(true)
const isAnalyzing = ref(false)
const dashboardData = ref(null)
const modelStats = ref(null)
const error = ref('')
const activeTab = ref('predictions')
const aiAnalysisResults = ref(null)

// Tab configuration
const tabs = [
  { id: 'predictions', name: 'Prediksi Donasi', icon: BarChart3 },
  { id: 'donors', name: 'Analisis Donatur', icon: Users },
  { id: 'beneficiaries', name: 'Kebutuhan Mustahiq', icon: Target },
  { id: 'fraud', name: 'Deteksi Penipuan', icon: Shield },
  { id: 'performance', name: 'Performa', icon: Activity },
  { id: 'recommendations', name: 'Rekomendasi', icon: Lightbulb }
]

// Methods
const loadDashboard = async () => {
  try {
    isLoading.value = true
    error.value = ''
    
    // Use correct endpoints relative to the axios base URL
    const [dashboardResponse, statsResponse] = await Promise.all([
      axios.get('/ml-analytics-auth/dashboard'),
      axios.get('/ml-analytics-auth/model-statistics')
    ])
    
    if (dashboardResponse.data.success) {
      dashboardData.value = dashboardResponse.data.data
    } else {
      throw new Error(dashboardResponse.data.message)
    }
    
    if (statsResponse.data.success) {
      modelStats.value = statsResponse.data.data
    }
    
  } catch (err: any) {
    console.error('Kesalahan memuat dashboard analitik ML:', err)
    error.value = err.response?.data?.message || err.message || 'Gagal memuat analitik'
  } finally {
    isLoading.value = false
  }
}

const refreshData = async () => {
  await loadDashboard()
}

const clearCache = async () => {
  try {
    // Use correct endpoint relative to the axios base URL
    const response = await axios.delete('/ml-analytics-auth/cache')
    if (response.data.success) {
      alert('Cache berhasil dihapus')
      await loadDashboard()
    }
  } catch (err: any) {
    console.error('Kesalahan menghapus cache:', err)
    alert('Gagal menghapus cache')
  }
}

const runAIAnalysis = async () => {
  try {
    isAnalyzing.value = true
    aiAnalysisResults.value = null
    
    // Use the correct endpoint path relative to the axios base URL
    const response = await axios.post('/ml-analytics-auth/trigger-fraud-detection')
    
    if (response.data.success) {
      aiAnalysisResults.value = response.data
      // Show notification for 10 seconds
      setTimeout(() => {
        aiAnalysisResults.value = null
      }, 10000)
    } else {
      throw new Error(response.data.message || 'Kesalahan tidak dikenal dari server')
    }
  } catch (err: any) {
    console.error('Kesalahan menjalankan analisis AI:', err)
    // Provide more detailed error information
    let errorMessage = 'Gagal menjalankan analisis AI: '
    if (err.response) {
      // Server responded with error status
      errorMessage += err.response.data.message || err.response.statusText || 'Kesalahan server'
      console.error('Respons server:', err.response.data)
    } else if (err.request) {
      // Request was made but no response received
      errorMessage += 'Tidak ada respons dari server. Periksa koneksi jaringan Anda.'
      console.error('Tidak ada respons diterima:', err.request)
    } else {
      // Something else happened
      errorMessage += err.message || 'Kesalahan tidak dikenal'
    }
    alert(errorMessage)
  } finally {
    isAnalyzing.value = false
  }
}

const viewFraudResults = () => {
  activeTab.value = 'fraud'
  // Scroll to the fraud detection section
  setTimeout(() => {
    const element = document.querySelector('.bg-white.rounded-lg.shadow')
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    }
  }, 100)
}

const getFraudAlertCount = (): number => {
  if (!dashboardData.value?.fraud_detection) return 0
  
  const fraud = dashboardData.value.fraud_detection
  return (
    (fraud.duplicate_donors?.length || 0) +
    (fraud.unusual_transactions?.length || 0) +
    (fraud.suspicious_beneficiaries?.length || 0) +
    (fraud.timing_anomalies?.length || 0)
  )
}

const getFraudAlertCountFromResults = (data: any): number => {
  if (!data) return 0
  
  return (
    (data.duplicate_donors?.length || 0) +
    (data.unusual_transactions?.length || 0) +
    (data.suspicious_beneficiaries?.length || 0) +
    (data.timing_anomalies?.length || 0)
  )
}

const formatDateTime = (dateString: string): string => {
  return new Date(dateString).toLocaleString('id-ID')
}

// Lifecycle
onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
  @apply text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
  @apply text-blue-600 bg-blue-100 hover:bg-blue-200 focus:ring-blue-500;
}

.btn-outline {
  @apply text-gray-700 bg-white border-gray-300 hover:bg-gray-50 focus:ring-blue-500;
}
</style>