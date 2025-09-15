<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Data UPZ</h1>
      <p class="text-gray-600">Kelola Unit Pengumpul Zakat</p>
    </div>

    <!-- Actions -->
    <div class="mb-6">
      <!-- Search Filters -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search by Name/Kode -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama/Kode</label>
            <User class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Masukkan nama atau kode UPZ..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchUpz()"
            />
          </div>
          
          <!-- Search by Address -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Alamat</label>
            <MapPin class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchAddress"
              type="text"
              placeholder="Masukkan alamat..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchUpz()"
            />
          </div>
          
          <!-- Filter by Validation Status -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Validasi</label>
            <CheckCircle class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <select
              v-model="filterValidasi"
              class="form-input pl-10 w-full"
              @change="() => fetchUpz()"
            >
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="verified">Terverifikasi</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
          
          <!-- Filter by Deposit Type -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Setoran</label>
            <Coins class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <select
              v-model="filterJenisSetoran"
              class="form-input pl-10 w-full"
              @change="() => fetchUpz()"
            >
              <option value="">Semua Jenis</option>
              <option value="zakat">Zakat</option>
              <option value="infaq">Infaq</option>
              <option value="sedekah">Sedekah</option>
            </select>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button
              @click="() => fetchUpz()"
              class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
              title="Cari Data"
            >
              <Search class="w-4 h-4" />
            </button>
            
            <button
              v-if="hasActiveFilters"
              @click="clearSearch"
              class="inline-flex items-center px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
              title="Reset Filter"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
          
          <button
            @click="openModal()"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
          >
            <Plus class="w-4 h-4 mr-2" />
            Tambah UPZ
          </button>
        </div>
        
        <!-- Search Results Info -->
        <div v-if="hasActiveFilters" class="mt-3 text-sm text-gray-600">
          <div class="flex items-center">
            <span class="font-medium">{{ upzList.length }} hasil ditemukan</span>
            <div class="ml-2 flex flex-wrap gap-1">
              <span v-if="searchQuery" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                Nama/Kode: "{{ searchQuery }}"
              </span>
              <span v-if="searchAddress" class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                Alamat: "{{ searchAddress }}"
              </span>
              <span v-if="filterValidasi" class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">
                Validasi: {{ getValidationStatusText(filterValidasi) }}
              </span>
              <span v-if="filterJenisSetoran" class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">
                Jenis: {{ getDepositTypeText(filterJenisSetoran) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <User class="w-4 h-4 mr-2" />
                  Nama UPZ
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <MapPin class="w-4 h-4 mr-2" />
                  Alamat
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Calendar class="w-4 h-4 mr-2" />
                  Tanggal Setoran
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Coins class="w-4 h-4 mr-2" />
                  Jumlah Setoran
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Tag class="w-4 h-4 mr-2" />
                  Jenis Setoran
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <CheckCircle class="w-4 h-4 mr-2" />
                  Validasi
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
            <tr v-for="upz in upzList" :key="upz.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ upz.nama }}</div>
                <div class="text-xs text-gray-500">Kode: {{ upz.kode }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ upz.alamat }}</div>
                <div class="text-xs text-gray-500">PIC: {{ upz.pic_nama }} ({{ upz.pic_telepon }})</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(upz.tanggal_setoran) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(upz.jumlah_setoran) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getDepositTypeClass(upz.jenis_setoran)">
                  {{ getDepositTypeText(upz.jenis_setoran) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getValidationStatusClass(upz.validasi)">
                  {{ getValidationStatusText(upz.validasi) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="openModal(upz)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteUpz(upz.id)"
                  class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                  title="Hapus"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
                <button
                  v-if="upz.bukti_transfer"
                  @click="viewTransferProof(upz.bukti_transfer)"
                  class="text-green-600 hover:text-green-900 p-1 hover:bg-green-50 rounded"
                  title="Lihat Bukti Transfer"
                >
                  <FileText class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Menampilkan {{ pagination.from || 0 }} sampai {{ pagination.to || 0 }} dari {{ pagination.total || 0 }} data
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sebelumnya
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-700">
              Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
              {{ editingUpz ? 'Edit UPZ' : 'Tambah UPZ' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <form @submit.prevent="saveUpz">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Basic Information -->
              <div class="sm:col-span-2">
                <h4 class="text-base font-semibold text-gray-900 mb-4">Informasi Dasar UPZ</h4>
              </div>
              
              <div>
                <label class="form-label">Nama UPZ *</label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.nama" type="text" required class="form-input pl-10" placeholder="Masukkan nama UPZ" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Kode UPZ *</label>
                <div class="relative">
                  <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.kode" type="text" required class="form-input pl-10" placeholder="Kode unik UPZ" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Alamat *</label>
                <div class="relative">
                  <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea v-model="form.alamat" required class="form-input pl-10" rows="3" placeholder="Alamat lengkap UPZ"></textarea>
                </div>
              </div>
              
              <div>
                <label class="form-label">Nama PIC *</label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.pic_nama" type="text" required class="form-input pl-10" placeholder="Nama Penanggung Jawab" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Telepon PIC *</label>
                <div class="relative">
                  <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.pic_telepon" type="text" required class="form-input pl-10" placeholder="Nomor telepon PIC" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Status *</label>
                <div class="relative">
                  <ToggleLeft class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.status" required class="form-input pl-10">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                  </select>
                </div>
              </div>
              
              <!-- Deposit Information -->
              <div class="sm:col-span-2">
                <h4 class="text-base font-semibold text-gray-900 mb-4 mt-6">Informasi Setoran</h4>
              </div>
              
              <div>
                <label class="form-label">Tanggal Setoran</label>
                <div class="relative">
                  <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.tanggal_setoran" type="date" class="form-input pl-10" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Jumlah Setoran (Rp)</label>
                <div class="relative">
                  <Coins class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.jumlah_setoran" type="number" step="0.01" min="0" class="form-input pl-10" placeholder="0.00" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Jenis Setoran</label>
                <div class="relative">
                  <Tag class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.jenis_setoran" class="form-input pl-10">
                    <option value="">Pilih jenis setoran</option>
                    <option value="zakat">Zakat</option>
                    <option value="infaq">Infaq</option>
                    <option value="sedekah">Sedekah</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="form-label">Bukti Transfer</label>
                <div class="relative">
                  <FileText class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.bukti_transfer" type="text" class="form-input pl-10" placeholder="Link atau referensi bukti transfer" />
                </div>
                <p class="text-xs text-gray-500 mt-1">File upload akan diimplementasikan di versi berikutnya</p>
              </div>
              
              <div>
                <label class="form-label">Status Validasi *</label>
                <div class="relative">
                  <CheckCircle class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.validasi" required class="form-input pl-10">
                    <option value="pending">Pending</option>
                    <option value="verified">Terverifikasi</option>
                    <option value="rejected">Ditolak</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn btn-secondary w-full sm:w-auto">
                Batal
              </button>
              <button type="submit" :disabled="isLoading" class="btn btn-primary w-full sm:w-auto">
                {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { 
  Search, Plus, Edit, Trash2, User, MapPin, Phone, Hash, 
  Calendar, Coins, Tag, CheckCircle, Settings, X, FileText, ToggleLeft
} from 'lucide-vue-next'

const upzList = ref([])
const showModal = ref(false)
const editingUpz = ref(null)
const isLoading = ref(false)
const searchQuery = ref('')
const searchAddress = ref('')
const filterValidasi = ref('')
const filterJenisSetoran = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return searchQuery.value || searchAddress.value || filterValidasi.value || filterJenisSetoran.value
})

const form = ref({
  nama: '',
  kode: '',
  alamat: '',
  pic_nama: '',
  pic_telepon: '',
  status: 'aktif',
  tanggal_setoran: '',
  jumlah_setoran: '',
  bukti_transfer: '',
  jenis_setoran: '',
  validasi: 'pending'
})

onMounted(() => {
  fetchUpz()
})

const fetchUpz = async (page = 1) => {
  try {
    const params: any = {
      page: page,
      per_page: 10 // Limit to 10 records per page
    }
    
    // Add search parameters
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    if (searchAddress.value) {
      params.search_address = searchAddress.value
    }
    
    if (filterValidasi.value) {
      params.validasi = filterValidasi.value
    }
    
    if (filterJenisSetoran.value) {
      params.jenis_setoran = filterJenisSetoran.value
    }
    
    const response = await axios.get('/upz', {
      params: params
    })
    
    // Handle both paginated and direct data responses
    if (response.data.success) {
      const data = response.data.data
      upzList.value = data.data || []
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        from: data.from || 0,
        to: data.to || 0,
        total: data.total || 0
      }
    } else {
      upzList.value = response.data.data || response.data || []
    }
  } catch (error) {
    console.error('Error fetching UPZ:', error)
    upzList.value = []
  }
}

const openModal = (upz = null) => {
  editingUpz.value = upz
  if (upz) {
    form.value = { ...upz }
  } else {
    form.value = {
      nama: '',
      kode: '',
      alamat: '',
      pic_nama: '',
      pic_telepon: '',
      status: 'aktif',
      tanggal_setoran: '',
      jumlah_setoran: '',
      bukti_transfer: '',
      jenis_setoran: '',
      validasi: 'pending'
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingUpz.value = null
}

const saveUpz = async () => {
  try {
    isLoading.value = true
    let response
    if (editingUpz.value) {
      response = await axios.put(`/upz/${editingUpz.value.id}`, form.value)
    } else {
      response = await axios.post('/upz', form.value)
    }
    
    if (response.data.success) {
      alert(response.data.message || 'Data berhasil disimpan')
      await fetchUpz(pagination.value.current_page)
      closeModal()
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error saving UPZ:', error)
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      alert('Validation Error: ' + errors.join(', '))
    } else {
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    isLoading.value = false
  }
}

const deleteUpz = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus UPZ ini?')) {
    try {
      const response = await axios.delete(`/upz/${id}`)
      if (response.data.success) {
        alert(response.data.message || 'Data berhasil dihapus')
        await fetchUpz(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error) {
      console.error('Error deleting UPZ:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  searchAddress.value = ''
  filterValidasi.value = ''
  filterJenisSetoran.value = ''
  fetchUpz(1)
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUpz(page)
  }
}

const getDepositTypeClass = (jenis: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (jenis) {
    case 'zakat':
      return `${baseClass} bg-blue-100 text-blue-800`
    case 'infaq':
      return `${baseClass} bg-green-100 text-green-800`
    case 'sedekah':
      return `${baseClass} bg-purple-100 text-purple-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const getDepositTypeText = (jenis: string) => {
  switch (jenis) {
    case 'zakat': return 'Zakat'
    case 'infaq': return 'Infaq'
    case 'sedekah': return 'Sedekah'
    default: return '-'
  }
}

const getValidationStatusClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'verified':
      return `${baseClass} bg-green-100 text-green-800`
    case 'rejected':
      return `${baseClass} bg-red-100 text-red-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const getValidationStatusText = (status: string) => {
  switch (status) {
    case 'verified': return 'Terverifikasi'
    case 'rejected': return 'Ditolak'
    case 'pending': return 'Pending'
    default: return status
  }
}

const formatDate = (date: string) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const formatCurrency = (amount: number) => {
  if (!amount) return '-'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const viewTransferProof = (proof: string) => {
  // In a real implementation, this would open a modal or new tab to view the proof
  alert(`Bukti transfer: ${proof}\nFitur lengkap akan diimplementasikan di versi berikutnya.`)
}
</script>