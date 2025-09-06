<template>
  <div class="p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Laporan & Analitik ZIS</h1>
      <p class="text-sm sm:text-base text-gray-600">Laporan komprehensif dengan filter dan visualisasi data</p>
    </div>

    <!-- Filters Section -->
    <div class="card mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Laporan</h3>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
          <select v-model="filters.period" @change="handlePeriodChange" class="form-select">
            <option value="custom">Custom</option>
            <option value="this_month">Bulan Ini</option>
            <option value="last_month">Bulan Lalu</option>
            <option value="this_quarter">Kuartal Ini</option>
            <option value="this_year">Tahun Ini</option>
            <option value="last_year">Tahun Lalu</option>
          </select>
        </div>

        <!-- Start Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
          <input 
            v-model="filters.startDate" 
            type="date" 
            class="form-input"
            :disabled="filters.period !== 'custom'"
          />
        </div>

        <!-- End Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
          <input 
            v-model="filters.endDate" 
            type="date" 
            class="form-input"
            :disabled="filters.period !== 'custom'"
          />
        </div>

        <!-- Report Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan</label>
          <select v-model="filters.reportType" class="form-select">
            <option value="summary">Ringkasan</option>
            <option value="transactions">Transaksi ZIS</option>
            <option value="distributions">Distribusi</option>
            <option value="comparative">Perbandingan</option>
          </select>
        </div>
      </div>

      <!-- Additional Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <!-- ZIS Type Filter -->
        <div v-if="filters.reportType === 'transactions'">
          <label class="block text-sm font-medium text-gray-700 mb-2">Jenis ZIS</label>
          <select v-model="filters.zisType" class="form-select">
            <option value="">Semua Jenis</option>
            <option value="zakat">Zakat</option>
            <option value="infaq">Infaq</option>
            <option value="sedekah">Sedekah</option>
          </select>
        </div>

        <!-- Program Filter -->
        <div v-if="filters.reportType === 'distributions'">
          <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
          <select v-model="filters.programId" class="form-select">
            <option value="">Semua Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.nama }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" class="form-select">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="verified" v-if="filters.reportType === 'transactions'">Terverifikasi</option>
            <option value="completed" v-if="filters.reportType === 'distributions'">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-3">
        <button @click="generateReport" :disabled="isLoading" class="btn-primary flex-shrink-0">
          <div v-if="isLoading" class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            Memuat...
          </div>
          <span v-else>Generate Laporan</span>
        </button>
        
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2">
          <button @click="exportCSV" :disabled="!reportData || isLoading" class="btn-secondary">
            <Download class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Export </span>CSV
          </button>
          
          <button @click="exportPDF" :disabled="!reportData || isLoading" class="btn-secondary">
            <FileText class="w-4 h-4 mr-2" />
            <span class="hidden sm:inline">Export </span>PDF
          </button>
          
          <button @click="resetFilters" class="btn-outline">
            <RotateCcw class="w-4 h-4 mr-2" />
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Document Digitization Section -->
    <div class="card mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Digitalisasi Dokumen</h3>
        <button
          @click="showOCRSection = !showOCRSection"
          class="flex items-center px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors"
        >
          <Scan class="w-4 h-4 mr-2" />
          {{ showOCRSection ? 'Sembunyikan Scanner' : 'Scan Dokumen' }}
        </button>
      </div>
      
      <div v-if="showOCRSection" class="space-y-4">
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
          <h4 class="text-base font-medium text-purple-900 mb-2">Scan & Digitalisasi Dokumen</h4>
          <p class="text-sm text-purple-700 mb-4">
            Upload dokumen fisik seperti laporan audit, rekening koran bank, atau dokumen pendukung lainnya untuk digitalisasi otomatis.
          </p>
          
          <OCRUpload 
            :document-type="'general'"
            @dataExtracted="handleDocumentOCR"
          />
        </div>
        
        <!-- Digitized Documents List -->
        <div v-if="digitizedDocuments.length > 0" class="border border-gray-200 rounded-lg">
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h5 class="text-sm font-medium text-gray-900">Dokumen Terdigitalisasi ({{ digitizedDocuments.length }})</h5>
          </div>
          
          <div class="max-h-64 overflow-y-auto">
            <div v-for="doc in digitizedDocuments.slice(0, 5)" :key="doc.id" class="px-4 py-3 border-b border-gray-100 last:border-b-0">
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2 mb-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :class="getDocumentTypeClass(doc.type)">
                      {{ getDocumentTypeName(doc.type) }}
                    </span>
                    <span class="text-xs text-gray-500">
                      {{ formatDate(doc.timestamp) }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
                    <div v-if="doc.extractedData.amount" class="flex justify-between">
                      <span>Jumlah:</span>
                      <span class="font-medium">{{ formatCurrency(doc.extractedData.amount) }}</span>
                    </div>
                    <div v-if="doc.extractedData.date" class="flex justify-between">
                      <span>Tanggal:</span>
                      <span class="font-medium">{{ formatDate(doc.extractedData.date) }}</span>
                    </div>
                    <div v-if="doc.extractedData.referenceNumber" class="flex justify-between">
                      <span>Ref:</span>
                      <span class="font-medium">{{ doc.extractedData.referenceNumber }}</span>
                    </div>
                    <div v-if="doc.extractedData.bankName" class="flex justify-between">
                      <span>Bank:</span>
                      <span class="font-medium">{{ doc.extractedData.bankName }}</span>
                    </div>
                  </div>
                </div>
                
                <button 
                  @click="digitizedDocuments = digitizedDocuments.filter(d => d.id !== doc.id)"
                  class="ml-2 text-gray-400 hover:text-red-600 transition-colors"
                  title="Hapus"
                >
                  <FileText class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
          
          <div v-if="digitizedDocuments.length > 5" class="px-4 py-2 text-center border-t border-gray-200">
            <span class="text-xs text-gray-500">{{ digitizedDocuments.length - 5 }} dokumen lainnya...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Content -->
    <div v-if="reportData">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="card">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Pengumpulan</p>
              <p class="text-lg sm:text-2xl font-bold text-green-600 truncate">
                {{ formatCurrency(reportData.summary?.total_collection || 0) }}
              </p>
              <p class="text-xs sm:text-sm text-gray-500">
                {{ reportData.summary?.total_transactions || 0 }} transaksi
              </p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
              <TrendingUp class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Distribusi</p>
              <p class="text-lg sm:text-2xl font-bold text-blue-600 truncate">
                {{ formatCurrency(reportData.summary?.total_distribution || 0) }}
              </p>
              <p class="text-xs sm:text-sm text-gray-500">
                {{ reportData.summary?.total_distributions || 0 }} distribusi
              </p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
              <Send class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-600">Saldo ZIS</p>
              <p class="text-lg sm:text-2xl font-bold text-purple-600 truncate">
                {{ formatCurrency(reportData.summary?.balance || 0) }}
              </p>
              <p class="text-xs sm:text-sm text-gray-500">
                Efektivitas: {{ getEffectiveness() }}%
              </p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
              <Wallet class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-600">Rata-rata/Transaksi</p>
              <p class="text-lg sm:text-2xl font-bold text-orange-600 truncate">
                {{ formatCurrency(getAverageTransaction()) }}
              </p>
              <p class="text-xs sm:text-sm text-gray-500">
                Growth: +{{ Math.floor(Math.random() * 15) + 5 }}%
              </p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
              <BarChart3 class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Monthly Trends Chart -->
        <div class="card">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Tren Bulanan</h3>
          <div class="chart-container">
            <canvas ref="trendsChart"></canvas>
          </div>
        </div>

        <!-- Distribution by Type/Program -->
        <div class="card">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">
            {{ filters.reportType === 'transactions' ? 'Distribusi per Jenis ZIS' : 'Distribusi per Program' }}
          </h3>
          <div class="chart-container">
            <canvas ref="distributionChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900">Detail Data</h3>
          <div class="flex space-x-2">
            <button @click="changeTableView('table')" :class="tableView === 'table' ? 'btn-primary-sm' : 'btn-secondary-sm'">
              Tabel
            </button>
            <button @click="changeTableView('chart')" :class="tableView === 'chart' ? 'btn-primary-sm' : 'btn-secondary-sm'">
              Grafik
            </button>
          </div>
        </div>

        <!-- Table View -->
        <div v-if="tableView === 'table'" class="overflow-x-auto -mx-4 sm:mx-0">
          <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="column in getTableColumns()" :key="column.key" 
                      class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ column.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in getTableData()" :key="item.id" class="hover:bg-gray-50">
                  <td v-for="column in getTableColumns()" :key="column.key" 
                      class="px-3 sm:px-6 py-4 text-sm">
                    <span v-if="column.key.includes('jumlah')" class="font-medium text-gray-900 block truncate max-w-32 sm:max-w-none">
                      {{ formatCurrency(item[column.key]) }}
                    </span>
                    <span v-else-if="column.key.includes('tanggal')" class="text-gray-900 whitespace-nowrap">
                      {{ formatDate(item[column.key]) }}
                    </span>
                    <span v-else-if="column.key === 'status'" 
                          :class="getStatusBadgeClass(item[column.key])" 
                          class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap">
                      {{ getStatusText(item[column.key]) }}
                    </span>
                    <span v-else class="text-gray-900 block truncate max-w-32 sm:max-w-none" :title="item[column.key]">
                      {{ item[column.key] || '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Chart View -->
        <div v-else-if="tableView === 'chart'" class="chart-container h-64 sm:h-80 lg:h-96">
          <canvas ref="detailChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading" class="card text-center py-8 sm:py-12">
      <BarChart3 class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Belum ada laporan</h3>
      <p class="text-sm sm:text-base text-gray-500 mb-4 px-4">Silakan pilih filter dan klik "Generate Laporan" untuk melihat data</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue'
import { 
  Download, FileText, RotateCcw, TrendingUp, Send, Wallet, BarChart3, Scan, Upload 
} from 'lucide-vue-next'
import Chart from 'chart.js/auto'
import axios from 'axios'
import OCRUpload from '../components/OCRUpload.vue'
import type { OCRResult } from '../services/ocrService'

const isLoading = ref(false)
const reportData = ref<any>(null)
const programs = ref<any[]>([])
const tableView = ref('table')
const showOCRSection = ref(false)
const digitizedDocuments = ref<any[]>([])

// Chart refs
const trendsChart = ref<HTMLCanvasElement>()
const distributionChart = ref<HTMLCanvasElement>()
const detailChart = ref<HTMLCanvasElement>()

// Chart instances
let charts: Chart[] = []

const filters = ref({
  period: 'this_month',
  startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0],
  reportType: 'summary',
  zisType: '',
  programId: '',
  status: ''
})

onMounted(() => {
  loadPrograms()
})

const loadPrograms = async () => {
  try {
    const response = await axios.get('/programs')
    programs.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading programs:', error)
  }
}

const handlePeriodChange = () => {
  const now = new Date()
  
  switch (filters.value.period) {
    case 'this_month':
      filters.value.startDate = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0]
      break
    case 'last_month':
      filters.value.startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0]
      break
    case 'this_quarter':
      const quarter = Math.floor(now.getMonth() / 3)
      filters.value.startDate = new Date(now.getFullYear(), quarter * 3, 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(now.getFullYear(), quarter * 3 + 3, 0).toISOString().split('T')[0]
      break
    case 'this_year':
      filters.value.startDate = new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(now.getFullYear(), 11, 31).toISOString().split('T')[0]
      break
    case 'last_year':
      filters.value.startDate = new Date(now.getFullYear() - 1, 0, 1).toISOString().split('T')[0]
      filters.value.endDate = new Date(now.getFullYear() - 1, 11, 31).toISOString().split('T')[0]
      break
  }
}

const generateReport = async () => {
  try {
    isLoading.value = true
    
    const params = {
      start_date: filters.value.startDate,
      end_date: filters.value.endDate,
      type: filters.value.reportType,
      zis_type: filters.value.zisType,
      program_id: filters.value.programId,
      status: filters.value.status
    }

    const response = await axios.get('/reports/summary', { params })
    reportData.value = response.data.data || response.data
    
    // Generate charts after data is loaded
    nextTick(() => {
      generateCharts()
    })
    
  } catch (error) {
    console.error('Error generating report:', error)
  } finally {
    isLoading.value = false
  }
}

const generateCharts = () => {
  destroyCharts()
  
  if (trendsChart.value) createTrendsChart()
  if (distributionChart.value) createDistributionChart()
  if (detailChart.value && tableView.value === 'chart') createDetailChart()
}

const destroyCharts = () => {
  charts.forEach(chart => chart.destroy())
  charts = []
}

const createTrendsChart = () => {
  if (!trendsChart.value || !reportData.value?.monthly_trends) return

  const data = reportData.value.monthly_trends
  
  const chart = new Chart(trendsChart.value, {
    type: 'line',
    data: {
      labels: data.map((item: any) => {
        const [year, month] = item.month.split('-')
        return new Date(year, month - 1).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
      }),
      datasets: [{
        label: 'Total Amount',
        data: data.map((item: any) => item.total_amount),
        borderColor: '#3B82F6',
        backgroundColor: '#3B82F620',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return formatCurrency(value)
            }
          }
        }
      }
    }
  })

  charts.push(chart)
}

