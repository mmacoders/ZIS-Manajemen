<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Data Aset</h1>
      <p class="text-gray-600">Kelola data aset</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <button 
        @click="openModal()"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
      >
        <Plus class="w-4 h-4 mr-2" />
        Tambah Aset
      </button>
      
      <div class="flex flex-wrap items-center gap-2">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari aset..." 
          class="form-input"
          @keyup.enter="fetchAssets"
        />
        <button 
          @click="fetchAssets"
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi</label>
          <select v-model="filters.kondisi" class="form-select" @change="fetchAssets">
            <option value="">Semua Kondisi</option>
            <option value="baik">Baik</option>
            <option value="rusak">Rusak</option>
            <option value="perlu_perbaikan">Perlu Perbaikan</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
          <select v-model="filters.lokasi" class="form-select" @change="fetchAssets">
            <option value="">Semua Lokasi</option>
            <option value="Kantor Pusat">Kantor Pusat</option>
            <option value="Kantor Cabang">Kantor Cabang</option>
            <option value="Gudang">Gudang</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Mulai</label>
          <input v-model="filters.start_year" type="number" class="form-input" @change="fetchAssets" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Selesai</label>
          <input v-model="filters.end_year" type="number" class="form-input" @change="fetchAssets" />
        </div>
      </div>
    </div>

    <!-- Assets Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Aset</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai (Rp)</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="assets.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada aset ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="asset in assets" :key="asset.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ asset.kode_aset }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="asset.nama_aset">
                  {{ asset.nama_aset }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ asset.tahun_pengadaan }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ asset.lokasi }}</td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ asset.nilai_aset ? formatCurrency(asset.nilai_aset) : '-' }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span :class="getConditionClass(asset.kondisi)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                  {{ getConditionText(asset.kondisi) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm font-medium space-x-2">
                <button 
                  @click="openModal(asset)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteAsset(asset.id)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} aset
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
            {{ editingAsset ? 'Edit Aset' : 'Tambah Aset' }}
          </h3>
          
          <form @submit.prevent="saveAsset">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aset *</label>
                  <input 
                    v-model="form.nama_aset" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan nama aset"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kode Aset *</label>
                  <input 
                    v-model="form.kode_aset" 
                    type="text" 
                    required 
                    class="form-input" 
                    placeholder="Masukkan kode aset"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Pengadaan *</label>
                  <input 
                    v-model.number="form.tahun_pengadaan" 
                    type="number" 
                    required 
                    min="1900" 
                    :max="new Date().getFullYear() + 1"
                    class="form-input" 
                    placeholder="Masukkan tahun pengadaan"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi *</label>
                  <select v-model="form.lokasi" required class="form-select">
                    <option value="">Pilih Lokasi</option>
                    <option value="Kantor Pusat">Kantor Pusat</option>
                    <option value="Kantor Cabang">Kantor Cabang</option>
                    <option value="Gudang">Gudang</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Aset (Rp)</label>
                  <input 
                    v-model.number="form.nilai_aset" 
                    type="number" 
                    min="0" 
                    step="1000"
                    class="form-input" 
                    placeholder="Masukkan nilai aset (opsional)"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi *</label>
                  <select v-model="form.kondisi" required class="form-select">
                    <option value="">Pilih Kondisi</option>
                    <option value="baik">Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="perlu_perbaikan">Perlu Perbaikan</option>
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
const assets = ref<any[]>([])
const showModal = ref(false)
const editingAsset = ref<any>(null)
const loading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Filters
const filters = ref({
  kondisi: '',
  lokasi: '',
  start_year: '',
  end_year: ''
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
  nama_aset: '',
  kode_aset: '',
  tahun_pengadaan: new Date().getFullYear(),
  kondisi: 'baik',
  lokasi: '',
  nilai_aset: null as number | null,
  keterangan: ''
})

// Lifecycle
onMounted(() => {
  fetchAssets()
})

// Methods
const fetchAssets = async (page = 1) => {
  try {
    loading.value = true
    const params: any = {
      page,
      per_page: 10
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await axios.get('/assets', { params })
    
    if (response.data.success) {
      assets.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      assets.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching assets:', error)
    assets.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchAssets(page)
  }
}

const openModal = (asset: any = null) => {
  editingAsset.value = asset
  if (asset) {
    form.value = { ...asset }
  } else {
    form.value = {
      nama_aset: '',
      kode_aset: '',
      tahun_pengadaan: new Date().getFullYear(),
      kondisi: 'baik',
      lokasi: '',
      nilai_aset: null,
      keterangan: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingAsset.value = null
}

const saveAsset = async () => {
  try {
    isSubmitting.value = true
    
    const method = editingAsset.value ? 'PUT' : 'POST'
    // Use relative paths since axios baseURL already includes /api
    const url = editingAsset.value 
      ? `/assets/${editingAsset.value.id}` 
      : '/assets'
    
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    if (response.data.success) {
      alert(editingAsset.value ? 'Aset berhasil diperbarui' : 'Aset berhasil ditambahkan')
      closeModal()
      fetchAssets(pagination.value.current_page)
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving asset:', error)
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

const deleteAsset = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus aset ini?')) {
    try {
      // Use relative path since axios baseURL already includes /api
      const response = await axios.delete(`/assets/${id}`)
      if (response.data.success) {
        alert('Aset berhasil dihapus')
        fetchAssets(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting asset:', error)
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

const getConditionClass = (condition: string) => {
  switch (condition) {
    case 'baik':
      return 'bg-green-100 text-green-800'
    case 'rusak':
      return 'bg-red-100 text-red-800'
    case 'perlu_perbaikan':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getConditionText = (condition: string) => {
  const conditionMap: Record<string, string> = {
    'baik': 'Baik',
    'rusak': 'Rusak', 
    'perlu_perbaikan': 'Perlu Perbaikan'
  }
  return conditionMap[condition] || condition
}
</script>