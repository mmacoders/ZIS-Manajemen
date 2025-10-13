<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Penyaluran Dana</h1>
      <p class="text-gray-600 text-sm mt-1">Kelola penyaluran dana untuk program bantuan</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium text-sm"
      >
        <Plus class="w-4 h-4 mr-1.5" />
        Tambah Penyaluran
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari penyaluran..." 
            class="form-input pl-10 text-sm"
            @keyup.enter="fetchFundDistributions"
          />
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
        </div>
        <button 
          @click="fetchFundDistributions"
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
          <select v-model="filters.program_id" class="form-select text-sm" @change="fetchFundDistributions">
            <option value="">Semua Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.nama }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="form-select text-sm" @change="fetchFundDistributions">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="disetujui">Disetujui</option>
            <option value="ditolak">Ditolak</option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input text-sm" @change="fetchFundDistributions" />
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input text-sm" @change="fetchFundDistributions" />
        </div>
      </div>
    </div>

    <!-- Fund Distributions Table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Bukti</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 8" :key="col" class="px-4 py-3">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="fundDistributions.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                <Database class="w-10 h-10 mx-auto mb-2 text-gray-400" />
                <p class="text-sm">Tidak ada penyaluran dana ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="distribution in fundDistributions" :key="distribution.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ distribution.nomor_bukti }}</td>
              <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                <div class="max-w-xs truncate" :title="distribution.program?.nama">
                  {{ distribution.program?.nama || '-' }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ distribution.bidang_program }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(distribution.tanggal_penyaluran) }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ formatCurrency(distribution.anggaran_dialokasikan) }}</td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(distribution.nominal_bantuan) }}</td>
              <td class="px-4 py-3 text-sm">
                <span :class="getStatusClass(distribution.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ getStatusText(distribution.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex space-x-1">
                  <button 
                    @click="openModal(distribution)"
                    class="text-blue-600 hover:text-blue-900 p-1.5 hover:bg-blue-50 rounded-full"
                    title="Edit"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    @click="deleteFundDistribution(distribution.id)"
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
          Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} penyaluran
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
              {{ editingFundDistribution ? 'Edit Penyaluran Dana' : 'Tambah Penyaluran Dana' }}
            </h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100">
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <form @submit.prevent="saveFundDistribution">
            <div class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Program *</label>
                  <select v-model="form.program_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Program</option>
                    <option v-for="program in programs" :key="program.id" :value="program.id">
                      {{ program.nama }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Bidang Program *</label>
                  <select v-model="form.bidang_program" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="">Pilih Bidang</option>
                    <option value="Pendidikan">Pendidikan</option>
                    <option value="Kesehatan">Kesehatan</option>
                    <option value="Sosial">Sosial</option>
                    <option value="Ekonomi">Ekonomi</option>
                    <option value="Dakwah">Dakwah</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Anggaran Dialokasikan (Rp) *</label>
                  <input 
                    v-model.number="form.anggaran_dialokasikan" 
                    type="number" 
                    required 
                    min="0" 
                    step="1000" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                    placeholder="Masukkan anggaran dialokasikan"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Nominal Bantuan (Rp) *</label>
                  <input 
                    v-model.number="form.nominal_bantuan" 
                    type="number" 
                    required 
                    min="0" 
                    step="1000" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                    placeholder="Masukkan nominal bantuan"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Penyaluran *</label>
                  <input 
                    v-model="form.tanggal_penyaluran" 
                    type="date" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1.5">Status *</label>
                  <select v-model="form.status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                    <option value="pending">Pending</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Bukti Penyaluran</label>
                <input 
                  v-model="form.bukti_penyaluran" 
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm" 
                  placeholder="Link atau deskripsi bukti penyaluran"
                />
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
const fundDistributions = ref<any[]>([])
const programs = ref<any[]>([])
const showModal = ref(false)
const editingFundDistribution = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Filters
const filters = ref({
  program_id: '',
  status: '',
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
  program_id: '',
  bidang_program: '',
  anggaran_dialokasikan: 0,
  nominal_bantuan: 0,
  tanggal_penyaluran: new Date().toISOString().split('T')[0],
  bukti_penyaluran: '',
  status: 'pending',
  keterangan: ''
})

// Lifecycle
onMounted(() => {
  fetchFundDistributions()
  fetchPrograms()
})

// Methods
const fetchFundDistributions = async (page = 1) => {
  try {
    loading.value = true
    const params: any = {
      page,
      per_page: 10
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await axios.get('/fund-distributions', { params })
    
    if (response.data.success) {
      fundDistributions.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      fundDistributions.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching fund distributions:', error)
    fundDistributions.value = []
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
    fetchFundDistributions(page)
  }
}

const openModal = (fundDistribution: any = null) => {
  editingFundDistribution.value = fundDistribution
  if (fundDistribution) {
    form.value = { ...fundDistribution }
  } else {
    form.value = {
      program_id: '',
      bidang_program: '',
      anggaran_dialokasikan: 0,
      nominal_bantuan: 0,
      tanggal_penyaluran: new Date().toISOString().split('T')[0],
      bukti_penyaluran: '',
      status: 'pending',
      keterangan: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingFundDistribution.value = null
}

const saveFundDistribution = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingFundDistribution.value ? 'PUT' : 'POST'
    const url = editingFundDistribution.value 
      ? `/fund-distributions/${editingFundDistribution.value.id}` 
      : '/fund-distributions'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingFundDistribution.value ? 'Penyaluran dana berhasil diperbarui' : 'Penyaluran dana berhasil ditambahkan')
      closeModal()
      fetchFundDistributions(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving fund distribution:', error)
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

const deleteFundDistribution = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus penyaluran dana ini?')) {
    try {
      const response = await axios.delete(`/fund-distributions/${id}`)
      if (response.data.success) {
        alert('Penyaluran dana berhasil dihapus')
        fetchFundDistributions(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting fund distribution:', error)
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
    case 'disetujui':
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
    'pending': 'Pending',
    'disetujui': 'Disetujui', 
    'ditolak': 'Ditolak'
  }
  return statusMap[status] || status
}
</script>