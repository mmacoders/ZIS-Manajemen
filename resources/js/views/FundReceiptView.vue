<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Penerimaan Dana</h1>
      <p class="text-gray-600 text-sm mt-1">Kelola penerimaan dana dari donatur dan UPZ</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium text-sm"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Penerimaan
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari penerimaan..." 
            class="form-input pl-10 text-sm"
            @keyup.enter="fetchFundReceipts"
          />
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
        </div>
        <button 
          @click="fetchFundReceipts"
          class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          <Search class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6 p-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status_penerimaan" class="form-select text-sm" @change="fetchFundReceipts">
            <option value="">Semua Status</option>
            <option value="diterima">Diterima</option>
            <option value="ditolak">Ditolak</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input text-sm" @change="fetchFundReceipts" />
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input text-sm" @change="fetchFundReceipts" />
        </div>
      </div>
    </div>

    <!-- Fund Receipts Table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Bukti</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber Dana</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Dana</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-4 py-3">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="fundReceipts.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                <Database class="w-10 h-10 mx-auto mb-2 text-gray-400" />
                <p class="text-sm">Tidak ada penerimaan dana ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="(receipt, index) in fundReceipts" :key="receipt && receipt.id ? receipt.id : index" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ receipt && receipt.nomor_bukti ? receipt.nomor_bukti : '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ receipt && receipt.tanggal_setor ? formatDate(receipt.tanggal_setor) : '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                <div v-if="receipt && receipt.muzakki">
                  <div class="font-medium">{{ receipt.muzakki.nama }}</div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ receipt.kategori_donatur ? getDonorCategoryText(receipt.kategori_donatur) : '' }}
                  </div>
                </div>
                <div v-else-if="receipt && receipt.upz">
                  UPZ: {{ receipt.upz.nama_upz }}
                </div>
                <div v-else>
                  {{ receipt && receipt.sumber_dana ? getSourceOfFundsText(receipt.sumber_dana) : '-' }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ receipt && receipt.jenis_dana ? receipt.jenis_dana : '-' }}</td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ receipt && receipt.jumlah_setor !== undefined ? formatCurrency(receipt.jumlah_setor) : '-' }}</td>
              <td class="px-4 py-3 text-sm">
                <span :class="getStatusClass(receipt && receipt.status_penerimaan ? receipt.status_penerimaan : '')" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ receipt && receipt.status_penerimaan ? getStatusText(receipt.status_penerimaan) : '-' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex space-x-1">
                  <button 
                    @click="openModal(receipt)"
                    class="text-blue-600 hover:text-blue-900 p-1.5 hover:bg-blue-50 rounded-full"
                    title="Edit"
                    :disabled="!receipt"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    @click="receipt && receipt.id ? deleteFundReceipt(receipt.id) : null"
                    class="text-red-600 hover:text-red-900 p-1.5 hover:bg-red-50 rounded-full"
                    title="Hapus"
                    :disabled="!receipt || !receipt.id"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-gray-700">
          Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} penerimaan
        </div>
        <div class="flex items-center space-x-1">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Sebelumnya
          </button>
          
          <span class="px-3 py-1.5 text-xs text-gray-700 bg-gray-50 rounded-md">
            {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md sm:max-w-2xl max-h-[95vh] overflow-y-auto" @click.stop>
        <div class="p-5">
          <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-800">
              {{ editingFundReceipt ? 'Edit Penerimaan Dana' : 'Tambah Penerimaan Dana' }}
            </h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100">
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="saveFundReceipt">
            <div class="space-y-4">
              <!-- Tanggal dan Status -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Setor *</label>
                  <input 
                    v-model="form.tanggal_setor" 
                    type="date" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Status *</label>
                  <select v-model="form.status_penerimaan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="pending">Pending</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                  </select>
                </div>
              </div>
              
              <!-- Sumber Dana dan Jenis Dana -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Sumber Dana *</label>
                  <select v-model="form.sumber_dana" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Sumber</option>
                    <option value="individu">Individu</option>
                    <option value="perusahaan">Perusahaan</option>
                    <option value="upz">UPZ</option>
                    <option value="instansi_pemerintah">Instansi Pemerintah</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                  <input 
                    v-if="form.sumber_dana === 'lainnya'"
                    v-model="form.custom_sumber_dana" 
                    type="text" 
                    class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                    placeholder="Sebutkan sumber dana"
                    required
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Jenis Dana *</label>
                  <select v-model="form.jenis_dana" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Jenis</option>
                    <option value="zakat">Zakat</option>
                    <option value="infaq">Infaq</option>
                    <option value="sedekah">Sedekah</option>
                    <option value="hibah">Hibah</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </div>
              </div>
              
              <!-- Donatur dengan Search -->
              <div class="space-y-2">
                <label class="block text-xs font-medium text-gray-700">Donatur</label>
                <div class="relative">
                  <input
                    v-model="donaturSearch"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                    placeholder="Cari donatur..."
                    @input="searchDonatur"
                  />
                  <div
                    v-if="donaturSearch && filteredDonaturList.length > 0"
                    class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-lg py-1 max-h-60 overflow-auto border border-gray-200"
                  >
                    <div
                      v-for="donatur in filteredDonaturList"
                      :key="donatur.id"
                      @click="selectDonatur(donatur)"
                      class="cursor-pointer px-4 py-2 hover:bg-blue-50 text-sm"
                    >
                      <div class="font-medium">{{ donatur.nama }}</div>
                      <div class="text-xs text-gray-500">{{ donatur.nik }}</div>
                    </div>
                  </div>
                </div>
                <!-- Selected Donatur Display -->
                <div v-if="selectedDonatur" class="mt-2 flex items-center justify-between bg-blue-50 p-2 rounded-lg border border-blue-100">
                  <div>
                    <div class="text-sm font-medium">{{ selectedDonatur.nama }}</div>
                    <div class="text-xs text-gray-500">{{ selectedDonatur.nik }}</div>
                  </div>
                  <button @click="clearDonatur" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-100">
                    <X class="w-4 h-4" />
                  </button>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Kategori Donatur</label>
                  <select v-model="form.kategori_donatur" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Kategori</option>
                    <option value="muzakki">Muzakki</option>
                    <option value="munfiq">Munfiq</option>
                    <option value="lembaga">Lembaga</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </div>
              </div>
              
              <!-- UPZ -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">UPZ</label>
                <select v-model="form.upz_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                  <option value="">Pilih UPZ (Opsional)</option>
                  <option v-for="upz in upzList" :key="upz && upz.id ? upz.id : null" :value="upz && upz.id ? upz.id : null" :disabled="!upz || !upz.id">
                    {{ upz && upz.nama_upz ? upz.nama_upz : 'Invalid UPZ' }}
                  </option>
                </select>
              </div>
              
              <!-- Jumlah Setor -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Jumlah Setor (Rp) *</label>
                <input 
                  v-model.number="form.jumlah_setor" 
                  type="number" 
                  required 
                  min="0" 
                  step="1000" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  placeholder="Masukkan nominal dana"
                />
              </div>
              
              <!-- Keterangan -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Keterangan</label>
                <textarea 
                  v-model="form.keterangan" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  placeholder="Keterangan tambahan (opsional)"
                ></textarea>
              </div>
              
              <!-- Bukti Transfer dengan Upload -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Bukti Transfer</label>
                <div class="flex flex-col sm:flex-row gap-2">
                  <div class="flex-1">
                    <input 
                      v-model="form.bukti_transfer" 
                      type="text" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                      placeholder="Link atau deskripsi bukti transfer"
                    />
                  </div>
                  <div>
                    <input 
                      type="file" 
                      ref="fileInput"
                      @change="handleFileUpload"
                      class="hidden"
                      accept=".jpg,.jpeg,.png,.pdf"
                    />
                    <button 
                      type="button"
                      @click="triggerFileInput"
                      class="w-full sm:w-auto px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition flex items-center justify-center text-sm"
                    >
                      <Upload class="w-4 h-4 mr-1.5" />
                      Upload
                    </button>
                  </div>
                </div>
                <div v-if="uploadingFile" class="mt-2 text-xs text-blue-600 flex items-center">
                  <Loader class="w-3.5 h-3.5 mr-1.5 animate-spin" />
                  Mengunggah file...
                </div>
                <div v-if="uploadedFileName" class="mt-2 text-xs text-green-600 flex items-center">
                  <Check class="w-3.5 h-3.5 mr-1.5" />
                  File terunggah: {{ uploadedFileName }}
                </div>
              </div>
            </div>
            
            <!-- Responsive Button Container -->
            <div class="flex flex-col-reverse sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="w-full sm:w-auto px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 text-sm">
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
import { Plus, Search, Edit, Trash2, Database, X, Upload, Loader, Check } from 'lucide-vue-next'

// State
const fundReceipts = ref<any[]>([])
const muzakkiList = ref<any[]>([])
const upzList = ref<any[]>([])
const showModal = ref(false)
const editingFundReceipt = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Donatur search
const donaturSearch = ref('')
const filteredDonaturList = ref<any[]>([])
const selectedDonatur = ref<any>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploadingFile = ref(false)
const uploadedFileName = ref('')

// Filters
const filters = ref({
  status_penerimaan: '',
  start_date: '',
  end_date: ''
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
  tanggal_setor: new Date().toISOString().split('T')[0],
  status_penerimaan: 'pending',
  sumber_dana: '',
  custom_sumber_dana: '',
  jenis_dana: '',
  jumlah_setor: null as number | null,
  muzakki_id: null as number | null,
  kategori_donatur: '',
  upz_id: null as number | null,
  keterangan: '',
  bukti_transfer: ''
})

// Lifecycle
onMounted(() => {
  fetchFundReceipts()
  fetchMuzakkiList()
  fetchUpzList()
})

// Methods
const fetchFundReceipts = async (page = 1) => {
  try {
    loading.value = true
    const params: any = {
      page,
      per_page: 10,
      ...filters.value
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/fund-receipts', { params })
    
    if (response.data.success) {
      fundReceipts.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      fundReceipts.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching fund receipts:', error)
    fundReceipts.value = []
  } finally {
    loading.value = false
  }
}

const fetchMuzakkiList = async () => {
  try {
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/donatur')
    if (response.data.success) {
      muzakkiList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching donatur list:', error)
    muzakkiList.value = []
  }
}

const fetchUpzList = async () => {
  try {
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/upz')
    if (response.data.success) {
      upzList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching UPZ list:', error)
    upzList.value = []
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchFundReceipts(page)
  }
}

// Donatur search methods
const searchDonatur = () => {
  if (donaturSearch.value.trim() === '') {
    filteredDonaturList.value = []
    return
  }
  
  filteredDonaturList.value = muzakkiList.value.filter((donatur: any) => 
    donatur.nama.toLowerCase().includes(donaturSearch.value.toLowerCase()) ||
    donatur.nik.includes(donaturSearch.value)
  ).slice(0, 10) // Limit to 10 results
}

const selectDonatur = (donatur: any) => {
  selectedDonatur.value = donatur
  form.value.muzakki_id = donatur.id
  donaturSearch.value = donatur.nama
  filteredDonaturList.value = []
}

const clearDonatur = () => {
  selectedDonatur.value = null
  form.value.muzakki_id = null
  donaturSearch.value = ''
}

// File upload method
const handleFileUpload = async (event: any) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']
  const maxSize = 5 * 1024 * 1024 // 5MB
  
  if (!allowedTypes.includes(file.type)) {
    alert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.')
    return
  }
  
  if (file.size > maxSize) {
    alert('Ukuran file terlalu besar. Maksimal 5MB.')
    return
  }
  
  uploadingFile.value = true
  uploadedFileName.value = file.name
  
  // In a real implementation, you would upload the file to the server here
  // For now, we'll just simulate the upload and store the file name
  try {
    // Simulate upload delay
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // In a real app, you would get a URL from the server after upload
    // For now, we'll just store the file name
    form.value.bukti_transfer = `uploaded_file_${Date.now()}_${file.name}`
    
    alert('File berhasil diunggah!')
  } catch (error) {
    console.error('Error uploading file:', error)
    alert('Gagal mengunggah file. Silakan coba lagi.')
  } finally {
    uploadingFile.value = false
  }
}

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

const openModal = (fundReceipt: any = null) => {
  editingFundReceipt.value = fundReceipt
  if (fundReceipt && fundReceipt.id) {
    // Create a copy of the fund receipt to avoid modifying the original
    form.value = { 
      tanggal_setor: fundReceipt.tanggal_setor || new Date().toISOString().split('T')[0],
      status_penerimaan: fundReceipt.status_penerimaan || 'pending',
      sumber_dana: fundReceipt.sumber_dana || '',
      custom_sumber_dana: '',
      jenis_dana: fundReceipt.jenis_dana || '',
      jumlah_setor: fundReceipt.jumlah_setor || null,
      muzakki_id: fundReceipt.muzakki_id || null,
      kategori_donatur: fundReceipt.kategori_donatur || '',
      upz_id: fundReceipt.upz_id || null,
      keterangan: fundReceipt.keterangan || '',
      bukti_transfer: fundReceipt.bukti_transfer || ''
    }
    
    // Set selected donatur if exists
    if (fundReceipt.muzakki_id && muzakkiList.value.length > 0) {
      const donatur = muzakkiList.value.find((d: any) => d.id === fundReceipt.muzakki_id)
      if (donatur) {
        selectedDonatur.value = donatur
        donaturSearch.value = donatur.nama
      }
    }
    
    // Handle custom source of funds
    const predefinedSources = ['individu', 'perusahaan', 'upz', 'instansi_pemerintah', 'lainnya']
    if (fundReceipt.sumber_dana && !predefinedSources.includes(fundReceipt.sumber_dana)) {
      form.value.custom_sumber_dana = fundReceipt.sumber_dana
      form.value.sumber_dana = 'lainnya'
    }
  } else {
    form.value = {
      tanggal_setor: new Date().toISOString().split('T')[0],
      status_penerimaan: 'pending',
      sumber_dana: '',
      custom_sumber_dana: '',
      jenis_dana: '',
      jumlah_setor: null,
      muzakki_id: null,
      kategori_donatur: '',
      upz_id: null,
      keterangan: '',
      bukti_transfer: ''
    }
    
    // Reset donatur selection
    selectedDonatur.value = null
    donaturSearch.value = ''
  }
  
  // Reset file upload state
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  uploadedFileName.value = ''
  uploadingFile.value = false
  
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingFundReceipt.value = null
  
  // Reset donatur search
  donaturSearch.value = ''
  filteredDonaturList.value = []
  selectedDonatur.value = null
  
  // Reset file upload state
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  uploadedFileName.value = ''
  uploadingFile.value = false
}

const saveFundReceipt = async () => {
  try {
    isSubmitting.value = true
    
    // Prepare form data
    const { custom_sumber_dana, ...formData } = { ...form.value }
    
    // If 'lainnya' is selected for sumber_dana, use the custom value
    if (form.value.sumber_dana === 'lainnya' && custom_sumber_dana) {
      formData.sumber_dana = custom_sumber_dana
    }
    
    // Check if we're editing or creating
    const isEditing = editingFundReceipt.value && editingFundReceipt.value.id
    
    const method = isEditing ? 'PUT' : 'POST'
    // Use relative path since axios baseURL already includes /api
    const url = isEditing 
      ? `/fund-receipts/${editingFundReceipt.value.id}` 
      : '/fund-receipts'
    
    const response = await axios({
      method,
      url,
      data: formData
    })
    
    if (response.data.success) {
      alert(isEditing ? 'Penerimaan dana berhasil diperbarui' : 'Penerimaan dana berhasil ditambahkan')
      closeModal()
      fetchFundReceipts(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving fund receipt:', error)
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      alert('Validation Error: ' + errors.join(', '))
    } else {
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    isSubmitting.value = false
  }
}

const deleteFundReceipt = async (id: number) => {
  // Check if id is valid
  if (!id || id <= 0) {
    console.error('Invalid ID for fund receipt deletion:', id)
    alert('ID penerimaan dana tidak valid')
    return
  }
  
  if (confirm('Apakah Anda yakin ingin menghapus penerimaan dana ini?')) {
    try {
      // Use relative path since axios baseURL already includes /api
      const response = await axios.delete(`/fund-receipts/${id}`)
      if (response.data.success) {
        alert('Penerimaan dana berhasil dihapus')
        fetchFundReceipts(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting fund receipt:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
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

const getStatusClass = (status: string) => {
  switch (status) {
    case 'diterima':
      return 'bg-green-100 text-green-800'
    case 'ditolak':
      return 'bg-red-100 text-red-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'diterima': 'Diterima',
    'ditolak': 'Ditolak', 
    'pending': 'Pending'
  }
  return statusMap[status] || status
}

const getDonorCategoryText = (category: string) => {
  const categoryMap: Record<string, string> = {
    'muzakki': 'Muzakki',
    'munfiq': 'Munfiq',
    'lembaga': 'Lembaga',
    'lainnya': 'Lainnya'
  }
  return categoryMap[category] || category
}

const getSourceOfFundsText = (source: string) => {
  const sourceMap: Record<string, string> = {
    'individu': 'Individu',
    'perusahaan': 'Perusahaan',
    'upz': 'UPZ',
    'instansi_pemerintah': 'Instansi Pemerintah',
    'lainnya': 'Lainnya'
  }
  return sourceMap[source] || source
}

</script>
