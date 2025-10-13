<template>
  <div 
    class="bg-white border border-gray-200 rounded-lg p-6 relative transition-all duration-200 hover:shadow-md"
  >
    <!-- Icon positioned at top right -->
    <div class="absolute top-4 right-4">
      <div 
        class="rounded-full p-2"
        :class="getIconBgClass(color)"
      >
        <component 
          :is="icon" 
          class="w-5 h-5"
          :class="getIconColorClass(color)"
        />
      </div>
    </div>
    
    <!-- Card Content -->
    <div class="pr-12">
      <!-- Header/Title -->
      <p class="text-sm font-medium text-gray-500 mb-1">{{ title }}</p>
      
      <!-- Value/Angka Utama -->
      <p class="text-base lg:text-lg font-weight:500 font-bold text-gray-900 mb-2">{{ value }}</p>
      
      <!-- Supporting Info -->
      <p v-if="description" class="text-xs text-gray-400">{{ description }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  value: string | number
  description?: string
  icon: any
  color: 'blue' | 'green' | 'yellow' | 'red' | 'purple' | 'indigo'
}

const props = defineProps<Props>()

const getIconBgClass = (color: string) => {
  switch (color) {
    case 'blue':
      return 'bg-blue-100'
    case 'green':
      return 'bg-green-100'
    case 'yellow':
      return 'bg-yellow-100'
    case 'red':
      return 'bg-red-100'
    case 'purple':
      return 'bg-purple-100'
    case 'indigo':
      return 'bg-indigo-100'
    default:
      return 'bg-gray-100'
  }
}

const getIconColorClass = (color: string) => {
  switch (color) {
    case 'blue':
      return 'text-blue-600'
    case 'green':
      return 'text-green-600'
    case 'yellow':
      return 'text-yellow-600'
    case 'red':
      return 'text-red-600'
    case 'purple':
      return 'text-purple-600'
    case 'indigo':
      return 'text-indigo-600'
    default:
      return 'text-gray-600'
  }
}
</script>

<style scoped>
/* Responsive grid will be handled by parent component */
</style>