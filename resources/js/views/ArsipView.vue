<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Arsip Surat</h1>
      <p class="text-gray-600">Kelola arsip surat masuk dan keluar</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
      <router-link to="/documents" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <FileText class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Dokumen</h3>
            <p class="text-sm text-gray-600">Kelola surat masuk dan keluar</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FileText } from 'lucide-vue-next'
</script>