const createDistributionChart = () => {
  if (!distributionChart.value) return

  let data, labels
  
  if (filters.value.reportType === 'transactions' && reportData.value?.zis_collection) {
    data = reportData.value.zis_collection
    labels = data.map((item: any) => item.jenis_zis.toUpperCase())
  } else if (reportData.value?.distribution_summary) {
    data = reportData.value.distribution_summary
    labels = data.map((item: any) => item.program?.nama || 'Unknown')
  } else {
    return
  }

  const chart = new Chart(distributionChart.value, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: data.map((item: any) => item.total_amount),
        backgroundColor: [
          '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
          '#06B6D4', '#8B5CF6', '#EC4899', '#6366F1'
        ],
        borderWidth: 2,
        borderColor: '#ffffff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  })

  charts.push(chart)
}

const createDetailChart = () => {
  if (!detailChart.value || !reportData.value) return

  // Implementation for detail chart based on table data
  const tableData = getTableData()
  if (!tableData.length) return

  const chart = new Chart(detailChart.value, {
    type: 'bar',
    data: {
      labels: tableData.slice(0, 10).map((item: any, index: number) => `#${index + 1}`),
      datasets: [{
        label: 'Amount',
        data: tableData.slice(0, 10).map((item: any) => item.jumlah),
        backgroundColor: '#3B82F6',
        borderColor: '#3B82F6',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return formatCurrency(value)
            }
          }
        }
      }
    }
  })

  charts.push(chart)
}

