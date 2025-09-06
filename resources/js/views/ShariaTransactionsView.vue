<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Transaksi Syariah</h1>
      <p class="text-gray-600">Kelola transaksi akuntansi syariah dan integrasi dengan ZIS</p>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div class="flex items-center space-x-2">
        <button @click="showCreateModal = true" class="btn btn-primary">
          <Plus class="w-4 h-4 mr-2" />
          Transaksi Manual
        </button>
        <button @click="showZisIntegrationModal = true" class="btn btn-secondary">
          <Database class="w-4 h-4 mr-2" />
          Integrasi ZIS
        </button>
        <button @click="loadTransactions" class="btn btn-secondary">
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex items-center space-x-2">
        <div class="relative">
          <Search class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
          <input 
            v-model="searchQuery" 
            @input="searchTransactions"
            type="text" 
            placeholder="Cari transaksi..." 
            class="form-input pl-10"
          />
        </div>
        
        <select v-model="statusFilter" @change="loadTransactions" class="form-select text-sm">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="posted">Posted</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <select v-model="complianceFilter" @change="loadTransactions" class="form-select text-sm">
          <option value="">Semua Compliance</option>
          <option value="compliant">Compliant</option>
          <option value="non_compliant">Non-Compliant</option>
          <option value="pending">Pending</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Transactions Table -->
    <div v-else-if="transactions.length > 0" class="card overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Transaksi
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Kategori Dana
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Akun
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Jumlah
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Compliance
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="transaction in transactions" :key="transaction.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ transaction.nomor_transaksi }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(transaction.tanggal) }}</p>
                <p class="text-xs text-gray-400">{{ transaction.keterangan }}</p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ transaction.fund_category?.nama }}</p>
                <p class="text-xs text-gray-500">{{ transaction.fund_category?.kode }}</p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ transaction.account?.nama }}</p>
                <p class="text-xs text-gray-500">{{ transaction.account?.kode }}</p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-right">
                <p class="text-sm font-medium" :class="transaction.jenis === 'debit' ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.jenis === 'debit' ? '+' : '-' }}{{ formatCurrency(transaction.jumlah) }}
                </p>
                <p v-if="transaction.amil_amount > 0" class="text-xs text-gray-500">
                  Amil: {{ formatCurrency(transaction.amil_amount) }}
                </p>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(transaction.status)">
                {{ getStatusText(transaction.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getComplianceStatusClass(transaction.baznas_compliance_status)">
                {{ getComplianceText(transaction.baznas_compliance_status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center space-x-2">
                <button 
                  @click="viewTransaction(transaction)" 
                  class="text-blue-600 hover:text-blue-900"
                  title="Lihat Detail"
                >
                  <Eye class="w-4 h-4" />
                </button>
                
                <button 
                  v-if="transaction.status === 'draft'"
                  @click="editTransaction(transaction)" 
                  class="text-yellow-600 hover:text-yellow-900"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                
                <button 
                  v-if="transaction.status === 'draft'"
                  @click="postTransaction(transaction)" 
                  class="text-green-600 hover:text-green-900"
                  title="Post Transaction"
                >
                  <CheckCircle class="w-4 h-4" />
                </button>
                
                <button 
                  v-if="transaction.status === 'draft'"
                  @click="deleteTransaction(transaction)" 
                  class="text-red-600 hover:text-red-900"
                  title="Delete"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="card text-center py-12">
      <Database class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada transaksi</h3>
      <p class="text-gray-500 mb-4">Mulai dengan membuat transaksi manual atau integrasi dengan ZIS</p>
      <div class="space-x-2">
        <button @click="showCreateModal = true" class="btn btn-primary">
          <Plus class="w-4 h-4 mr-2" />
          Transaksi Manual
        </button>
        <button @click="showZisIntegrationModal = true" class="btn btn-secondary">
          <Database class="w-4 h-4 mr-2" />
          Integrasi ZIS
        </button>
      </div>
    </div>

    <!-- Create/Edit Transaction Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ editingTransaction ? 'Edit Transaksi' : 'Transaksi Manual Baru' }}
            </h3>
            <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveTransaction">
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Dana</label>
                  <select v-model="form.fund_category_id" class="form-select" required>
                    <option value="">Pilih Kategori</option>
                    <option v-for="category in fundCategories" :key="category.id" :value="category.id">
                      {{ category.nama }} ({{ category.kode }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Akun</label>
                  <select v-model="form.account_id" class="form-select" required>
                    <option value="">Pilih Akun</option>
                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                      {{ account.nama }} ({{ account.kode }})
                    </option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                  <select v-model="form.jenis" class="form-select" required>
                    <option value="">Pilih Jenis</option>
                    <option value="debit">Debit (+)</option>
                    <option value="kredit">Kredit (-)</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                  <input 
                    v-model.number="form.jumlah" 
                    type="number" 
                    class="form-input" 
                    min="0"
                    step="0.01"
                    placeholder="0"
                    required 
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input 
                  v-model="form.tanggal" 
                  type="date" 
                  class="form-input" 
                  required 
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea 
                  v-model="form.keterangan" 
                  class="form-input" 
                  rows="3"
                  placeholder="Keterangan transaksi"
                  required
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeCreateModal" class="btn btn-secondary">
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

    <!-- ZIS Integration Modal -->
    <div v-if="showZisIntegrationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Integrasi dengan ZIS Transactions</h3>
            <button @click="closeZisIntegrationModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="space-y-4">
            <p class="text-sm text-gray-600">
              Pilih transaksi ZIS yang belum diintegrasikan ke akuntansi syariah:
            </p>

            <div v-if="unintegratedZisTransactions.length > 0" class="max-h-60 overflow-y-auto space-y-2">
              <div 
                v-for="zisTransaction in unintegratedZisTransactions" 
                :key="zisTransaction.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ zisTransaction.muzakki?.nama }}</p>
                  <p class="text-sm text-gray-600">{{ zisTransaction.jenis_zis }} - {{ formatCurrency(zisTransaction.jumlah) }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(zisTransaction.tanggal) }}</p>
                </div>
                <button 
                  @click="integrateZisTransaction(zisTransaction)"
                  class="btn btn-sm btn-primary"
                >
                  Integrasikan
                </button>
              </div>
            </div>

            <div v-else class="text-center py-6">
              <p class="text-gray-500">Tidak ada transaksi ZIS yang belum diintegrasikan</p>
            </div>
          </div>

          <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
            <button @click="closeZisIntegrationModal" class="btn btn-secondary">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { 
  Plus, Database, RefreshCw, Search, Eye, Edit, CheckCircle, 
  Trash2, X 
} from 'lucide-vue-next'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const showCreateModal = ref(false)
const showZisIntegrationModal = ref(false)
const editingTransaction = ref<any>(null)
const transactions = ref<any[]>([])
const fundCategories = ref<any[]>([])
const accounts = ref<any[]>([])
const unintegratedZisTransactions = ref<any[]>([])

// Filters
const searchQuery = ref('')
const statusFilter = ref('')
const complianceFilter = ref('')

// Form
const form = ref({
  fund_category_id: '',
  account_id: '',
  jenis: '',
  jumlah: 0,
  tanggal: new Date().toISOString().split('T')[0],
  keterangan: ''
})

// Methods
const loadTransactions = async () => {
  try {
    isLoading.value = true
    const params = new URLSearchParams()
    if (statusFilter.value) params.append('status', statusFilter.value)
    if (complianceFilter.value) params.append('compliance', complianceFilter.value)
    if (searchQuery.value) params.append('search', searchQuery.value)
    
    const response = await axios.get(`/sharia-accounting/transactions?${params.toString()}`)
    transactions.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading transactions:', error)
  } finally {
    isLoading.value = false
  }
}

const loadFormData = async () => {
  try {
    const [categoriesResponse, accountsResponse] = await Promise.all([
      axios.get('/sharia-accounting/fund-categories'),
      axios.get('/sharia-accounting/accounts')
    ])
    
    fundCategories.value = categoriesResponse.data.data || categoriesResponse.data
    accounts.value = accountsResponse.data.data || accountsResponse.data
  } catch (error) {
    console.error('Error loading form data:', error)
  }
}

const loadUnintegratedZisTransactions = async () => {
  try {
    const response = await axios.get('/sharia-accounting/zis-transactions/unintegrated')
    unintegratedZisTransactions.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading unintegrated ZIS transactions:', error)
  }
}

const saveTransaction = async () => {
  try {
    isSubmitting.value = true
    if (editingTransaction.value) {
      await axios.put(`/sharia-accounting/transactions/${editingTransaction.value.id}`, form.value)
    } else {
      await axios.post('/sharia-accounting/transactions', form.value)
    }
    await loadTransactions()
    closeCreateModal()
  } catch (error) {
    console.error('Error saving transaction:', error)
  } finally {
    isSubmitting.value = false
  }
}

const integrateZisTransaction = async (zisTransaction: any) => {
  try {
    await axios.post('/sharia-accounting/integrate-zis', {
      zis_transaction_id: zisTransaction.id
    })
    await loadTransactions()
    await loadUnintegratedZisTransactions()
  } catch (error) {
    console.error('Error integrating ZIS transaction:', error)
  }
}

const postTransaction = async (transaction: any) => {
  try {
    await axios.post(`/sharia-accounting/transactions/${transaction.id}/post`)
    await loadTransactions()
  } catch (error) {
    console.error('Error posting transaction:', error)
  }
}

const deleteTransaction = async (transaction: any) => {
  if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
    try {
      await axios.delete(`/sharia-accounting/transactions/${transaction.id}`)
      await loadTransactions()
    } catch (error) {
      console.error('Error deleting transaction:', error)
    }
  }
}

const viewTransaction = (transaction: any) => {
  // Implement view transaction details
  console.log('View transaction:', transaction)
}

const editTransaction = (transaction: any) => {
  editingTransaction.value = transaction
  form.value = {
    fund_category_id: transaction.fund_category_id,
    account_id: transaction.account_id,
    jenis: transaction.jenis,
    jumlah: transaction.jumlah,
    tanggal: transaction.tanggal,
    keterangan: transaction.keterangan
  }
  showCreateModal.value = true
}

const searchTransactions = () => {
  // Debounce search
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadTransactions()
  }, 500)
}

let searchTimeout: NodeJS.Timeout

const closeCreateModal = () => {
  showCreateModal.value = false
  editingTransaction.value = null
  form.value = {
    fund_category_id: '',
    account_id: '',
    jenis: '',
    jumlah: 0,
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: ''
  }
}

const closeZisIntegrationModal = () => {
  showZisIntegrationModal.value = false
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

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    posted: 'Posted',
    cancelled: 'Cancelled'
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'draft':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'posted':
      return `${baseClass} bg-green-100 text-green-800`
    case 'cancelled':
      return `${baseClass} bg-red-100 text-red-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const getComplianceText = (status: string) => {
  const statusMap: Record<string, string> = {
    compliant: 'Compliant',
    non_compliant: 'Non-Compliant',
    pending: 'Pending'
  }
  return statusMap[status] || status
}

const getComplianceStatusClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'compliant':
      return `${baseClass} bg-green-100 text-green-800`
    case 'non_compliant':
      return `${baseClass} bg-red-100 text-red-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

// Lifecycle
onMounted(() => {
  loadTransactions()
  loadFormData()
  loadUnintegratedZisTransactions()
})
</script>