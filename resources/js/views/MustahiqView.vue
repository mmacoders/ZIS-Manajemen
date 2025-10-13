<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Data Mustahiq</h1>
      <p class="text-gray-600">Kelola data penerima zakat (8 asnaf)</p>
    </div>

    <!-- Search Filters -->
    <div class="mb-6">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search by Name -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
            <User class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchName"
              type="text"
              placeholder="Masukkan nama..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchMustahiq()"
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
              @keyup.enter="() => fetchMustahiq()"
            />
          </div>
          
          <!-- Search by Contact -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Kontak</label>
            <Phone class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <input
              v-model="searchContact"
              type="text"
              placeholder="Nomor telepon..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchMustahiq()"
            />
          </div>
          
          <!-- Filter by Category -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <Tag class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <select
              v-model="filterKategori"
              class="form-input pl-10 w-full"
              @change="() => fetchMustahiq()"
            >
              <option value="">Semua Kategori</option>
              <option value="fakir">Fakir</option>
              <option value="miskin">Miskin</option>
              <option value="amil">Amil</option>
              <option value="muallaf">Muallaf</option>
              <option value="riqab">Riqab</option>
              <option value="gharim">Gharim</option>
              <option value="fisabilillah">Fisabilillah</option>
              <option value="ibnu_sabil">Ibnu Sabil</option>
            </select>
          </div>
          
          <!-- Filter by Status -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <CheckCircle class="absolute left-3 top-9 w-4 h-4 text-gray-400" />
            <select
              v-model="filterStatus"
              class="form-input pl-10 w-full"
              @change="() => fetchMustahiq()"
            >
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Non-aktif</option>
            </select>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button
              @click="() => fetchMustahiq()"
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
            Tambah Mustahiq
          </button>
        </div>
        
        <!-- Search Results Info -->
        <div v-if="hasActiveFilters" class="mt-3 text-sm text-gray-600">
          <div class="flex items-center">
            <span class="font-medium">{{ mustahiqList.length }} hasil ditemukan</span>
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
              <span v-if="filterKategori" class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">
                Kategori: {{ getKategoriName(filterKategori) }}
              </span>
              <span v-if="filterStatus" class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                Status: {{ filterStatus }}
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
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <User class="w-4 h-4 mr-2" />
                  Nama
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <IdCard class="w-4 h-4 mr-2" />
                  NIK
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <MapPin class="w-4 h-4 mr-2" />
                  Alamat
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Tag class="w-4 h-4 mr-2" />
                  Kategori
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Phone class="w-4 h-4 mr-2" />
                  Kontak
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <CheckCircle class="w-4 h-4 mr-2" />
                  Status
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs lg:text-sm font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Settings class="w-4 h-4 mr-2" />
                  Aksi
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="mustahiq in mustahiqList" :key="mustahiq.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm lg:text-base font-medium text-gray-900">{{ mustahiq.nama }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm lg:text-base text-gray-900">
                {{ mustahiq.nik }}
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ mustahiq.alamat }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getKategoriClass(mustahiq.kategori)">
                  {{ getKategoriName(mustahiq.kategori) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div v-if="mustahiq.telepon" class="flex items-center">
                  <Phone class="w-3 h-3 mr-1 text-gray-400" />
                  <span>{{ mustahiq.telepon }}</span>
                </div>
                <div v-else class="text-gray-400">-</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(mustahiq.status)">
                  {{ mustahiq.status === 'aktif' ? 'Aktif' : 'Non-aktif' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="openModal(mustahiq)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button
                  @click="deleteMustahiq(mustahiq.id)"
                  class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                  title="Hapus"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="mustahiqList.length === 0">
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                Tidak ada data mustahiq
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
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md md:max-w-lg lg:max-w-xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">
            {{ editingMustahiq ? 'Edit Mustahiq' : 'Tambah Mustahiq' }}
          </h3>
          
          <!-- OCR Section (Only for Create) -->
          <div v-if="!editingMustahiq" class="mb-6">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-base font-semibold text-gray-900">Scan Dokumen Identitas</h4>
              <button
                type="button"
                @click="showOCRSection = !showOCRSection"
                class="flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
              >
                <Scan class="w-4 h-4 mr-2" />
                {{ showOCRSection ? 'Sembunyikan OCR' : 'Gunakan OCR' }}
              </button>
            </div>
            
            <div v-if="showOCRSection" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
              <OCRUpload 
                :document-type="'identity'"
                :show-language-selector="true"
                :enable-multi-language="true"
                @dataExtracted="handleOCRData"
                @multiLanguageResult="handleMultiLanguageResult"
              />
            </div>
          </div>
          
          <form @submit.prevent="saveMustahiq">
            <div class="space-y-4">
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
                  <input v-model="form.nik" type="text" required class="form-input pl-10" placeholder="Nomor Induk Kependudukan" maxlength="16" />
                </div>
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
                <label class="form-label">Kategori *</label>
                <div class="relative">
                  <Tag class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.kategori" required class="form-input pl-10">
                    <option value="">Pilih kategori</option>
                    <option value="fakir">Fakir</option>
                    <option value="miskin">Miskin</option>
                    <option value="amil">Amil</option>
                    <option value="muallaf">Muallaf</option>
                    <option value="riqab">Riqab</option>
                    <option value="gharim">Gharim</option>
                    <option value="fisabilillah">Fisabilillah</option>
                    <option value="ibnu_sabil">Ibnu Sabil</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="form-label">Status *</label>
                <div class="relative">
                  <CheckCircle class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <select v-model="form.status" required class="form-input pl-10">
                    <option value="">Pilih status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Non-aktif</option>
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
import { Search, Plus, Edit, Trash2, User, IdCard, MapPin, Phone, Tag, CheckCircle, FileText, Settings, X, Scan } from 'lucide-vue-next'
import OCRUpload from '../components/OCRUpload.vue'
import type { OCRResult, MultiLanguageOCRResult } from '../services/ocrService'

// Define interface for Mustahiq data
interface Mustahiq {
  id: number
  nama: string
  nik: string
  alamat: string
  telepon: string
  kategori: string
  keterangan: string
  status: string
  created_at?: string
  updated_at?: string
}

const mustahiqList = ref<Mustahiq[]>([])
const showModal = ref(false)
const editingMustahiq = ref<Mustahiq | null>(null)
const isLoading = ref(false)
const searchName = ref('')
const searchAddress = ref('')
const searchContact = ref('')
const filterKategori = ref('')
const filterStatus = ref('')
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
  return searchName.value || searchAddress.value || searchContact.value || filterKategori.value || filterStatus.value
})

interface MustahiqForm {
  nama: string
  nik: string
  alamat: string
  telepon: string
  kategori: string
  status: string
  keterangan: string
  // Additional fields for enhanced identity data
  tempat_lahir: string
  tanggal_lahir: string
  jenis_kelamin: string
  rt: string
  rw: string
  kelurahan: string
  kecamatan: string
  agama: string
  status_perkawinan: string
  pekerjaan: string
  kewarganegaraan: string
  provinsi: string
  kota: string
}

const form = ref<MustahiqForm>({
  nama: '',
  nik: '',
  alamat: '',
  telepon: '',
  kategori: '',
  status: 'aktif',
  keterangan: '',
  // Additional fields for enhanced identity data
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  rt: '',
  rw: '',
  kelurahan: '',
  kecamatan: '',
  agama: '',
  status_perkawinan: '',
  pekerjaan: '',
  kewarganegaraan: '',
  provinsi: '',
  kota: ''
})

onMounted(() => {
  fetchMustahiq()
})

const fetchMustahiq = async (page = 1) => {
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
    
    if (filterKategori.value) {
      params.kategori = filterKategori.value
    }
    
    if (filterStatus.value) {
      params.status = filterStatus.value
    }
    
    const response = await axios.get('/mustahiq', {
      params: params
    })
    
    // Handle both paginated and direct data responses
    if (response.data.success) {
      const data = response.data.data
      mustahiqList.value = data.data || []
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        from: data.from || 0,
        to: data.to || 0,
        total: data.total || 0
      }
    } else {
      mustahiqList.value = response.data.data || response.data || []
    }
  } catch (error) {
    console.error('Error fetching mustahiq:', error)
    mustahiqList.value = []
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchMustahiq(page)
  }
}

