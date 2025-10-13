<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Surat Pertanggungjawaban (SPJ)</h1>
      <p class="text-gray-600 text-sm mt-1">Kelola surat pertanggungjawaban kegiatan</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium text-sm"
      >
        <Plus class="w-4 h-4 mr-1.5" />
        Tambah SPJ
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari SPJ..." 
            class="form-input pl-10 text-sm"
            @keyup.enter="fetchSpj"
          />
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
        </div>
        <button 
          @click="fetchSpj"
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
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="filters.program_id" class="form-select text-sm" @change="fetchSpj">
            <option value="">Semua Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.nama }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Status Validasi</label>
          <select v-model="filters.status_validasi" class="form-select text-sm" @change="fetchSpj">
            <option value="">Semua Status</option>
            <option value="sudah">Sudah</option>
            <option value="belum">Belum</option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input text-sm" @change="fetchSpj" />
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input text-sm" @change="fetchSpj" />
        </div>
      </div>
    </div>

    <!-- SPJ Table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. SPJ</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Validasi</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-4 py-3">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="spjList.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                <Database class="w-10 h-10 mx-auto mb-2 text-gray-400" />
                <p class="text-sm">Tidak ada SPJ ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="spj in spjList" :key="spj.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ spj.nomor_spj }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ spj.nama_penerima }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                <div class="max-w-xs truncate" :title="spj.program?.nama">
                  {{ spj.program?.nama || '-' }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(spj.tanggal_spj) }}</td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(spj.nominal) }}</td>
              <td class="px-4 py-3 text-sm">
                <span :class="getValidationStatusClass(spj.status_validasi)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ getValidationStatusText(spj.status_validasi) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex space-x-1">
                  <button 
                    @click="openModal(spj)"
                    class="text-blue-600 hover:text-blue-900 p-1.5 hover:bg-blue-50 rounded-full"
                    title="Edit"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    @click="deleteSpj(spj.id)"
                    class="text-red-600 hover:text-red-900 p-1.5 hover:bg-red-50 rounded-full"
                    title="Hapus"
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
          Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} SPJ
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
              {{ editingSpj ? 'Edit SPJ' : 'Tambah SPJ' }}
            </h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100">
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="saveSpj">
            <div class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama Penerima *</label>
                  <input 
                    v-model="form.nama_penerima" 
                    type="text" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                    placeholder="Masukkan nama penerima"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Program *</label>
                  <select v-model="form.program_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Program</option>
                    <option v-for="program in programs" :key="program.id" :value="program.id">
                      {{ program.nama }}
                    </option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Nominal (Rp) *</label>
                  <input 
                    v-model.number="form.nominal" 
                    type="number" 
                    required 
                    min="0" 
                    step="1000" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                    placeholder="Masukkan nominal"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal SPJ *</label>
                  <input 
                    v-model="form.tanggal_spj" 
                    type="date" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Status Validasi *</label>
                  <select v-model="form.status_validasi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="belum">Belum</option>
                    <option value="sudah">Sudah</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Keterangan</label>
                <textarea 
                  v-model="form.keterangan" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  placeholder="Keterangan tambahan (opsional)"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Dokumen SPJ</label>
                <input 
                  v-model="form.dokumen_spj" 
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  placeholder="Link atau deskripsi dokumen SPJ"
                />
              </div>
            </div>
            
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
import { Plus, Search, Edit, Trash2, Database, X } from 'lucide-vue-next'

// State
const spjList = ref<any[]>([])
const programs = ref<any[]>([])
const showModal = ref(false)
const editingSpj = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Filters
const filters = ref({
  program_id: '',
  status_validasi: '',
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
  nama_penerima: '',
  program_id: '',
  nominal: 0,
  tanggal_spj: new Date().toISOString().split('T')[0],
  status_validasi: 'belum',
  keterangan: '',
  dokumen_spj: ''
})

// Lifecycle
onMounted(() => {
  fetchSpj()
  fetchPrograms()
})

// Methods
const fetchSpj = async (page = 1) => {
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
    
    const response = await axios.get('/spj', { params })
    
    if (response.data.success) {
      spjList.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      spjList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching SPJ:', error)
    spjList.value = []
  } finally {
    loading.value = false
  }
}

const fetchPrograms = async () => {
  try {
    const response = await axios.get('/programs')
    if (response.data.success) {
      programs.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Error fetching programs:', error)
    programs.value = []
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchSpj(page)
  }
}

const openModal = (spj: any = null) => {
  editingSpj.value = spj
  if (spj) {
    form.value = { ...spj }
  } else {
    form.value = {
      nama_penerima: '',
      program_id: '',
      nominal: 0,
      tanggal_spj: new Date().toISOString().split('T')[0],
      status_validasi: 'belum',
      keterangan: '',
      dokumen_spj: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingSpj.value = null
}

const saveSpj = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingSpj.value ? 'PUT' : 'POST'
    const url = editingSpj.value 
      ? `/spj/${editingSpj.value.id}` 
      : '/spj'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingSpj.value ? 'SPJ berhasil diperbarui' : 'SPJ berhasil ditambahkan')
      closeModal()
      fetchSpj(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving SPJ:', error)
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

const deleteSpj = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus SPJ ini?')) {
    try {
      const response = await axios.delete(`/spj/${id}`)
      if (response.data.success) {
        alert('SPJ berhasil dihapus')
        fetchSpj(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting SPJ:', error)
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

const getValidationStatusClass = (status: string) => {
  switch (status) {
    case 'sudah':
      return 'bg-green-100 text-green-800'
    case 'belum':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getValidationStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'sudah': 'Sudah',
    'belum': 'Belum'
  }
  return statusMap[status] || status
}
</script>