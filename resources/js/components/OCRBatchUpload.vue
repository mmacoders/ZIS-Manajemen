<template>
  <div class="ocr-batch-upload-component">
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <h3 class="text-lg font-semibold text-blue-900 mb-2">
        <Layers class="w-5 h-5 inline mr-2" />
        Pemrosesan Dokumen Batch
      </h3>
      <p class="text-sm text-blue-700">
        Unggah beberapa dokumen untuk pemrosesan OCR batch. Format yang didukung: JPG, PNG, PDF (Maks: 5MB per file)
      </p>
    </div>

    <!-- Template Selection -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Template Dokumen (Opsional)</label>
      <select v-model="selectedTemplate" class="form-select">
        <option value="">Deteksi otomatis jenis dokumen</option>
        <option v-for="template in documentTemplates" :key="template.name" :value="template.name">
          {{ template.name }}
        </option>
      </select>
    </div>

    <!-- File Upload Area -->
    <div 
      v-if="!selectedFiles.length"
      @dragover.prevent
      @dragenter.prevent
      @drop="handleDrop"
      @click="triggerFileSelect"
      class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/*,.pdf"
        multiple
        @change="handleFileSelect"
        class="hidden"
      />
      
      <div class="flex flex-col items-center">
        <Upload class="w-16 h-16 text-gray-400 mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
          Unggah Banyak Dokumen
        </h3>
        <p class="text-sm text-gray-600 mb-4 max-w-sm">
          Pilih beberapa file atau seret dan lepas di sini untuk pemrosesan OCR batch
        </p>
        <div class="flex flex-col sm:flex-row gap-2 text-xs sm:text-sm text-gray-500">
          <span>• Klik untuk memilih beberapa file</span>
          <span class="hidden sm:inline">atau</span>
          <span>• Seret & lepas file di sini</span>
        </div>
      </div>
    </div>

    <!-- File List -->
    <div v-else class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold text-gray-900">
          File Terpilih ({{ selectedFiles.length }})
        </h4>
        <div class="flex gap-2">
          <button
            @click="addMoreFiles"
            class="btn-secondary-sm"
          >
            <Plus class="w-4 h-4 mr-1" />
            Tambah Lagi
          </button>
          <button
            @click="clearFiles"
            class="btn-outline-sm"
          >
            <X class="w-4 h-4 mr-1" />
            Hapus Semua
          </button>
        </div>
      </div>

      <!-- File Preview List -->
      <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
        <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
              <FileImage class="w-4 h-4 text-gray-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 truncate max-w-48">{{ file.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <!-- Processing Status -->
            <div v-if="processingStatus[index]" class="flex items-center space-x-2">
              <div v-if="processingStatus[index] === 'processing'" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                <span class="text-xs text-blue-600">Memproses...</span>
              </div>
              <div v-else-if="processingStatus[index] === 'success'" class="flex items-center">
                <CheckCircle class="w-4 h-4 text-green-600 mr-1" />
                <span class="text-xs text-green-600">Berhasil</span>
              </div>
              <div v-else-if="processingStatus[index] === 'error'" class="flex items-center">
                <AlertCircle class="w-4 h-4 text-red-600 mr-1" />
                <span class="text-xs text-red-600">Gagal</span>
              </div>
            </div>
            
            <button
              @click="removeFile(index)"
              :disabled="isProcessing"
              class="text-gray-400 hover:text-red-600 transition-colors disabled:opacity-50"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Process Button -->
      <div class="flex justify-center">
        <button
          @click="processFiles"
          :disabled="isProcessing || selectedFiles.length === 0"
          class="btn-primary px-6 py-3"
        >
          <div v-if="isProcessing" class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            Memproses {{ currentProcessing }}/{{ selectedFiles.length }}...
          </div>
          <span v-else>
            <Zap class="w-4 h-4 mr-2" />
            Proses {{ selectedFiles.length }} File
          </span>
        </button>
      </div>
    </div>

    <!-- Processing Progress -->
    <div v-if="isProcessing" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-blue-900">Progres Pemrosesan Batch</span>
        <span class="text-sm text-blue-700">{{ Math.round((currentProcessing / selectedFiles.length) * 100) }}%</span>
      </div>
      
      <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
        <div 
          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: (currentProcessing / selectedFiles.length) * 100 + '%' }"
        ></div>
      </div>
      
      <div class="text-xs text-blue-700">
        Sedang memproses: {{ currentFileName }}
      </div>
    </div>

    <!-- Results Summary -->
    <div v-if="batchResults && !isProcessing" class="mt-4 space-y-4">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex items-center">
            <CheckCircle class="w-8 h-8 text-green-600 mr-3" />
            <div>
              <p class="text-lg font-bold text-green-900">{{ batchResults.successCount }}</p>
              <p class="text-sm text-green-700">Berhasil</p>
            </div>
          </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-center">
            <AlertCircle class="w-8 h-8 text-red-600 mr-3" />
            <div>
              <p class="text-lg font-bold text-red-900">{{ batchResults.failedCount }}</p>
              <p class="text-sm text-red-700">Gagal</p>
            </div>
          </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center">
            <FileText class="w-8 h-8 text-blue-600 mr-3" />
            <div>
              <p class="text-lg font-bold text-blue-900">{{ batchResults.totalProcessed }}</p>
              <p class="text-sm text-blue-700">Total Diproses</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-3">
        <button
          @click="applyBatchResults"
          :disabled="batchResults.successCount === 0"
          class="flex-1 btn-primary"
        >
          <Download class="w-4 h-4 mr-2" />
          Terapkan {{ batchResults.successCount }} Hasil Berhasil
        </button>
        
        <button
          @click="exportResults"
          class="flex-1 sm:flex-none btn-secondary"
        >
          <FileText class="w-4 h-4 mr-2" />
          Ekspor Hasil
        </button>
        
        <button
          @click="resetBatch"
          class="flex-1 sm:flex-none btn-outline"
        >
          <RotateCcw class="w-4 h-4 mr-2" />
          Reset
        </button>
      </div>

      <!-- Failed Files Details -->
      <div v-if="batchResults.failed.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h5 class="text-sm font-medium text-red-900 mb-2">File Gagal ({{ batchResults.failed.length }})</h5>
        <div class="space-y-2">
          <div v-for="(failure, index) in batchResults.failed" :key="index" class="text-xs">
            <span class="font-medium text-red-800">{{ failure.file.name }}:</span>
            <span class="text-red-700 ml-1">{{ failure.error }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { 
  Layers, Upload, Plus, X, FileImage, CheckCircle, AlertCircle, 
  Zap, FileText, Download, RotateCcw 
} from 'lucide-vue-next'
import ocrService, { type OCRBatchResult, type DocumentTemplate } from '../services/ocrService'

interface Props {
  documentType?: 'transaction' | 'identity' | 'general'
}

interface Emits {
  (e: 'batchCompleted', results: OCRBatchResult): void
  (e: 'resultsApplied', successResults: any[]): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// State
const fileInput = ref<HTMLInputElement>()
const selectedFiles = ref<File[]>([])
const selectedTemplate = ref<string>('')
const isProcessing = ref(false)
const currentProcessing = ref(0)
const currentFileName = ref('')
const processingStatus = ref<Record<number, 'processing' | 'success' | 'error'>>({})
const batchResults = ref<OCRBatchResult | null>(null)
const documentTemplates = ref<DocumentTemplate[]>([])

onMounted(async () => {
  await ocrService.initialize()
  documentTemplates.value = ocrService.getDocumentTemplates()
})

// Methods
const triggerFileSelect = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  addFiles(files)
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  const files = Array.from(event.dataTransfer?.files || [])
  addFiles(files)
}

const addFiles = (files: File[]) => {
  const validFiles = files.filter(file => {
    // Validate file type
    if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
      console.warn(`Skipping invalid file type: ${file.name}`)
      return false
    }
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
      console.warn(`Skipping oversized file: ${file.name}`)
      return false
    }
    return true
  })
  
  selectedFiles.value.push(...validFiles)
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const addMoreFiles = () => {
  triggerFileSelect()
}

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
  delete processingStatus.value[index]
}

const clearFiles = () => {
  selectedFiles.value = []
  processingStatus.value = {}
  batchResults.value = null
}

const processFiles = async () => {
  if (selectedFiles.value.length === 0) return
  
  isProcessing.value = true
  currentProcessing.value = 0
  processingStatus.value = {}
  
  try {
    const templateName = selectedTemplate.value || undefined
    
    batchResults.value = await ocrService.processBatch(
      selectedFiles.value,
      (processed, total, fileName) => {
        currentProcessing.value = processed
        currentFileName.value = fileName
        
        // Update individual file status
        if (processed > 0 && processed <= selectedFiles.value.length) {
          processingStatus.value[processed - 1] = 'processing'
        }
      },
      props.documentType || 'general',
      templateName
    )
    
    // Update final statuses
    batchResults.value.success.forEach((_, index) => {
      processingStatus.value[index] = 'success'
    })
    
    batchResults.value.failed.forEach((failure) => {
      const index = selectedFiles.value.indexOf(failure.file)
      if (index !== -1) {
        processingStatus.value[index] = 'error'
      }
    })
    
    emit('batchCompleted', batchResults.value)
    
  } catch (error) {
    console.error('Batch processing error:', error)
  } finally {
    isProcessing.value = false
    currentFileName.value = ''
  }
}

const applyBatchResults = () => {
  if (batchResults.value) {
    const successData = batchResults.value.success.map(result => result.extractedData)
    emit('resultsApplied', successData)
  }
}

const exportResults = () => {
  if (!batchResults.value) return
  
  const data = {
    summary: {
      totalProcessed: batchResults.value.totalProcessed,
      successCount: batchResults.value.successCount,
      failedCount: batchResults.value.failedCount,
      timestamp: new Date().toISOString()
    },
    successful: batchResults.value.success.map(result => ({
      text: result.text,
      confidence: result.confidence,
      extractedData: result.extractedData
    })),
    failed: batchResults.value.failed.map(failure => ({
      fileName: failure.file.name,
      error: failure.error
    }))
  }
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `ocr_batch_results_${new Date().toISOString().slice(0, 10)}.json`
  link.click()
  URL.revokeObjectURL(url)
}

const resetBatch = () => {
  selectedFiles.value = []
  processingStatus.value = {}
  batchResults.value = null
  selectedTemplate.value = ''
  currentProcessing.value = 0
  currentFileName.value = ''
}

// Utility functions
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.ocr-batch-upload-component {
  @apply w-full;
}

/* Hover effects for upload area */
.border-dashed:hover {
  @apply border-blue-400 bg-blue-50;
}

/* Loading animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>