const clearSearch = () => {
  searchName.value = ''
  searchAddress.value = ''
  searchContact.value = ''
  filterKategori.value = ''
  filterStatus.value = ''
  fetchMustahiq(1)
}

const openModal = (mustahiq: Mustahiq | null = null) => {
  editingMustahiq.value = mustahiq
  if (mustahiq) {
    // When editing, map the Mustahiq object to the form structure
    form.value = {
      nama: mustahiq.nama,
      nik: mustahiq.nik,
      alamat: mustahiq.alamat,
      telepon: mustahiq.telepon || '',
      kategori: mustahiq.kategori,
      status: mustahiq.status,
      keterangan: mustahiq.keterangan || '',
      // Additional fields for enhanced identity data (not in Mustahiq model)
      tempat_lahir: '',
      tanggal_lahir: '',
      jenis_kelamin: '',
      rt: '',
      rw: '',
      kelurahan: '',
      kecamatan: '',
      agama: '',
      status_perkawinan: '',
      pekerjaan: '',
      kewarganegaraan: '',
      provinsi: '',
      kota: ''
    }
  } else {
    form.value = {
      nama: '',
      nik: '',
      alamat: '',
      telepon: '',
      kategori: '',
      status: 'aktif',
      keterangan: '',
      // Additional fields for enhanced identity data
      tempat_lahir: '',
      tanggal_lahir: '',
      jenis_kelamin: '',
      rt: '',
      rw: '',
      kelurahan: '',
      kecamatan: '',
      agama: '',
      status_perkawinan: '',
      pekerjaan: '',
      kewarganegaraan: '',
      provinsi: '',
      kota: ''
    }
  }
  showOCRSection.value = false // Reset OCR section when opening modal
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingMustahiq.value = null
  showOCRSection.value = false
}

