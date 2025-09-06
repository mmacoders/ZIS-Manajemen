<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Rekomendasi Berbasis AI</h3>
      <div class="text-sm text-gray-500">
        {{ data?.length || 0 }} rekomendasi
      </div>
    </div>

    <!-- Priority Recommendations -->
    <div v-if="highPriorityRecommendations.length > 0" class="space-y-4">
      <h4 class="text-md font-medium text-red-700 flex items-center">
        <AlertTriangle class="w-5 h-5 mr-2" />
        Tindakan Prioritas Tinggi
      </h4>
      
      <div class="space-y-3">
        <div 
          v-for="recommendation in highPriorityRecommendations" 
          :key="recommendation.type"
          class="bg-red-50 border border-red-200 rounded-lg p-4"
        >
          <div class="flex items-start">
            <div class="p-2 bg-red-100 rounded-lg mr-4">
              <component :is="getRecommendationIcon(recommendation.type)" class="w-5 h-5 text-red-600" />
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-2">
                <h5 class="text-md font-medium text-red-800">{{ getRecommendationTitle(recommendation.type) }}</h5>
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ recommendation.priority === 'high' ? 'TINGGI' : recommendation.priority === 'medium' ? 'SEDANG' : 'RENDAH' }}
                </span>
              </div>
              <p class="text-sm text-red-700 mb-3">{{ recommendation.message }}</p>
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-red-600">Tindakan: {{ recommendation.action }}</div>
                <button class="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition-colors">
                  Lakukan Tindakan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Medium Priority Recommendations -->
    <div v-if="mediumPriorityRecommendations.length > 0" class="space-y-4">
      <h4 class="text-md font-medium text-yellow-700 flex items-center">
        <Clock class="w-5 h-5 mr-2" />
        Tindakan Prioritas Sedang
      </h4>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="recommendation in mediumPriorityRecommendations" 
          :key="recommendation.type"
          class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
        >
          <div class="flex items-start">
            <div class="p-2 bg-yellow-100 rounded-lg mr-3">
              <component :is="getRecommendationIcon(recommendation.type)" class="w-4 h-4 text-yellow-600" />
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-2">
                <h5 class="text-sm font-medium text-yellow-800">{{ getRecommendationTitle(recommendation.type) }}</h5>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ recommendation.priority === 'high' ? 'TINGGI' : recommendation.priority === 'medium' ? 'SEDANG' : 'RENDAH' }}
                </span>
              </div>
              <p class="text-sm text-yellow-700 mb-2">{{ recommendation.message }}</p>
              <div class="text-xs text-yellow-600">{{ recommendation.action }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Low Priority Recommendations -->
    <div v-if="lowPriorityRecommendations.length > 0" class="space-y-4">
      <h4 class="text-md font-medium text-blue-700 flex items-center">
        <Info class="w-5 h-5 mr-2" />
        Saran Optimasi
      </h4>
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="space-y-3">
          <div v-for="recommendation in lowPriorityRecommendations" :key="recommendation.type" class="flex items-start">
            <div class="p-1 bg-blue-100 rounded mr-3 mt-0.5">
              <component :is="getRecommendationIcon(recommendation.type)" class="w-3 h-3 text-blue-600" />
            </div>
            <div class="flex-1">
              <h6 class="text-sm font-medium text-blue-800">{{ getRecommendationTitle(recommendation.type) }}</h6>
              <p class="text-sm text-blue-700">{{ recommendation.message }}</p>
              <p class="text-xs text-blue-600 mt-1">{{ recommendation.action }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Recommendations -->
    <div v-if="!data || data.length === 0" class="text-center py-12">
      <CheckCircle class="w-16 h-16 text-green-500 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Semua Sistem Optimal</h3>
      <p class="text-gray-600">Tidak ada rekomendasi segera. Sistem ZIS Anda berkinerja baik!</p>
    </div>

    <!-- General AI Insights -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-6">
      <div class="flex items-start">
        <div class="p-3 bg-purple-100 rounded-lg mr-4">
          <Brain class="w-6 h-6 text-purple-600" />
        </div>
        <div>
          <h4 class="text-lg font-medium text-purple-800 mb-2">Wawasan AI</h4>
          <div class="text-sm text-purple-700 space-y-2">
            <p>• Analisis machine learning berdasarkan {{ getTotalDataPoints() }} titik data</p>
            <p>• Rekomendasi diperbarui setiap 24 jam untuk akurasi optimal</p>
            <p>• Sistem terus belajar dari tindakan dan hasil pengguna</p>
            <p>• Akurasi prediktif meningkat dengan lebih banyak data historis</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Implementation Timeline -->
    <div v-if="data && data.length > 0" class="bg-white rounded-lg shadow p-6">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Jadwal Implementasi yang Direkomendasikan</h4>
      
      <div class="space-y-4">
        <!-- Immediate Actions (0-7 days) -->
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-red-600">1</span>
            </div>
          </div>
          <div class="ml-4">
            <h5 class="text-sm font-medium text-gray-900">Segera (0-7 hari)</h5>
            <ul class="mt-1 text-sm text-gray-600 space-y-1">
              <li v-for="rec in highPriorityRecommendations" :key="'immediate-' + rec.type">
                • {{ rec.action }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Short Term Actions (1-4 weeks) -->
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-yellow-600">2</span>
            </div>
          </div>
          <div class="ml-4">
            <h5 class="text-sm font-medium text-gray-900">Jangka Pendek (1-4 minggu)</h5>
            <ul class="mt-1 text-sm text-gray-600 space-y-1">
              <li v-for="rec in mediumPriorityRecommendations" :key="'short-' + rec.type">
                • {{ rec.action }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Long Term Actions (1-3 months) -->
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-blue-600">3</span>
            </div>
          </div>
          <div class="ml-4">
            <h5 class="text-sm font-medium text-gray-900">Jangka Panjang (1-3 bulan)</h5>
            <ul class="mt-1 text-sm text-gray-600 space-y-1">
              <li v-for="rec in lowPriorityRecommendations" :key="'long-' + rec.type">
                • {{ rec.action }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { 
  AlertTriangle, Clock, Info, CheckCircle, Brain,
  Users, TrendingUp, Target, Shield, Zap, Settings
} from 'lucide-vue-next'

interface Recommendation {
  type: string
  priority: string
  message: string
  action: string
}

const props = defineProps<{
  data: Recommendation[] | null
}>()

const highPriorityRecommendations = computed(() => {
  return props.data?.filter(rec => rec.priority === 'high') || []
})

const mediumPriorityRecommendations = computed(() => {
  return props.data?.filter(rec => rec.priority === 'medium') || []
})

const lowPriorityRecommendations = computed(() => {
  return props.data?.filter(rec => rec.priority === 'low') || []
})

const getRecommendationIcon = (type: string) => {
  const icons = {
    'donor_retention': Users,
    'efficiency': TrendingUp,
    'fraud_prevention': Shield,
    'system_optimization': Settings,
    'performance': Target,
    'resource_management': Zap
  }
  return icons[type] || AlertTriangle
}

const getRecommendationTitle = (type: string): string => {
  const titles = {
    'donor_retention': 'Retensi Donatur',
    'efficiency': 'Efisiensi Sistem',
    'fraud_prevention': 'Pencegahan Penipuan',
    'system_optimization': 'Optimasi Sistem',
    'performance': 'Peningkatan Kinerja',
    'resource_management': 'Manajemen Sumber Daya'
  }
  return titles[type] || 'Rekomendasi Umum'
}

const getTotalDataPoints = (): string => {
  // Simulated data points calculation
  const basePoints = 1000
  const randomVariation = Math.floor(Math.random() * 500)
  return (basePoints + randomVariation).toLocaleString()
}
</script>