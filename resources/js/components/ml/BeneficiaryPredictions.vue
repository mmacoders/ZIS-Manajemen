<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Prediksi Kebutuhan Mustahiq</h3>
      <div class="text-sm text-gray-500">
        Kebutuhan total diprediksi: {{ formatCurrency(data?.total_predicted_need || 0) }}
      </div>
    </div>

    <!-- High Priority Beneficiaries -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
      <div class="flex items-center mb-4">
        <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
        <h4 class="text-lg font-medium text-red-800">Mustahiq Prioritas Tinggi</h4>
        <span class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
          {{ getHighPriorityCount() }}
        </span>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="prediction in getHighPriorityPredictions()" 
          :key="prediction.beneficiary.id"
          class="bg-white rounded-lg p-4 shadow-sm border border-red-200"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h5 class="font-medium text-gray-900">{{ prediction.beneficiary.nama }}</h5>
              <p class="text-sm text-gray-600">{{ formatCategory(prediction.beneficiary.kategori) }}</p>
              
              <div class="mt-3 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Probabilitas Kebutuhan:</span>
                  <span class="font-medium text-red-600">{{ Math.round(prediction.need_probability) }}%</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Jumlah Disarankan:</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(prediction.suggested_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Distribusi Terakhir:</span>
                  <span class="text-gray-600">{{ prediction.days_since_last }} hari yang lalu</span>
                </div>
              </div>
            </div>
            
            <div class="ml-3">
              <span :class="getUrgencyClass(prediction.urgency_level)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ prediction.urgency_level === 'high' ? 'TINGGI' : prediction.urgency_level === 'medium' ? 'SEDANG' : prediction.urgency_level === 'low' ? 'RENDAH' : 'SANGAT RENDAH' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Analysis -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 border-b">
        <h4 class="text-lg font-medium text-gray-900">Analisis Kebutuhan berdasarkan Kategori</h4>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="(analysis, category) in data?.category_analysis" 
            :key="category"
            class="text-center p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full" :class="getCategoryBg(category)">
              <component :is="getCategoryIcon(category)" class="w-6 h-6" :class="getCategoryColor(category)" />
            </div>
            <h5 class="font-medium text-gray-900 mb-2">{{ formatCategory(category) }}</h5>
            <div class="space-y-1 text-sm">
              <p class="text-gray-600">{{ analysis.count }} mustahiq</p>
              <p class="font-medium text-gray-900">{{ formatCurrency(analysis.total_needed) }}</p>
              <p class="text-xs text-gray-500">Probabilitas rata-rata: {{ Math.round(analysis.avg_need_probability) }}%</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- All Predictions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 border-b">
        <h4 class="text-lg font-medium text-gray-900">Semua Prediksi Mustahiq</h4>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mustahiq</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Probabilitas Kebutuhan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Disarankan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urgensi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari Sejak Terakhir</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="prediction in data?.individual_predictions" :key="prediction.beneficiary.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ prediction.beneficiary.nama }}</div>
                  <div class="text-sm text-gray-500">{{ prediction.beneficiary.alamat }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getCategoryClass(prediction.beneficiary.kategori)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ formatCategory(prediction.beneficiary.kategori) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                    <div 
                      class="bg-blue-600 h-2 rounded-full"
                      :style="{ width: prediction.need_probability + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ Math.round(prediction.need_probability) }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(prediction.suggested_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getUrgencyClass(prediction.urgency_level)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ prediction.urgency_level === 'high' ? 'TINGGI' : prediction.urgency_level === 'medium' ? 'SEDANG' : prediction.urgency_level === 'low' ? 'RENDAH' : 'SANGAT RENDAH' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ prediction.days_since_last }} hari
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Insights -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
      <div class="flex items-start">
        <Lightbulb class="w-6 h-6 text-blue-600 mt-1 mr-3" />
        <div>
          <h4 class="text-lg font-medium text-blue-800 mb-2">Wawasan Prediksi</h4>
          <ul class="space-y-2 text-sm text-blue-700">
            <li>• {{ getHighPriorityCount() }} mustahiq memerlukan perhatian segera</li>
            <li>• Kebutuhan dana diprediksi: {{ formatCurrency(data?.total_predicted_need || 0) }}</li>
            <li>• Kategori paling mendesak: {{ getMostUrgentCategory() }}</li>
            <li>• Rata-rata hari sejak distribusi terakhir: {{ getAverageDaysSinceLast() }} hari</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { 
  AlertTriangle, Users, Heart, BookOpen, Globe, 
  Shield, Lightbulb, Home, MapPin
} from 'lucide-vue-next'

interface BeneficiaryPrediction {
  beneficiary: {
    id: number
    nama: string
    kategori: string
    alamat: string
  }
  need_probability: number
  suggested_amount: number
  urgency_level: string
  days_since_last: number
}

interface CategoryAnalysis {
  count: number
  total_needed: number
  avg_need_probability: number
}

interface BeneficiaryNeeds {
  individual_predictions: BeneficiaryPrediction[]
  category_analysis: Record<string, CategoryAnalysis>
  total_predicted_need: number
}

const props = defineProps<{
  data: BeneficiaryNeeds | null
}>()

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatCategory = (category: string): string => {
  const categories = {
    'fakir': 'Fakir',
    'miskin': 'Miskin',
    'amil': 'Amil',
    'muallaf': 'Muallaf',
    'riqab': 'Riqab',
    'gharim': 'Gharim',
    'fisabilillah': 'Fisabilillah',
    'ibnu_sabil': 'Ibnu Sabil'
  }
  return categories[category] || category
}

const getHighPriorityCount = (): number => {
  return props.data?.individual_predictions.filter(p => p.urgency_level === 'high').length || 0
}

const getHighPriorityPredictions = (): BeneficiaryPrediction[] => {
  return props.data?.individual_predictions.filter(p => p.urgency_level === 'high').slice(0, 6) || []
}

const getUrgencyClass = (urgency: string): string => {
  const classes = {
    'high': 'bg-red-100 text-red-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'low': 'bg-green-100 text-green-800',
    'very_low': 'bg-gray-100 text-gray-800'
  }
  return classes[urgency] || 'bg-gray-100 text-gray-800'
}

const getCategoryClass = (category: string): string => {
  const classes = {
    'fakir': 'bg-red-100 text-red-800',
    'miskin': 'bg-orange-100 text-orange-800',
    'amil': 'bg-blue-100 text-blue-800',
    'muallaf': 'bg-purple-100 text-purple-800',
    'riqab': 'bg-yellow-100 text-yellow-800',
    'gharim': 'bg-pink-100 text-pink-800',
    'fisabilillah': 'bg-green-100 text-green-800',
    'ibnu_sabil': 'bg-indigo-100 text-indigo-800'
  }
  return classes[category] || 'bg-gray-100 text-gray-800'
}

const getCategoryIcon = (category: string) => {
  const icons = {
    'fakir': Home,
    'miskin': Heart,
    'amil': Users,
    'muallaf': BookOpen,
    'riqab': Shield,
    'gharim': AlertTriangle,
    'fisabilillah': Globe,
    'ibnu_sabil': MapPin
  }
  return icons[category] || Users
}

const getCategoryBg = (category: string): string => {
  const classes = {
    'fakir': 'bg-red-100',
    'miskin': 'bg-orange-100',
    'amil': 'bg-blue-100',
    'muallaf': 'bg-purple-100',
    'riqab': 'bg-yellow-100',
    'gharim': 'bg-pink-100',
    'fisabilillah': 'bg-green-100',
    'ibnu_sabil': 'bg-indigo-100'
  }
  return classes[category] || 'bg-gray-100'
}

const getCategoryColor = (category: string): string => {
  const classes = {
    'fakir': 'text-red-600',
    'miskin': 'text-orange-600',
    'amil': 'text-blue-600',
    'muallaf': 'text-purple-600',
    'riqab': 'text-yellow-600',
    'gharim': 'text-pink-600',
    'fisabilillah': 'text-green-600',
    'ibnu_sabil': 'text-indigo-600'
  }
  return classes[category] || 'text-gray-600'
}

const getMostUrgentCategory = (): string => {
  if (!props.data?.category_analysis) return 'N/A'
  
  let maxProbability = 0
  let mostUrgenCategory = ''
  
  Object.entries(props.data.category_analysis).forEach(([category, analysis]) => {
    if (analysis.avg_need_probability > maxProbability) {
      maxProbability = analysis.avg_need_probability
      mostUrgenCategory = category
    }
  })
  
  return formatCategory(mostUrgenCategory)
}

const getAverageDaysSinceLast = (): number => {
  if (!props.data?.individual_predictions.length) return 0
  
  const total = props.data.individual_predictions.reduce((sum, p) => sum + p.days_since_last, 0)
  return Math.round(total / props.data.individual_predictions.length)
}
</script>