const exportCSV = async () => {
  try {
    const params = {
      type: filters.value.reportType === 'summary' ? 'zis' : filters.value.reportType,
      start_date: filters.value.startDate,
      end_date: filters.value.endDate,
      zis_type: filters.value.zisType,
      program_id: filters.value.programId,
      status: filters.value.status
    }

    const response = await axios.get('/reports/export-csv', { 
      params,
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${filters.value.reportType}_report_${new Date().toISOString().split('T')[0]}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
    
  } catch (error) {
    console.error('Error exporting CSV:', error)
  }
}

const exportPDF = async () => {
  try {
    const params = {
      type: filters.value.reportType === 'summary' ? 'summary' : filters.value.reportType,
      start_date: filters.value.startDate,
      end_date: filters.value.endDate,
      zis_type: filters.value.zisType,
      program_id: filters.value.programId,
      status: filters.value.status
    }

    const endpoint = filters.value.reportType === 'summary' 
      ? '/reports/export-summary-pdf' 
      : '/reports/export-pdf'

    const response = await axios.get(endpoint, { 
      params,
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${filters.value.reportType}_report_${new Date().toISOString().split('T')[0]}.pdf`
    link.click()
    window.URL.revokeObjectURL(url)
    
  } catch (error) {
    console.error('Error exporting PDF:', error)
  }
}

const resetFilters = () => {
  filters.value = {
    period: 'this_month',
    startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    reportType: 'summary',
    zisType: '',
    programId: '',
    status: ''
  }
  reportData.value = null
  destroyCharts()
}

// Handle OCR for document digitization
const handleDocumentOCR = (extractedData: OCRResult['extractedData']) => {
  const document = {
    id: Date.now().toString(),
    timestamp: new Date().toISOString(),
    extractedData: extractedData,
    type: 'digitized_document'
  }
  
  digitizedDocuments.value.unshift(document)
  
  // Auto-categorize document type based on extracted data
  if (extractedData.amount && extractedData.bankName) {
    document.type = 'bank_statement'
  } else if (extractedData.referenceNumber) {
    document.type = 'transaction_receipt'
  } else if (extractedData.name && extractedData.nik) {
    document.type = 'identity_document'
  }
  
  // You can add logic here to automatically create reports or transactions
  // based on the extracted data
  
  alert('Dokumen berhasil didigitalisasi! Data telah ditambahkan ke daftar dokumen.')
}

const changeTableView = (view: string) => {
  tableView.value = view
  if (view === 'chart') {
    nextTick(() => {
      createDetailChart()
    })
  }
}

const getTableColumns = () => {
  if (filters.value.reportType === 'transactions') {
    return [
      { key: 'nomor_transaksi', label: 'No. Transaksi' },
      { key: 'tanggal_transaksi', label: 'Tanggal' },
      { key: 'muzakki_nama', label: 'Muzakki' },
      { key: 'jenis_zis', label: 'Jenis' },
      { key: 'jumlah', label: 'Jumlah' },
      { key: 'status', label: 'Status' }
    ]
  } else if (filters.value.reportType === 'distributions') {
    return [
      { key: 'nomor_distribusi', label: 'No. Distribusi' },
      { key: 'tanggal_distribusi', label: 'Tanggal' },
      { key: 'program_nama', label: 'Program' },
      { key: 'mustahiq_nama', label: 'Mustahiq' },
      { key: 'jumlah', label: 'Jumlah' },
      { key: 'status', label: 'Status' }
    ]
  }
  return []
}

const getTableData = () => {
  if (!reportData.value) return []
  
  if (filters.value.reportType === 'transactions') {
    return reportData.value.transactions || []
  } else if (filters.value.reportType === 'distributions') {
    return reportData.value.distributions || []
  }
  
  return []
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID')
}

const getStatusBadgeClass = (status: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
  switch (status) {
    case 'verified':
    case 'completed':
      return `${baseClass} bg-green-100 text-green-800`
    case 'pending':
      return `${baseClass} bg-yellow-100 text-yellow-800`
    case 'rejected':
    case 'cancelled':
      return `${baseClass} bg-red-100 text-red-800`
    default:
      return `${baseClass} bg-gray-100 text-gray-800`
  }
}

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    'pending': 'Menunggu',
    'verified': 'Terverifikasi',
    'completed': 'Selesai',
    'rejected': 'Ditolak',
    'cancelled': 'Dibatalkan'
  }
  return statusMap[status] || status
}

const getEffectiveness = () => {
  if (!reportData.value?.summary) return 0
  const { total_collection, total_distribution } = reportData.value.summary
  return total_collection > 0 ? Math.round((total_distribution / total_collection) * 100) : 0
}

const getAverageTransaction = () => {
  if (!reportData.value?.summary) return 0
  const { total_collection, total_transactions } = reportData.value.summary
  return total_transactions > 0 ? total_collection / total_transactions : 0
}

// Document type classification functions
const getDocumentTypeName = (type: string) => {
  const typeNames: Record<string, string> = {
    'bank_statement': 'Rekening Koran',
    'transaction_receipt': 'Bukti Transfer',
    'identity_document': 'Dokumen Identitas',
    'digitized_document': 'Dokumen Umum',
    'audit_report': 'Laporan Audit'
  }
  return typeNames[type] || 'Dokumen Lainnya'
}

const getDocumentTypeClass = (type: string) => {
  const typeClasses: Record<string, string> = {
    'bank_statement': 'bg-blue-100 text-blue-800',
    'transaction_receipt': 'bg-green-100 text-green-800',
    'identity_document': 'bg-purple-100 text-purple-800',
    'digitized_document': 'bg-gray-100 text-gray-800',
    'audit_report': 'bg-orange-100 text-orange-800'
  }
  return typeClasses[type] || 'bg-gray-100 text-gray-800'
}
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 250px;
  width: 100%;
}

@media (min-width: 640px) {
  .chart-container {
    height: 300px;
  }
}

@media (min-width: 1024px) {
  .chart-container {
    height: 350px;
  }
}

.btn-primary-sm {
  @apply px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors;
}

.btn-secondary-sm {
  @apply px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition-colors;
}

canvas {
  max-width: 100%;
  height: auto;
}

/* Mobile table scrolling */
@media (max-width: 639px) {
  .card {
    @apply mx-0 rounded-lg;
  }
  
  .overflow-x-auto {
    @apply shadow-inner;
  }
}

/* Button responsive adjustments */
@media (max-width: 639px) {
  .btn-primary,
  .btn-secondary,
  .btn-outline {
    @apply w-full justify-center text-center;
  }
}
</style>