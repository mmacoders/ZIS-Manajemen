<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Laporan BAZNAS</h1>
      <p class="text-gray-600">Generate dan kelola laporan kepatuhan BAZNAS</p>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div class="flex items-center space-x-2">
        <button @click="showCreateModal = true" class="btn btn-primary">
          <Plus class="w-4 h-4 mr-2" />
          Buat Laporan Baru
        </button>
        <button @click="loadReports" class="btn btn-secondary">
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </button>
      </div>

      <!-- Filters -->
      <div class="flex items-center space-x-2">
        <select v-model="statusFilter" @change="loadReports" class="form-select text-sm">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="submitted">Submitted</option>
        </select>
        
        <select v-model="periodFilter" @change="loadReports" class="form-select text-sm">
          <option value="">Semua Periode</option>
          <option value="monthly">Bulanan</option>
          <option value="quarterly">Kuartalan</option>
          <option value="annual">Tahunan</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Reports List -->
    <div v-else-if="reports.length > 0" class="space-y-4">
      <div 
        v-for="report in reports" 
        :key="report.id"
        class="card hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="flex items-center space-x-3">
              <div>
                <h3 class="font-semibold text-gray-900">{{ report.judul }}</h3>
                <p class="text-sm text-gray-600">
                  {{ formatPeriodText(report.periode_type, report.periode_tahun, report.periode_bulan, report.periode_kuartal) }}
                </p>
              </div>
              <span :class="getStatusBadgeClass(report.status)">
                {{ getStatusText(report.status) }}
              </span>
            </div>
            
            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <p class="text-gray-600">Total Dana</p>
                <p class="font-medium text-gray-900">{{ formatCurrency(report.total_dana || 0) }}</p>
              </div>
              <div>
                <p class="text-gray-600">Compliance Score</p>
                <p class="font-medium text-gray-900">{{ report.compliance_score || 0 }}%</p>
              </div>
              <div>
                <p class="text-gray-600">Dibuat</p>
                <p class="font-medium text-gray-900">{{ formatDate(report.created_at) }}</p>
              </div>
              <div>
                <p class="text-gray-600">Dibuat oleh</p>
                <p class="font-medium text-gray-900">{{ report.created_by?.name || '-' }}</p>
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-2 ml-4">
            <button 
              @click="viewReport(report)" 
              class="btn btn-sm btn-secondary"
              title="Lihat Detail"
            >
              <Eye class="w-4 h-4" />
            </button>
            
            <button 
              v-if="report.status === 'draft'"
              @click="editReport(report)" 
              class="btn btn-sm btn-secondary"
              title="Edit"
            >
              <Edit class="w-4 h-4" />
            </button>
            
            <button 
              v-if="report.status === 'draft'"
              @click="approveReport(report)" 
              class="btn btn-sm btn-success"
              title="Approve"
            >
              <CheckCircle class="w-4 h-4" />
            </button>
            
            <button 
              v-if="report.status === 'approved'"
              @click="submitReport(report)" 
              class="btn btn-sm btn-primary"
              title="Submit ke BAZNAS"
            >
              <Send class="w-4 h-4" />
            </button>
            
            <button 
              @click="downloadPdf(report)" 
              class="btn btn-sm btn-primary"
              title="Download PDF"
            >
              <Download class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card text-center py-12">
      <FileText class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada laporan</h3>
      <p class="text-gray-500 mb-4">Mulai dengan membuat laporan BAZNAS pertama Anda</p>
      <button @click="showCreateModal = true" class="btn btn-primary">
        <Plus class="w-4 h-4 mr-2" />
        Buat Laporan Baru
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ editingReport ? 'Edit Laporan' : 'Buat Laporan Baru' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveReport">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul Laporan</label>
                <input 
                  v-model="form.judul" 
                  type="text" 
                  class="form-input" 
                  placeholder="Contoh: Laporan BAZNAS Kuartal I 2024"
                  required 
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select v-model="form.periode_type" @change="resetPeriodFields" class="form-select" required>
                  <option value="">Pilih Periode</option>
                  <option value="monthly">Bulanan</option>
                  <option value="quarterly">Kuartalan</option>
                  <option value="annual">Tahunan</option>
                </select>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                  <select v-model="form.periode_tahun" class="form-select" required>
                    <option value="">Pilih Tahun</option>
                    <option v-for="year in getYearOptions()" :key="year" :value="year">{{ year }}</option>
                  </select>
                </div>

                <div v-if="form.periode_type === 'monthly'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                  <select v-model="form.periode_bulan" class="form-select" required>
                    <option value="">Pilih Bulan</option>
                    <option v-for="(month, index) in months" :key="index + 1" :value="index + 1">{{ month }}</option>
                  </select>
                </div>

                <div v-if="form.periode_type === 'quarterly'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kuartal</label>
                  <select v-model="form.periode_kuartal" class="form-select" required>
                    <option value="">Pilih Kuartal</option>
                    <option value="1">Kuartal I (Jan-Mar)</option>
                    <option value="2">Kuartal II (Apr-Jun)</option>
                    <option value="3">Kuartal III (Jul-Sep)</option>
                    <option value="4">Kuartal IV (Oct-Des)</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea 
                  v-model="form.deskripsi" 
                  class="form-input" 
                  rows="3"
                  placeholder="Deskripsi laporan (opsional)"
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn btn-secondary">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-primary">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { 
  Plus, RefreshCw, Eye, Edit, CheckCircle, Send, Download, 
  FileText, X 
} from 'lucide-vue-next'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const showCreateModal = ref(false)
const editingReport = ref<any>(null)
const reports = ref<any[]>([])

