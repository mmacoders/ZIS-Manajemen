<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Transaksi ZIS</h1>
      <p class="text-gray-600">Kelola transaksi Zakat, Infaq, dan Sedekah</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <Search class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="No. kwitansi, nama donatur..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchTransactions()"
            />
          </div>
          
          <!-- ZIS Type -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis ZIS</label>
            <Tag class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <select v-model="filters.jenis_zis" class="form-input pl-10 w-full">
              <option value="">Semua Jenis</option>
              <option value="zakat">Zakat</option>
              <option value="infaq">Infaq</option>
              <option value="sedekah">Sedekah</option>
            </select>
          </div>
          
          <!-- Status -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <Settings class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <select v-model="filters.status" class="form-input pl-10 w-full">
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="verified">Terverifikasi</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
          
          <!-- Date -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <Calendar class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <input 
              v-model="filters.date" 
              type="date" 
              class="form-input pl-10 w-full"
            />
          </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2 mt-4">
          <button
            @click="() => fetchTransactions()"
            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
            title="Cari Data"
          >
            <Search class="w-4 h-4" />
          </button>
          
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="inline-flex items-center px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
            title="Reset Filter"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>
      
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <button 
            @click="refreshData"
            :disabled="isLoading"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 font-medium"
          >
            <RefreshCw :class="isLoading ? 'animate-spin' : ''" class="w-4 h-4 mr-2" />
            Refresh
          </button>
          
          <button 
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
          >
            <Plus class="w-4 h-4 mr-2" />
            Tambah Transaksi
          </button>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Hash class="w-4 h-4 mr-2" />
                  No. Kwitansi
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <User class="w-4 h-4 mr-2" />
                  Donatur
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Tag class="w-4 h-4 mr-2" />
                  Jenis ZIS
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Coins class="w-4 h-4 mr-2" />
                  Jumlah
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Calendar class="w-4 h-4 mr-2" />
                  Tanggal
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Settings class="w-4 h-4 mr-2" />
                  Status
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
            <tr v-if="isLoading" v-for="n in 5" :key="n" class="animate-pulse">
              <td v-for="col in 7" :key="col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded"></div>
              </td>
            </tr>
            
            <tr v-else-if="transactions.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada transaksi ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ transaction.nomor_transaksi }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate" :title="transaction.donatur?.nama">
                  {{ transaction.donatur?.nama || 'Unknown' }}
                </div>
                <div v-if="transaction.donatur" class="text-xs text-gray-500">
                  <span v-if="transaction.donatur.jenis_donatur === 'lembaga'">NPWP: {{ transaction.donatur.npwp || 'N/A' }}</span>
                  <span v-else>NIK: {{ transaction.donatur.nik || 'N/A' }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                      :class="getZisTypeClass(transaction.jenis_zis)">
                  {{ transaction.jenis_zis?.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(transaction.jumlah) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(transaction.tanggal_transaksi) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(transaction.status)" 
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                  {{ getStatusText(transaction.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button 
                  @click="viewTransaction(transaction)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Lihat Detail"
                >
                  <Eye class="w-4 h-4" />
                </button>
                <button 
                  @click="editTransaction(transaction)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 hover:bg-yellow-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteTransaction(transaction)"
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
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} transaksi
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="fetchTransactions(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sebelumnya
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-700">
              Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            </span>
            
            <button
              @click="fetchTransactions(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
              {{ showCreateModal ? 'Tambah Transaksi ZIS Baru' : 'Edit Transaksi ZIS' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveTransaction">
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi *</label>
                  <div class="relative">
                    <Calendar class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.tanggal_transaksi" 
                      type="date" 
                      class="form-input pl-10 w-full"
                      required
                    />
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi</label>
                  <div class="relative">
                    <Tag class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <select 
                      v-model="transactionType" 
                      class="form-select pl-10 w-full"
                      @change="handleTransactionTypeChange"
                    >
                      <option value="donation">Donasi (Donatur)</option>
                      <option value="operational">Operasional</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div v-if="transactionType === 'donation'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Donatur *</label>
                <div class="relative">
                  <User class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <select 
                    v-model="form.donatur_id" 
                    class="form-select pl-10 w-full"
                    required
                    :disabled="donaturLoading"
                  >
                    <option value="">Pilih Donatur</option>
                    <option 
                      v-for="donatur in donaturList" 
                      :key="donatur.id" 
                      :value="donatur.id"
                    >
                      {{ donatur.nama }} - 
                      <span v-if="donatur.jenis_donatur === 'lembaga'">{{ donatur.npwp || 'NPWP tidak tersedia' }}</span>
                      <span v-else>{{ donatur.nik || 'NIK tidak tersedia' }}</span>
                    </option>
                  </select>
                  <div v-if="donaturLoading" class="absolute right-3 top-3">
                    <RefreshCw class="w-4 h-4 animate-spin text-gray-400" />
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jenis ZIS *</label>
                  <div class="relative">
                    <Tag class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <select v-model="form.jenis_zis" class="form-select pl-10 w-full" required>
                      <option value="">Pilih Jenis</option>
                      <option value="zakat">Zakat</option>
                      <option value="infaq">Infaq</option>
                      <option value="sedekah">Sedekah</option>
                    </select>
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp) *</label>
                  <div class="relative">
                    <Coins class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.jumlah" 
                      type="number" 
                      class="form-input pl-10 w-full"
                      required
                      placeholder="100000"
                    />
                  </div>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <div class="relative">
                  <FileText class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea 
                    v-model="form.keterangan" 
                    rows="3"
                    class="form-input pl-10 w-full"
                    placeholder="Tambahkan catatan transaksi..."
                  ></textarea>
                </div>
              </div>
              
              <div v-if="showEditModal && selectedTransaction">
                <label class="block text-sm font-medium text-gray-700 mb-2">No. Kwitansi</label>
                <div class="relative">
                  <Hash class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <input 
                    v-model="form.nomor_transaksi" 
                    type="text" 
                    class="form-input pl-10 w-full bg-gray-100"
                    readonly
                  />
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn btn-secondary">
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="isSubmitting" 
                class="btn btn-primary"
              >
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeDetailModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Detail Transaksi</h3>
            <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div v-if="selectedTransaction" class="space-y-6">
            <!-- Receipt Header -->
            <div class="text-center border-b pb-4">
              <h2 class="text-2xl font-bold text-gray-900 mb-2">KWITANSI</h2>
              <p class="text-gray-600">No. {{ selectedTransaction.nomor_transaksi }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ formatDate(selectedTransaction.tanggal_transaksi) }}
              </p>
            </div>

            <!-- Donor Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-gray-900 mb-3">Informasi Donatur</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <p class="text-sm text-gray-500">Nama</p>
                  <p class="font-medium">{{ selectedTransaction.donatur?.nama || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500" v-if="selectedTransaction.donatur?.jenis_donatur === 'lembaga'">NPWP</p>
                  <p class="text-sm text-gray-500" v-else>NIK</p>
                  <p class="font-medium" v-if="selectedTransaction.donatur?.jenis_donatur === 'lembaga'">{{ selectedTransaction.donatur?.npwp || '-' }}</p>
                  <p class="font-medium" v-else>{{ selectedTransaction.donatur?.nik || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Alamat</p>
                  <p class="font-medium">{{ selectedTransaction.donatur?.alamat || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Telepon</p>
                  <p class="font-medium">{{ selectedTransaction.donatur?.telepon || '-' }}</p>
                </div>
              </div>
            </div>

            <!-- Transaction Details -->
            <div class="border rounded-lg overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ getZisTypeText(selectedTransaction.jenis_zis) }}
                      </div>
                      <div class="text-sm text-gray-500" v-if="selectedTransaction.keterangan">
                        {{ selectedTransaction.keterangan }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                      {{ formatCurrency(selectedTransaction.jumlah) }}
                    </td>
                  </tr>
                  <tr class="bg-gray-50 font-semibold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                      {{ formatCurrency(selectedTransaction.jumlah) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Status and Verification -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-2">Status</h4>
                <div class="flex items-center">
                  <span :class="getStatusBadgeClass(selectedTransaction.status)" 
                        class="inline-flex px-3 py-1 text-sm font-medium rounded-full">
                    {{ getStatusText(selectedTransaction.status) }}
                  </span>
                </div>
              </div>
              
              <div v-if="selectedTransaction.status === 'verified' && selectedTransaction.verifier" class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-2">Verifikasi</h4>
                <p class="text-sm text-gray-700">
                  Diverifikasi oleh {{ selectedTransaction.verifier.name }} pada 
                  {{ formatDate(selectedTransaction.verified_at) }}
                </p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
              <button 
                @click="printReceipt" 
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
              >
                <Scan class="w-4 h-4 mr-2" />
                Cetak Kwitansi
              </button>
              <button 
                @click="editTransaction(selectedTransaction)" 
                class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors duration-200"
              >
                <Edit class="w-4 h-4 mr-2" />
                Edit
              </button>
              <button 
                @click="deleteTransaction(selectedTransaction)" 
                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
              >
                <Trash2 class="w-4 h-4 mr-2" />
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- OCR Section -->
    <div v-if="showOCRSection" class="mt-6">
      <OCRUpload 
        @ocr-success="handleOCRSuccess"
        @close="showOCRSection = false"
      />
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showDeleteConfirmModal = false">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.stop>
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
            <button @click="showDeleteConfirmModal = false" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="mb-6">
            <p class="text-gray-700">
              Apakah Anda yakin ingin menghapus transaksi dengan No. Kwitansi 
              <span class="font-bold">{{ selectedTransaction?.nomor_transaksi }}</span>?
            </p>
          </div>

          <div class="flex justify-end space-x-2">
            <button 
              @click="showDeleteConfirmModal = false" 
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button 
              @click="confirmDeleteTransaction" 
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Success Modal -->
    <div v-if="showDeleteSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showDeleteSuccessModal = false">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.stop>
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Berhasil</h3>
            <button @click="showDeleteSuccessModal = false" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="mb-6 flex items-center">
            <CheckCircle class="w-10 h-10 text-green-500 mr-3" />
            <p class="text-gray-700">Transaksi berhasil dihapus.</p>
          </div>

          <div class="flex justify-end">
            <button 
              @click="showDeleteSuccessModal = false" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { 
  Plus, RefreshCw, Eye, Edit, Database, Scan,
  Search, Tag, Calendar, Coins, Settings, Hash, User, CreditCard, FileText, X, Trash2, CheckCircle
} from 'lucide-vue-next'
import OCRUpload from '@/components/OCRUpload.vue'
import axios from 'axios'

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const donaturLoading = ref(false)
const showOCRSection = ref(false)
const transactions = ref<any[]>([])
const donaturList = ref<any[]>([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDetailModal = ref(false)
const showDeleteConfirmModal = ref(false)
const showDeleteSuccessModal = ref(false)
const selectedTransaction = ref<any>(null)
const ocrResult = ref<any>(null)
const transactionType = ref('donation') // 'donation' or 'operational'

// Filters
const filters = ref({
  search: '',
  jenis_zis: '',
  status: '',
  date: ''
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
  nomor_transaksi: '',
  tanggal_transaksi: new Date().toISOString().split('T')[0],
  donatur_id: '',
  jenis_zis: '',
  jumlah: 0,
  metode_pembayaran: 'tunai',
  no_referensi: '',
  keterangan: ''
})

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.jenis_zis || filters.value.status || filters.value.date
})

// Watchers
watch(filters, () => {
  pagination.value.current_page = 1
  fetchTransactions()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchTransactions()
  fetchDonaturList()
})

// Methods
const fetchTransactions = async (page = 1) => {
  try {
    isLoading.value = true
    const params: any = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    }

    console.log('Fetching transactions with params:', params)
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get('/zis-transactions', { params })
    
    console.log('Transactions API Response:', response)
    
    if (response.data && response.data.success) {
      // Handle paginated response from ZisTransactionController
      if (response.data.data && response.data.data.data) {
        // Paginated response: data.data.data contains the actual transaction records
        transactions.value = response.data.data.data || []
        pagination.value = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          from: response.data.data.from,
          to: response.data.data.to,
          total: response.data.data.total,
          per_page: response.data.data.per_page
        }
      } else {
        // Non-paginated response
        transactions.value = response.data.data || []
        // Reset pagination for non-paginated response
        pagination.value = {
          current_page: 1,
          last_page: 1,
          from: 1,
          to: transactions.value.length,
          total: transactions.value.length,
          per_page: transactions.value.length || 10
        }
      }
      
      console.log('Transactions List:', transactions.value)
    } else {
      transactions.value = []
      const errorMessage = response.data?.message || 'Unknown error occurred'
      console.error('Failed to fetch transactions:', errorMessage)
      // Show user-friendly error message
      alert('Gagal memuat data transaksi: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error fetching transactions:', error)
    console.error('Error response:', error.response)
    
    // Check if it's an authentication error
    if (error.response?.status === 401) {
      console.error('Authentication required to fetch transactions')
    }
    
    transactions.value = []
    alert('Terjadi kesalahan saat memuat data transaksi')
  } finally {
    isLoading.value = false
  }
}

const handleTransactionTypeChange = () => {
  // Clear donor selection when switching to operational
  if (transactionType.value === 'operational') {
    form.value.donatur_id = ''
  }
}

const fetchDonaturList = async () => {
  try {
    donaturLoading.value = true
    const response = await axios.get('/donatur?per_page=100&include=npwp')
    if (response.data && response.data.success) {
      donaturList.value = response.data.data.data || response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching donatur list:', error)
  } finally {
    donaturLoading.value = false
  }
}

const saveTransaction = async () => {
  try {
    isSubmitting.value = true
    
    // Validate donor is selected for donation transactions
    if (transactionType.value === 'donation' && !form.value.donatur_id) {
      alert('Donatur harus dipilih untuk transaksi donasi')
      isSubmitting.value = false
      return
    }
    
    // Ensure amount is a number
    const amount = typeof form.value.jumlah === 'string' ? parseFloat(form.value.jumlah) || 0 : form.value.jumlah
    
    // Remove donor_id from form data for operational transactions
    const formData = { ...form.value, jumlah: amount }
    if (transactionType.value === 'operational') {
      delete formData.donatur_id
    }
    
    const url = showCreateModal.value ? '/zis-transactions' : `/zis-transactions/${selectedTransaction.value.id}`
    const method = showCreateModal.value ? 'post' : 'put'
    
    const response = await axios[method](url, formData)
    
    if (response.data && response.data.success) {
      closeModal()
      fetchTransactions()
      alert(showCreateModal.value ? 'Transaksi berhasil ditambahkan' : 'Transaksi berhasil diperbarui')
    } else {
      const errorMessage = response.data?.message || 'Unknown error occurred'
      alert('Gagal menyimpan transaksi: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error saving transaction:', error)
    console.error('Error response:', error.response)
    
    if (error.response?.data?.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat()
      alert('Validasi error: ' + errorMessages.join(', '))
    } else {
      alert('Terjadi kesalahan saat menyimpan transaksi')
    }
  } finally {
    isSubmitting.value = false
  }
}

const viewTransaction = (transaction: any) => {
  selectedTransaction.value = transaction
  showDetailModal.value = true
}

const editTransaction = (transaction: any) => {
  selectedTransaction.value = transaction
  form.value = {
    nomor_transaksi: transaction.nomor_transaksi,
    tanggal_transaksi: transaction.tanggal_transaksi,
    donatur_id: transaction.donatur_id,
    jenis_zis: transaction.jenis_zis,
    jumlah: transaction.jumlah,
    metode_pembayaran: transaction.metode_pembayaran || 'tunai',
    no_referensi: transaction.no_referensi || '',
    keterangan: transaction.keterangan || ''
  }
  
  // Set transaction type based on whether donor exists
  transactionType.value = transaction.donatur_id ? 'donation' : 'operational'
  
  showEditModal.value = true
  showDetailModal.value = false
  showOCRSection.value = false
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  showDetailModal.value = false
  showOCRSection.value = false
  selectedTransaction.value = null
  ocrResult.value = null
  transactionType.value = 'donation' // Reset to default
  
  // Reset form
  form.value = {
    nomor_transaksi: '',
    tanggal_transaksi: new Date().toISOString().split('T')[0],
    donatur_id: '',
    jenis_zis: '',
    jumlah: 0,
    metode_pembayaran: 'tunai',
    no_referensi: '',
    keterangan: ''
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedTransaction.value = null
}

const refreshData = () => {
  fetchTransactions()
  fetchDonaturList()
}

const deleteTransaction = async (transaction: any) => {
  selectedTransaction.value = transaction
  showDeleteConfirmModal.value = true
}

const confirmDeleteTransaction = async () => {
  showDeleteConfirmModal.value = false
  
  try {
    // Show loading state
    isLoading.value = true
    
    const response = await axios.delete(`/zis-transactions/${selectedTransaction.value.id}`)
    
    if (response.data && response.data.success) {
      // Close detail modal if it's for the deleted transaction
      if (selectedTransaction.value && selectedTransaction.value.id === selectedTransaction.value.id) {
        closeDetailModal()
      }
      
      // Refresh the transaction list
      await fetchTransactions()
      showDeleteSuccessModal.value = true
    } else {
      const errorMessage = response.data?.message || 'Unknown error occurred'
      alert('Gagal menghapus transaksi: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error deleting transaction:', error)
    console.error('Error response:', error.response)
    
    if (error.response?.status === 404) {
      alert('Transaksi tidak ditemukan')
    } else if (error.response?.status === 403) {
      alert('Anda tidak memiliki izin untuk menghapus transaksi ini')
    } else {
      alert('Terjadi kesalahan saat menghapus transaksi')
    }
  } finally {
    isLoading.value = false
  }
}

const clearFilters = () => {
  filters.value = {
    search: '',
    jenis_zis: '',
    status: '',
    date: ''
  }
  fetchTransactions(1)
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
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Helper function to convert number to words (simplified version)
const convertNumberToWords = (number: number): string => {
  // This is a simplified version - in a real application, you might want to use a proper library
  const ones = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan']
  const teens = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas']
  const tens = ['', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh']
  
  if (number === 0) return 'nol'
  
  const numStr = Math.floor(number).toString()
  if (numStr.length > 12) return numStr // Too large, return as is
  
  // Handle simple cases
  if (number < 10) return ones[number]
  if (number < 20) return teens[number - 10]
  if (number < 100) {
    const ten = Math.floor(number / 10)
    const one = number % 10
    return tens[ten] + (one > 0 ? ' ' + ones[one] : '')
  }
  
  // For larger numbers, we'll use a simplified approach
  return number.toLocaleString('id-ID')
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: 'Pending',
    verified: 'Terverifikasi',
    rejected: 'Ditolak'
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'verified':
      return 'bg-green-100 text-green-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getZisTypeClass = (type: string) => {
  switch (type) {
    case 'zakat':
      return 'bg-blue-100 text-blue-800'
    case 'infaq':
      return 'bg-purple-100 text-purple-800'
    case 'sedekah':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getZisTypeText = (type: string) => {
  const typeMap: Record<string, string> = {
    zakat: 'Pembayaran Zakat',
    infaq: 'Infaq',
    sedekah: 'Sedekah'
  }
  return typeMap[type] || type
}

const handleOCRSuccess = (result: any) => {
  ocrResult.value = result
  showOCRSection.value = false
  
  // Auto-fill form with OCR data if available
  if (result.fields) {
    // This would depend on the structure of your OCR result
    // You might need to adjust based on actual OCR output
    if (result.fields.amount) {
      form.value.jumlah = parseFloat(result.fields.amount.replace(/[^0-9]/g, '')) || 0
    }
    if (result.fields.date) {
      form.value.tanggal_transaksi = result.fields.date
    }
    if (result.fields.description) {
      form.value.keterangan = result.fields.description
    }
  }
}

const printReceipt = () => {
  if (!selectedTransaction.value) return
  
  const transaction = selectedTransaction.value
  
  // Get transaction type description
  let transactionTypeDesc = getZisTypeText(transaction.jenis_zis)
  
  // Add specific zakat type if applicable
  if (transaction.jenis_zis === 'zakat' && transaction.donatur?.jenis_zakat) {
    transactionTypeDesc += ` (${transaction.donatur.jenis_zakat})`
  }
  
  // Format the receipt content
  const receiptContent = ``
  
  // Open the receipt in a new window for printing
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(receiptContent)
    printWindow.document.close()
    printWindow.focus()
    
    // Wait a bit for content to load before printing
    setTimeout(() => {
      printWindow.print()
      // Optionally close the window after printing
      // printWindow.close()
    }, 250)
  } else {
    alert('Popup blocker mencegah pencetakan. Mohon izinkan popup untuk situs ini.')
  }
}

</script>
