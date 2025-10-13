<template>
  <nav class="flex" v-if="breadcrumbs.length > 0">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rounded-md">
      <li v-for="(breadcrumb, index) in breadcrumbs" :key="index" class="inline-flex items-center">
        <router-link 
          v-if="index < breadcrumbs.length - 1" 
          :to="breadcrumb.href"
          class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600"
        >
          {{ breadcrumb.title }}
        </router-link>
        <span 
          v-else 
          class="text-sm font-semibold text-gray-900"
        >
          {{ breadcrumb.title }}
        </span>
        
        <svg 
          v-if="index < breadcrumbs.length - 1" 
          class="w-4 h-4 mx-1 text-gray-400" 
          fill="currentColor" 
          viewBox="0 0 20 20" 
          xmlns="http://www.w3.org/2000/svg"
        >
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { BreadcrumbItem } from '@/types'
import { title } from 'process'

const route = useRoute()

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
  const routeName = route.name as string
  
  // Map route names to breadcrumb structures
  const breadcrumbMap: Record<string, BreadcrumbItem[]> = {
    'Dashboard': [
      { title: 'Dashboard', href: '/dashboard' }
    ],
    'Donatur': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 1 - Pengumpulan', href: '/pengumpulan' },
      { title: 'Data Donatur', href: '/donatur' }
    ],
    'UPZ': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 1 - Pengumpulan', href: '/pengumpulan' },
      { title: 'Data UPZ', href: '/upz' }
    ],
    'ZISTransactions': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 1 - Pengumpulan', href: '/pengumpulan' },
      { title: 'Transaksi ZIS', href: '/zis-transactions' }
    ],
    'Mustahiq': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 2 Distribusi dan Pemberdayaan', href: '/distribusi' },
      { title: 'Data Mustahiq', href: '/mustahiq' }
    ],
    'Programs': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 2 Distribusi dan Pemberdayaan', href: '/distribusi' },
      { title: 'Data Program Bantuan', href: '/programs' }
    ],
    'Distributions': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 2 Distribusi dan Pemberdayaan', href: '/distribusi' },
      { title: 'Data Realisasi Bantuan', href: '/distributions' }
    ],
    'RKAT': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 3 Keuangan', href: '/keuangan' },
      { title: 'Data RKAT', href: '/rkat' }
    ],
    'FundReceipts': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 3 Keuangan', href: '/keuangan' },
      { title: 'Data Penerimaan Dana', href: '/fund-receipts' }
    ],
    'FundDistributions': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 3 Keuangan', href: '/keuangan' },
      { title: 'Data Penyaluran Dana', href: '/fund-distributions' }
    ],
    'SPJ': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 3 Keuangan', href: '/keuangan' },
      { title: 'Laporan SPJ', href: '/spj' }
    ],
    'Staff': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 4 SDM, Admistrasi Umum', href: '/sdm' },
      { title: 'Staff Data', href: '/staff' }
    ],
    'IncomingLetters': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 4 SDM, Admistrasi Umum', href: '/sdm' },
      { title: 'Surat Masuk', href: '/incoming-letters' }
    ],
    'OutgoingLetters': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 4 SDM, Admistrasi Umum', href: '/sdm' },
      { title: 'Surat Keluar', href: '/outgoing-letters' }
    ],
    'Assets': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 4 SDM, Admistrasi Umum', href: '/sdm' },
      { title: 'Data Asset Kantor', href: '/assets' }
    ],
    'Reports': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Laporan', href: '/reports' }
    ],
    'BaznasReports': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Laporan BAZNAS', href: '/baznas-reports' }
    ],
    'MLAnalytics': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Analitik ML', href: '/ml-analytics' }
    ],
    'Pengumpulan': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 1 - Pengumpulan', href: '/pengumpulan' }
    ],
    'Distribusi': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 2 - Distribusi', href: '/distribusi' }
    ],
    'Keuangan': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 3 - Keuangan', href: '/keuangan' }
    ],
    'SDM': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Bidang 4 - SDM', href: '/sdm' }
    ],
    'Arsip': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Arsip', href: '/arsip' }
    ],
    'Documents': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Dokumen', href: '/documents' }
    ],
    'ShariaAccounting': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Akuntansi Syariah', href: '/sharia-accounting' }
    ],
    'ShariaTransactions': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Transaksi Syariah', href: '/sharia-transactions' }
    ]
  }
  
  return breadcrumbMap[routeName] || []
})
</script>

<style scoped>
nav {
  padding: 0.5rem 0;
}
</style>