<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Data Muzakki</h1>
      <p class="text-gray-600">Kelola data muzakki (pemberi zakat)</p>
    </div>

    <!-- Actions -->
    <div class="mb-6">
      <!-- Search Filters -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search by Name -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
            <User class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchName"
              type="text"
              placeholder="Masukkan nama..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchMuzakki()"
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
              @keyup.enter="() => fetchMuzakki()"
            />
          </div>
          
          <!-- Search by Contact -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Kontak</label>
            <Phone class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchContact"
              type="text"
              placeholder="Telepon atau email..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchMuzakki()"
            />
          </div>
          
          <!-- Filter by Type -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
            <Building class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <select
              v-model="filterJenis"
              class="form-input pl-10 w-full"
              @change="() => fetchMuzakki()"
            >
              <option value="">Semua Jenis</option>
              <option value="individu">Individu</option>
              <option value="perusahaan">Perusahaan</option>
            </select>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button
              @click="() => fetchMuzakki()"
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
            Tambah Muzakki
          </button>
        </div>
        
        <!-- Search Results Info -->
        <div v-if="hasActiveFilters" class="mt-3 text-sm text-gray-600">
          <div class="flex items-center">
            <span class="font-medium">{{ muzakkiList.length }} hasil ditemukan</span>
            <div class="ml-2 flex flex-wrap gap-1">
              <span v-if="searchName" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                Nama: "{{ searchName }}"
              </span>
              <span v-if="searchAddress" class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                Alamat: "{{ searchAddress }}"
              </span>
              <span v-if="searchContact" class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">
                Kontak: "{{ searchContact }}"
              </span>
              <span v-if="filterJenis" class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">
                Jenis: {{ filterJenis }}
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
                  Nama
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
                  <IdCard class="w-4 h-4 mr-2" />
                  NIK
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Building class="w-4 h-4 mr-2" />
                  Jenis
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Phone class="w-4 h-4 mr-2" />
                  Kontak
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
            <tr v-for="muzakki in muzakkiList" :key="muzakki.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ muzakki.nama }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ muzakki.alamat }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ muzakki.nik }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getJenisClass(muzakki.jenis)">
                  {{ muzakki.jenis }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center space-y-1 flex-col">
                  <div v-if="muzakki.telepon" class="flex items-center space-x-2">
                    <div class="flex items-center">
                      <Phone class="w-3 h-3 mr-1 text-gray-400" />
                      <span>{{ muzakki.telepon }}</span>
                    </div>
                    <a 
                      :href="getWhatsAppLink(muzakki.telepon)"
                      target="_blank"
                      class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors duration-200"
                      title="Hubungi via WhatsApp"
                    >
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                      </svg>
                    </a>
                  </div>
                  <div v-else class="text-gray-400">-</div>
                  <div v-if="muzakki.email" class="flex items-center">
                    <Mail class="w-3 h-3 mr-1 text-gray-400" />
                    <a 
                      :href="`mailto:${muzakki.email}`"
                      class="text-blue-600 hover:text-blue-800 hover:underline"
                    >
                      {{ muzakki.email }}
                    </a>
                  </div>
                  <div v-else class="text-gray-400">-</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="openModal(muzakki)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteMuzakki(muzakki.id)"
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
              {{ editingMuzakki ? 'Edit Muzakki' : 'Tambah Muzakki' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <!-- OCR Section Toggle (Only for Create) -->
          <div v-if="!editingMuzakki" class="mb-6">
            <button 
              @click="showOCRSection = !showOCRSection"
              type="button"
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Camera class="w-4 h-4 mr-2" />
              {{ showOCRSection ? 'Sembunyikan' : 'Scan KTP/Kartu Identitas' }}
            </button>
          </div>
          
          <!-- OCR Upload Component -->
          <div v-if="showOCRSection && !editingMuzakki" class="mb-6">
            <OCRUpload 
              v-model="ocrResult"
              @dataExtracted="handleOCRData"
              :documentType="'identity'"
            />
          </div>
          
          <form @submit.prevent="saveMuzakki">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Basic Information -->
              <div class="sm:col-span-2">
                <h4 class="text-base font-semibold text-gray-900 mb-4">Informasi Dasar</h4>
              </div>
              <div>
                <label class="form-label">Nama *</label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.nama" type="text" required class="form-input pl-10" placeholder="Masukkan nama lengkap" />
                </div>
              </div>
              
              <div>
                <label class="form-label">NIK *</label>
                <div class="relative">
                  <IdCard class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.nik" type="text" required class="form-input pl-10" placeholder="Nomor Induk Kependudukan (16 digit)" maxlength="16" pattern="[0-9]{16}" />
                </div>
                <p class="text-xs text-gray-500 mt-1">Masukkan 16 digit NIK sesuai KTP</p>
              </div>
              
              <!-- Additional Identity Fields -->
              <div class="sm:col-span-2">
                <h4 class="text-base font-semibold text-gray-900 mb-4 mt-6">Informasi Tambahan</h4>
              </div>
              
              <div>
                <label class="form-label">Tempat Lahir</label>
                <input v-model="form.tempat_lahir" type="text" class="form-input" placeholder="Tempat lahir" />
              </div>
              
              <div>
                <label class="form-label">Tanggal Lahir</label>
                <input v-model="form.tanggal_lahir" type="date" class="form-input" />
              </div>
              
              <div>
                <label class="form-label">Jenis Kelamin</label>
                <select v-model="form.jenis_kelamin" class="form-input">
                  <option value="">Pilih jenis kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>
              
              <div>
                <label class="form-label">Pekerjaan</label>
                <input v-model="form.pekerjaan" type="text" class="form-input" placeholder="Pekerjaan" />
              </div>
              
              <div>
                <label class="form-label">Alamat *</label>
                <div class="relative">
                  <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea v-model="form.alamat" required class="form-input pl-10" rows="3" placeholder="Alamat lengkap"></textarea>
                </div>
              </div>
              
              <div>
                <label class="form-label">Telepon</label>
                <div class="relative">
                  <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.telepon" type="text" class="form-input pl-10" placeholder="Nomor telepon" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Email</label>
                <div class="relative">
                  <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input v-model="form.email" type="email" class="form-input pl-10" placeholder="Alamat email" />
                </div>
              </div>
              
              <div>
                <label class="form-label">Jenis *</label>
                <div class="relative">
                  <Building class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.jenis" required class="form-input pl-10">
                    <option value="">Pilih jenis</option>
                    <option value="individu">Individu</option>
                    <option value="perusahaan">Perusahaan</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="form-label">Keterangan</label>
                <div class="relative">
                  <FileText class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea v-model="form.keterangan" class="form-input pl-10" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
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
import { Search, Plus, Edit, Trash2, User, IdCard, MapPin, Phone, Mail, Building, FileText, Settings, X, Camera } from 'lucide-vue-next'
import OCRUpload from '../components/OCRUpload.vue'
import type { OCRResult } from '../services/ocrService'

const muzakkiList = ref([])
const showModal = ref(false)
const editingMuzakki = ref(null)
const isLoading = ref(false)
const searchName = ref('')
const searchAddress = ref('')
const searchContact = ref('')
const filterJenis = ref('')
const ocrResult = ref<OCRResult | null>(null)
const showOCRSection = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
})

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return searchName.value || searchAddress.value || searchContact.value || filterJenis.value
})

