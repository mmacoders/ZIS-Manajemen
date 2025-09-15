<template>
  <div class="ocr-upload-component">
    <!-- Language Selector -->
    <OCRLanguageSelector 
      v-if="showLanguageSelector"
      @language-changed="handleLanguageChange"
      @auto-detect-changed="handleAutoDetectChange"
      class="mb-3"
    />

    <!-- Upload Area -->
    <div 
      v-if="!selectedImage"
      @dragover.prevent
      @dragenter.prevent
      @drop="handleDrop"
      @click="triggerFileSelect"
      class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        @change="handleFileSelect"
        class="hidden"
      />
      
      <div class="flex flex-col items-center">
        <Camera class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400 mb-2" />
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-1">
          Scan Dokumen dengan OCR
        </h3>
        <p class="text-xs text-gray-600 mb-2 max-w-xs">
          Upload gambar bukti transfer atau dokumen lainnya
        </p>
        <div class="flex flex-col text-xs text-gray-500">
          <span>• Klik untuk pilih file</span>
          <span>• Drag & drop gambar di sini</span>
        </div>
        <div class="mt-1 text-xs text-gray-400">
          Format: JPG, PNG (Max: 5MB)
        </div>
      </div>
    </div>

    <!-- Image Preview & Processing -->
    <div v-else class="space-y-3">
      <!-- Image Preview -->
      <div class="relative">
        <img 
          :src="imagePreview" 
          :alt="selectedImage.name"
          class="w-full max-h-32 sm:max-h-40 object-contain rounded-lg border border-gray-200"
        />
        <button
          @click="clearImage"
          class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors text-xs"
        >
          <X class="w-3 h-3" />
        </button>
      </div>

      <!-- File Info -->
      <div class="bg-gray-50 rounded-lg p-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-gray-900 truncate">{{ selectedImage.name }}</span>
          <span class="text-xs text-gray-500">{{ formatFileSize(selectedImage.size) }}</span>
        </div>
      </div>

      <!-- Processing Status -->
      <div v-if="isProcessing" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div class="flex items-center mb-2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
          <span class="text-xs font-medium text-blue-900">Memproses gambar dengan OCR...</span>
        </div>
        
        <div class="w-full bg-blue-200 rounded-full h-1.5 mb-1">
          <div 
            class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
            :style="{ width: processingProgress + '%' }"
          ></div>
        </div>
        
        <div class="text-xs text-blue-700">
          {{ processingStatus }} ({{ Math.round(processingProgress) }}%)
        </div>
      </div>

      <!-- OCR Results -->
      <div v-if="ocrResults && !isProcessing" class="space-y-3">
        <!-- Multi-language Detection Results -->
        <div v-if="multiLanguageResults" class="bg-purple-50 border border-purple-200 rounded-lg p-3">
          <h4 class="text-xs font-semibold text-purple-900 mb-1">Hasil Deteksi Multi-bahasa</h4>
          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-xs text-purple-700">Bahasa Terdeteksi:</span>
              <span class="text-xs font-medium text-purple-900">
                {{ getLanguageName(multiLanguageResults.detectedLanguage) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs text-purple-700">Tingkat Kepercayaan Bahasa:</span>
              <span class="text-xs font-medium text-purple-900">
                {{ Math.round(multiLanguageResults.languageConfidence) }}%
              </span>
            </div>
          </div>
                  
          <!-- Alternative Results -->
          <div v-if="multiLanguageResults.alternativeResults && multiLanguageResults.alternativeResults.length > 0" class="mt-2">
            <h5 class="text-xs font-medium text-purple-800 mb-1">Hasil Alternatif:</h5>
            <div class="space-y-1">
              <div 
                v-for="alt in multiLanguageResults.alternativeResults" 
                :key="alt.language"
                class="flex items-center justify-between text-xs"
              >
                <span class="text-purple-600">{{ getLanguageName(alt.language) }}:</span>
                <span class="text-purple-800">{{ Math.round(alt.confidence) }}%</span>
              </div>
            </div>
          </div>
        </div>
      
        <!-- Confidence Score -->
        <div class="flex items-center justify-between p-2 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center">
            <CheckCircle class="w-4 h-4 text-green-600 mr-1.5" />
            <span class="text-xs font-medium text-green-900">OCR Berhasil</span>
          </div>
          <div class="text-xs text-green-700">
            Akurasi: {{ Math.round(ocrResults.confidence) }}%
          </div>
        </div>

        <!-- Extracted Data -->
        <div v-if="hasExtractedData" class="bg-white border border-gray-200 rounded-lg p-3">
          <h4 class="text-xs font-semibold text-gray-900 mb-2">Data Terekstraksi:</h4>
          
          <div class="grid grid-cols-1 gap-2">
            <div v-if="ocrResults.extractedData.amount" class="flex justify-between">
              <span class="text-xs text-gray-600">Jumlah:</span>
              <span class="text-xs font-medium text-gray-900">
                {{ formatCurrency(ocrResults.extractedData.amount) }}
              </span>
            </div>
            
            <div v-if="ocrResults.extractedData.date" class="flex justify-between">
              <span class="text-xs text-gray-600">Tanggal:</span>
              <span class="text-xs font-medium text-gray-900">
                {{ formatDate(ocrResults.extractedData.date) }}
              </span>
            </div>
            
            <div v-if="ocrResults.extractedData.referenceNumber" class="flex justify-between">
              <span class="text-xs text-gray-600">No. Referensi:</span>
              <span class="text-xs font-medium text-gray-900">
                {{ ocrResults.extractedData.referenceNumber }}
              </span>
            </div>
            
            <div v-if="ocrResults.extractedData.bankName" class="flex justify-between">
              <span class="text-xs text-gray-600">Bank:</span>
              <span class="text-xs font-medium text-gray-900">
                {{ ocrResults.extractedData.bankName }}
              </span>
            </div>
            
            <div v-if="ocrResults.extractedData.donorName" class="flex justify-between">
              <span class="text-xs text-gray-600">Nama Pengirim:</span>
              <span class="text-xs font-medium text-gray-900">
                {{ ocrResults.extractedData.donorName }}
              </span>
            </div>
          </div>
        </div>

        <!-- Raw Text (Collapsible) -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg">
          <button
            @click="showRawText = !showRawText"
            class="w-full px-3 py-2 text-left text-xs font-medium text-gray-700 hover:text-gray-900 focus:outline-none flex items-center justify-between"
          >
            <span>Teks Lengkap (Raw OCR)</span>
            <ChevronDown :class="showRawText ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" />
          </button>
          
          <div v-show="showRawText" class="px-3 pb-2">
            <div class="bg-white rounded border p-2 text-xs text-gray-700 max-h-24 overflow-y-auto">
              {{ ocrResults.text }}
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-2">
          <button
            @click="applyExtractedData"
            :disabled="!hasExtractedData"
            class="flex-1 btn-primary py-2 px-3 text-xs flex items-center justify-center"
          >
            <Download class="w-3 h-3 mr-1" />
            Gunakan Data Ini
          </button>
          
          <button
            @click="processImage"
            class="flex-1 sm:flex-none btn-secondary py-2 px-3 text-xs flex items-center justify-center"
          >
            <RotateCcw class="w-3 h-3 mr-1" />
            Proses Ulang
          </button>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
        <div class="flex items-start">
          <AlertCircle class="w-4 h-4 text-red-600 mr-2 mt-0.5 flex-shrink-0" />
          <div>
            <h4 class="text-xs font-medium text-red-900">Gagal Memproses Gambar</h4>
            <p class="text-xs text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
        
        <div class="mt-2 flex gap-2">
          <button @click="processImage" class="btn-secondary text-xs py-1.5 px-2">
            Coba Lagi
          </button>
          <button @click="clearImage" class="btn-outline text-xs py-1.5 px-2">
            Pilih Gambar Lain
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { 
  Camera, X, CheckCircle, AlertCircle, ChevronDown, 
  Download, RotateCcw 
} from 'lucide-vue-next'
import ocrService, { type OCRResult, type MultiLanguageOCRResult } from '../services/ocrService'
import OCRLanguageSelector from './OCRLanguageSelector.vue'

interface Props {
  modelValue?: OCRResult | null
  documentType?: 'transaction' | 'identity' | 'general'
  showLanguageSelector?: boolean
  enableMultiLanguage?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: OCRResult | null): void
  (e: 'dataExtracted', data: OCRResult['extractedData']): void
  (e: 'multiLanguageResult', result: MultiLanguageOCRResult): void
}

const props = withDefaults(defineProps<Props>(), {
  showLanguageSelector: true,
  enableMultiLanguage: false
})
const emit = defineEmits<Emits>()

const documentType = computed(() => props.documentType || 'general')

// State
const fileInput = ref<HTMLInputElement>()
const selectedImage = ref<File | null>(null)
const imagePreview = ref<string>('')
const isProcessing = ref(false)
const processingProgress = ref(0)
const processingStatus = ref('')
const ocrResults = ref<OCRResult | null>(null)
const multiLanguageResults = ref<MultiLanguageOCRResult | null>(null)
const error = ref<string>('')
const showRawText = ref(false)
const currentLanguage = ref<string>('ind')
const autoDetectEnabled = ref<boolean>(false)
const selectedLanguages = ref<string[]>(['ind', 'eng'])

// Computed
const hasExtractedData = computed(() => {
  if (!ocrResults.value?.extractedData) return false
  const data = ocrResults.value.extractedData
  return !!(data.amount || data.date || data.referenceNumber || data.bankName || data.donorName || 
            data.nik || data.name || data.address || data.birthDate || data.gender)
})

// Methods
const triggerFileSelect = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  const file = event.dataTransfer?.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const validateAndSetFile = (file: File) => {
  // Validate file type
  if (!file.type.startsWith('image/')) {
    error.value = 'File harus berupa gambar (JPG, PNG, dll.)'
    return
  }

  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'Ukuran file maksimal 5MB'
    return
  }

  selectedImage.value = file
  error.value = ''
  
  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)

  // Auto-process if image is selected
  processImage()
}

