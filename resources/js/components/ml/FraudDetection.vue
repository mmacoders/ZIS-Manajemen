<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Analisis Deteksi Penipuan</h3>
      <div class="flex items-center space-x-2">
        <div class="text-sm text-gray-500">
          Tingkat Risiko: 
        </div>
        <span :class="getOverallRiskClass()" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
          {{ getOverallRiskLevel() }}
        </span>
      </div>
    </div>

    <!-- Risk Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-lg">
            <Users class="w-6 h-6 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Donatur Duplikat</p>
            <p class="text-2xl font-bold text-red-600">{{ data?.duplicate_donors?.length || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-orange-100 rounded-lg">
            <AlertTriangle class="w-6 h-6 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Transaksi Tidak Biasa</p>
            <p class="text-2xl font-bold text-orange-600">{{ data?.unusual_transactions?.length || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-lg">
            <UserX class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Mustahiq Mencurigakan</p>
            <p class="text-2xl font-bold text-yellow-600">{{ data?.suspicious_beneficiaries?.length || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <Clock class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Anomali Waktu</p>
            <p class="text-2xl font-bold text-purple-600">{{ data?.timing_anomalies?.length || 0 }}</p>
          </div>
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
                ? 'border-red-500 text-red-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <component :is="tab.icon" class="w-5 h-5 mr-2 inline" />
            {{ tab.name }} ({{ getTabCount(tab.id) }})
          </button>
        </nav>
      </div>

      <div class="p-6">
        <!-- Duplicate Donors -->
        <div v-if="activeTab === 'duplicates'">
          <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start">
              <AlertTriangle class="w-5 h-5 text-red-500 mt-0.5 mr-2" />
              <div>
                <h5 class="text-sm font-medium text-red-800">Peringatan Keamanan Kritis</h5>
                <p class="text-sm text-red-700 mt-1">Ditemukan beberapa donatur dengan nomor NIK yang identik. Ini memerlukan investigasi segera.</p>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Donasi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat Risiko</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="duplicate in data?.duplicate_donors" :key="duplicate.nik">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ duplicate.nik }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ duplicate.count }} akun</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(getTotalDonations(duplicate.zis_transactions)) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-red-100 text-red-800 inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      TINGGI
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Unusual Transactions -->
        <div v-if="activeTab === 'unusual'">
          <div class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <div class="flex items-start">
              <AlertTriangle class="w-5 h-5 text-orange-500 mt-0.5 mr-2" />
              <div>
                <h5 class="text-sm font-medium text-orange-800">Pola Transaksi Tidak Biasa</h5>
                <p class="text-sm text-orange-700 mt-1">Transaksi dengan jumlah yang jauh lebih tinggi dari rata-rata memerlukan verifikasi.</p>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donatur</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor Anomali</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in data?.unusual_transactions" :key="transaction.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ transaction.muzakki?.nama || 'Tidak Diketahui' }}</div>
                      <div class="text-sm text-gray-500">{{ transaction.muzakki?.jenis || 'N/A' }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-orange-600">
                    {{ formatCurrency(transaction.jumlah) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getAnomalyScoreClass(transaction.anomaly_score)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ Math.round(transaction.anomaly_score) }}x
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(transaction.tanggal_transaksi) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Suspicious Beneficiaries -->
        <div v-if="activeTab === 'beneficiaries'">
          <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-start">
              <AlertTriangle class="w-5 h-5 text-yellow-500 mt-0.5 mr-2" />
              <div>
                <h5 class="text-sm font-medium text-yellow-800">Penerima Frekuensi Tinggi</h5>
                <p class="text-sm text-yellow-700 mt-1">Mustahiq yang menerima distribusi terlalu sering mungkin perlu ditinjau.</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="beneficiary in data?.suspicious_beneficiaries" :key="beneficiary.id" class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
              <div class="flex items-start justify-between">
                <div>
                  <h5 class="font-medium text-gray-900">{{ beneficiary.nama }}</h5>
                  <p class="text-sm text-gray-600">{{ formatCategory(beneficiary.kategori) }}</p>
                  <div class="mt-2 space-y-1">
                    <p class="text-sm">
                      <span class="font-medium text-gray-700">Distribusi (6 bulan):</span>
                      <span class="ml-1 font-bold text-yellow-600">{{ beneficiary.distributions_count }}</span>
                    </p>
                    <p class="text-sm">
                      <span class="font-medium text-gray-700">Total Diterima:</span>
                      <span class="ml-1 text-gray-900">{{ formatCurrency(getTotalDistributions(beneficiary.distributions)) }}</span>
                    </p>
                  </div>
                </div>
                <UserX class="w-6 h-6 text-yellow-500" />
              </div>
            </div>
          </div>
        </div>

        <!-- Timing Anomalies -->
        <div v-if="activeTab === 'timing'">
          <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
            <div class="flex items-start">
              <Clock class="w-5 h-5 text-purple-500 mt-0.5 mr-2" />
              <div>
                <h5 class="text-sm font-medium text-purple-800">Waktu Transaksi Tidak Biasa</h5>
                <p class="text-sm text-purple-700 mt-1">Transaksi yang terjadi di luar jam kerja normal (06.00 - 22.00).</p>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donatur</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in data?.timing_anomalies" :key="transaction.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ transaction.muzakki?.nama || 'Tidak Diketahui' }}</div>
                      <div class="text-sm text-gray-500">{{ transaction.muzakki?.jenis || 'N/A' }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(transaction.jumlah) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-purple-600">
                      {{ formatTime(transaction.created_at) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(transaction.tanggal_transaksi) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Recommendations -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
      <div class="flex items-start">
        <Shield class="w-6 h-6 text-red-600 mt-1 mr-3" />
        <div>
          <h4 class="text-lg font-medium text-red-800 mb-2">Rekomendasi Keamanan</h4>
          <ul class="space-y-2 text-sm text-red-700">
            <li v-if="(data?.duplicate_donors?.length || 0) > 0">
              • Segera investigasi {{ data?.duplicate_donors?.length }} rekam donatur duplikat
            </li>
            <li v-if="(data?.unusual_transactions?.length || 0) > 0">
              • Tinjau {{ data?.unusual_transactions?.length }} transaksi dengan jumlah tidak biasa
            </li>
            <li v-if="(data?.suspicious_beneficiaries?.length || 0) > 0">
              • Verifikasi kelayakan {{ data?.suspicious_beneficiaries?.length }} mustahiq frekuensi tinggi
            </li>
            <li v-if="(data?.timing_anomalies?.length || 0) > 0">
              • Audit {{ data?.timing_anomalies?.length }} transaksi dengan pola waktu tidak biasa
            </li>
            <li v-if="getTotalAnomalies() === 0">
              • Tidak ada pola penipuan signifikan terdeteksi saat ini
            </li>
            <li>• Implementasikan verifikasi tambahan untuk transaksi di atas {{ formatCurrency(10000000) }}</li>
            <li>• Pertimbangkan untuk menerapkan batasan transaksi berbasis waktu</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { 
  AlertTriangle, Users, UserX, Clock, Shield
} from 'lucide-vue-next'

interface FraudDetection {
  duplicate_donors: any[]
  unusual_transactions: any[]
  suspicious_beneficiaries: any[]
  timing_anomalies: any[]
}

const props = defineProps<{
  data: FraudDetection | null
}>()

const activeTab = ref('duplicates')

const tabs = [
  { id: 'duplicates', name: 'Donatur Duplikat', icon: Users },
  { id: 'unusual', name: 'Transaksi Tidak Biasa', icon: AlertTriangle },
  { id: 'beneficiaries', name: 'Mustahiq Mencurigakan', icon: UserX },
  { id: 'timing', name: 'Anomali Waktu', icon: Clock }
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

const formatTime = (dateString: string): string => {
  return new Date(dateString).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
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

const getTabCount = (tabId: string): number => {
  const key = tabId === 'duplicates' ? 'duplicate_donors' : 
             tabId === 'unusual' ? 'unusual_transactions' :
             tabId === 'beneficiaries' ? 'suspicious_beneficiaries' :
             'timing_anomalies'
  return props.data?.[key]?.length || 0
}

const getTotalAnomalies = (): number => {
  return (
    (props.data?.duplicate_donors?.length || 0) +
    (props.data?.unusual_transactions?.length || 0) +
    (props.data?.suspicious_beneficiaries?.length || 0) +
    (props.data?.timing_anomalies?.length || 0)
  )
}

const getOverallRiskLevel = (): string => {
  const total = getTotalAnomalies()
  if (total >= 10) return 'RISIKO TINGGI'
  if (total >= 5) return 'RISIKO SEDANG'
  if (total > 0) return 'RISIKO RENDAH'
  return 'NORMAL'
}

const getOverallRiskClass = (): string => {
  const total = getTotalAnomalies()
  if (total >= 10) return 'bg-red-100 text-red-800'
  if (total >= 5) return 'bg-yellow-100 text-yellow-800'
  if (total > 0) return 'bg-orange-100 text-orange-800'
  return 'bg-green-100 text-green-800'
}

const getAnomalyScoreClass = (score: number): string => {
  if (score >= 20) return 'bg-red-100 text-red-800'
  if (score >= 10) return 'bg-orange-100 text-orange-800'
  return 'bg-yellow-100 text-yellow-800'
}

const getTotalDonations = (transactions: any[]): number => {
  return transactions?.reduce((sum, t) => sum + (t.jumlah || 0), 0) || 0
}

const getTotalDistributions = (distributions: any[]): number => {
  return distributions?.reduce((sum, d) => sum + (d.jumlah || 0), 0) || 0
}
</script>