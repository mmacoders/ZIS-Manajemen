<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">RKAT</h1>
      <p class="text-gray-600">Rencana Kerja dan Anggaran Tahunan</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah RKAT
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari RKAT..." 
          class="form-input"
          @keyup.enter="fetchRkat"
        />
        <button 
          @click="fetchRkat"
          class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
        >
          <Search class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Bidang</label>
          <select v-model="filters.bidang" class="form-select" @change="fetchRkat">
            <option value="">Semua Bidang</option>
            <option value="Pendidikan">Pendidikan</option>
            <option value="Kesehatan">Kesehatan</option>
            <option value="Sosial">Sosial</option>
            <option value="Ekonomi">Ekonomi</option>
            <option value="Dakwah">Dakwah</option>
          </select>
        </div>
      </div>
    </div>

    <!-- RKAT Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Urut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 8" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="rkatList.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada data RKAT ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="rkat in rkatList" :key="rkat.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ rkat.nomor_urut }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ rkat.bidang }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="rkat.nama_program">
                  {{ rkat.nama_program }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="rkat.jenis_kegiatan">
                  {{ rkat.jenis_kegiatan }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ rkat.volume }} {{ rkat.satuan }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ formatCurrency(rkat.harga_satuan) }}</td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ formatCurrency(rkat.jumlah) }}</td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(rkat)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteRkat(rkat.id)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} data
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
            {{ editingRkat ? 'Edit RKAT' : 'Tambah RKAT' }}
          </h3>
          
          <form @submit.prevent="saveRkat">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">No. Urut *</label>
                  <input 
                    v-model="form.nomor_urut" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan nomor urut"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bidang *</label>
                  <select v-model="form.bidang" required class="form-select">
                    <option value="">Pilih Bidang</option>
                    <option value="Pendidikan">Pendidikan</option>
                    <option value="Kesehatan">Kesehatan</option>
                    <option value="Sosial">Sosial</option>
                    <option value="Ekonomi">Ekonomi</option>
                    <option value="Dakwah">Dakwah</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Program *</label>
                <input 
                  v-model="form.nama_program" 
                  type="text" 
                  required 
                  class="form-input" 
                  placeholder="Masukkan nama program"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kegiatan *</label>
                <input 
                  v-model="form.jenis_kegiatan" 
                  type="text" 
                  required 
                  class="form-input" 
                  placeholder="Masukkan jenis kegiatan"
                />
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Volume *</label>
                  <input 
                    v-model.number="form.volume" 
                    type="number" 
                    required 
                    min="1" 
                    class="form-input" 
                    placeholder="Volume"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Satuan *</label>
                  <input 
                    v-model="form.satuan" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Satuan"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Harga Satuan (Rp) *</label>
                  <input 
                    v-model.number="form.harga_satuan" 
                    type="number" 
                    required 
                    min="0" 
                    step="1000" 
                    class="form-input" 
                    placeholder="Harga satuan"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp)</label>
                  <input 
                    :value="form.volume && form.harga_satuan ? formatCurrency(form.volume * form.harga_satuan) : '0'"
                    type="text" 
                    class="form-input" 
                    placeholder="Jumlah"
                    readonly
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Program Terkait</label>
                <select v-model="form.program_id" class="form-select">
                  <option value="">Pilih Program (Opsional)</option>
                  <option v-for="program in programs" :key="program.id" :value="program.id">
                    {{ program.nama }}
                  </option>
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
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { Plus, Search, Edit, Trash2, Database } from 'lucide-vue-next'

// State
const rkatList = ref<any[]>([])
const programs = ref<any[]>([])
const showModal = ref(false)
const editingRkat = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Filters
const filters = ref({
  bidang: ''
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
  nomor_urut: '',
  bidang: '',
  nama_program: '',
  jenis_kegiatan: '',
  volume: 1,
  satuan: '',
  harga_satuan: 0,
  program_id: null as number | null
})

// Lifecycle
onMounted(() => {
  fetchRkat()
  fetchPrograms()
})

// Methods
const fetchRkat = async (page = 1) => {
  try {
    loading.value = true
    const params = {
      page,
      per_page: pagination.value.per_page,
      search: filters.value.search
    }
    
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/rkat', { params })
    
    if (response.data.success) {
      rkatList.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      rkatList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching RKAT:', error)
    rkatList.value = []
  } finally {
    loading.value = false
  }
}

const fetchPrograms = async () => {
  try {
    // Use relative path since axios baseURL already includes /api
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
    fetchRkat(page)
  }
}

const openModal = (rkat: any = null) => {
  editingRkat.value = rkat
  if (rkat) {
    form.value = { ...rkat }
  } else {
    form.value = {
      nomor_urut: '',
      bidang: '',
      nama_program: '',
      jenis_kegiatan: '',
      volume: 1,
      satuan: '',
      harga_satuan: 0,
      program_id: null
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingRkat.value = null
}

const saveRkat = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingRkat.value ? 'PUT' : 'POST'
    // Use relative paths since axios baseURL already includes /api
    const url = editingRkat.value 
      ? `/rkat/${editingRkat.value.id}` 
      : '/rkat'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingRkat.value ? 'RKAT berhasil diperbarui' : 'RKAT berhasil ditambahkan')
      closeModal()
      fetchRkat(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving RKAT:', error)
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

const deleteRkat = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus data RKAT ini?')) {
    try {
      // Use relative path since axios baseURL already includes /api
      const response = await axios.delete(`/rkat/${id}`)
      if (response.data.success) {
        alert('Data RKAT berhasil dihapus')
        fetchRkat(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting RKAT:', error)
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
</script>