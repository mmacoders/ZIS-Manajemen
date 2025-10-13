<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Arsip Dokumen</h3>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Archives list -->
    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumen</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diarsipkan</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Versi</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="archive in archives" :key="archive.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <DocumentTextIcon class="h-10 w-10 text-blue-400" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ archive.file_name }}</div>
                    <div class="text-sm text-gray-500">{{ archive.document?.judul || 'Dokumen' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ archive.formatted_file_size }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  v-if="archive.category"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                >
                  {{ archive.category }}
                </span>
                <span v-else class="text-sm text-gray-500">-</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ formatDate(archive.archived_at) }}</div>
                <div class="text-xs text-gray-400">Oleh {{ archive.archivedBy?.name || 'Unknown' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                v{{ archive.version }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a 
                  :href="`/document-archives/${archive.id}/download`" 
                  target="_blank"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Download
                </a>
                <button 
                  @click="deleteArchive(archive)"
                  class="text-red-600 hover:text-red-900"
                >
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- No archives message -->
      <div v-if="archives.length === 0" class="text-center py-8 text-gray-500">
        Belum ada dokumen yang diarsipkan.
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Previous
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ pagination.from }}</span>
              to
              <span class="font-medium">{{ pagination.to }}</span>
              of
              <span class="font-medium">{{ pagination.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
              >
                <ChevronLeftIcon class="h-5 w-5" />
              </button>
              <template v-for="page in pagination.last_page" :key="page">
                <button
                  v-if="page === 1 || page === pagination.last_page || (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)"
                  @click="changePage(page)"
                  :class="[
                    page === pagination.current_page 
                      ? 'relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' 
                      : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
                    'cursor-pointer'
                  ]"
                >
                  {{ page }}
                </button>
                <span 
                  v-else-if="page === pagination.current_page - 2 || page === pagination.current_page + 2"
                  class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"
                >
                  ...
                </span>
              </template>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
              >
                <ChevronRightIcon class="h-5 w-5" />
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Upload form -->
    <div class="mt-6 pt-6 border-t border-gray-200">
      <h4 class="text-md font-medium text-gray-900 mb-3">Arsipkan Dokumen Baru</h4>
      <form @submit.prevent="uploadArchive">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dokumen</label>
            <input 
              type="file"
              @change="handleFileChange"
              class="block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <input 
              v-model="newArchive.category"
              type="text"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Kategori dokumen"
            />
          </div>
        </div>
        <div class="mt-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea 
            v-model="newArchive.description"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Deskripsi dokumen"
            rows="2"
          ></textarea>
        </div>
        <div class="mt-3">
          <button 
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            :disabled="!selectedFile || uploading"
          >
            {{ uploading ? 'Mengarsipkan...' : 'Arsipkan Dokumen' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { 
  DocumentTextIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/solid'

const props = defineProps<{
  documentId?: number
}>()

const emit = defineEmits<{
  (e: 'archive-added'): void
}>()

const archives = ref<any[]>([])
const loading = ref(false)
const uploading = ref(false)
const selectedFile = ref<File | null>(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 1,
  to: 1,
  total: 0
})

const newArchive = ref({
  category: '',
  description: ''
})

const fetchArchives = async (page = 1) => {
  try {
    loading.value = true
    const response = await axios.get('/document-archives', {
      params: {
        document_id: props.documentId,
        page: page
      }
    })
    
    archives.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      from: response.data.from,
      to: response.data.to,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching archives:', error)
  } finally {
    loading.value = false
  }
}

const handleFileChange = (event: any) => {
  const files = event.target.files
  if (files && files.length > 0) {
    selectedFile.value = files[0]
  }
}

const uploadArchive = async () => {
  if (!selectedFile.value) return
  
  try {
    uploading.value = true
    
    const formData = new FormData()
    formData.append('document_id', props.documentId?.toString() || '')
    formData.append('file', selectedFile.value)
    formData.append('category', newArchive.value.category)
    formData.append('description', newArchive.value.description)
    
    await axios.post('/document-archives', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    // Reset form
    selectedFile.value = null
    newArchive.value = {
      category: '',
      description: ''
    }
    
    emit('archive-added')
    fetchArchives()
  } catch (error) {
    console.error('Error uploading archive:', error)
  } finally {
    uploading.value = false
  }
}

const deleteArchive = async (archive: any) => {
  if (!confirm('Apakah Anda yakin ingin menghapus arsip ini?')) return
  
  try {
    await axios.delete(`/document-archives/${archive.id}`)
    fetchArchives()
  } catch (error) {
    console.error('Error deleting archive:', error)
  }
}

const changePage = (page: number) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchArchives(page)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  fetchArchives()
})

defineExpose({
  fetchArchives
})
</script>