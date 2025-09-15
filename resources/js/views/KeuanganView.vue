<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Bidang 3 - Keuangan</h1>
      <p class="text-gray-600">Kelola data keuangan dan pelaporan</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <router-link to="/rkat" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <FileText class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data RKAT</h3>
            <p class="text-sm text-gray-600">Rencana Kerja dan Anggaran Tahunan</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/fund-receipts" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Download class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data Penerimaan Dana</h3>
            <p class="text-sm text-gray-600">Kelola penerimaan dana</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/fund-distributions" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Upload class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data Penyaluran Dana</h3>
            <p class="text-sm text-gray-600">Kelola penyaluran dana</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/spj" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <FileCheck class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Laporan SPJ</h3>
            <p class="text-sm text-gray-600">Surat Pertanggungjawaban</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FileText, Download, Upload, FileCheck } from 'lucide-vue-next'
</script>