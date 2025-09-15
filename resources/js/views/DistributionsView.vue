<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Realisasi Bantuan</h1>
      <p class="text-gray-600">Kelola penyaluran bantuan kepada mustahiq</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Realisasi
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari realisasi..." 
          class="form-input"
          @keyup.enter="fetchDistributions"
        />
        <button 
          @click="fetchDistributions"
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
          <select v-model="filters.program_id" class="form-select" @change="fetchDistributions">
            <option value="">Semua Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.nama }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" class="form-select" @change="fetchDistributions">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
          <input v-model="filters.start_date" type="date" class="form-input" @change="fetchDistributions" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
          <input v-model="filters.end_date" type="date" class="form-input" @change="fetchDistributions" />
        </div>
      </div>
    </div>

    <!-- Distributions Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mustahiq</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori Asnaf</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program yang Diikuti</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Bantuan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal Bantuan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti Penyaluran</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Realisasi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 9" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="distributions.length === 0">
              <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada realisasi ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="(distribution, index) in distributions" :key="distribution && distribution.id ? distribution.id : 'dist_' + index" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ distribution && distribution.id ? distribution.id : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="distribution && distribution.mustahiq && distribution.mustahiq.nama ? distribution.mustahiq.nama : ''">
                  {{ distribution && distribution.mustahiq && distribution.mustahiq.nama ? distribution.mustahiq.nama : '-' }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ distribution && distribution.mustahiq && distribution.mustahiq.kategori ? distribution.mustahiq.kategori : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="distribution && distribution.program && distribution.program.nama ? distribution.program.nama : ''">
                  {{ distribution && distribution.program && distribution.program.nama ? distribution.program.nama : '-' }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ distribution && distribution.jenis_bantuan ? distribution.jenis_bantuan : '-' }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ distribution && distribution.jumlah ? formatCurrency(distribution.jumlah) : 'Rp0' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ distribution && distribution.bukti_distribusi ? distribution.bukti_distribusi : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ distribution && distribution.tanggal_distribusi ? formatDate(distribution.tanggal_distribusi) : '-' }}
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="viewDistribution(distribution)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Lihat"
                  :disabled="!distribution || !distribution.id"
                >
                  <Eye class="w-4 h-4" />
                </button>
                <button 
                  @click="openModal(distribution)"
                  class="text-yellow-600 hover:text-yellow-800 p-1 hover:bg-yellow-50 rounded"
                  title="Edit"
                  :disabled="!distribution || !distribution.id"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteDistribution(distribution && distribution.id ? distribution.id : null)"
                  class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                  title="Hapus"
                  :disabled="!distribution || !distribution.id"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} realisasi
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
            {{ editingDistribution ? 'Edit Realisasi Bantuan' : 'Tambah Realisasi Bantuan' }}
          </h3>
          
          <form @submit.prevent="saveDistribution">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Program *</label>
                  <select v-model="form.program_id" required class="form-select">
                    <option value="">Pilih Program</option>
                    <option v-for="(program, index) in programs" :key="program && program.id ? program.id : 'program_' + index" :value="program && program.id ? program.id : ''">
                      {{ program && program.nama ? program.nama : '-' }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Mustahiq *</label>
                  <select v-model="form.mustahiq_id" required class="form-select">
                    <option value="">Pilih Mustahiq</option>
                    <option v-for="(mustahiq, index) in mustahiqList" :key="mustahiq && mustahiq.id ? mustahiq.id : 'mustahiq_' + index" :value="mustahiq && mustahiq.id ? mustahiq.id : ''">
                      {{ mustahiq && mustahiq.nama ? mustahiq.nama : '-' }} ({{ mustahiq && mustahiq.nik ? mustahiq.nik : '-' }})
                    </option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Bantuan *</label>
                  <input 
                    v-model="form.jenis_bantuan" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan jenis bantuan"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nominal Bantuan (Rp) *</label>
                  <input 
                    v-model.number="form.jumlah" 
                    type="number" 
                    required 
                    min="0" 
                    step="1000" 
                    class="form-input" 
                    placeholder="Masukkan nominal bantuan"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Realisasi *</label>
                  <input 
                    v-model="form.tanggal_distribusi" 
                    type="date" 
                    required 
                    class="form-input" 
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Penyaluran</label>
                  <input 
                    v-model="form.bukti_distribusi" 
                    type="text" 
                    class="form-input" 
                    placeholder="Link atau deskripsi bukti penyaluran"
                  />
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select v-model="form.status" required class="form-select">
                  <option value="pending">Pending</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Dibatalkan</option>
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

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeViewModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Detail Realisasi Bantuan</h3>
          
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-500">ID</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.id ? selectedDistribution.id : '-' }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-500">Tanggal Realisasi</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.tanggal_distribusi ? formatDate(selectedDistribution.tanggal_distribusi) : '-' }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Nama Mustahiq</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.mustahiq && selectedDistribution.mustahiq.nama ? selectedDistribution.mustahiq.nama : '-' }}</p>
              <p class="mt-1 text-sm text-gray-500">NIK: {{ selectedDistribution && selectedDistribution.mustahiq && selectedDistribution.mustahiq.nik ? selectedDistribution.mustahiq.nik : '-' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Kategori Asnaf</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.mustahiq && selectedDistribution.mustahiq.kategori ? selectedDistribution.mustahiq.kategori : '-' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Program yang Diikuti</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.program && selectedDistribution.program.nama ? selectedDistribution.program.nama : '-' }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-500">Jenis Bantuan</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.jenis_bantuan ? selectedDistribution.jenis_bantuan : '-' }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-500">Nominal Bantuan</label>
                <p class="mt-1 text-sm font-medium text-gray-900">{{ selectedDistribution && selectedDistribution.jumlah ? formatCurrency(selectedDistribution.jumlah) : 'Rp0' }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Bukti Penyaluran</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.bukti_distribusi ? selectedDistribution.bukti_distribusi : '-' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Keterangan</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedDistribution && selectedDistribution.keterangan ? selectedDistribution.keterangan : '-' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-500">Status</label>
              <span :class="getStatusClass(selectedDistribution && selectedDistribution.status ? selectedDistribution.status : '')" class="inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1">
                {{ getStatusText(selectedDistribution && selectedDistribution.status ? selectedDistribution.status : '') }}
              </span>
            </div>
          </div>
          
          <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
            <button type="button" @click="closeViewModal" class="btn-secondary">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { Plus, Search, Edit, Trash2, Eye, Database } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'

// Use auth store
const authStore = useAuthStore()

console.log('DistributionsView component initialized')
console.log('Auth store initialized:', {
  token: authStore.token,
  isAuthenticated: authStore.isAuthenticated,
  user: authStore.user
})

// State
const distributions = ref<any[]>([])
const programs = ref<any[]>([])
const mustahiqList = ref<any[]>([])
const showModal = ref(false)
const showViewModal = ref(false)
const editingDistribution = ref<any>(null)
const selectedDistribution = ref<any>(null)
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
  mustahiq_id: '',
  jenis_bantuan: '',
  jumlah: 0,
  tanggal_distribusi: new Date().toISOString().split('T')[0],
  keterangan: '',
  bukti_distribusi: '',
  status: 'pending'
})

