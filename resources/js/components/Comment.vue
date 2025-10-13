<template>
  <div class="border rounded-lg bg-white shadow-sm">
    <div class="p-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
            <UserIcon class="w-5 h-5 text-blue-600" />
          </div>
        </div>
        <div class="ml-3 flex-1">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-medium text-gray-900">{{ comment.user?.name || 'Unknown User' }}</h4>
            <div class="flex items-center space-x-2">
              <span class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</span>
              <button 
                v-if="canEdit" 
                @click="toggleEdit"
                class="text-gray-400 hover:text-gray-600"
              >
                <PencilIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="canDelete" 
                @click="deleteComment"
                class="text-gray-400 hover:text-red-600"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div v-if="!isEditing" class="mt-2 text-sm text-gray-700">
            {{ comment.content }}
          </div>
          <div v-else class="mt-2">
            <textarea 
              v-model="editContent" 
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
            ></textarea>
            <div class="mt-2 flex space-x-2">
              <button 
                @click="saveEdit"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Save
              </button>
              <button 
                @click="cancelEdit"
                class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Replies -->
    <div v-if="comment.replies && comment.replies.length > 0" class="pl-12 pr-4 pb-4 space-y-4">
      <Comment 
        v-for="reply in comment.replies" 
        :key="reply.id"
        :comment="reply"
        @update="emit('update')"
        @delete="emit('update')"
      />
    </div>
    
    <!-- Reply form -->
    <div v-if="showReplyForm" class="pl-12 pr-4 pb-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
            <UserIcon class="w-4 h-4 text-green-600" />
          </div>
        </div>
        <div class="ml-3 flex-1">
          <textarea 
            v-model="replyContent" 
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Write a reply..."
            rows="2"
          ></textarea>
          <div class="mt-2 flex space-x-2">
            <button 
              @click="submitReply"
              class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
              :disabled="!replyContent.trim()"
            >
              Reply
            </button>
            <button 
              @click="showReplyForm = false"
              class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Reply button -->
    <div v-if="!showReplyForm" class="pl-12 pr-4 pb-4">
      <button 
        @click="showReplyForm = true"
        class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
      >
        <ChatBubbleLeftRightIcon class="w-4 h-4 mr-1" />
        Reply
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { 
  UserIcon, 
  PencilIcon, 
  TrashIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/solid'

const props = defineProps<{
  comment: any
}>()

const emit = defineEmits<{
  (e: 'update'): void
  (e: 'delete'): void
}>()

const authStore = useAuthStore()
const isEditing = ref(false)
const editContent = ref(props.comment.content)
const showReplyForm = ref(false)
const replyContent = ref('')

const canEdit = computed(() => {
  return authStore.user && authStore.user.id === props.comment.user_id
})

const canDelete = computed(() => {
  return authStore.user && authStore.user.id === props.comment.user_id
})

const toggleEdit = () => {
  isEditing.value = !isEditing.value
  editContent.value = props.comment.content
}

const cancelEdit = () => {
  isEditing.value = false
  editContent.value = props.comment.content
}

const saveEdit = async () => {
  try {
    await axios.put(`/comments/${props.comment.id}`, {
      content: editContent.value
    })
    isEditing.value = false
    emit('update')
  } catch (error) {
    console.error('Error updating comment:', error)
  }
}

const deleteComment = async () => {
  if (!confirm('Are you sure you want to delete this comment?')) return
  
  try {
    await axios.delete(`/comments/${props.comment.id}`)
    emit('delete')
  } catch (error) {
    console.error('Error deleting comment:', error)
  }
}

const submitReply = async () => {
  if (!replyContent.value.trim()) return
  
  try {
    await axios.post('/comments', {
      commentable_type: props.comment.commentable_type,
      commentable_id: props.comment.commentable_id,
      content: replyContent.value,
      parent_id: props.comment.id
    })
    
    replyContent.value = ''
    showReplyForm.value = false
    emit('update')
  } catch (error) {
    console.error('Error submitting reply:', error)
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
</script>