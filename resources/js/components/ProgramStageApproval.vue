<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tahapan Program</h3>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Stages list -->
    <div v-else class="space-y-4">
      <div 
        v-for="stage in stages" 
        :key="stage.id"
        class="border rounded-lg p-4"
        :class="{
          'border-green-200 bg-green-50': stage.status === 'approved',
          'border-yellow-200 bg-yellow-50': stage.status === 'pending',
          'border-red-200 bg-red-50': stage.status === 'rejected',
          'border-gray-200 bg-gray-50': stage.is_locked
        }"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
              :class="{
                'bg-green-100 text-green-600': stage.status === 'approved',
                'bg-yellow-100 text-yellow-600': stage.status === 'pending',
                'bg-red-100 text-red-600': stage.status === 'rejected',
                'bg-gray-100 text-gray-600': stage.is_locked
              }">
              <LockClosedIcon v-if="stage.is_locked" class="w-4 h-4" />
              <CheckCircleIcon v-else-if="stage.status === 'approved'" class="w-4 h-4" />
              <ClockIcon v-else-if="stage.status === 'pending'" class="w-4 h-4" />
              <XCircleIcon v-else-if="stage.status === 'rejected'" class="w-4 h-4" />
            </div>
            <div>
              <h4 class="font-medium text-gray-900">{{ stage.stage_name }}</h4>
              <p class="text-sm text-gray-500">{{ stage.description }}</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <!-- Approval status -->
            <span 
              class="px-2 py-1 text-xs font-medium rounded-full"
              :class="{
                'bg-green-100 text-green-800': stage.status === 'approved',
                'bg-yellow-100 text-yellow-800': stage.status === 'pending',
                'bg-red-100 text-red-800': stage.status === 'rejected'
              }">
              {{ stage.status === 'approved' ? 'Disetujui' : stage.status === 'pending' ? 'Pending' : 'Ditolak' }}
            </span>
            
            <!-- Lock status -->
            <span 
              v-if="stage.is_locked"
              class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
              Terkunci
            </span>
            
            <!-- Approval actions -->
            <div v-if="canApprove && !stage.is_locked" class="flex space-x-1">
              <button 
                @click="approveStage(stage)"
                class="p-1 text-green-600 hover:text-green-800"
                title="Setujui"
              >
                <CheckCircleIcon class="w-5 h-5" />
              </button>
              <button 
                @click="rejectStage(stage)"
                class="p-1 text-red-600 hover:text-red-800"
                title="Tolak"
              >
                <XCircleIcon class="w-5 h-5" />
              </button>
            </div>
            
            <!-- Lock/unlock actions -->
            <div v-if="canLock" class="flex space-x-1">
              <button 
                v-if="!stage.is_locked"
                @click="lockStage(stage)"
                class="p-1 text-gray-600 hover:text-gray-800"
                title="Kunci"
              >
                <LockClosedIcon class="w-5 h-5" />
              </button>
              <button 
                v-else
                @click="unlockStage(stage)"
                class="p-1 text-gray-600 hover:text-gray-800"
                title="Buka Kunci"
              >
                <LockOpenIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Approval details -->
        <div v-if="stage.approved_by" class="mt-3 text-sm text-gray-600">
          <p>
            Disetujui oleh {{ stage.approver?.name }} pada {{ formatDate(stage.approved_at) }}
          </p>
        </div>
      </div>
      
      <!-- No stages message -->
      <div v-if="stages.length === 0" class="text-center py-8 text-gray-500">
        Belum ada tahapan program yang ditentukan.
      </div>
    </div>
    
    <!-- Add stage form (for admins) -->
    <div v-if="canAddStage" class="mt-6 pt-6 border-t border-gray-200">
      <h4 class="text-md font-medium text-gray-900 mb-3">Tambah Tahapan Program</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tahapan</label>
          <input 
            v-model="newStage.name"
            type="text"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Nama tahapan"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
          <input 
            v-model="newStage.order"
            type="number"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Urutan"
          />
        </div>
        <div class="flex items-end">
          <button 
            @click="addStage"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            :disabled="!newStage.name || !newStage.order"
          >
            Tambah Tahapan
          </button>
        </div>
      </div>
      <div class="mt-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <textarea 
          v-model="newStage.description"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Deskripsi tahapan"
          rows="2"
        ></textarea>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { 
  CheckCircleIcon, 
  XCircleIcon, 
  ClockIcon,
  LockClosedIcon,
  LockOpenIcon
} from '@heroicons/vue/24/solid'

const props = defineProps<{
  programId: number
}>()

const emit = defineEmits<{
  (e: 'stage-updated'): void
}>()

const authStore = useAuthStore()
const stages = ref<any[]>([])
const loading = ref(false)
const newStage = ref({
  name: '',
  description: '',
  order: 1
})

// Permissions
const canApprove = computed(() => {
  // Only certain roles can approve stages
  const approverRoles = ['admin', 'wakil1', 'wakil2', 'wakil3', 'wakil4']
  return authStore.user && approverRoles.includes(authStore.user.role.name)
})

const canLock = computed(() => {
  // Only admins can lock/unlock stages
  return authStore.user && authStore.user.role.name === 'admin'
})

const canAddStage = computed(() => {
  // Only admins can add stages
  return authStore.user && authStore.user.role.name === 'admin'
})

const fetchStages = async () => {
  try {
    loading.value = true
    const response = await axios.get('/program-stages', {
      params: {
        program_id: props.programId
      }
    })
    stages.value = response.data
  } catch (error) {
    console.error('Error fetching stages:', error)
  } finally {
    loading.value = false
  }
}

const approveStage = async (stage: any) => {
  if (!confirm('Apakah Anda yakin ingin menyetujui tahapan ini?')) return
  
  try {
    await axios.put(`/program-stages/${stage.id}`, {
      status: 'approved'
    })
    fetchStages()
    emit('stage-updated')
  } catch (error) {
    console.error('Error approving stage:', error)
  }
}

const rejectStage = async (stage: any) => {
  if (!confirm('Apakah Anda yakin ingin menolak tahapan ini?')) return
  
  try {
    await axios.put(`/program-stages/${stage.id}`, {
      status: 'rejected'
    })
    fetchStages()
    emit('stage-updated')
  } catch (error) {
    console.error('Error rejecting stage:', error)
  }
}

const lockStage = async (stage: any) => {
  if (!confirm('Apakah Anda yakin ingin mengunci tahapan ini? Setelah dikunci, tahapan tidak dapat diubah.')) return
  
  try {
    await axios.post(`/program-stages/${stage.id}/lock`)
    fetchStages()
    emit('stage-updated')
  } catch (error) {
    console.error('Error locking stage:', error)
  }
}

const unlockStage = async (stage: any) => {
  if (!confirm('Apakah Anda yakin ingin membuka kunci tahapan ini?')) return
  
  try {
    await axios.post(`/program-stages/${stage.id}/unlock`)
    fetchStages()
    emit('stage-updated')
  } catch (error) {
    console.error('Error unlocking stage:', error)
  }
}

const addStage = async () => {
  if (!newStage.value.name || !newStage.value.order) return
  
  try {
    await axios.post('/program-stages', {
      program_id: props.programId,
      stage_name: newStage.value.name,
      description: newStage.value.description,
      order: newStage.value.order
    })
    
    newStage.value = {
      name: '',
      description: '',
      order: 1
    }
    
    fetchStages()
    emit('stage-updated')
  } catch (error) {
    console.error('Error adding stage:', error)
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchStages()
})

defineExpose({
  fetchStages
})
</script>