// Lifecycle
onMounted(() => {
  console.log('DistributionsView mounted - checking authentication...')
  console.log('Auth store state:', {
    token: authStore.token,
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user
  })
  console.log('Axios default headers:', axios.defaults.headers.common)
  
  // Ensure we have a valid authentication token before fetching data
  if (authStore.isAuthenticated) {
    initializeData()
  } else {
    console.warn('User not authenticated, waiting for authentication...')
  }
})

// Watch for authentication state changes
watch(() => authStore.isAuthenticated, (newVal) => {
  console.log('Authentication state changed:', newVal)
  console.log('Auth store state:', {
    token: authStore.token,
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user
  })
  console.log('Axios default headers:', axios.defaults.headers.common)
  
  if (newVal) {
    console.log('Authentication state changed to authenticated, initializing data...')
    initializeData()
  }
})

// Methods
const initializeData = async () => {
  try {
    console.log('Initializing data...')
    console.log('Auth store token:', authStore.token)
    console.log('Auth store isAuthenticated:', authStore.isAuthenticated)
    console.log('Axios default headers:', axios.defaults.headers.common)
    
    // Ensure axios has the proper authorization header
    if (authStore.token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${authStore.token}`
      console.log('Set axios authorization header')
    }
    
    // Fetch all data in parallel
    await Promise.all([
      fetchDistributions(),
      fetchPrograms(),
      fetchMustahiqList()
    ])
  } catch (error) {
    console.error('Error initializing data:', error)
  }
}

const fetchDistributions = async (page = 1) => {
  try {
    loading.value = true
    const params: any = {
      page,
      per_page: 10
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    // Ensure we have proper headers
    const config = {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      params: params
    }
    
    if (authStore.token) {
      config.headers['Authorization'] = `Bearer ${authStore.token}`
    }
    
    const response = await axios.get('/distributions', config)
    console.log('Distributions API Response:', response.data)
    
    if (response.data.success) {
      // Filter out any null or undefined items
      const rawData = response.data.data?.data || []
      console.log('Raw distributions data:', rawData)
      
      distributions.value = rawData.filter((distribution: any) => distribution !== null && distribution !== undefined)
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      // Filter out any null or undefined items
      const rawData = response.data.data || []
      distributions.value = rawData.filter((distribution: any) => distribution !== null && distribution !== undefined)
    }
  } catch (error: any) {
    console.error('Error fetching distributions:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    console.error('Error headers:', error.response?.headers)
    distributions.value = []
  } finally {
    loading.value = false
  }
}

const fetchPrograms = async () => {
  try {
    console.log('Fetching programs with token:', authStore.token)
    console.log('Axios default headers:', axios.defaults.headers.common)
    
    // Ensure we have proper headers
    const config = {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    }
    
    if (authStore.token) {
      config.headers['Authorization'] = `Bearer ${authStore.token}`
    }
    
    const response = await axios.get('/programs', config)
    console.log('Programs API Response:', response.data)
    
    if (response.data.success) {
      // Programs return paginated data with structure: response.data.data.data
      const rawData = response.data.data?.data || []
      console.log('Raw programs data:', rawData)
      
      const programsData = rawData.filter((program: any) => program !== null && program !== undefined)
      programs.value = programsData
      console.log('Processed programs:', programsData)
    } else {
      console.error('Programs API returned success=false:', response.data)
      programs.value = []
    }
  } catch (error: any) {
    console.error('Error fetching programs:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    console.error('Error headers:', error.response?.headers)
    programs.value = []
  }
}

const fetchMustahiqList = async () => {
  try {
    console.log('Fetching mustahiq with token:', authStore.token)
    console.log('Axios default headers:', axios.defaults.headers.common)
    
    // Ensure we have proper headers
    const config = {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    }
    
    if (authStore.token) {
      config.headers['Authorization'] = `Bearer ${authStore.token}`
    }
    
    const response = await axios.get('/mustahiq', config)
    console.log('Mustahiq API Response:', response.data)
    
    if (response.data.success) {
      // Mustahiq also returns paginated data with structure: response.data.data.data
      const rawData = response.data.data?.data || []
      console.log('Raw mustahiq data:', rawData)
      
      const mustahiqData = rawData.filter((mustahiq: any) => mustahiq !== null && mustahiq !== undefined)
      mustahiqList.value = mustahiqData
      console.log('Processed mustahiqList:', mustahiqData)
    } else {
      console.error('Mustahiq API returned success=false:', response.data)
      mustahiqList.value = []
    }
  } catch (error: any) {
    console.error('Error fetching mustahiq list:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    console.error('Error headers:', error.response?.headers)
    mustahiqList.value = []
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchDistributions(page)
  }
}

const openModal = (distribution: any = null) => {
  editingDistribution.value = distribution
  if (distribution) {
    form.value = { ...distribution }
  } else {
    form.value = {
      program_id: '',
      mustahiq_id: '',
      jenis_bantuan: '',
      jumlah: 0,
      tanggal_distribusi: new Date().toISOString().split('T')[0],
      keterangan: '',
      bukti_distribusi: '',
      status: 'pending'
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingDistribution.value = null
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedDistribution.value = null
}

const saveDistribution = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingDistribution.value ? 'PUT' : 'POST'
    const url = editingDistribution.value 
      ? `/distributions/${editingDistribution.value.id}` 
      : '/distributions'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingDistribution.value ? 'Realisasi berhasil diperbarui' : 'Realisasi berhasil ditambahkan')
      closeModal()
      fetchDistributions(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving distribution:', error)
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

const deleteDistribution = async (id: number) => {
  if (!id) {
    alert('ID distribusi tidak valid')
    return
  }
  
  if (confirm('Apakah Anda yakin ingin menghapus realisasi ini?')) {
    try {
      const response = await axios.delete(`/distributions/${id}`)
      if (response.data.success) {
        alert('Realisasi berhasil dihapus')
        fetchDistributions(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting distribution:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}

const viewDistribution = (distribution: any) => {
  if (!distribution || !distribution.id) {
    alert('Data distribusi tidak valid')
    return
  }
  selectedDistribution.value = distribution
  showViewModal.value = true
}

// Utility functions
const formatCurrency = (amount: number) => {
  if (!amount) return 'Rp0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date: string) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'pending': 'Pending',
    'completed': 'Selesai', 
    'cancelled': 'Dibatalkan'
  }
  return statusMap[status] || status || '-'
}
</script>