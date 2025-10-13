<template>
  <nav class="fixed top-0 left-0 h-screen bg-white shadow-xl border-r border-gray-200 z-50 overflow-y-auto transition-all duration-300"
       :class="isCollapsed ? 'w-20' : 'w-64'">
    <div class="flex flex-col h-full">
      <!-- Logo/Title -->
      <div class="p-4 border-b border-gray-100 flex flex-col items-center">
        <div class="flex flex-col items-center justify-center">
          <img v-if="!isCollapsed" src="@/assets/img/logo.png" alt="BAZNAS Logo" class="h-24 mb-2" />
          <img v-else src="@/assets/img/logo.png" alt="BAZNAS Logo" class="h-10 my-2" />
          
        </div>
        <h1 v-if="!isCollapsed" class="text-base font-bold text-gray-900 tracking-wide text-center">
          ZIS Management
        </h1>
        <h1 v-else class="text-xs font-bold text-gray-900 tracking-wide text-center mt-1">
          ZIS
        </h1>
      </div>

      <!-- menu aplikasi -->
     <!-- Saat sidebar terbuka -->
    <div v-if="!isCollapsed" class="flex items-center justify-between px-5 py-2 mb-2">
      <span class="text-xs font-semibold text-gray-800 uppercase tracking-wider">
        Menu Aplikasi
      </span>
      <button @click="toggleSidebar" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded">
        <ChevronLeft class="w-5 h-5" />
      </button>
    </div>

    <!-- Saat sidebar tertutup -->
    <div v-else class="flex items-center justify-center px-3 py-2 mb-2">
      <button @click="toggleSidebar" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded">
      <ChevronRight class="w-5 h-5" />
      </button>
    </div>
      <div class="flex-1 px-3">
        <ul class="space-y-1">
          <li>
            <router-link
              to="/dashboard"
              class="nav-link"
              :class="{ 'active': $route.name === 'Dashboard', 'collapsed': isCollapsed }"
            >
              <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
                <LayoutDashboard class="w-5 h-5" />
              </div>  
              <span v-if="!isCollapsed" class="ml-3">Dashboard</span>
            </router-link>
          </li>

          <!-- Bidang 1 - Pengumpulan -->
          <li v-if="canAccess(['admin', 'bidang1'])">
            <router-link
              to="/pengumpulan"
              class="nav-link"
              :class="{ 'active': $route.name === 'Pengumpulan', 'collapsed': isCollapsed }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <UserCircle class="w-5 h-5" />
            </div>
              <span v-if="!isCollapsed" class="ml-3">Bidang 1 - Pengumpulan</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/donatur"
              class="nav-link"
              :class="{ 'active': $route.name === 'Donatur' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <UserCircle class="w-5 h-5" />
            </div>
              <span class="ml-3">Data Donatur</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/upz"
              class="nav-link"
              :class="{ 'active': $route.name === 'UPZ' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <Landmark class="w-5 h-5" />
            </div>
              <span class="ml-3">Data UPZ</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/zis-transactions"
              class="nav-link"
              :class="{ 'active': $route.name === 'ZISTransactions' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <Receipt class="w-5 h-5" />
            </div>
              <span class="ml-3">Transaksi ZIS</span>
            </router-link>
          </li>

          <!-- Bidang 2 - Distribusi -->
          <li v-if="canAccess(['admin', 'bidang2'])">
            <router-link
              to="/distribusi"
              class="nav-link"
              :class="{ 'active': $route.name === 'Distribusi', 'collapsed': isCollapsed }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <BarChart3 class="w-5 h-5" />
              </div>
              <span v-if="!isCollapsed" class="ml-3">Bidang 2 - Distribusi</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/mustahiq"
              class="nav-link"
              :class="{ 'active': $route.name === 'Mustahiq' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <Handshake class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Mustahiq</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/programs"
              class="nav-link"
              :class="{ 'active': $route.name === 'Programs' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <ClipboardList class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Program Bantuan</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang2'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/distributions"
              class="nav-link"
              :class="{ 'active': $route.name === 'Distributions' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <ArrowRightCircle class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Realisasi Bantuan</span>
            </router-link>
          </li>

          <!-- Bidang 3 - Keuangan -->
          <li v-if="canAccess(['admin', 'bidang3'])">
            <router-link
              to="/keuangan"
              class="nav-link"
              :class="{ 'active': $route.name === 'Keuangan', 'collapsed': isCollapsed }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <BarChart3 class="w-5 h-5" />
              </div>
              <span v-if="!isCollapsed" class="ml-3">Bidang 3 - Keuangan</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang3'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/rkat"
              class="nav-link"
              :class="{ 'active': $route.name === 'RKAT' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <FileSpreadsheet class="w-5 h-5" />
              </div>
              <span class="ml-3">Data RKAT</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang3'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/fund-receipts"
              class="nav-link"
              :class="{ 'active': $route.name === 'FundReceipts' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <ArrowDownToLine class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Penerimaan Dana</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang3'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/fund-distributions"
              class="nav-link"
              :class="{ 'active': $route.name === 'FundDistributions' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <ArrowUpToLine class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Penyaluran Dana</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang3'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/spj"
              class="nav-link"
              :class="{ 'active': $route.name === 'SPJ' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <ShieldCheck class="w-5 h-5" />
              </div>
              <span class="ml-3">Laporan SPJ</span>
            </router-link>
          </li>

          <!-- Bidang 4 - SDM, Administrasi dan Umum -->
          <li v-if="canAccess(['admin', 'bidang4'])">
            <router-link
              to="/sdm"
              class="nav-link"
              :class="{ 'active': $route.name === 'SDM', 'collapsed': isCollapsed }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <BarChart3 class="w-5 h-5" />
              </div>
              <span v-if="!isCollapsed" class="ml-3">Bidang 4 - SDM & Administrasi</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang4'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/staff"
              class="nav-link"
              :class="{ 'active': $route.name === 'Staff' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <UserCircle	 class="w-5 h-5" />
              </div>
              <span class="ml-3">Staff Data</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang4'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/incoming-letters"
              class="nav-link"
              :class="{ 'active': $route.name === 'IncomingLetters' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <MessageSquare class="w-5 h-5" />
              </div>
              <span class="ml-3">Surat Masuk</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang4'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/outgoing-letters"
              class="nav-link"
              :class="{ 'active': $route.name === 'OutgoingLetters' }"
            >
              <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
              <MessageSquare class="w-5 h-5" />
              </div>
              <span class="ml-3">Surat Keluar</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang4'])" class="ml-6" v-show="!isCollapsed">
            <router-link
              to="/assets"
              class="nav-link"
              :class="{ 'active': $route.name === 'Assets' }"
            >
              <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">
                <Package class="w-5 h-5" />
              </div>
              <span class="ml-3">Data Asset Kantor</span>
            </router-link>
          </li>

          <!-- Reports Section -->
          <li v-show="!isCollapsed">
            <div class="nav-section">Laporan & Analitik</div>
          </li>
          <li v-show="!isCollapsed">
            <router-link
              to="/reports"
              class="nav-link"
              :class="{ 'active': $route.name === 'Reports' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">            
              <BarChart3 class="w-5 h-5" />
              </div>
              <span class="ml-3">Laporan</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin'])" v-show="!isCollapsed">
            <router-link
              to="/baznas-reports"
              class="nav-link"
              :class="{ 'active': $route.name === 'BaznasReports' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">            
              <FileCheck class="w-5 h-5" />
            </div>
              <span class="ml-3">Laporan BAZNAS</span>
            </router-link>
          </li>
          <li v-if="canAccess(['admin', 'bidang1'])" v-show="!isCollapsed">
            <router-link
              to="/ml-analytics"
              class="nav-link"
              :class="{ 'active': $route.name === 'MLAnalytics' }"
            >
            <div class="p-2 rounded-full bg-gray-100 group-hover:bg-gray-200">            
              <BarChart3 class="w-5 h-5" />
              </div>
              <span class="ml-3">Analitik ML</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- User Section -->
      <div class="border-t border-gray-200 p-4">
        <div class="flex items-center" :class="isCollapsed ? 'justify-center' : 'justify-between'">
          <div v-if="!isCollapsed" class="flex items-center min-w-0 flex-1">
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
            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
            :class="isCollapsed ? '' : 'ml-2'"
            :title="isCollapsed ? 'Logout' : ''"
          >
            <Power class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  LayoutDashboard,
  UserCircle,
  Landmark,
  Receipt,
  Handshake,
  ClipboardList,
  ArrowRightCircle,
  FileSpreadsheet,
  Power,
  Scale,
  Server,
  FileCheck,
  ArrowDownToLine,
  ArrowUpToLine,
  MessageSquare,
  Package,
  BarChart3,
  ChevronLeft,
  ChevronRight,
  ShieldCheck
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

