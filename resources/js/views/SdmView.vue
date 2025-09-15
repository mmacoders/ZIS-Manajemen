<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Bidang 4 - SDM, Administrasi dan Umum</h1>
      <p class="text-gray-600">Kelola data SDM, administrasi, dan aset kantor</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <router-link to="/staff" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Users class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Staff Data</h3>
            <p class="text-sm text-gray-600">Kelola data staff amil</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/incoming-letters" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Mail class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Surat Masuk</h3>
            <p class="text-sm text-gray-600">Kelola surat masuk</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/outgoing-letters" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Send class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Surat Keluar</h3>
            <p class="text-sm text-gray-600">Kelola surat keluar</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/assets" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Box class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data Asset Kantor</h3>
            <p class="text-sm text-gray-600">Kelola aset kantor</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Users, Mail, Send, Box } from 'lucide-vue-next'
</script>