const form = ref({
  nama: '',
  nik: '',
  alamat: '',
  telepon: '',
  email: '',
  jenis: '',
  keterangan: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  pekerjaan: ''
})

onMounted(() => {
  fetchMuzakki()
})

const fetchMuzakki = async (page = 1) => {
  try {
    const params: any = {
      page: page,
      per_page: 10 // Limit to 10 records per page
    }
    
    // Add search parameters
    if (searchName.value) {
      params.search_name = searchName.value
    }
    
    if (searchAddress.value) {
      params.search_address = searchAddress.value
    }
    
    if (searchContact.value) {
      params.search_contact = searchContact.value
    }
    
    if (filterJenis.value) {
      params.jenis = filterJenis.value
    }
    
    const response = await axios.get('/muzakki', {
      params: params
    })
    
    // Handle both paginated and direct data responses
    if (response.data.success) {
      const data = response.data.data
      muzakkiList.value = data.data || []
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        from: data.from || 0,
        to: data.to || 0,
        total: data.total || 0
      }
    } else {
      muzakkiList.value = response.data.data || response.data || []
    }
  } catch (error) {
    console.error('Error fetching muzakki:', error)
    muzakkiList.value = []
  }
}

const openModal = (muzakki = null) => {
  editingMuzakki.value = muzakki
  if (muzakki) {
    form.value = { ...muzakki }
  } else {
    form.value = {
      nama: '',
      nik: '',
      alamat: '',
      telepon: '',
      email: '',
      jenis: '',
      keterangan: '',
      tempat_lahir: '',
      tanggal_lahir: '',
      jenis_kelamin: '',
      pekerjaan: ''
    }
  }
  showModal.value = true
  showOCRSection.value = false
  ocrResult.value = null
}