// Define props to receive the collapsed state from parent
const props = defineProps<{
  collapsed?: boolean
}>()

const isCollapsed = ref(props.collapsed || false)

// Watch for changes in the prop and update the local state
watch(() => props.collapsed, (newVal) => {
  isCollapsed.value = newVal || false
})

// Emit event when sidebar state changes
const emit = defineEmits(['update:collapsed'])

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
  // Emit the collapsed state to parent component
  emit('update:collapsed', isCollapsed.value)
}

const canAccess = (roles: string[]) => {
  const userRole = authStore.user?.role?.name
  return userRole && roles.includes(userRole)
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Expose the collapsed state
defineExpose({
  isCollapsed
})
</script>

<style scoped>
.nav-link {
  @apply flex items-center px-4 py-2 rounded-2xl transition-all duration-200;
}

.nav-link span {
  @apply ml-3;
}

.nav-link:hover {
  @apply bg-gray-50 text-gray-900;
}

.nav-link.active {
  background-color: #264ad9;
  color: #e9ecf3;
}

.nav-link.active span {
  @apply font-medium;
}

.nav-link div {
  @apply p-2 rounded-full bg-gray-100 text-gray-600 transition-colors duration-200;
}

.nav-link:hover div {
  @apply bg-gray-200 text-gray-800;
}

.nav-link.active div {
  @apply bg-blue-100 text-blue-500;
}

/* Add specific styling for active links when sidebar is collapsed */
.nav-link.active.collapsed div {
  @apply bg-blue-500 text-white;
}

.nav-section {
  @apply text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-2 mt-6 mb-1;
}
</style>