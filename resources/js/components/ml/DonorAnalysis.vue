<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Analisis Perilaku Donatur</h3>
      <div class="text-sm text-gray-500">
        Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID') }}
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div v-for="(pattern, key) in patterns" :key="key" class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 capitalize">{{ formatPatternName(key) }}</p>
            <p class="text-2xl font-bold mt-1" :class="getPatternColor(key)">
              {{ Array.isArray(pattern) ? pattern.length : 0 }}
            </p>
          </div>
          <component :is="getPatternIcon(key)" class="w-8 h-8" :class="getPatternColor(key)" />
        </div>
      </div>
    </div>

    <!-- Detailed Analysis Tabs -->
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
            {{ tab.name }} ({{ getTabCount(tab.id) }})
          </button>
        </nav>
      </div>

      <div class="p-6">
        <!-- Regular Donors -->
        <div v-if="activeTab === 'regular'">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Donatur Reguler</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donatur</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Total</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frekuensi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konsistensi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in patterns.regular_donors" :key="item.donor.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ item.donor.nama }}</div>
                      <div class="text-sm text-gray-500">{{ item.donor.jenis }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.total_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.frequency }} donasi
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.avg_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getConsistencyClass(item.consistency_score)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ Math.round(item.consistency_score) }}%
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- High Value Donors -->
        <div v-if="activeTab === 'high_value'">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Donatur Nilai Tinggi</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="item in patterns.high_value_donors" :key="item.donor.id" class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg p-6 border">
              <div class="flex items-start justify-between">
                <div>
                  <h5 class="text-lg font-semibold text-gray-900">{{ item.donor.nama }}</h5>
                  <p class="text-sm text-gray-600">{{ item.donor.jenis }}</p>
                  <div class="mt-4 space-y-2">
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-600">Total Donasi:</span>
                      <span class="text-sm font-bold text-orange-600">{{ formatCurrency(item.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-600">Rata-rata:</span>
                      <span class="text-sm text-gray-900">{{ formatCurrency(item.avg_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-600">Skor Dampak:</span>
                      <span class="text-sm font-bold text-orange-600">{{ Math.round(item.impact_score) }}%</span>
                    </div>
                  </div>
                </div>
                <Crown class="w-8 h-8 text-yellow-500" />
              </div>
            </div>
          </div>
        </div>

        <!-- Seasonal Donors -->
        <div v-if="activeTab === 'seasonal'">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Donatur Musiman</h4>
          <div class="space-y-4">
            <div v-for="item in patterns.seasonal_donors" :key="item.donor.id" class="bg-blue-50 rounded-lg p-4 border">
              <div class="flex items-center justify-between">
                <div>
                  <h5 class="font-medium text-gray-900">{{ item.donor.nama }}</h5>
                  <p class="text-sm text-gray-600">{{ item.donor.jenis }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-blue-600">{{ formatSeasonalPattern(item.seasonal_pattern) }}</p>
                  <p class="text-xs text-gray-500">Bulan puncak: {{ formatPeakMonths(item.peak_months) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Declining Donors -->
        <div v-if="activeTab === 'declining'">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Donatur Menurun</h4>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-start">
              <AlertTriangle class="w-5 h-5 text-red-500 mt-0.5 mr-2" />
              <div>
                <h5 class="text-sm font-medium text-red-800">Perhatian Diperlukan</h5>
                <p class="text-sm text-red-700 mt-1">Donatur ini menunjukkan pola donasi yang menurun dan mungkin perlu direkrut kembali.</p>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donatur</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat Penurunan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donasi Terakhir</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat Risiko</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in patterns.declining_donors" :key="item.donor.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ item.donor.nama }}</div>
                      <div class="text-sm text-gray-500">{{ item.donor.jenis }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-red-600">-{{ Math.round(item.decline_rate) }}%</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(item.last_donation.tanggal_transaksi) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getRiskLevelClass(item.decline_rate)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getRiskLevel(item.decline_rate) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- One Time Donors -->
        <div v-if="activeTab === 'one_time'">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Donatur Satu Kali</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="item in patterns.one_time_donors" :key="item.donor.id" class="bg-gray-50 rounded-lg p-4">
              <div>
                <h5 class="font-medium text-gray-900">{{ item.donor.nama }}</h5>
                <p class="text-sm text-gray-600">{{ item.donor.jenis }}</p>
                <div class="mt-2">
                  <p class="text-lg font-bold text-gray-900">{{ formatCurrency(item.amount) }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(item.transaction_date) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { 
  Users, Crown, TrendingDown, Calendar, 
  AlertTriangle, Star, Clock 
} from 'lucide-vue-next'

interface DonorPatterns {
  regular_donors: any[]
  seasonal_donors: any[]
  one_time_donors: any[]
  high_value_donors: any[]
  declining_donors: any[]
}

const props = defineProps<{
  data: DonorPatterns | null
}>()

const activeTab = ref('regular')

const patterns = props.data || {
  regular_donors: [],
  seasonal_donors: [],
  one_time_donors: [],
  high_value_donors: [],
  declining_donors: []
}

const tabs = [
  { id: 'regular', name: 'Donatur Reguler' },
  { id: 'high_value', name: 'Nilai Tinggi' },
  { id: 'seasonal', name: 'Musiman' },
  { id: 'declining', name: 'Menurun' },
  { id: 'one_time', name: 'Satu Kali' }
]

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('id-ID')
}

const formatPatternName = (key: string): string => {
  const names = {
    'regular_donors': 'Donatur Reguler',
    'high_value_donors': 'Donatur Nilai Tinggi',
    'seasonal_donors': 'Donatur Musiman',
    'declining_donors': 'Donatur Menurun',
    'one_time_donors': 'Donatur Satu Kali'
  }
  return names[key] || key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatSeasonalPattern = (pattern: string): string => {
  const patterns = {
    'winter_peak': 'Puncak Musim Dingin',
    'ramadan_peak': 'Puncak Ramadan',
    'spring_peak': 'Puncak Musim Semi',
    'autumn_peak': 'Puncak Musim Gugur'
  }
  return patterns[pattern] || pattern
}

const formatPeakMonths = (months: number[]): string => {
  const monthNames = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
  ]
  return months.map(m => monthNames[m - 1]).join(', ')
}

const getPatternIcon = (key: string) => {
  const icons = {
    'regular_donors': Users,
    'high_value_donors': Crown,
    'seasonal_donors': Calendar,
    'declining_donors': TrendingDown,
    'one_time_donors': Clock
  }
  return icons[key] || Users
}

const getPatternColor = (key: string): string => {
  const colors = {
    'regular_donors': 'text-blue-600',
    'high_value_donors': 'text-yellow-600',
    'seasonal_donors': 'text-purple-600',
    'declining_donors': 'text-red-600',
    'one_time_donors': 'text-gray-600'
  }
  return colors[key] || 'text-gray-600'
}

const getTabCount = (tabId: string): number => {
  const key = tabId === 'high_value' ? 'high_value_donors' : `${tabId}_donors`
  return patterns[key]?.length || 0
}

const getConsistencyClass = (score: number): string => {
  if (score >= 80) return 'bg-green-100 text-green-800'
  if (score >= 60) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getRiskLevel = (declineRate: number): string => {
  if (declineRate >= 50) return 'Risiko Tinggi'
  if (declineRate >= 30) return 'Risiko Sedang'
  return 'Risiko Rendah'
}

const getRiskLevelClass = (declineRate: number): string => {
  if (declineRate >= 50) return 'bg-red-100 text-red-800'
  if (declineRate >= 30) return 'bg-yellow-100 text-yellow-800'
  return 'bg-orange-100 text-orange-800'
}
</script>