// Handle OCR extracted data
const handleOCRData = (extractedData: OCRResult['extractedData']) => {
  // Map OCR data to form fields
  if (extractedData.name) {
    form.value.nama = extractedData.name
  }
  if (extractedData.nik) {
    form.value.nik = extractedData.nik
  }
  if (extractedData.address) {
    form.value.alamat = extractedData.address
  }
  if (extractedData.phoneNumber) {
    form.value.telepon = extractedData.phoneNumber
  }
  
  // Additional identity fields
  if (extractedData.birthPlace) {
    form.value.tempat_lahir = extractedData.birthPlace
  }
  if (extractedData.birthDate) {
    form.value.tanggal_lahir = extractedData.birthDate
  }
  if (extractedData.gender) {
    form.value.jenis_kelamin = extractedData.gender
  }
  if (extractedData.rt) {
    form.value.rt = extractedData.rt
  }
  if (extractedData.rw) {
    form.value.rw = extractedData.rw
  }
  if (extractedData.kelurahan) {
    form.value.kelurahan = extractedData.kelurahan
  }
  if (extractedData.kecamatan) {
    form.value.kecamatan = extractedData.kecamatan
  }
  if (extractedData.religion) {
    form.value.agama = extractedData.religion
  }
  if (extractedData.maritalStatus) {
    form.value.status_perkawinan = extractedData.maritalStatus
  }
  if (extractedData.occupation) {
    form.value.pekerjaan = extractedData.occupation
  }
  if (extractedData.nationality) {
    form.value.kewarganegaraan = extractedData.nationality
  }
  if (extractedData.province) {
    form.value.provinsi = extractedData.province
  }
  if (extractedData.city) {
    form.value.kota = extractedData.city
  }
}

// Handle multi-language OCR results
const handleMultiLanguageResult = (result: MultiLanguageOCRResult) => {
  console.log('Multi-language OCR result:', {
    detectedLanguage: result.detectedLanguage,
    languageConfidence: result.languageConfidence,
    alternativeResults: result.alternativeResults
  })
  
  // Use the best result for form filling
  handleOCRData(result.extractedData)
}

const saveMustahiq = async () => {
  try {
    isLoading.value = true
    let response
    if (editingMustahiq.value) {
      response = await axios.put(`/mustahiq/${editingMustahiq.value.id}`, form.value)
    } else {
      response = await axios.post('/mustahiq', form.value)
    }
    
    if (response.data.success) {
      alert(response.data.message || 'Data berhasil disimpan')
      await fetchMustahiq(pagination.value.current_page)
      closeModal()
    } else {
      alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error: any) {
    console.error('Error saving mustahiq:', error)
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

const deleteMustahiq = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus mustahiq ini?')) {
    try {
      const response = await axios.delete(`/mustahiq/${id}`)
      if (response.data.success) {
        alert(response.data.message || 'Data berhasil dihapus')
        await fetchMustahiq(pagination.value.current_page)
      } else {
        alert('Terjadi kesalahan: ' + (response.data.message || 'Unknown error'))
      }
    } catch (error: any) {
      console.error('Error deleting mustahiq:', error)
      alert('Terjadi kesalahan: ' + (error.response?.data?.message || error.message))
    }
  }
}

const getKategoriName = (kategori: string) => {
  const names: Record<string, string> = {
    'fakir': 'Fakir',
    'miskin': 'Miskin',
    'amil': 'Amil',
    'muallaf': 'Muallaf',
    'riqab': 'Riqab',
    'gharim': 'Gharim',
    'fisabilillah': 'Fisabilillah',
    'ibnu_sabil': 'Ibnu Sabil'
  }
  return names[kategori] || kategori
}

const getKategoriClass = (kategori: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  const colors: Record<string, string> = {
    'fakir': 'bg-red-100 text-red-800',
    'miskin': 'bg-orange-100 text-orange-800',
    'amil': 'bg-blue-100 text-blue-800',
    'muallaf': 'bg-green-100 text-green-800',
    'riqab': 'bg-purple-100 text-purple-800',
    'gharim': 'bg-yellow-100 text-yellow-800',
    'fisabilillah': 'bg-indigo-100 text-indigo-800',
    'ibnu_sabil': 'bg-pink-100 text-pink-800'
  }
  return `${baseClass} ${colors[kategori] || 'bg-gray-100 text-gray-800'}`
}

const getStatusClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  return status === 'aktif' 
    ? `${baseClass} bg-green-100 text-green-800`
    : `${baseClass} bg-gray-100 text-gray-800`
}
</script>