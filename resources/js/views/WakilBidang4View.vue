<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard Wakil Bidang IV - SDM & SOP</h1>
      <p class="text-gray-600">Fokus: Kepatuhan SOP, Status SDM, dan Aset</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardData">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <StatCard
          title="Total Staff"
          :value="dashboardData.summary.total_staff?.toLocaleString() || '0'"
          description="Jumlah karyawan"
          icon="UserGroupIcon"
          color="blue"
        />
        <StatCard
          title="Staff Aktif"
          :value="dashboardData.summary.active_staff?.toLocaleString() || '0'"
          description="Karyawan aktif"
          icon="UserIcon"
          color="green"
        />
        <StatCard
          title="Total Aset"
          :value="dashboardData.summary.total_assets?.toLocaleString() || '0'"
          description="Aset terdaftar"
          icon="BuildingOfficeIcon"
          color="yellow"
        />
        <StatCard
          title="Total Dokumen"
          :value="dashboardData.summary.total_documents?.toLocaleString() || '0'"
          description="Dokumen tersimpan"
          icon="DocumentTextIcon"
          color="red"
        />
        <StatCard
          title="Dokumen Pending"
          :value="dashboardData.summary.pending_documents?.toLocaleString() || '0'"
          description="Menunggu proses"
          icon="ClockIcon"
          color="purple"
        />
      </div>

      <!-- Compliance Metrics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Kepatuhan SOP - Manajemen Surat</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-600">Total Surat</span>
              <span class="font-medium">{{ dashboardData.compliance_metrics.letter_management.total_letters }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Diproses</span>
              <span class="font-medium text-green-600">{{ dashboardData.compliance_metrics.letter_management.processed_letters }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tingkat Pemrosesan</span>
              <span class="font-medium" :class="getComplianceClass(dashboardData.compliance_metrics.letter_management.processing_rate)">
                {{ dashboardData.compliance_metrics.letter_management.processing_rate }}%
              </span>
            </div>
            <div class="pt-2">
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full" 
                  :class="getComplianceBarClass(dashboardData.compliance_metrics.letter_management.processing_rate)"
                  :style="{ width: dashboardData.compliance_metrics.letter_management.processing_rate + '%' }">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Kepatuhan Dokumen</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-600">Total Dokumen</span>
              <span class="font-medium">{{ dashboardData.compliance_metrics.document_compliance.total_documents }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tervalidasi</span>
              <span class="font-medium text-green-600">{{ dashboardData.compliance_metrics.document_compliance.validated_documents }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tingkat Kepatuhan</span>
              <span class="font-medium" :class="getComplianceClass(dashboardData.compliance_metrics.document_compliance.compliance_rate)">
                {{ dashboardData.compliance_metrics.document_compliance.compliance_rate }}%
              </span>
            </div>
            <div class="pt-2">
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full" 
                  :class="getComplianceBarClass(dashboardData.compliance_metrics.document_compliance.compliance_rate)"
                  :style="{ width: dashboardData.compliance_metrics.document_compliance.compliance_rate + '%' }">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Asset Management -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Manajemen Aset</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="border rounded-lg p-4">
            <h4 class="font-medium text-gray-900 mb-2">Total Aset</h4>
            <p class="text-2xl font-bold text-blue-600">{{ dashboardData.asset_management.total_assets }}</p>
            <p class="text-sm text-gray-500 mt-1">Nilai Total: {{ formatCurrency(dashboardData.asset_management.total_asset_value) }}</p>
          </div>
          
          <div class="border rounded-lg p-4">
            <h4 class="font-medium text-gray-900 mb-2">Aset per Status</h4>
            <div class="space-y-2">
              <div v-for="(asset, status) in dashboardData.asset_management.assets_by_status" :key="status" class="flex justify-between">
                <span class="text-sm capitalize">{{ status }}</span>
                <span class="text-sm font-medium">{{ asset.count }}</span>
              </div>
            </div>
          </div>
          
          <div class="border rounded-lg p-4">
            <h4 class="font-medium text-gray-900 mb-2">Aset per Kategori</h4>
            <div class="space-y-2">
              <div v-for="asset in dashboardData.asset_management.assets_by_category" :key="asset.kategori" class="flex justify-between">
                <span class="text-sm">{{ asset.kategori }}</span>
                <span class="text-sm font-medium">{{ asset.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
        <div class="space-y-3">
          <div
            v-for="activity in dashboardData.recent_activities"
            :key="activity.id"
            class="flex items-start py-2 border-b border-gray-100 last:border-b-0"
          >
            <div class="flex-shrink-0 mt-1">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-blue-600" />
              </div>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
              <p v-if="activity.position || activity.subject || activity.value" class="text-sm text-gray-500">
                {{ activity.position || activity.subject || formatCurrency(activity.value) }}
              </p>
              <p class="text-xs text-gray-400">{{ formatDate(activity.date) }}</p>
            </div>
            <span v-if="activity.status" :class="getStatusBadgeClass(activity.status)">
              {{ activity.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="flex items-center justify-center py-12">
      <div class="text-center">
        <p class="text-gray-500">Tidak ada data dashboard tersedia</p>
        <button 
          @click="fetchDashboardData"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Muat Ulang Data
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { 
  UserGroupIcon, 
  UserIcon, 
  BuildingOfficeIcon, 
  DocumentTextIcon,
  ClockIcon,
  InboxIcon,
  PaperClipIcon,
  CreditCardIcon
} from '@heroicons/vue/24/solid'
import StatCard from '@/components/dashboard/StatCard.vue'

const dashboardData = ref<any>(null)
const isLoading = ref(false)

const fetchDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/wakil4/dashboard')
    dashboardData.value = response.data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    isLoading.value = false
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getComplianceClass = (rate: number) => {
  if (rate >= 90) return 'text-green-600'
  if (rate >= 75) return 'text-yellow-600'
  return 'text-red-600'
}

const getComplianceBarClass = (rate: number) => {
  if (rate >= 90) return 'bg-green-600'
  if (rate >= 75) return 'bg-yellow-600'
  return 'bg-red-600'
}

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'staff':
      return UserIcon
    case 'incoming_letter':
      return InboxIcon
    case 'asset':
      return BuildingOfficeIcon
    case 'document':
      return PaperClipIcon
    default:
      return DocumentTextIcon
  }
}

const getStatusBadgeClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'aktif':
    case 'processed':
    case 'sent':
    case 'validated':
      return `${baseClass} bg-green-100 text-green-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'rejected':
      return `${baseClass} bg-red-100 text-red-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>