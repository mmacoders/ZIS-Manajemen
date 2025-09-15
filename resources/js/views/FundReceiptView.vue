<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Penerimaan Dana</h1>
      <p class="text-gray-600">Kelola penerimaan dana dari muzakki dan UPZ</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Penerimaan
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari penerimaan..." 
          class="form-input"
          @keyup.enter="fetchFundReceipts"
        />
        <button 
          @click="fetchFundReceipts"
          class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          <Search class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status_penerimaan" class="form-select" @change="fetchFundReceipts">
            <option value="">Semua Status</option>
            <option value="diterima">Diterima</option>
            <option value="ditolak">Ditolak</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input" @change="fetchFundReceipts" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input" @change="fetchFundReceipts" />
        </div>
      </div>
    </div>

    <!-- Fund Receipts Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Bukti</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber Dana</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Dana</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="fundReceipts.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada penerimaan dana ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="receipt in fundReceipts" :key="receipt.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ receipt.nomor_bukti }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(receipt.tanggal_setor) }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div v-if="receipt.muzakki">
                  Muzakki: {{ receipt.muzakki.nama }}
                </div>
                <div v-else-if="receipt.upz">
                  UPZ: {{ receipt.upz.nama_upz }}
                </div>
                <div v-else>
                  {{ receipt.sumber_dana }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ receipt.jenis_dana }}</td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ formatCurrency(receipt.jumlah_setor) }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="getStatusClass(receipt.status_penerimaan)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ getStatusText(receipt.status_penerimaan) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(receipt)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteFundReceipt(receipt.id)"
                  class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                  title="Hapus"
                >
                  <Trash2 class="w-4 h-4" />
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} penerimaan
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
      <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">
            {{ editingFundReceipt ? 'Edit Penerimaan Dana' : 'Tambah Penerimaan Dana' }}
          </h3>
          
          <form @submit.prevent="saveFundReceipt">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Setor *</label>
                  <input 
                    v-model="form.tanggal_setor" 
                    type="date" 
                    required 
                    class="form-input" 
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status Penerimaan *</label>
                  <select v-model="form.status_penerimaan" required class="form-select">
                    <option value="pending">Pending</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Sumber Dana *</label>
                  <input 
                    v-model="form.sumber_dana" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan sumber dana"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Dana *</label>
                  <select v-model="form.jenis_dana" required class="form-select">
                    <option value="">Pilih Jenis Dana</option>
                    <option value="zakat">Zakat</option>
                    <option value="infaq">Infaq</option>
                    <option value="sedekah">Sedekah</option>
                    <option value="hibah">Hibah</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Setor (Rp) *</label>
                <input 
                  v-model.number="form.jumlah_setor" 
                  type="number" 
                  required 
                  min="0" 
                  step="1000" 
                  class="form-input" 
                  placeholder="Masukkan jumlah setor"
                />
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Muzakki</label>
                  <select v-model="form.muzakki_id" class="form-select">
                    <option value="">Pilih Muzakki (Opsional)</option>
                    <option v-for="muzakki in muzakkiList" :key="muzakki.id" :value="muzakki.id">
                      {{ muzakki.nama }} ({{ muzakki.nik }})
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">UPZ</label>
                  <select v-model="form.upz_id" class="form-select">
                    <option value="">Pilih UPZ (Opsional)</option>
                    <option v-for="upz in upzList" :key="upz.id" :value="upz.id">
                      {{ upz.nama_upz }}
                    </option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea 
                  v-model="form.keterangan" 
                  rows="3" 
                  class="form-textarea" 
                  placeholder="Keterangan tambahan (opsional)"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer</label>
                <input 
                  v-model="form.bukti_transfer" 
                  type="text" 
                  class="form-input" 
                  placeholder="Link atau deskripsi bukti transfer"
                />
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn-secondary">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="btn-primary">
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
import { Plus, Search, Edit, Trash2, Database } from 'lucide-vue-next'

// State
const fundReceipts = ref<any[]>([])
const muzakkiList = ref<any[]>([])
const upzList = ref<any[]>([])
const showModal = ref(false)
const editingFundReceipt = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

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
  jenis_dana: '',
  jumlah_setor: 0,
  muzakki_id: null as number | null,
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
    
    const response = await axios.get('/api/fund-receipts', { params })
    
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
    const response = await axios.get('/muzakki')
    if (response.data.success) {
      muzakkiList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching muzakki list:', error)
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

const openModal = (fundReceipt: any = null) => {
  editingFundReceipt.value = fundReceipt
  if (fundReceipt) {
    form.value = { ...fundReceipt }
  } else {
    form.value = {
      tanggal_setor: new Date().toISOString().split('T')[0],
      status_penerimaan: 'pending',
      sumber_dana: '',
      jenis_dana: '',
      jumlah_setor: 0,
      muzakki_id: null,
      upz_id: null,
      keterangan: '',
      bukti_transfer: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingFundReceipt.value = null
}

const saveFundReceipt = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingFundReceipt.value ? 'PUT' : 'POST'
    const url = editingFundReceipt.value 
      ? `/api/fund-receipts/${editingFundReceipt.value.id}` 
      : '/api/fund-receipts'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingFundReceipt.value ? 'Penerimaan dana berhasil diperbarui' : 'Penerimaan dana berhasil ditambahkan')
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
  if (confirm('Apakah Anda yakin ingin menghapus penerimaan dana ini?')) {
    try {
      const response = await axios.delete(`/api/fund-receipts/${id}`)
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
</script>