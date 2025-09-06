<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Akuntansi Syariah</h1>
      <p class="text-gray-600">Kelola akuntansi syariah dan kepatuhan BAZNAS</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100">
              <DollarSign class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Dana</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(dashboardData?.total_funds || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100">
              <PieChart class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Dana Amil</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(dashboardData?.amil_funds || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100">
              <Users class="w-6 h-6 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Dana Asnaf</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(dashboardData?.asnaf_funds || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100">
              <CheckCircle class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Compliance Score</p>
              <p class="text-2xl font-bold text-gray-900">{{ dashboardData?.compliance_score || 0 }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Fund Categories Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Fund Categories -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Kategori Dana</h3>
            <button @click="showFundCategoryModal = true" class="btn btn-sm btn-primary">
              <Plus class="w-4 h-4 mr-2" />
              Tambah
            </button>
          </div>
          <div class="space-y-3">
            <div 
              v-for="category in fundCategories" 
              :key="category.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="font-medium text-gray-900">{{ category.nama }}</p>
                <p class="text-sm text-gray-600">{{ category.kode }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-blue-600">{{ formatCurrency(category.total_balance || 0) }}</p>
                <p class="text-xs text-gray-500">{{ category.amil_percentage }}% Amil</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
            <router-link to="/sharia-transactions" class="text-blue-600 hover:text-blue-800 text-sm">
              Lihat Semua
            </router-link>
          </div>
          <div class="space-y-3">
            <div 
              v-for="transaction in recentTransactions" 
              :key="transaction.id"
              class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
            >
              <div>
                <p class="font-medium text-gray-900">{{ transaction.keterangan }}</p>
                <p class="text-sm text-gray-600">{{ formatDate(transaction.tanggal) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium" :class="transaction.jenis === 'debit' ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.jenis === 'debit' ? '+' : '-' }}{{ formatCurrency(transaction.jumlah) }}
                </p>
                <span :class="getComplianceStatusClass(transaction.baznas_compliance_status)">
                  {{ transaction.baznas_compliance_status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <router-link to="/sharia-transactions" class="card hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <Database class="w-8 h-8 text-blue-600 mr-4" />
            <div>
              <h3 class="font-semibold text-gray-900">Transaksi Syariah</h3>
              <p class="text-sm text-gray-600">Kelola transaksi akuntansi syariah</p>
            </div>
          </div>
        </router-link>

        <router-link to="/baznas-reports" class="card hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <FileText class="w-8 h-8 text-green-600 mr-4" />
            <div>
              <h3 class="font-semibold text-gray-900">Laporan BAZNAS</h3>
              <p class="text-sm text-gray-600">Generate dan kelola laporan BAZNAS</p>
            </div>
          </div>
        </router-link>

        <router-link to="/sharia-accounts" class="card hover:shadow-md transition-shadow">
          <div class="flex items-center">
            <Building class="w-8 h-8 text-purple-600 mr-4" />
            <div>
              <h3 class="font-semibold text-gray-900">Chart of Accounts</h3>
              <p class="text-sm text-gray-600">Kelola bagan akun syariah</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Fund Category Modal -->
    <div v-if="showFundCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Kategori Dana</h3>
            <button @click="closeFundCategoryModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveFundCategory">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                <input 
                  v-model="fundCategoryForm.nama" 
                  type="text" 
                  class="form-input" 
                  placeholder="Contoh: Zakat Fitrah"
                  required 
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode</label>
                <input 
                  v-model="fundCategoryForm.kode" 
                  type="text" 
                  class="form-input" 
                  placeholder="Contoh: ZF"
                  required 
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Persentase Amil (%)</label>
                <input 
                  v-model.number="fundCategoryForm.amil_percentage" 
                  type="number" 
                  class="form-input" 
                  min="0" 
                  max="12.5" 
                  step="0.1"
                  placeholder="Maksimal 12.5%"
                  required 
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea 
                  v-model="fundCategoryForm.deskripsi" 
                  class="form-input" 
                  rows="3"
                  placeholder="Deskripsi kategori dana"
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeFundCategoryModal" class="btn btn-secondary">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-primary">
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
import { 
  DollarSign, PieChart, Users, CheckCircle, Plus, Database, 
  FileText, Building, X 
} from 'lucide-vue-next'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const dashboardData = ref<any>(null)
const fundCategories = ref<any[]>([])
const recentTransactions = ref<any[]>([])
const showFundCategoryModal = ref(false)

// Form
const fundCategoryForm = ref({
  nama: '',
  kode: '',
  amil_percentage: 12.5,
  deskripsi: ''
})

// Methods
const loadDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/sharia-accounting/dashboard')
    dashboardData.value = response.data
    fundCategories.value = response.data.fund_categories || []
    recentTransactions.value = response.data.recent_transactions || []
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    isLoading.value = false
  }
}

const saveFundCategory = async () => {
  try {
    isSubmitting.value = true
    await axios.post('/sharia-accounting/fund-categories', fundCategoryForm.value)
    await loadDashboardData()
    closeFundCategoryModal()
  } catch (error) {
    console.error('Error saving fund category:', error)
  } finally {
    isSubmitting.value = false
  }
}

const closeFundCategoryModal = () => {
  showFundCategoryModal.value = false
  fundCategoryForm.value = {
    nama: '',
    kode: '',
    amil_percentage: 12.5,
    deskripsi: ''
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getComplianceStatusClass = (status: string) => {
  switch (status) {
    case 'compliant':
      return 'text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full'
    case 'non_compliant':
      return 'text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full'
    case 'pending':
      return 'text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full'
    default:
      return 'text-xs px-2 py-1 bg-gray-100 text-gray-800 rounded-full'
  }
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>