<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Surat Keluar</h1>
      <p class="text-gray-600">Kelola surat keluar</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Surat Keluar
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari surat keluar..." 
          class="form-input"
          @keyup.enter="fetchOutgoingLetters"
        />
        <button 
          @click="fetchOutgoingLetters"
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
          <select v-model="filters.program_id" class="form-select" @change="fetchOutgoingLetters">
            <option value="">Semua Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.nama }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" class="form-select" @change="fetchOutgoingLetters">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="dikirim">Dikirim</option>
            <option value="diterima">Diterima</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input" @change="fetchOutgoingLetters" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input" @change="fetchOutgoingLetters" />
        </div>
      </div>
    </div>

    <!-- Outgoing Letters Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
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
            
            <tr v-else-if="outgoingLetters.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada surat keluar ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="letter in outgoingLetters" :key="letter.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ letter.nomor_surat }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(letter.tanggal) }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ letter.tujuan }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="letter.perihal">
                  {{ letter.perihal }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="letter.program?.nama">
                  {{ letter.program?.nama || '-' }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm">
                <span :class="getStatusClass(letter.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ getStatusText(letter.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(letter)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteOutgoingLetter(letter.id)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} surat
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
            {{ editingLetter ? 'Edit Surat Keluar' : 'Tambah Surat Keluar' }}
          </h3>
          
          <form @submit.prevent="saveOutgoingLetter">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
                  <input 
                    v-model="form.tanggal" 
                    type="date" 
                    required 
                    class="form-input" 
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan *</label>
                  <input 
                    v-model="form.tujuan" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan tujuan surat"
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Perihal *</label>
                <input 
                  v-model="form.perihal" 
                  type="text" 
                  required 
                  class="form-input" 
                  placeholder="Masukkan perihal surat"
                />
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                  <select v-model="form.program_id" class="form-select">
                    <option value="">Pilih Program (Opsional)</option>
                    <option v-for="program in programs" :key="program.id" :value="program.id">
                      {{ program.nama }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                  <select v-model="form.status" required class="form-select">
                    <option value="draft">Draft</option>
                    <option value="dikirim">Dikirim</option>
                    <option value="diterima">Diterima</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">File Dokumen</label>
                <input 
                  v-model="form.file_dokumen" 
                  type="text" 
                  class="form-input" 
                  placeholder="Link atau deskripsi file dokumen"
                />
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
const outgoingLetters = ref<any[]>([])
const programs = ref<any[]>([])
const showModal = ref(false)
const editingLetter = ref<any>(null)
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
  tanggal: new Date().toISOString().split('T')[0],
  tujuan: '',
  perihal: '',
  file_dokumen: '',
  keterangan: '',
  status: 'draft',
  program_id: null as number | null
})

// Lifecycle
onMounted(() => {
  fetchOutgoingLetters()
  fetchPrograms()
})

// Methods
const fetchOutgoingLetters = async (page = 1) => {
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
    
    const response = await axios.get('/api/outgoing-letters', { params })
    
    if (response.data.success) {
      outgoingLetters.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      outgoingLetters.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching outgoing letters:', error)
    outgoingLetters.value = []
  } finally {
    loading.value = false
  }
}

const fetchPrograms = async () => {
  try {
    const response = await axios.get('/api/programs')
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
    fetchOutgoingLetters(page)
  }
}

const openModal = (letter: any = null) => {
  editingLetter.value = letter
  if (letter) {
    form.value = { ...letter }
  } else {
    form.value = {
      tanggal: new Date().toISOString().split('T')[0],
      tujuan: '',
      perihal: '',
      file_dokumen: '',
      keterangan: '',
      status: 'draft',
      program_id: null
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingLetter.value = null
}

const saveOutgoingLetter = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingLetter.value ? 'PUT' : 'POST'
    const url = editingLetter.value 
      ? `/api/outgoing-letters/${editingLetter.value.id}` 
      : '/api/outgoing-letters'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingLetter.value ? 'Surat keluar berhasil diperbarui' : 'Surat keluar berhasil ditambahkan')
      closeModal()
      fetchOutgoingLetters(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving outgoing letter:', error)
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

const deleteOutgoingLetter = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus surat keluar ini?')) {
    try {
      const response = await axios.delete(`/api/outgoing-letters/${id}`)
      if (response.data.success) {
        alert('Surat keluar berhasil dihapus')
        fetchOutgoingLetters(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting outgoing letter:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}

// Utility functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'diterima':
      return 'bg-green-100 text-green-800'
    case 'dikirim':
      return 'bg-yellow-100 text-yellow-800'
    case 'draft':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'draft': 'Draft',
    'dikirim': 'Dikirim', 
    'diterima': 'Diterima'
  }
  return statusMap[status] || status
}
</script>