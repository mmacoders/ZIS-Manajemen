<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Program Bantuan</h1>
      <p class="text-gray-600">Kelola program distribusi bantuan</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Program
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari program..." 
          class="form-input"
          @keyup.enter="fetchPrograms"
        />
        <button 
          @click="fetchPrograms"
          class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          <Search class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Programs Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Program</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Program</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang Program</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran Dialokasikan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pelaksanaan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penanggung Jawab</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 8" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="programs.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada program ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="program in programs" :key="program.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ program.id }}</td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ program.nama }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ getJenisProgramLabel(program.jenis_program) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ getBidangProgramLabel(program.bidang_program) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatCurrency(program.target_dana) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatDate(program.tanggal_mulai) }} - {{ formatDate(program.tanggal_selesai) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ program.penanggung_jawab || '-' }}
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(program)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteProgram(program.id)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} program
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
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">
            {{ editingProgram ? 'Edit Program Bantuan' : 'Tambah Program Bantuan' }}
          </h3>
          
          <form @submit.prevent="saveProgram">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Program *</label>
                <input 
                  v-model="form.nama" 
                  type="text" 
                  required 
                  class="form-input" 
                  placeholder="Masukkan nama program"
                />
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Program *</label>
                  <select v-model="form.jenis_program" required class="form-select" @change="onJenisProgramChange">
                    <option value="">Pilih Jenis Program</option>
                    <option value="distribusi">Distribusi</option>
                    <option value="pemberdayaan">Pemberdayaan</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bidang Program *</label>
                  <select v-model="form.bidang_program" required class="form-select" :disabled="!form.jenis_program">
                    <option value="">Pilih Bidang Program</option>
                    <option 
                      v-for="bidang in bidangOptions" 
                      :key="bidang.value" 
                      :value="bidang.value"
                    >
                      {{ bidang.label }}
                    </option>
                  </select>
                  <p v-if="!form.jenis_program" class="text-sm text-gray-500 mt-1">
                    Pilih jenis program terlebih dahulu
                  </p>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi *</label>
                <textarea 
                  v-model="form.deskripsi" 
                  required 
                  rows="3" 
                  class="form-textarea" 
                  placeholder="Deskripsi program"
                ></textarea>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Anggaran Dialokasikan (Rp) *</label>
                  <input 
                    v-model="formattedTargetDana"
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan anggaran dialokasikan"
                    @blur="formatTargetDanaInput"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Penanggung Jawab *</label>
                  <input 
                    v-model="form.penanggung_jawab" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan nama penanggung jawab"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                  <input 
                    v-model="form.tanggal_mulai" 
                    type="date" 
                    required 
                    class="form-input" 
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                  <input 
                    v-model="form.tanggal_selesai" 
                    type="date" 
                    required 
                    class="form-input" 
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select v-model="form.status" required class="form-select">
                  <option value="draft">Draft</option>
                  <option value="aktif">Aktif</option>
                  <option value="selesai">Selesai</option>
                  <option value="batal">Batal</option>
                </select>
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
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { Plus, Search, Edit, Trash2, Database } from 'lucide-vue-next'

// State
const programs = ref<any[]>([])
const showModal = ref(false)
const editingProgram = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

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
  nama: '',
  jenis_program: '',
  bidang_program: '',
  deskripsi: '',
  target_dana: 0,
  penanggung_jawab: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  status: 'draft'
})

// Bidang options based on jenis program
const bidangOptionsDistribusi = [
  { value: 'kemanusiaan', label: 'Kemanusiaan' },
  { value: 'pendidikan_distribusi', label: 'Pendidikan' },
  { value: 'dakwah_dan_advokasi', label: 'Dakwah dan Advokasi' },
  { value: 'kesehatan_distribusi', label: 'Kesehatan' }
]

const bidangOptionsPemberdayaan = [
  { value: 'ekonomi_produk', label: 'Ekonomi Produk' },
  { value: 'pendidikan_pemberdayaan', label: 'Pendidikan' },
  { value: 'kesehatan_pemberdayaan', label: 'Kesehatan' }
]