const processImage = async () => {
  if (!selectedImage.value) return

  isProcessing.value = true
  processingProgress.value = 0
  processingStatus.value = 'Memuat OCR engine...'
  error.value = ''
  ocrResults.value = null
  multiLanguageResults.value = null

  try {
    let result: OCRResult

    if (props.enableMultiLanguage || autoDetectEnabled.value) {
      // Multi-language processing
      processingStatus.value = 'Mendeteksi bahasa dokumen...'
      
      const multiResult = await ocrService.processImageMultiLanguage(
        selectedImage.value,
        selectedLanguages.value,
        (progress) => {
          processingProgress.value = progress.progress
          processingStatus.value = progress.status
        },
        documentType.value
      )
      
      multiLanguageResults.value = multiResult
      result = multiResult
      emit('multiLanguageResult', multiResult)
      
    } else {
      // Single language processing
      await ocrService.initialize(currentLanguage.value)
      
      result = await ocrService.processImage(
        selectedImage.value,
        (progress) => {
          processingProgress.value = progress.progress
          processingStatus.value = progress.status
        },
        documentType.value
      )
    }

    ocrResults.value = result
    emit('update:modelValue', result)

  } catch (err) {
    console.error('OCR Error:', err)
    error.value = err instanceof Error ? err.message : 'Terjadi kesalahan saat memproses gambar'
  } finally {
    isProcessing.value = false
  }
}

