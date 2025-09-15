<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Staff Amil</h1>
      <p class="text-gray-600">Kelola data staff amil</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Staff
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari staff..." 
          class="form-input"
          @keyup.enter="fetchStaff"
        />
        <button 
          @click="fetchStaff"
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
          <select v-model="filters.bidang" class="form-select" @change="fetchStaff">
            <option value="">Semua Bidang</option>
            <option value="Pendidikan">Pendidikan</option>
            <option value="Kesehatan">Kesehatan</option>
            <option value="Sosial">Sosial</option>
            <option value="Ekonomi">Ekonomi</option>
            <option value="Dakwah">Dakwah</option>
            <option value="Administrasi">Administrasi</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
          <select v-model="filters.jabatan" class="form-select" @change="fetchStaff">
            <option value="">Semua Jabatan</option>
            <option value="Koordinator">Koordinator</option>
            <option value="Staff">Staff</option>
            <option value="Manager">Manager</option>
            <option value="Supervisor">Supervisor</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Staff Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 6" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="staffList.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada staff ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ staff.nama }}</div>
                <div v-if="staff.nip" class="text-sm text-gray-500">NIP: {{ staff.nip }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ staff.jabatan }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ staff.bidang }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ staff.periode }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div v-if="staff.telepon" class="flex items-center">
                  <Phone class="w-3 h-3 mr-1 text-gray-400" />
                  {{ staff.telepon }}
                </div>
                <div v-if="staff.email" class="flex items-center mt-1">
                  <Mail class="w-3 h-3 mr-1 text-gray-400" />
                  {{ staff.email }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(staff)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteStaff(staff.id)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} staff
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
            {{ editingStaff ? 'Edit Staff Amil' : 'Tambah Staff Amil' }}
          </h3>
          
          <form @submit.prevent="saveStaff">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                  <input 
                    v-model="form.nama" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan nama lengkap"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                  <input 
                    v-model="form.nip" 
                    type="text" 
                    class="form-input" 
                    placeholder="Masukkan NIP (opsional)"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan *</label>
                  <select v-model="form.jabatan" required class="form-select">
                    <option value="">Pilih Jabatan</option>
                    <option value="Koordinator">Koordinator</option>
                    <option value="Staff">Staff</option>
                    <option value="Manager">Manager</option>
                    <option value="Supervisor">Supervisor</option>
                    <option value="Administrator">Administrator</option>
                  </select>
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
                    <option value="Administrasi">Administrasi</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Periode *</label>
                  <input 
                    v-model="form.periode" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Contoh: 2025-2026"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                  <input 
                    v-model="form.telepon" 
                    type="text" 
                    class="form-input" 
                    placeholder="Masukkan nomor telepon"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    class="form-input" 
                    placeholder="Masukkan alamat email"
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea 
                  v-model="form.alamat" 
                  rows="3" 
                  class="form-textarea" 
                  placeholder="Masukkan alamat lengkap"
                ></textarea>
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
import { Plus, Search, Edit, Trash2, Database, Phone, Mail } from 'lucide-vue-next'

// State
const staffList = ref<any[]>([])
const showModal = ref(false)
const editingStaff = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Filters
const filters = ref({
  bidang: '',
  jabatan: ''
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
  nama: '',
  jabatan: '',
  bidang: '',
  periode: '',
  nip: '',
  alamat: '',
  telepon: '',
  email: '',
  keterangan: ''
})

// Lifecycle
onMounted(() => {
  fetchStaff()
})

// Methods
const fetchStaff = async (page = 1) => {
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
    
    const response = await axios.get('/api/staff', { params })
    
    if (response.data.success) {
      staffList.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      staffList.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching staff:', error)
    staffList.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchStaff(page)
  }
}

const openModal = (staff: any = null) => {
  editingStaff.value = staff
  if (staff) {
    form.value = { ...staff }
  } else {
    form.value = {
      nama: '',
      jabatan: '',
      bidang: '',
      periode: '',
      nip: '',
      alamat: '',
      telepon: '',
      email: '',
      keterangan: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingStaff.value = null
}

const saveStaff = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingStaff.value ? 'PUT' : 'POST'
    const url = editingStaff.value 
      ? `/api/staff/${editingStaff.value.id}` 
      : '/api/staff'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingStaff.value ? 'Staff berhasil diperbarui' : 'Staff berhasil ditambahkan')
      closeModal()
      fetchStaff(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving staff:', error)
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

const deleteStaff = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus staff ini?')) {
    try {
      const response = await axios.delete(`/api/staff/${id}`)
      if (response.data.success) {
        alert('Staff berhasil dihapus')
        fetchStaff(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting staff:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}
</script>