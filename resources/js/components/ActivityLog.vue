<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Log Aktivitas</h3>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Activities list -->
    <div v-else>
      <div class="flow-root">
        <ul class="-mb-8">
          <li v-for="(activity, index) in activities" :key="activity.id">
            <div class="relative pb-8">
              <span v-if="index !== activities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
              <div class="relative flex space-x-3">
                <div>
                  <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                    <UserIcon class="h-5 w-5 text-white" />
                  </span>
                </div>
                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                  <div>
                    <p class="text-sm text-gray-500">
                      {{ activity.message }}
                    </p>
                  </div>
                  <div class="text-right text-sm whitespace-nowrap text-gray-500">
                    <time :datetime="activity.timestamp">{{ activity.created_at }}</time>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      
      <!-- No activities message -->
      <div v-if="activities.length === 0" class="text-center py-8 text-gray-500">
        Belum ada aktivitas yang dicatat.
      </div>
      
      <!-- Load more button -->
      <div v-if="hasMore" class="mt-4 text-center">
        <button 
          @click="loadMore"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Muat Lebih Banyak
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { UserIcon } from '@heroicons/vue/24/solid'

const activities = ref<any[]>([])
const loading = ref(false)
const offset = ref(0)
const hasMore = ref(true)
const limit = 10 // Number of activities per page

const fetchActivities = async (loadMore = false) => {
  try {
    loading.value = true
    // Use relative path since axios baseURL already includes /api
    const response = await axios.get(`/activities?offset=${offset.value}&limit=${limit}`)
    
    // Check if response and data exist before accessing
    if (response && response.data && response.data.activities) {
      if (loadMore) {
        activities.value = [...activities.value, ...response.data.activities]
      } else {
        activities.value = response.data.activities
      }
      
      // Check if there are more activities to load
      hasMore.value = response.data.activities.length === limit
    } else {
      // Handle case when no activities are returned
      if (!loadMore) {
        activities.value = []
      }
      hasMore.value = false
    }
  } catch (error) {
    console.error('Error fetching activities:', error)
    // In a real implementation, you might want to show an error message to the user
    if (!loadMore) {
      activities.value = []
    }
    hasMore.value = false
  } finally {
    loading.value = false
  }
}

const loadMore = () => {
  offset.value += limit
  fetchActivities(true)
}

onMounted(() => {
  fetchActivities()
})
</script>