const handleLanguageChange = (language: string) => {
  currentLanguage.value = language
  if (selectedImage.value && ocrResults.value) {
    // Re-process with new language
    processImage()
  }
}

const handleAutoDetectChange = (enabled: boolean, languages: string[]) => {
  autoDetectEnabled.value = enabled
  selectedLanguages.value = languages
  if (selectedImage.value && ocrResults.value) {
    // Re-process with new settings
    processImage()
  }
}

const getLanguageName = (code: string): string => {
  const languageMap: Record<string, string> = {
    'ind': 'Indonesia',
    'ara': 'Arab',
    'eng': 'Inggris'
  }
  return languageMap[code] || code.toUpperCase()
}

const applyExtractedData = () => {
  console.log('Apply button clicked')
  console.log('OCR Results:', ocrResults.value)
  
  if (ocrResults.value?.extractedData) {
    console.log('Applying extracted data:', ocrResults.value.extractedData)
    emit('dataExtracted', ocrResults.value.extractedData)
  } else {
    console.warn('No extracted data to apply')
    // Show a user-friendly message
    alert('Tidak ada data yang dapat digunakan. Pastikan OCR telah berhasil membaca dokumen.')
  }
}

const clearImage = () => {
  selectedImage.value = null
  imagePreview.value = ''
  ocrResults.value = null
  multiLanguageResults.value = null
  error.value = ''
  processingProgress.value = 0
  processingStatus.value = ''
  showRawText.value = false
  emit('update:modelValue', null)
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Utility functions
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateStr: string): string => {
  try {
    return new Date(dateStr).toLocaleDateString('id-ID')
  } catch {
    return dateStr
  }
}
</script>

<style scoped>
.ocr-upload-component {
  @apply w-full;
}

/* Hover effects for upload area */
.border-dashed:hover {
  @apply border-blue-400 bg-blue-50;
}

/* Loading animation for processing */
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