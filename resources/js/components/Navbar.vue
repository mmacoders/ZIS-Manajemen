<template>
  <nav class="fixed top-0 left-0 h-screen w-64 bg-white shadow-xl border-r border-gray-200 z-50 overflow-y-auto">
    <div class="flex flex-col h-full">
      <!-- Logo/Title -->
      <div class="p-6 border-b border-gray-100">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">Z</span>
          </div>
          <div class="ml-3">
            <h1 class="text-lg font-bold text-gray-800">ZIS Management</h1>
            <p class="text-xs text-gray-500">{{ authStore.user?.role?.display_name }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <div class="flex-1 px-4 py-6">
        <ul class="space-y-1">
          <li>
            <router-link
              to="/dashboard"
              class="nav-link"
              :class="{ 'active': $route.name === 'Dashboard' }"
            >
              <BarChart3 class="w-5 h-5" />
              <span>Dashboard</span>
            </router-link>
          </li>

          <!-- Bidang 1 - Pengumpulan & Akuntansi Syariah -->
          <li v-if="canAccess(['admin', 'bidang1'])">
            <div class="nav-section">Pengumpulan</div>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])">
            <router-link
              to="/muzakki"
              class="nav-link"
              :class="{ 'active': $route.name === 'Muzakki' }"
            >
              <Users class="w-5 h-5" />
              <span>Data Muzakki</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])">
            <router-link
              to="/upz"
              class="nav-link"
              :class="{ 'active': $route.name === 'UPZ' }"
            >
              <Building class="w-5 h-5" />
              <span>Data UPZ</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])">
            <router-link
              to="/zis-transactions"
              class="nav-link"
              :class="{ 'active': $route.name === 'ZISTransactions' }"
            >
              <DollarSign class="w-5 h-5" />
              <span>Transaksi ZIS</span>
            </router-link>
          </li>

          <!-- Sharia Accounting Section -->
          <li v-if="canAccess(['admin', 'bidang1'])">
            <div class="nav-section">Akuntansi Syariah</div>
            <router-link
              to="/sharia-accounting"
              class="nav-link"
              :class="{ 'active': $route.name === 'ShariaAccounting' }"
            >
              <Calculator class="w-5 h-5" />
              <span>Akuntansi Syariah</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])">
            <router-link
              to="/sharia-transactions"
              class="nav-link"
              :class="{ 'active': $route.name === 'ShariaTransactions' }"
            >
              <Database class="w-5 h-5" />
              <span>Transaksi Syariah</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin'])">
            <router-link
              to="/baznas-reports"
              class="nav-link"
              :class="{ 'active': $route.name === 'BaznasReports' }"
            >
              <FileCheck class="w-5 h-5" />
              <span>Laporan BAZNAS</span>
            </router-link>
          </li>

          <!-- Bidang 2 - Distribusi & Pemberdayaan -->
          <li v-if="canAccess(['admin', 'bidang2'])">
            <div class="nav-section">Distribusi & Pemberdayaan</div>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])">
            <router-link
              to="/mustahiq"
              class="nav-link"
              :class="{ 'active': $route.name === 'Mustahiq' }"
            >
              <UserCheck class="w-5 h-5" />
              <span>Data Mustahiq</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])">
            <router-link
              to="/programs"
              class="nav-link"
              :class="{ 'active': $route.name === 'Programs' }"
            >
              <Briefcase class="w-5 h-5" />
              <span>Program Bantuan</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])">
            <router-link
              to="/distributions"
              class="nav-link"
              :class="{ 'active': $route.name === 'Distributions' }"
            >
              <Send class="w-5 h-5" />
              <span>Distribusi</span>
            </router-link>
          </li>

          <!-- Bidang 4 - Arsip Surat -->
          <li v-if="canAccess(['admin', 'bidang4'])">
            <div class="nav-section">Arsip Surat</div>
          </li>
          <li v-if="canAccess(['admin', 'bidang4'])">
            <router-link
              to="/documents"
              class="nav-link"
              :class="{ 'active': $route.name === 'Documents' }"
            >
              <FileText class="w-5 h-5" />
              <span>Dokumen</span>
            </router-link>
          </li>

          <!-- Reports Section -->
          <li>
            <div class="nav-section">Laporan & Analitik</div>
          </li>
          <li>
            <router-link
              to="/reports"
              class="nav-link"
              :class="{ 'active': $route.name === 'Reports' }"
            >
              <BarChart3 class="w-5 h-5" />
              <span>Laporan</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- User Section -->
      <div class="border-t border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center min-w-0 flex-1">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-semibold">
                {{ authStore.user?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 truncate">{{ authStore.user?.name }}</p>
              <p class="text-xs text-gray-500 truncate">{{ authStore.user?.email }}</p>
            </div>
          </div>
          <button
            @click="logout"
            class="ml-2 p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
            title="Logout"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  BarChart3,
  Users,
  Building,
  DollarSign,
  UserCheck,
  Briefcase,
  Send,
  FileText,
  LogOut,
  Calculator,
  Database,
  FileCheck
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const canAccess = (roles: string[]) => {
  const userRole = authStore.user?.role?.name
  return userRole && roles.includes(userRole)
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.nav-link {
  @apply flex items-center px-3 py-2.5 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200;
}

.nav-link span {
  @apply ml-3;
}

.nav-link:hover {
  @apply bg-gray-50 text-gray-900;
}

.nav-link.active {
  @apply bg-blue-50 text-blue-700 border-l-4 border-blue-600;
}

.nav-link.active span {
  @apply font-medium;
}

.nav-section {
  @apply text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-2 mt-6 mb-1;
}
</style>