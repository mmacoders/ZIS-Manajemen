<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Modul Pengumpulan</h1>
      <p class="text-gray-600">Kelola data pengumpulan ZIS</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <router-link to="/donatur" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Users class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data Donatur</h3>
            <p class="text-sm text-gray-600">Kelola data donatur individu dan lembaga</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/upz" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Building class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data UPZ</h3>
            <p class="text-sm text-gray-600">Kelola Unit Pengumpul Zakat</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/zis-transactions" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <DollarSign class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Transaksi ZIS</h3>
            <p class="text-sm text-gray-600">Kelola transaksi zakat, infaq, sedekah</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Users, Building, DollarSign } from 'lucide-vue-next'
</script>