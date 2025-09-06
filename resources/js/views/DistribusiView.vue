<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Distribusi & Pemberdayaan</h1>
      <p class="text-gray-600">Kelola distribusi dan program pemberdayaan</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <router-link to="/mustahiq" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <UserCheck class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Data Mustahiq</h3>
            <p class="text-sm text-gray-600">Kelola data penerima zakat</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/programs" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Briefcase class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Program Bantuan</h3>
            <p class="text-sm text-gray-600">Kelola program distribusi</p>
          </div>
        </div>
      </router-link>
      
      <router-link to="/distributions" class="card hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <Send class="w-8 h-8 text-blue-600 mr-4" />
          <div>
            <h3 class="font-semibold text-gray-900">Distribusi</h3>
            <p class="text-sm text-gray-600">Kelola penyaluran bantuan</p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UserCheck, Briefcase, Send } from 'lucide-vue-next'
</script>