// Filters
const statusFilter = ref('')
const periodFilter = ref('')

// Form
const form = ref({
  judul: '',
  periode_type: '',
  periode_tahun: new Date().getFullYear(),
  periode_bulan: null,
  periode_kuartal: null,
  deskripsi: ''
})

// Constants
const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

// Methods
const loadReports = async () => {
  try {
    isLoading.value = true
    const params = new URLSearchParams()
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (periodFilter.value) params.append('periode_type', periodFilter.value)
    
    const response = await axios.get(`/baznas-reports?${params.toString()}`)
    reports.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading reports:', error)
  } finally {
    isLoading.value = false
  }
}

const saveReport = async () => {
  try {
    isSubmitting.value = true
    if (editingReport.value) {
      await axios.put(`/baznas-reports/${editingReport.value.id}`, form.value)
    } else {
      await axios.post('/baznas-reports', form.value)
    }
    await loadReports()
    closeModal()
  } catch (error) {
    console.error('Error saving report:', error)
  } finally {
    isSubmitting.value = false
  }
}

const viewReport = (report: any) => {
  // Open report details in new tab or modal
  window.open(`/baznas-reports/${report.id}/pdf`, '_blank')
}

const editReport = (report: any) => {
  editingReport.value = report
  form.value = {
    judul: report.judul,
    periode_type: report.periode_type,
    periode_tahun: report.periode_tahun,
    periode_bulan: report.periode_bulan,
    periode_kuartal: report.periode_kuartal,
    deskripsi: report.deskripsi || ''
  }
  showCreateModal.value = true
}

const approveReport = async (report: any) => {
  try {
    await axios.post(`/baznas-reports/${report.id}/approve`)
    await loadReports()
  } catch (error) {
    console.error('Error approving report:', error)
  }
}

const submitReport = async (report: any) => {
  try {
    await axios.post(`/baznas-reports/${report.id}/submit`)
    await loadReports()
  } catch (error) {
    console.error('Error submitting report:', error)
  }
}

const downloadPdf = async (report: any) => {
  try {
    const response = await axios.get(`/baznas-reports/${report.id}/pdf`, {
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `Laporan-BAZNAS-${report.id}.pdf`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error downloading PDF:', error)
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingReport.value = null
  form.value = {
    judul: '',
    periode_type: '',
    periode_tahun: new Date().getFullYear(),
    periode_bulan: null,
    periode_kuartal: null,
    deskripsi: ''
  }
}

const resetPeriodFields = () => {
  form.value.periode_bulan = null
  form.value.periode_kuartal = null
}

const getYearOptions = () => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = currentYear; i >= currentYear - 5; i--) {
    years.push(i)
  }
  return years
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatPeriodText = (type: string, year: number, month?: number, quarter?: number) => {
  if (type === 'monthly' && month) {
    return `${months[month - 1]} ${year}`
  } else if (type === 'quarterly' && quarter) {
    return `Kuartal ${quarter} ${year}`
  } else if (type === 'annual') {
    return `Tahun ${year}`
  }
  return `${year}`
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    pending: 'Pending',
    approved: 'Approved',
    submitted: 'Submitted'
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'draft':
      return `${baseClass} bg-gray-100 text-gray-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'approved':
      return `${baseClass} bg-green-100 text-green-800`
    case 'submitted':
      return `${baseClass} bg-blue-100 text-blue-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

// Lifecycle
onMounted(() => {
  loadReports()
})
</script>