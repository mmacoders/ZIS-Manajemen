<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Transaksi ZIS</h1>
      <p class="text-sm sm:text-base text-gray-600">Kelola transaksi Zakat, Infaq, dan Sedekah</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <button 
        @click="showCreateModal = true"
        class="btn-primary"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Transaksi
      </button>
      
      <button 
        @click="refreshData"
        :disabled="isLoading"
        class="btn-secondary"
      >
        <RefreshCw :class="isLoading ? 'animate-spin' : ''" class="w-4 h-4 mr-2" />
        Refresh
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Filter & Pencarian</h3>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="No. transaksi, nama muzakki..."
            class="form-input"
          />
        </div>
        
        <!-- ZIS Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jenis ZIS</label>
          <select v-model="filters.jenis_zis" class="form-select">
            <option value="">Semua Jenis</option>
            <option value="zakat">Zakat</option>
            <option value="infaq">Infaq</option>
            <option value="sedekah">Sedekah</option>
          </select>
        </div>
        
        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="verified">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>
        
        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
          <input 
            v-model="filters.date"
            type="date" 
            class="form-input"
          />
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Daftar Transaksi</h3>
        <div class="text-sm text-gray-600">
          Total: {{ pagination.total }} transaksi
        </div>
      </div>
      
      <div class="overflow-x-auto -mx-4 sm:mx-0">
        <div class="inline-block min-w-full align-middle">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  No. Transaksi
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Muzakki
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jenis ZIS
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jumlah
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="isLoading" v-for="n in 5" :key="n" class="animate-pulse">
                <td v-for="col in 7" :key="col" class="px-3 sm:px-6 py-4">
                  <div class="h-4 bg-gray-200 rounded"></div>
                </td>
              </tr>
              
              <tr v-else-if="transactions.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                  <p>Tidak ada transaksi ditemukan</p>
                </td>
              </tr>
              
              <tr v-else v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
                <td class="px-3 sm:px-6 py-4 text-sm font-medium text-gray-900">
                  {{ transaction.nomor_transaksi }}
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900">
                  <div class="max-w-32 sm:max-w-none truncate" :title="transaction.muzakki?.nama">
                    {{ transaction.muzakki?.nama || 'Unknown' }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900">
                  <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                        :class="getZisTypeClass(transaction.jenis_zis)">
                    {{ transaction.jenis_zis?.toUpperCase() }}
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm font-medium text-gray-900">
                  <div class="max-w-24 sm:max-w-none truncate">
                    {{ formatCurrency(transaction.jumlah) }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                  {{ formatDate(transaction.tanggal_transaksi) }}
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm">
                  <span :class="getStatusBadgeClass(transaction.status)" 
                        class="inline-flex px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                    {{ getStatusText(transaction.status) }}
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm">
                  <div class="flex space-x-2">
                    <button 
                      @click="viewTransaction(transaction)"
                      class="text-blue-600 hover:text-blue-800"
                      title="Lihat Detail"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                    <button 
                      @click="editTransaction(transaction)"
                      class="text-yellow-600 hover:text-yellow-800"
                      title="Edit"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal with OCR -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>
        
        <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
          <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              {{ showCreateModal ? 'Tambah Transaksi ZIS Baru' : 'Edit Transaksi ZIS' }}
            </h3>
            <p class="text-sm text-gray-600 mt-1">
              {{ showCreateModal ? 'Gunakan OCR untuk scan dokumen atau input manual' : 'Perbarui data transaksi' }}
            </p>
          </div>
          
          <form @submit.prevent="submitForm" class="p-4 sm:p-6">
            <!-- OCR Upload Section (Only for Create) -->
            <div v-if="showCreateModal" class="mb-6">
              <OCRUpload 
                v-model="ocrResult"
                @dataExtracted="handleOCRData"
              />
            </div>
            
            <!-- Form Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Transaction Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Transaksi *</label>
                <input 
                  v-model="form.nomor_transaksi"
                  type="text" 
                  required
                  class="form-input"
                  placeholder="Auto-generated jika kosong"
                />
              </div>
              
              <!-- Transaction Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi *</label>
                <input 
                  v-model="form.tanggal_transaksi"
                  type="date" 
                  required
                  class="form-input"
                />
              </div>
              
              <!-- Muzakki Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Muzakki *</label>
                <select v-model="form.muzakki_id" required class="form-select">
                  <option value="">Pilih Muzakki</option>
                  <option v-for="muzakki in muzakkiList" :key="muzakki.id" :value="muzakki.id">
                    {{ muzakki.nama }} - {{ muzakki.nik }}
                  </option>
                </select>
              </div>
              
              <!-- ZIS Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis ZIS *</label>
                <select v-model="form.jenis_zis" required class="form-select">
                  <option value="">Pilih Jenis ZIS</option>
                  <option value="zakat">Zakat</option>
                  <option value="infaq">Infaq</option>
                  <option value="sedekah">Sedekah</option>
                </select>
              </div>
              
              <!-- Amount -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp) *</label>
                <input 
                  v-model.number="form.jumlah"
                  type="number" 
                  required
                  min="1000"
                  step="1000"
                  class="form-input"
                  placeholder="Minimal Rp 1.000"
                />
              </div>
              
              <!-- Payment Method -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                <select v-model="form.metode_pembayaran" class="form-select">
                  <option value="tunai">Tunai</option>
                  <option value="transfer">Transfer Bank</option>
                  <option value="ovo">OVO</option>
                  <option value="gopay">GoPay</option>
                  <option value="dana">DANA</option>
                  <option value="shopee_pay">ShopeePay</option>
                </select>
              </div>
              
              <!-- Reference Number -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Referensi</label>
                <input 
                  v-model="form.no_referensi"
                  type="text" 
                  class="form-input"
                  placeholder="No. transfer, no. kuitansi, dll"
                />
              </div>
              
              <!-- Notes -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea 
                  v-model="form.keterangan"
                  rows="3"
                  class="form-textarea"
                  placeholder="Catatan tambahan (opsional)"
                ></textarea>
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
              <button 
                type="button" 
                @click="closeModal"
                class="btn-secondary"
              >
                Batal
              </button>
              
              <button 
                type="submit" 
                :disabled="isSubmitting"
                class="btn-primary"
              >
                <div v-if="isSubmitting" class="flex items-center">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Menyimpan...
                </div>
                <span v-else>{{ showCreateModal ? 'Simpan Transaksi' : 'Update Transaksi' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { 
  Plus, RefreshCw, Eye, Edit, Database
} from 'lucide-vue-next'
import OCRUpload from '@/components/OCRUpload.vue'
import type { OCRResult } from '@/services/ocrService'
import axios from 'axios'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const transactions = ref<any[]>([])
const muzakkiList = ref<any[]>([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedTransaction = ref<any>(null)
const ocrResult = ref<OCRResult | null>(null)

// Filters
const filters = ref({
  search: '',
  jenis_zis: '',
  status: '',
  date: ''
})

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

// Form
const form = ref({
  nomor_transaksi: '',
  tanggal_transaksi: new Date().toISOString().split('T')[0],
  muzakki_id: '',
  jenis_zis: '',
  jumlah: 0,
  metode_pembayaran: 'tunai',
  no_referensi: '',
  keterangan: ''
})

// Watchers
watch([filters], () => {
  pagination.value.current_page = 1
  fetchTransactions()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchTransactions()
  fetchMuzakkiList()
})

// Methods
const fetchTransactions = async (page = 1) => {
  try {
    isLoading.value = true
    const params: any = {
      page,
      per_page: 10,
      ...filters.value
    }

    const response = await axios.get('/api/zis-transactions', { params })
    
    if (response.data.success) {
      transactions.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      transactions.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching transactions:', error)
    transactions.value = []
  } finally {
    isLoading.value = false
  }
}

const fetchMuzakkiList = async () => {
  try {
    const response = await axios.get('/api/muzakki')
    if (response.data.success) {
      muzakkiList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching muzakki list:', error)
    muzakkiList.value = []
  }
}

const handleOCRData = (extractedData: any) => {
  console.log('Received OCR data:', extractedData)
  
  // Auto-populate form with OCR data
  if (extractedData.amount) {
    form.value.jumlah = extractedData.amount
    console.log('Set Amount:', extractedData.amount)
  }
  
  if (extractedData.date) {
    form.value.tanggal_transaksi = extractedData.date
    console.log('Set Date:', extractedData.date)
  }
  
  if (extractedData.referenceNumber) {
    form.value.no_referensi = extractedData.referenceNumber
    console.log('Set Reference Number:', extractedData.referenceNumber)
  }
  
  if (extractedData.bankName) {
    form.value.metode_pembayaran = 'transfer'
    console.log('Set Payment Method: transfer')
  }
  
  // Auto-populate keterangan with extracted info
  const notes = []
  if (extractedData.donorName) notes.push(`Pengirim: ${extractedData.donorName}`)
  if (extractedData.bankName) notes.push(`Bank: ${extractedData.bankName}`)
  if (extractedData.accountNumber) notes.push(`Rekening: ${extractedData.accountNumber}`)
  
  if (notes.length > 0) {
    form.value.keterangan = `OCR: ${notes.join(', ')}`
    console.log('Set Keterangan:', form.value.keterangan)
  }
  
  // Show success message
  alert('Data berhasil diisi dari hasil OCR!')
}

const submitForm = async () => {
  try {
    isSubmitting.value = true
    
    const method = showCreateModal.value ? 'POST' : 'PUT'
    const url = showCreateModal.value 
      ? '/api/zis-transactions'
      : `/api/zis-transactions/${selectedTransaction.value.id}`
    
    await axios({
      method,
      url,
      data: form.value
    })
    
    closeModal()
    fetchTransactions()
    
  } catch (error) {
    console.error('Error submitting form:', error)
  } finally {
    isSubmitting.value = false
  }
}

const viewTransaction = (transaction: any) => {
  // Implementation for view modal
  console.log('View transaction:', transaction)
}

const editTransaction = (transaction: any) => {
  selectedTransaction.value = transaction
  form.value = {
    nomor_transaksi: transaction.nomor_transaksi,
    tanggal_transaksi: transaction.tanggal_transaksi,
    muzakki_id: transaction.muzakki_id,
    jenis_zis: transaction.jenis_zis,
    jumlah: transaction.jumlah,
    metode_pembayaran: transaction.metode_pembayaran || 'tunai',
    no_referensi: transaction.no_referensi || '',
    keterangan: transaction.keterangan || ''
  }
  showEditModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedTransaction.value = null
  ocrResult.value = null
  
  // Reset form
  form.value = {
    nomor_transaksi: '',
    tanggal_transaksi: new Date().toISOString().split('T')[0],
    muzakki_id: '',
    jenis_zis: '',
    jumlah: 0,
    metode_pembayaran: 'tunai',
    no_referensi: '',
    keterangan: ''
  }
}

const refreshData = () => {
  fetchTransactions()
  fetchMuzakkiList()
}

// Utility functions
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'verified':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'pending': 'Menunggu',
    'verified': 'Terverifikasi', 
    'rejected': 'Ditolak'
  }
  return statusMap[status] || status
}

const getZisTypeClass = (jenis: string) => {
  switch (jenis) {
    case 'zakat':
      return 'bg-blue-100 text-blue-800'
    case 'infaq':
      return 'bg-green-100 text-green-800'
    case 'sedekah':
      return 'bg-purple-100 text-purple-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}
</script>

<style scoped>
/* Additional responsive styles */
@media (max-width: 639px) {
  .btn-primary,
  .btn-secondary {
    @apply w-full justify-center;
  }
  
  .card {
    @apply mx-0 rounded-lg;
  }
}
</style>