const bidangOptions = computed(() => {
  if (form.value.jenis_program === 'distribusi') {
    return bidangOptionsDistribusi
  } else if (form.value.jenis_program === 'pemberdayaan') {
    return bidangOptionsPemberdayaan
  }
  return []
})

// Handle jenis program change
const onJenisProgramChange = () => {
  // Reset bidang program when jenis program changes
  form.value.bidang_program = ''
}

// Computed property for formatted target dana
const formattedTargetDana = computed({
  get: () => {
    // Format the number with thousand separators for display
    if (form.value.target_dana === 0) return ''
    return form.value.target_dana.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  },
  set: (value) => {
    // Remove all non-digit characters except for the last decimal point
    const cleanValue = value.replace(/\./g, '')
    const numericValue = cleanValue === '' ? 0 : parseInt(cleanValue, 10)
    form.value.target_dana = isNaN(numericValue) ? 0 : numericValue
  }
})

// Format target dana input on blur
const formatTargetDanaInput = () => {
  if (form.value.target_dana > 0) {
    const formatted = form.value.target_dana.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    // We don't need to set the value here as the computed property handles it
  }
}

// Get label for jenis program
const getJenisProgramLabel = (jenisProgram: string) => {
  const labels: Record<string, string> = {
    'distribusi': 'Distribusi',
    'pemberdayaan': 'Pemberdayaan'
  }
  return labels[jenisProgram] || jenisProgram
}

// Get label for bidang program
const getBidangProgramLabel = (bidangProgram: string) => {
  const labels: Record<string, string> = {
    'kemanusiaan': 'Kemanusiaan',
    'pendidikan_distribusi': 'Pendidikan',
    'dakwah_dan_advokasi': 'Dakwah dan Advokasi',
    'kesehatan_distribusi': 'Kesehatan',
    'ekonomi_produk': 'Ekonomi Produk',
    'pendidikan_pemberdayaan': 'Pendidikan',
    'kesehatan_pemberdayaan': 'Kesehatan'
  }
  return labels[bidangProgram] || bidangProgram
}

// Lifecycle
onMounted(() => {
  fetchPrograms()
})

// Methods
const fetchPrograms = async (page = 1) => {
  try {
    loading.value = true
    const params: any = {
      page,
      per_page: 10
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await axios.get('/programs', { params })
    
    if (response.data.success) {
      programs.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      programs.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching programs:', error)
    programs.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchPrograms(page)
  }
}

const openModal = (program: any = null) => {
  editingProgram.value = program
  if (program) {
    form.value = { ...program }
  } else {
    form.value = {
      nama: '',
      jenis_program: '',
      bidang_program: '',
      deskripsi: '',
      target_dana: 0,
      penanggung_jawab: '',
      tanggal_mulai: new Date().toISOString().split('T')[0],
      tanggal_selesai: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 30 days from now
      status: 'draft'
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProgram.value = null
}

const saveProgram = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingProgram.value ? 'PUT' : 'POST'
    const url = editingProgram.value 
      ? `/programs/${editingProgram.value.id}` 
      : '/programs'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingProgram.value ? 'Program berhasil diperbarui' : 'Program berhasil ditambahkan')
      closeModal()
      fetchPrograms(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving program:', error)
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

const deleteProgram = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus program ini?')) {
    try {
      const response = await axios.delete(`/programs/${id}`)
      if (response.data.success) {
        alert('Program berhasil dihapus')
        fetchPrograms(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting program:', error)
      // Handle specific error cases
      if (error.response?.status === 422) {
        alert('Tidak dapat menghapus program yang memiliki distribusi')
      } else {
        alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
      }
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
    case 'aktif':
      return 'bg-green-100 text-green-800'
    case 'draft':
      return 'bg-yellow-100 text-yellow-800'
    case 'selesai':
      return 'bg-blue-100 text-blue-800'
    case 'batal':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'draft': 'Draft',
    'aktif': 'Aktif',
    'selesai': 'Selesai',
    'batal': 'Batal'
  }
  return statusMap[status] || status
}
</script>