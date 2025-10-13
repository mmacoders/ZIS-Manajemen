<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Komentar dan Review</h3>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Comments list -->
    <div v-else class="space-y-4">
      <Comment 
        v-for="comment in comments" 
        :key="comment.id"
        :comment="comment"
        @update="fetchComments"
        @delete="fetchComments"
      />
      
      <!-- No comments message -->
      <div v-if="comments.length === 0" class="text-center py-8 text-gray-500">
        Belum ada komentar. Jadilah yang pertama memberikan komentar.
      </div>
    </div>
    
    <!-- Add comment form -->
    <div class="mt-6 pt-6 border-t border-gray-200">
      <h4 class="text-md font-medium text-gray-900 mb-3">Tambahkan Komentar</h4>
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
            <UserIcon class="w-5 h-5 text-blue-600" />
          </div>
        </div>
        <div class="ml-3 flex-1">
          <textarea 
            v-model="newComment"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tulis komentar Anda..."
            rows="3"
          ></textarea>
          <div class="mt-2 flex justify-end">
            <button 
              @click="submitComment"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              :disabled="!newComment.trim() || submitting"
            >
              {{ submitting ? 'Mengirim...' : 'Kirim Komentar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Comment from '@/components/Comment.vue'
import { UserIcon } from '@heroicons/vue/24/solid'

const props = defineProps<{
  commentableType: string
  commentableId: number
}>()

const emit = defineEmits<{
  (e: 'comment-added'): void
}>()

const comments = ref<any[]>([])
const newComment = ref('')
const loading = ref(false)
const submitting = ref(false)

const fetchComments = async () => {
  try {
    loading.value = true
    const response = await axios.get('/comments', {
      params: {
        commentable_type: props.commentableType,
        commentable_id: props.commentableId
      }
    })
    comments.value = response.data
  } catch (error) {
    console.error('Error fetching comments:', error)
  } finally {
    loading.value = false
  }
}

const submitComment = async () => {
  if (!newComment.value.trim()) return
  
  try {
    submitting.value = true
    await axios.post('/comments', {
      commentable_type: props.commentableType,
      commentable_id: props.commentableId,
      content: newComment.value
    })
    
    newComment.value = ''
    emit('comment-added')
    fetchComments()
  } catch (error) {
    console.error('Error submitting comment:', error)
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchComments()
})

defineExpose({
  fetchComments
})
</script>