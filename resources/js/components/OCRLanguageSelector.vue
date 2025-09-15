<template>
  <div class="ocr-language-selector">
    <div class="mb-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
      <h4 class="text-sm font-semibold text-purple-900 mb-2 flex items-center">
        <Globe class="w-4 h-4 mr-2" />
        Pengaturan Bahasa OCR
      </h4>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Language Selection -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Bahasa Utama</label>
          <select 
            v-model="selectedLanguage" 
            @change="handleLanguageChange"
            class="form-select w-full text-sm"
          >
            <option 
              v-for="language in availableLanguages" 
              :key="language.code" 
              :value="language.code"
            >
              {{ language.name }} ({{ language.code.toUpperCase() }})
            </option>
          </select>
        </div>

        <!-- Auto-detection Toggle -->
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Mode Pemrosesan</label>
          <div class="flex items-center">
            <label class="flex items-center">
              <input
                v-model="autoDetect"
                type="checkbox"
                @change="handleAutoDetectChange"
                class="form-checkbox h-3 w-3 text-purple-600"
              />
              <span class="ml-2 text-xs text-gray-700">Deteksi otomatis bahasa</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Language Details -->
      <div v-if="currentLanguageConfig" class="mt-3 p-2 bg-white border border-purple-100 rounded text-xs">
        <div class="flex items-center justify-between mb-1">
          <h5 class="font-semibold text-gray-900">
            {{ currentLanguageConfig.name }}
          </h5>
          <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded">
            {{ currentLanguageConfig.direction.toUpperCase() }}
          </span>
        </div>
        
        <div class="grid grid-cols-2 gap-2">
          <div>
            <span class="font-medium text-gray-600">Tesseract:</span>
            <span class="ml-1 text-gray-800">{{ currentLanguageConfig.tesseractCode }}</span>
          </div>
        </div>
      </div>

      <!-- Multi-language Selection for Auto-detect -->
      <div v-if="autoDetect" class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded">
        <h5 class="text-xs font-semibold text-blue-900 mb-1">Bahasa untuk Deteksi Otomatis</h5>
        <div class="flex flex-wrap gap-1">
          <label 
            v-for="language in availableLanguages" 
            :key="language.code"
            class="flex items-center text-xs"
          >
            <input
              v-model="selectedLanguagesForDetection"
              :value="language.code"
              type="checkbox"
              class="form-checkbox h-3 w-3 text-blue-600 mr-1"
            />
            <span class="text-gray-700">{{ language.name }}</span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Globe } from 'lucide-vue-next'
import ocrService, { type OCRLanguageConfig } from '../services/ocrService'

interface Props {
  showStats?: boolean
}

interface Emits {
  (e: 'languageChanged', language: string): void
  (e: 'autoDetectChanged', enabled: boolean, languages: string[]): void
}

const props = withDefaults(defineProps<Props>(), {
  showStats: false
})

const emit = defineEmits<Emits>()

// State
const availableLanguages = ref<OCRLanguageConfig[]>([])
const selectedLanguage = ref<string>('ind')
const autoDetect = ref<boolean>(false)
const selectedLanguagesForDetection = ref<string[]>(['ind', 'eng'])
const processingStats = ref<any>(null)

// Computed
const currentLanguageConfig = computed(() => {
  return availableLanguages.value.find(lang => lang.code === selectedLanguage.value)
})

// Methods
onMounted(async () => {
  await initializeLanguages()
  await loadProcessingStats()
})

const initializeLanguages = async () => {
  try {
    await ocrService.initialize()
    availableLanguages.value = ocrService.getAvailableLanguages()
    selectedLanguage.value = ocrService.getCurrentLanguage()
  } catch (error) {
    console.error('Failed to initialize languages:', error)
  }
}

const loadProcessingStats = async () => {
  if (props.showStats) {
    try {
      // In a real app, this would come from an API
      processingStats.value = {
        totalProcessed: 1250,
        successRate: 94.5,
        avgConfidence: 87.2
      }
    } catch (error) {
      console.error('Failed to load processing stats:', error)
    }
  }
}

const handleLanguageChange = async () => {
  try {
    await ocrService.setLanguage(selectedLanguage.value)
    emit('languageChanged', selectedLanguage.value)
  } catch (error) {
    console.error('Failed to change language:', error)
    // Revert to previous language
    selectedLanguage.value = ocrService.getCurrentLanguage()
  }
}

const handleAutoDetectChange = () => {
  if (autoDetect.value && selectedLanguagesForDetection.value.length === 0) {
    // Ensure at least one language is selected for auto-detection
    selectedLanguagesForDetection.value = ['ind', 'eng']
  }
  
  emit('autoDetectChanged', autoDetect.value, selectedLanguagesForDetection.value)
}
</script>

<style scoped>
.ocr-language-selector {
  @apply w-full;
}

/* Custom checkbox styles */
.form-checkbox {
  @apply rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50;
}

/* Custom select styles */
.form-select {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500;
}

/* Language feature badges */
.language-features .badge {
  @apply inline-block px-2 py-1 text-xs font-medium rounded;
}
</style>