<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Transaksi ZIS</h1>
      <p class="text-gray-600">Kelola transaksi Zakat, Infaq, dan Sedekah</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <Search class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="No. transaksi, nama muzakki..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchTransactions()"
            />
          </div>
          
          <!-- ZIS Type -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis ZIS</label>
            <Tag class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <select v-model="filters.jenis_zis" class="form-input pl-10 w-full">
              <option value="">Semua Jenis</option>
              <option value="zakat">Zakat</option>
              <option value="infaq">Infaq</option>
              <option value="sedekah">Sedekah</option>
            </select>
          </div>
          
          <!-- Status -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <Settings class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <select v-model="filters.status" class="form-input pl-10 w-full">
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="verified">Terverifikasi</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
          
          <!-- Date Range -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <Calendar class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <input 
              v-model="filters.date"
              type="date" 
              class="form-input pl-10 w-full"
            />
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button
              @click="() => fetchTransactions()"
              class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
              title="Cari Data"
            >
              <Search class="w-4 h-4" />
            </button>
            
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="inline-flex items-center px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
              title="Reset Filter"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
          
          <div class="flex flex-wrap items-center gap-2">
            <button 
              @click="refreshData"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 font-medium"
            >
              <RefreshCw :class="isLoading ? 'animate-spin' : ''" class="w-4 h-4 mr-2" />
              Refresh
            </button>
            
            <button 
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
            >
              <Plus class="w-4 h-4 mr-2" />
              Tambah Transaksi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Hash class="w-4 h-4 mr-2" />
                  No. Transaksi
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <User class="w-4 h-4 mr-2" />
                  Muzakki
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Tag class="w-4 h-4 mr-2" />
                  Jenis ZIS
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Coins class="w-4 h-4 mr-2" />
                  Jumlah
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Calendar class="w-4 h-4 mr-2" />
                  Tanggal
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Settings class="w-4 h-4 mr-2" />
                  Status
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Settings class="w-4 h-4 mr-2" />
                  Aksi
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="isLoading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-6 py-4">
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
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ transaction.nomor_transaksi }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate" :title="transaction.muzakki?.nama">
                  {{ transaction.muzakki?.nama || 'Unknown' }}
                </div>
                <div v-if="transaction.muzakki" class="text-xs text-gray-500">
                  NIK: {{ transaction.muzakki.nik || 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                      :class="getZisTypeClass(transaction.jenis_zis)">
                  {{ transaction.jenis_zis?.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(transaction.jumlah) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(transaction.tanggal_transaksi) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(transaction.status)" 
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                  {{ getStatusText(transaction.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button 
                  @click="viewTransaction(transaction)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Lihat Detail"
                >
                  <Eye class="w-4 h-4" />
                </button>
                <button 
                  @click="editTransaction(transaction)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 hover:bg-yellow-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} transaksi
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="fetchTransactions(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sebelumnya
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-700">
              Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            </span>
            
            <button
              @click="fetchTransactions(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
              {{ showCreateModal ? 'Tambah Transaksi ZIS Baru' : 'Edit Transaksi ZIS' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <form @submit.prevent="submitForm">
            <!-- Toggle OCR Button -->
            <div v-if="showCreateModal" class="mb-6">
              <button 
                type="button"
                @click="showOCRSection = !showOCRSection"
                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                <Scan class="w-4 h-4 mr-2" />
                {{ showOCRSection ? 'Sembunyikan OCR' : 'Scan Bukti Transfer' }}
              </button>
            </div>
            
            <!-- OCR Upload Section (Only for Create) -->
            <div v-if="showCreateModal && showOCRSection" class="mb-6">
              <OCRUpload 
                v-model="ocrResult"
                @dataExtracted="handleOCRData"
                :showLanguageSelector="true"
                :enableMultiLanguage="true"
              />
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Transaction Number -->
              <div class="sm:col-span-2">
                <label class="form-label">No. Transaksi *</label>
                <div class="relative">
                  <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input 
                    v-model="form.nomor_transaksi"
                    type="text" 
                    required
                    class="form-input pl-10"
                    placeholder="Auto-generated jika kosong"
                  />
                </div>
              </div>
              
              <!-- Transaction Date -->
              <div>
                <label class="form-label">Tanggal Transaksi *</label>
                <div class="relative">
                  <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input 
                    v-model="form.tanggal_transaksi"
                    type="date" 
                    required
                    class="form-input pl-10"
                  />
                </div>
              </div>
              
              <!-- Muzakki Selection -->
              <div>
                <label class="form-label">Muzakki *</label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select 
                    v-model="form.muzakki_id" 
                    required 
                    class="form-input pl-10"
                    :disabled="muzakkiLoading"
                  >
                    <option value="">Pilih Muzakki</option>
                    <option 
                      v-for="muzakki in muzakkiList" 
                      :key="muzakki.id" 
                      :value="muzakki.id"
                    >
                      {{ muzakki.nama }} - {{ muzakki.nik }}
                    </option>
                  </select>
                  <div v-if="muzakkiLoading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                  </div>
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  Total muzakki: {{ muzakkiList.length }}
                </div>
                <button 
                  type="button" 
                  @click="fetchMuzakkiList"
                  class="mt-1 text-xs text-blue-600 hover:text-blue-800"
                >
                  Refresh Daftar Muzakki
                </button>
              </div>
              
              <!-- ZIS Type -->
              <div>
                <label class="form-label">Jenis ZIS *</label>
                <div class="relative">
                  <Tag class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.jenis_zis" required class="form-input pl-10">
                    <option value="">Pilih Jenis ZIS</option>
                    <option value="zakat">Zakat</option>
                    <option value="infaq">Infaq</option>
                    <option value="sedekah">Sedekah</option>
                  </select>
                </div>
              </div>
              
              <!-- Amount -->
              <div>
                <label class="form-label">Jumlah (Rp) *</label>
                <div class="relative">
                  <Coins class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input 
                    v-model.number="form.jumlah"
                    type="number" 
                    required
                    min="1000"
                    step="1000"
                    class="form-input pl-10"
                    placeholder="Minimal Rp 1.000"
                  />
                </div>
              </div>
              
              <!-- Payment Method -->
              <div>
                <label class="form-label">Metode Pembayaran</label>
                <div class="relative">
                  <CreditCard class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.metode_pembayaran" class="form-input pl-10">
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="ovo">OVO</option>
                    <option value="gopay">GoPay</option>
                    <option value="dana">DANA</option>
                    <option value="shopee_pay">ShopeePay</option>
                  </select>
                </div>
              </div>
              
              <!-- Reference Number -->
              <div class="sm:col-span-2">
                <label class="form-label">No. Referensi</label>
                <div class="relative">
                  <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input 
                    v-model="form.no_referensi"
                    type="text" 
                    class="form-input pl-10"
                    placeholder="No. transfer, no. kuitansi, dll"
                  />
                </div>
              </div>
              
              <!-- Notes -->
              <div class="sm:col-span-2">
                <label class="form-label">Keterangan</label>
                <div class="relative">
                  <FileText class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea 
                    v-model="form.keterangan"
                    rows="3"
                    class="form-input pl-10"
                    placeholder="Catatan tambahan (opsional)"
                  ></textarea>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn btn-secondary w-full sm:w-auto">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-primary w-full sm:w-auto">
                {{ isSubmitting ? 'Menyimpan...' : (showCreateModal ? 'Simpan Transaksi' : 'Update Transaksi') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { 
  Plus, RefreshCw, Eye, Edit, Database, Scan,
  Search, Tag, Calendar, Coins, Settings, Hash, User, CreditCard, FileText, X
} from 'lucide-vue-next'
import OCRUpload from '@/components/OCRUpload.vue'
import type { OCRResult } from '@/services/ocrService'
import axios from 'axios'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const muzakkiLoading = ref(false)
const showOCRSection = ref(false)
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
  total: 0,
  per_page: 10
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

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.jenis_zis || filters.value.status || filters.value.date
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
      per_page: pagination.value.per_page,
      ...filters.value
    }

    console.log('Fetching transactions with params:', params)
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/zis-transactions', { params })
    
    console.log('Transactions API Response:', response)
    
    if (response.data && response.data.success) {
      // Handle paginated response from ZisTransactionController
      if (response.data.data && response.data.data.data) {
        // Paginated response: data.data.data contains the actual transaction records
        transactions.value = response.data.data.data || []
        pagination.value = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          from: response.data.data.from,
          to: response.data.data.to,
          total: response.data.data.total,
          per_page: response.data.data.per_page
        }
      } else {
        // Non-paginated response
        transactions.value = response.data.data || []
        // Reset pagination for non-paginated response
        pagination.value = {
          current_page: 1,
          last_page: 1,
          from: 1,
          to: transactions.value.length,
          total: transactions.value.length,
          per_page: transactions.value.length || 10
        }
      }
      
      console.log('Transactions List:', transactions.value)
    } else {
      transactions.value = []
      const errorMessage = response.data?.message || 'Unknown error occurred'
      console.error('Failed to fetch transactions:', errorMessage)
      // Show user-friendly error message
      alert('Gagal memuat data transaksi: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error fetching transactions:', error)
    console.error('Error response:', error.response)
    
    // Check if it's an authentication error
    if (error.response?.status === 401) {
      console.error('Authentication required to fetch transactions')
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
      // You might want to redirect to login
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk mengakses data transaksi.')
    } else if (error.response?.status === 404) {
      console.error('API endpoint not found')
      alert('Endpoint API tidak ditemukan. Silakan hubungi administrator.')
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Terjadi kesalahan saat memuat data transaksi'
      alert('Gagal memuat data transaksi: ' + errorMessage)
    }
    transactions.value = []
  } finally {
    isLoading.value = false
  }
}

const fetchMuzakkiList = async () => {
  try {
    muzakkiLoading.value = true
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/muzakki', {
      params: {
        per_page: 100 // Fetch more muzakki for the dropdown
      }
    })
    
    console.log('Muzakki API Response:', response)
    
    if (response.data && response.data.success) {
      // Handle paginated response from MuzakkiController
      if (response.data.data && response.data.data.data) {
        // Paginated response: data.data.data contains the actual muzakki records
        muzakkiList.value = response.data.data.data || []
      } else if (response.data.data) {
        // Non-paginated response: data.data contains the muzakki records
        muzakkiList.value = response.data.data || []
      } else {
        muzakkiList.value = []
      }
      
      console.log('Muzakki List:', muzakkiList.value)
      
      // If no muzakki found, show a message
      if (muzakkiList.value.length === 0) {
        console.warn('No muzakki data found')
      }
    } else {
      muzakkiList.value = []
      const errorMessage = response.data?.message || 'Unknown error occurred'
      console.error('Failed to fetch muzakki list:', errorMessage)
      // Show user-friendly error message
      alert('Gagal memuat data muzakki: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error fetching muzakki list:', error)
    console.error('Error response:', error.response)
    
    // Check if it's an authentication error
    if (error.response?.status === 401) {
      console.error('Authentication required to fetch muzakki data')
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
      // You might want to redirect to login
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk mengakses data muzakki.')
    } else if (error.response?.status === 404) {
      console.error('API endpoint not found')
      alert('Endpoint API tidak ditemukan. Silakan hubungi administrator.')
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Terjadi kesalahan saat memuat data muzakki'
      alert('Gagal memuat data muzakki: ' + errorMessage)
    }
    muzakkiList.value = []
  } finally {
    muzakkiLoading.value = false
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
    // Use relative paths since axios baseURL already includes /api
    const url = showCreateModal.value 
      ? '/zis-transactions'
      : `/zis-transactions/${selectedTransaction.value.id}`
    
    // Validate that muzakki_id is selected and is a valid number
    if (!form.value.muzakki_id) {
      alert('Harap pilih muzakki terlebih dahulu')
      isSubmitting.value = false
      return
    }
    
    // Ensure muzakki_id is sent as a number if it's a string
    const formData = {
      ...form.value,
      muzakki_id: isNaN(form.value.muzakki_id) ? form.value.muzakki_id : Number(form.value.muzakki_id)
    }
    
    console.log('Submitting form data:', formData)
    const response = await axios({
      method,
      url,
      data: formData
    })
    
    console.log('Form submission response:', response)
    
    if (response.data && response.data.success) {
      closeModal()
      fetchTransactions()
      alert(showCreateModal.value ? 'Transaksi berhasil ditambahkan!' : 'Transaksi berhasil diperbarui!')
    } else {
      const errorMessage = response.data?.message || response.data?.error || 'Gagal menyimpan transaksi'
      alert('Terjadi kesalahan: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error submitting form:', error)
    console.error('Error response:', error.response)
    
    // Check if it's an authentication error
    if (error.response?.status === 401) {
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
      // You might want to redirect to login
      return
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk menyimpan data transaksi.')
    } else if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data?.errors
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat()
        alert('Validasi gagal:\n' + errorMessages.join('\n'))
      } else {
        const errorMessage = error.response.data?.message || 'Validasi gagal'
        alert('Validasi gagal: ' + errorMessage)
      }
    } else {
      const errorMessage = error.response?.data?.message || error.response?.data?.error || error.message || 'Terjadi kesalahan saat menyimpan transaksi'
      alert('Error: ' + errorMessage)
    }
  } finally {
    isSubmitting.value = false
  }
}

const viewTransaction = (transaction: any) => {
  // Implementation for view modal
  console.log('View transaction:', transaction)
  alert(`Detail transaksi: ${transaction.nomor_transaksi}`)
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
  showOCRSection.value = false
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  showOCRSection.value = false
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

const clearFilters = () => {
  filters.value = {
    search: '',
    jenis_zis: '',
    status: '',
    date: ''
  }
  fetchTransactions(1)
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
  
  table {
    font-size: 0.875rem;
  }
  
  th, td {
    padding: 0.5rem 0.75rem;
  }
  
  .max-w-32 {
    max-width: 8rem;
  }
  
  .max-w-24 {
    max-width: 6rem;
  }
}

/* Extra small devices */
@media (max-width: 480px) {
  .card {
    @apply p-3;
  }
  
  th, td {
    padding: 0.25rem 0.5rem;
  }
  
  .truncate {
    max-width: 6rem;
  }
}
</style>