<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Data Donatur</h1>
      <p class="text-gray-600">Kelola data donatur individu dan lembaga</p>
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
              placeholder="Nama, NIK, alamat..."
              class="form-input pl-10 w-full"
              @keyup.enter="() => fetchDonaturList()"
            />
          </div>
          
          <!-- Donatur Type -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Donatur
            </label>
            <User class="absolute left-3 top-8 w-4 h-4 text-gray-400" />
            <select v-model="filters.jenis_donatur" class="form-input pl-10 w-full">
              <option value="">Semua Jenis</option>
              <option value="individu">Individu</option>
              <option value="lembaga">Lembaga</option>
            </select>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between mt-4 pt-4 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button
              @click="() => fetchDonaturList()"
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
              Tambah Donatur
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Donatur Table -->
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
                  <Hash class="w-4 h-4 mr-2" />
                  NIK/NPWP
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
                  <Phone class="w-4 h-4 mr-2" />
                  Kontak
                </div>
              </th>
              <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <UserCheck class="w-7 h-8 mr-2" />
                  Kategori Donatur
                </div>
              </th>
              <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center">
                  <Tag class="w-6 h-8 mr-2" />
                  Jenis Donatur
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
            
            <tr v-else-if="donaturList.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <Database class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Tidak ada data donatur ditemukan</p>
              </td>
            </tr>
            
            <tr v-else v-for="donatur in donaturList" :key="donatur.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ donatur.nama }}</div>
                <div v-if="donatur.jenis_kelamin" class="text-xs text-gray-500">
                  {{ donatur.jenis_kelamin === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ donatur.nik }}
              </td>
              <td class="px-6 py-4 max-w-xs truncate text-sm text-gray-900" :title="donatur.alamat">
                {{ donatur.alamat }}
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ donatur.telepon || '-' }}</div>
                <div v-if="donatur.email" class="text-xs text-gray-500">{{ donatur.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                      :class="getDonaturTypeClass(donatur.jenis_donatur)">
                  {{ getDonaturTypeText(donatur.jenis_donatur) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                      :class="getJenisDonaturClass(donatur.jenis_zakat)">
                  {{ getJenisDonaturText(donatur.jenis_zakat) || '-' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button 
                  @click="viewDonatur(donatur)"
                  class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                  title="Lihat Detail"
                >
                  <Eye class="w-4 h-4" />
                </button>
                <button 
                  @click="editDonatur(donatur)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 hover:bg-yellow-50 rounded"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>
                <button 
                  @click="deleteDonatur(donatur)"
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
            Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} donatur
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="fetchDonaturList(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Sebelumnya
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-700">
              Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            </span>
            
            <button
              @click="fetchDonaturList(pagination.current_page + 1)"
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
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
              {{ showCreateModal ? 'Tambah Donatur Baru' : 'Edit Data Donatur' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Donatur Type Selection -->
              <div class="sm:col-span-2">
                <label class="form-label">Jenis Donatur *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                  <div 
                    v-for="type in donaturTypes" 
                    :key="type.value"
                    @click="selectDonaturType(type.value)"
                    :class="[
                      'border rounded-lg p-4 cursor-pointer transition-all',
                      form.jenis_donatur === type.value 
                        ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' 
                        : 'border-gray-300 hover:border-gray-400'
                    ]"
                  >
                    <div class="flex items-center">
                      <component :is="type.icon" class="w-5 h-5 mr-2 text-gray-600" />
                      <span class="font-medium">{{ type.label }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ type.description }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Individual Donatur Form -->
              <template v-if="form.jenis_donatur === 'individu'">
                <!-- Name -->
                <div>
                  <label class="form-label">Nama Lengkap *</label>
                  <div class="relative">
                    <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.nama"
                      type="text" 
                      required
                      class="form-input pl-10"
                      placeholder="Nama lengkap"
                    />
                  </div>
                </div>
                
                <!-- Gender -->
                <div>
                  <label class="form-label">Jenis Kelamin *</label>
                  <div class="relative">
                    <Users class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <select v-model="form.jenis_kelamin" required class="form-input pl-10">
                      <option value="">Pilih Jenis Kelamin</option>
                      <option value="laki-laki">Laki-laki</option>
                      <option value="perempuan">Perempuan</option>
                    </select>
                  </div>
                </div>
                
                <!-- NIK -->
                <div>
                  <label class="form-label">NIK *</label>
                  <div class="relative">
                    <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.nik"
                      type="text" 
                      required
                      maxlength="16"
                      class="form-input pl-10"
                      placeholder="16 digit NIK"
                    />
                  </div>
                </div>
                
                <!-- Address -->
                <div class="sm:col-span-2">
                  <label class="form-label">Alamat *</label>
                  <div class="relative">
                    <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <textarea 
                      v-model="form.alamat"
                      rows="3"
                      required
                      class="form-input pl-10"
                      placeholder="Alamat lengkap"
                    ></textarea>
                  </div>
                </div>
                
                <!-- Phone -->
                <div>
                  <label class="form-label">No. Telepon</label>
                  <div class="relative">
                    <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.telepon"
                      type="text" 
                      class="form-input pl-10"
                      placeholder="08xxxxxxxxxx"
                    />
                  </div>
                </div>
                
                <!-- Email -->
                <div>
                  <label class="form-label">Email</label>
                  <div class="relative">
                    <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.email"
                      type="email" 
                      class="form-input pl-10"
                      placeholder="email@example.com"
                    />
                  </div>
                </div>
                
                <!-- Base Salary -->
                <div>
                  <label class="form-label">Gaji Pokok (Rp)</label>
                  <div class="relative">
                    <Coins class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model.number="form.gaji_pokok"
                      type="number" 
                      min="0"
                      class="form-input pl-10"
                      placeholder="Rp 0"
                    />
                  </div>
                </div>
                
                <!-- Donatur Type -->
                <div>
                  <label class="form-label">Jenis Donatur</label>
                  <div class="relative">
                    <Tag class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <select v-model="form.jenis_zakat" class="form-input pl-10">
                      <option value="">Pilih Jenis Donatur</option>
                      <option value="zakat penghasilan">Zakat Penghasilan</option>
                      <option value="zakat mal">Zakat Mal</option>
                      <option value="zakat fitrah">Zakat Fitrah</option>
                      <option value="infaq">Infaq</option>
                      <option value="sedekah">Sedekah</option>
                    </select>
                  </div>
                </div>
              </template>
              
              <!-- Institutional Donatur Form -->
              <template v-else-if="form.jenis_donatur === 'lembaga'">
                <!-- Institution Name -->
                <div>
                  <label class="form-label">Nama Lembaga *</label>
                  <div class="relative">
                    <Building class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.nama"
                      type="text" 
                      required
                      class="form-input pl-10"
                      placeholder="Nama lembaga"
                    />
                  </div>
                </div>
                
                <!-- NPWP/ID Number -->
                <div>
                  <label class="form-label">NPWP/ID *</label>
                  <div class="relative">
                    <Hash class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.nik"
                      type="text" 
                      required
                      class="form-input pl-10"
                      placeholder="NPWP atau ID lembaga"
                    />
                  </div>
                </div>
                
                <!-- Address -->
                <div class="sm:col-span-2">
                  <label class="form-label">Alamat Kantor *</label>
                  <div class="relative">
                    <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <textarea 
                      v-model="form.alamat"
                      rows="3"
                      required
                      class="form-input pl-10"
                      placeholder="Alamat kantor"
                    ></textarea>
                  </div>
                </div>
                
                <!-- Phone -->
                <div>
                  <label class="form-label">No. Telepon Kantor</label>
                  <div class="relative">
                    <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.telepon"
                      type="text" 
                      class="form-input pl-10"
                      placeholder="021xxxxxxxx"
                    />
                  </div>
                </div>
                
                <!-- Email -->
                <div>
                  <label class="form-label">Email Kantor</label>
                  <div class="relative">
                    <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="form.email"
                      type="email" 
                      class="form-input pl-10"
                      placeholder="info@lembaga.com"
                    />
                  </div>
                </div>
                
                <!-- Contact Person -->
                <div>
                  <label class="form-label">Nama Kontak Person</label>
                  <div class="relative">
                    <User class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="contactPerson"
                      type="text" 
                      class="form-input pl-10"
                      placeholder="Nama kontak person"
                    />
                  </div>
                </div>
                
                <!-- Contact Person Phone -->
                <div>
                  <label class="form-label">No. HP Kontak Person</label>
                  <div class="relative">
                    <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model="contactPersonPhone"
                      type="text" 
                      class="form-input pl-10"
                      placeholder="08xxxxxxxxxx"
                    />
                  </div>
                </div>
              </template>
              
              <!-- Notes -->
              <div class="sm:col-span-2">
                <label class="form-label">Keterangan</label>
                <div class="relative">
                  <FileText class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <textarea 
                    v-model="form.keterangan"
                    rows="3"
                    class="form-input pl-10"
                    placeholder="Catatan tambahan (opsional)"
                  ></textarea>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-6 pt-4 border-t border-gray-200">
              <button type="button" @click="closeModal" class="btn btn-secondary w-full sm:w-auto">
                Batal
              </button>
              <button type="submit" :disabled="isSubmitting" class="btn btn-primary w-full sm:w-auto">
                {{ isSubmitting ? 'Menyimpan...' : (showCreateModal ? 'Simpan Donatur' : 'Update Donatur') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Donatur Detail Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeViewModal">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-y-auto" @click.stop>
        <div class="p-5">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-gray-900">Detail Donatur</h3>
            <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
              <X class="w-7 h-7" />
            </button>
          </div>
          
          <div v-if="selectedDonatur" class="space-y-5">
            <!-- Donor Info Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-100">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                  <h4 class="text-lg font-bold text-gray-900">{{ selectedDonatur.nama }}</h4>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex px-2.5 py-0.5 text-sm font-medium rounded-full" 
                          :class="getDonaturTypeClass(selectedDonatur.jenis_donatur)">
                      {{ getDonaturTypeText(selectedDonatur.jenis_donatur) }}
                    </span>
                    <span v-if="selectedDonatur.jenis_zakat" class="inline-flex px-2.5 py-0.5 text-sm font-medium rounded-full" 
                          :class="getJenisDonaturClass(selectedDonatur.jenis_zakat)">
                      {{ getJenisDonaturText(selectedDonatur.jenis_zakat) }}
                    </span>
                  </div>
                </div>
                <div class="bg-white rounded-md p-2.5 shadow-sm">
                  <p class="text-xs text-gray-500">ID DONATUR</p>
                  <p class="font-mono text-base font-bold text-gray-900">{{ selectedDonatur.nik }}</p>
                </div>
              </div>
            </div>

            <!-- Donor Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Personal Information -->
              <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-5 py-3 border-b border-gray-200">
                  <h5 class="text-base font-semibold text-gray-800 flex items-center">
                    <User class="w-4 h-4 mr-2 text-blue-500" />
                    Informasi Pribadi
                  </h5>
                </div>
                <div class="p-5 space-y-3">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Nama : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.nama }}</div>
                  </div>
                  
                  <div v-if="selectedDonatur.jenis_kelamin" class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Jenis Kelamin : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.jenis_kelamin === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Alamat : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.alamat }}</div>
                  </div>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-5 py-3 border-b border-gray-200">
                  <h5 class="text-base font-semibold text-gray-800 flex items-center">
                    <Phone class="w-4 h-4 mr-2 text-green-500" />
                    Informasi Kontak
                  </h5>
                </div>
                <div class="p-5 space-y-3">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Telepon : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.telepon || '-' }}</div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Email : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.email || '-' }}</div>
                  </div>
                  
                  <div v-if="selectedDonatur.jenis_donatur === 'lembaga'" class="flex items-start">
                    <div class="flex-shrink-0 w-28 text-sm font-medium text-gray-500">Kontak Person : </div>
                    <div class="text-gray-900 text-sm">
                      <div>{{ contactPerson || '-' }}</div>
                      <div v-if="contactPersonPhone" class="text-xs text-gray-500 mt-1">{{ contactPersonPhone }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Donation Information -->
              <div class="bg-white border border-gray-200 rounded-lg shadow-sm md:col-span-2">
                <div class="px-5 py-3 border-b border-gray-200">
                  <h5 class="text-base font-semibold text-gray-800 flex items-center">
                    <Coins class="w-4 h-4 mr-2 text-yellow-500" />
                    Informasi Donasi
                  </h5>
                </div>
                <div class="p-5 space-y-3">
                  <div v-if="selectedDonatur.jenis_zakat" class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm font-medium text-gray-500">Jenis Donatur : </div>
                    <div class="text-gray-900 text-sm">
                      <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full" 
                            :class="getJenisDonaturClass(selectedDonatur.jenis_zakat)">
                        {{ getJenisDonaturText(selectedDonatur.jenis_zakat) }}
                      </span>
                    </div>
                  </div>
                  
                  <div v-if="selectedDonatur.gaji_pokok" class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm font-medium text-gray-500">Gaji Pokok : </div>
                    <div class="text-gray-900 text-sm">Rp {{ formatNumber(selectedDonatur.gaji_pokok) }}</div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex-shrink-0 w-32 text-sm font-medium text-gray-500">Keterangan : </div>
                    <div class="text-gray-900 text-sm">{{ selectedDonatur.keterangan || '-' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { 
  Plus, RefreshCw, Eye, Edit, Database, Trash2,
  Search, User, Calendar, Hash, MapPin, Phone, Mail, 
  Building, Users, Tag, Coins, Settings, FileText, X, UserCheck
} from 'lucide-vue-next'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

// State
const authStore = useAuthStore()
const isLoading = ref(false)
const isSubmitting = ref(false)
const donaturList = ref<any[]>([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const selectedDonatur = ref<any>(null)
const contactPerson = ref('')
const contactPersonPhone = ref('')

// Donatur Types
const donaturTypes = [
  {
    value: 'individu',
    label: 'Individu',
    description: 'Donatur perseorangan',
    icon: User
  },
  {
    value: 'lembaga',
    label: 'Lembaga',
    description: 'Donatur organisasi/perusahaan',
    icon: Building
  }
]

// Filters
const filters = ref({
  search: '',
  jenis_donatur: '',
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
  nama: '',
  nik: '',
  alamat: '',
  telepon: '',
  email: '',
  jenis_donatur: 'individu',
  jenis_kelamin: '',
  keterangan: '',
  gaji_pokok: null,
  jenis_zakat: '',
  nominal_setoran: null,
  metode_pembayaran: 'tunai',
  tanggal_setoran: new Date().toISOString().split('T')[0]
})

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.jenis_donatur || filters.value.date
})

// Watchers
watch([filters], () => {
  pagination.value.current_page = 1
  fetchDonaturList()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchDonaturList()
})

// Methods
const fetchDonaturList = async (page = 1) => {
  try {
    isLoading.value = true
    const params: any = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value
    }

    console.log('Fetching donatur with params:', params)
    const response = await axios.get('/donatur', { params })
    
    console.log('Donatur API Response:', response)
    
    if (response.data && response.data.success) {
      // Handle paginated response
      if (response.data.data && response.data.data.data) {
        donaturList.value = response.data.data.data || []
        pagination.value = {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          from: response.data.data.from,
          to: response.data.data.to,
          total: response.data.data.total,
          per_page: response.data.data.per_page
        }
      } else {
        donaturList.value = response.data.data || []
        pagination.value = {
          current_page: 1,
          last_page: 1,
          from: 1,
          to: donaturList.value.length,
          total: donaturList.value.length,
          per_page: donaturList.value.length || 10
        }
      }
      
      console.log('Donatur List:', donaturList.value)
    } else {
      donaturList.value = []
      const errorMessage = response.data?.message || 'Unknown error occurred'
      console.error('Failed to fetch donatur list:', errorMessage)
      alert('Gagal memuat data donatur: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error fetching donatur list:', error)
    console.error('Error response:', error.response)
    
    if (error.response?.status === 401) {
      console.error('Authentication required to fetch donatur data')
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk mengakses data donatur.')
    } else if (error.response?.status === 404) {
      console.error('API endpoint not found or access denied')
      alert('Tidak dapat mengakses data donatur. Pastikan Anda memiliki izin yang sesuai atau coba login kembali.')
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Terjadi kesalahan saat memuat data donatur'
      alert('Gagal memuat data donatur: ' + errorMessage)
    }
    donaturList.value = []
  } finally {
    isLoading.value = false
  }
}

const selectDonaturType = (type: string) => {
  form.value.jenis_donatur = type
  // Reset gender for non-individual types
  if (type !== 'individu') {
    form.value.jenis_kelamin = ''
  }
}

const submitForm = async () => {
  try {
    isSubmitting.value = true
    
    const method = showCreateModal.value ? 'POST' : 'PUT'
    const url = showCreateModal.value 
      ? '/donatur'
      : `/donatur/${selectedDonatur.value.id}`
    
    // Validate required fields based on donor type
    if (!form.value.nama) {
      alert('Nama harus diisi')
      isSubmitting.value = false
      return
    }
    
    if (!form.value.nik) {
      alert('NIK/NPWP harus diisi')
      isSubmitting.value = false
      return
    }
    
    if (!form.value.alamat) {
      alert('Alamat harus diisi')
      isSubmitting.value = false
      return
    }
    
    if (form.value.jenis_donatur === 'individu' && !form.value.jenis_kelamin) {
      alert('Jenis kelamin harus dipilih untuk donatur individu')
      isSubmitting.value = false
      return
    }
    
    console.log('Submitting form data:', form.value)
    const response = await axios({
      method,
      url,
      data: form.value
    })
    
    console.log('Form submission response:', response)
    
    if (response.data && response.data.success) {
      closeModal()
      await fetchDonaturList()
      alert(showCreateModal.value ? 'Donatur berhasil ditambahkan!' : 'Data donatur berhasil diperbarui!')
    } else {
      const errorMessage = response.data?.message || response.data?.error || 'Gagal menyimpan data donatur'
      alert('Terjadi kesalahan: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error submitting form:', error)
    console.error('Error response:', error.response)
    
    if (error.response?.status === 401) {
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
      return
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk menyimpan data donatur.')
    } else if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data?.errors
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat()
        alert('Validasi gagal:\n' + errorMessages.join('\n'))
      } else {
        const errorMessage = error.response.data?.message || 'Validasi gagal'
        alert('Validasi gagal: ' + errorMessage)
      }
    } else {
      const errorMessage = error.response?.data?.message || error.response?.data?.error || error.message || 'Terjadi kesalahan saat menyimpan data donatur'
      alert('Error: ' + errorMessage)
    }
  } finally {
    isSubmitting.value = false
  }
}

const viewDonatur = (donatur: any) => {
  selectedDonatur.value = donatur
  // Extract contact person info if it exists in keterangan
  if (donatur.keterangan && donatur.keterangan.includes('Kontak Person:')) {
    const contactInfo = donatur.keterangan.split('Kontak Person:')[1]?.split('|') || []
    contactPerson.value = contactInfo[0]?.trim() || ''
    contactPersonPhone.value = contactInfo[1]?.trim() || ''
  }
  showViewModal.value = true
}

const editDonatur = (donatur: any) => {
  selectedDonatur.value = donatur
  form.value = {
    nama: donatur.nama,
    nik: donatur.nik,
    alamat: donatur.alamat,
    telepon: donatur.telepon || '',
    email: donatur.email || '',
    jenis_donatur: donatur.jenis_donatur || 'individu',
    jenis_kelamin: donatur.jenis_kelamin || '',
    keterangan: donatur.keterangan || '',
    gaji_pokok: donatur.gaji_pokok || null,
    jenis_zakat: donatur.jenis_zakat || '',
    nominal_setoran: donatur.nominal_setoran || null,
    metode_pembayaran: donatur.metode_pembayaran || 'tunai',
    tanggal_setoran: donatur.tanggal_setoran || new Date().toISOString().split('T')[0]
  }
  showEditModal.value = true
}

const deleteDonatur = async (donatur: any) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus donatur "${donatur.nama}"?`)) {
    return
  }
  
  try {
    const response = await axios.delete(`/donatur/${donatur.id}`)
    
    if (response.data && response.data.success) {
      await fetchDonaturList()
      alert('Donatur berhasil dihapus!')
    } else {
      const errorMessage = response.data?.message || 'Gagal menghapus donatur'
      alert('Terjadi kesalahan: ' + errorMessage)
    }
  } catch (error: any) {
    console.error('Error deleting donatur:', error)
    
    if (error.response?.status === 401) {
      alert('Sesi Anda telah berakhir. Silakan login kembali.')
    } else if (error.response?.status === 403) {
      console.error('Access forbidden - insufficient permissions')
      alert('Anda tidak memiliki izin untuk menghapus data donatur.')
    } else if (error.response?.status === 422) {
      const errorMessage = error.response.data?.message || 'Tidak dapat menghapus donatur yang memiliki transaksi'
      alert('Error: ' + errorMessage)
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Terjadi kesalahan saat menghapus donatur'
      alert('Error: ' + errorMessage)
    }
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedDonatur.value = null
  contactPerson.value = ''
  contactPersonPhone.value = ''
  
  // Reset form
  form.value = {
    nama: '',
    nik: '',
    alamat: '',
    telepon: '',
    email: '',
    jenis_donatur: 'individu',
    jenis_kelamin: '',
    keterangan: '',
    gaji_pokok: null,
    jenis_zakat: '',
    nominal_setoran: null,
    metode_pembayaran: 'tunai',
    tanggal_setoran: new Date().toISOString().split('T')[0]
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedDonatur.value = null
}

const refreshData = () => {
  fetchDonaturList()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    jenis_donatur: '',
    date: ''
  }
  fetchDonaturList(1)
}

// Utility functions
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const formatNumber = (number: number) => {
  return new Intl.NumberFormat('id-ID').format(number)
}

const getDonaturTypeClass = (type: string) => {
  switch (type) {
    case 'individu':
      return 'bg-blue-100 text-blue-800'
    case 'lembaga':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getDonaturTypeText = (type: string) => {
  const typeMap: Record<string, string> = {
    'individu': 'Individu',
    'lembaga': 'Lembaga'
  }
  return typeMap[type] || type
}

const getJenisDonaturClass = (type: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (type) {
    case 'zakat penghasilan':
    case 'zakat mal':
    case 'zakat fitrah':
      return `${baseClass} bg-purple-100 text-purple-800`
    case 'infaq':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'sedekah':
      return `${baseClass} bg-orange-100 text-orange-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const getJenisDonaturText = (type: string) => {
  const typeMap: Record<string, string> = {
    'zakat penghasilan': 'Zakat Penghasilan',
    'zakat mal': 'Zakat Mal',
    'zakat fitrah': 'Zakat Fitrah',
    'infaq': 'Infaq',
    'sedekah': 'Sedekah'
  }
  return typeMap[type] || type
}
</script>

<style scoped>
/* Additional responsive styles */
@media (max-width: 639px) {
  .btn-primary,
  .btn-secondary {
    @apply w-full justify-center;
  }
  
  .card {
    @apply mx-0 rounded-lg;
  }
  
  table {
    font-size: 0.875rem;
  }
  
  th, td {
    padding: 0.5rem 0.75rem;
  }
  
  .max-w-32 {
    max-width: 8rem;
  }
  
  .max-w-24 {
    max-width: 6rem;
  }
  
  /* Modal responsive adjustments */
  .fixed.inset-0 {
    padding: 0.75rem;
  }
  
  .max-w-3xl {
    max-width: 100%;
  }
  
  .p-5 {
    padding: 1rem;
  }
  
  .text-xl {
    font-size: 1.125rem;
    line-height: 1.75rem;
  }
  
  .text-lg {
    font-size: 1rem;
    line-height: 1.5rem;
  }
  
  .rounded-lg {
    border-radius: 0.5rem;
  }
}

/* Extra small devices */
@media (max-width: 480px) {
  .card {
    padding: 0.75rem;
  }
  
  th, td {
    padding: 0.25rem 0.5rem;
  }
  
  .truncate {
    max-width: 6rem;
  }
  
  /* Modal adjustments for small screens */
  .p-5 {
    padding: 0.75rem;
  }
  
  .px-5 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
  
  .py-3 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  
  .p-4 {
    padding: 0.75rem;
  }
  
  .text-base {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
  
  .text-sm {
    font-size: 0.75rem;
    line-height: 1rem;
  }
  
  .w-28 {
    width: 7rem;
  }
  
  .w-32 {
    width: 8rem;
  }
}

/* Medium devices */
@media (min-width: 768px) {
  .grid.grid-cols-1.md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>