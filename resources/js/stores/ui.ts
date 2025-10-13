// resources/js/stores/ui.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const isCollapsed = ref(false)
  const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value
  }
  return { isCollapsed, toggleSidebar }
})