const closeModal = () => {
  showModal.value = false
  editingMuzakki.value = null
}

const saveMuzakki = async () => {
  try {
    isLoading.value = true
    let response
    if (editingMuzakki.value) {
      response = await axios.put(`/muzakki/${editingMuzakki.value.id}`, form.value)
    } else {
      response = await axios.post('/muzakki', form.value)
    }
    
    if (response.data.success) {
      alert(response.data.message || 'Data berhasil disimpan')
      await fetchMuzakki(pagination.value.current_page)
      closeModal()
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error saving muzakki:', error)
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

const deleteMuzakki = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus muzakki ini?')) {
    try {
      const response = await axios.delete(`/muzakki/${id}`)
      if (response.data.success) {
        alert(response.data.message || 'Data berhasil dihapus')
        await fetchMuzakki(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error) {
      console.error('Error deleting muzakki:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}

const clearSearch = () => {
  searchName.value = ''
  searchAddress.value = ''
  searchContact.value = ''
  filterJenis.value = ''
  fetchMuzakki(1)
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchMuzakki(page)
  }
}

const getJenisClass = (jenis: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  return jenis === 'individu' 
    ? `${baseClass} bg-blue-100 text-blue-800`
    : `${baseClass} bg-green-100 text-green-800`
}

const getWhatsAppLink = (phoneNumber: string) => {
  if (!phoneNumber) return '#'
  
  // Remove all non-numeric characters
  let cleanNumber = phoneNumber.replace(/\D/g, '')
  
  // Convert Indonesian phone numbers to international format
  if (cleanNumber.startsWith('0')) {
    cleanNumber = '62' + cleanNumber.substring(1)
  } else if (!cleanNumber.startsWith('62')) {
    cleanNumber = '62' + cleanNumber
  }
  
  return `https://api.whatsapp.com/send?phone=${cleanNumber}`
}

// OCR handling methods
const handleOCRData = (extractedData: any) => {
  console.log('Received OCR data:', extractedData)
  
  // Auto-populate form with OCR data from ID card
  if (extractedData.nik) {
    form.value.nik = extractedData.nik
    console.log('Set NIK:', extractedData.nik)
  }
  
  if (extractedData.name) {
    form.value.nama = extractedData.name
    console.log('Set Name:', extractedData.name)
  }
  
  if (extractedData.address) {
    form.value.alamat = extractedData.address
    console.log('Set Address:', extractedData.address)
  }
  
  if (extractedData.birthPlace) {
    form.value.tempat_lahir = extractedData.birthPlace
    console.log('Set Birth Place:', extractedData.birthPlace)
  }
  
  if (extractedData.birthDate) {
    form.value.tanggal_lahir = extractedData.birthDate
    console.log('Set Birth Date:', extractedData.birthDate)
  }
  
  if (extractedData.gender) {
    form.value.jenis_kelamin = extractedData.gender
    console.log('Set Gender:', extractedData.gender)
  }
  
  if (extractedData.occupation) {
    form.value.pekerjaan = extractedData.occupation
    console.log('Set Occupation:', extractedData.occupation)
  }
  
  // Auto-detect jenis based on occupation
  if (extractedData.occupation) {
    const businessKeywords = ['PT', 'CV', 'UD', 'DIREKTUR', 'CEO', 'PENGUSAHA']
    const isCompany = businessKeywords.some(keyword => 
      extractedData.occupation.toUpperCase().includes(keyword)
    )
    if (isCompany) {
      form.value.jenis = 'perusahaan'
      console.log('Set Jenis: perusahaan')
    } else {
      form.value.jenis = 'individu'
      console.log('Set Jenis: individu')
    }
  }
  
  // Add OCR info to keterangan
  const ocrInfo = []
  if (extractedData.religion) ocrInfo.push(`Agama: ${extractedData.religion}`)
  if (extractedData.maritalStatus) ocrInfo.push(`Status: ${extractedData.maritalStatus}`)
  if (extractedData.nationality) ocrInfo.push(`WN: ${extractedData.nationality}`)
  
  if (ocrInfo.length > 0) {
    form.value.keterangan = `OCR: ${ocrInfo.join(', ')}`
    console.log('Set Keterangan:', form.value.keterangan)
  }
  
  // Show success message
  alert('Data berhasil diisi dari hasil OCR!')
}
</script>