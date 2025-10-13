import '../css/app.css'
import './bootstrap'

import { createApp } from 'vue'
import Highcharts from 'highcharts'
import HighchartsVue from 'highcharts-vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Import Highcharts modules
import HighchartsMore from 'highcharts/highcharts-more'
import Exporting from 'highcharts/modules/exporting'
import Accessibility from 'highcharts/modules/accessibility'

// Initialize modules - these are UMD modules that export a function directly
// Based on the module structure we examined, calling them directly should work
try {
  // Handle different export patterns for Highcharts modules
  // @ts-ignore - TypeScript doesn't recognize the correct export structure
  const highchartsMoreModule = HighchartsMore?.default || HighchartsMore
  // @ts-ignore - TypeScript doesn't recognize the correct export structure
  const exportingModule = Exporting?.default || Exporting
  // @ts-ignore - TypeScript doesn't recognize the correct export structure
  const accessibilityModule = Accessibility?.default || Accessibility
  
  if (typeof highchartsMoreModule === 'function') {
    highchartsMoreModule(Highcharts)
  }
  
  if (typeof exportingModule === 'function') {
    exportingModule(Highcharts)
  }
  
  if (typeof accessibilityModule === 'function') {
    accessibilityModule(Highcharts)
  }
} catch (error) {
  console.warn('Warning: Could not initialize Highcharts modules:', error)
}

try {
  const app = createApp(App)

  app.use(createPinia())
  app.use(router)
  // Register HighchartsVue plugin globally with proper options
  app.use(HighchartsVue, {
    highcharts: Highcharts
  })

  app.mount('#app')
  console.log('✅ Vue application started successfully!')
} catch (error) {
  console.error('❌ Error starting Vue application:', error)
  // Show error message on page if Vue fails to mount
  document.body.innerHTML = `
    <div style="padding: 20px; background: #fee; color: #c00; font-family: monospace;">
      <h1>Application Error</h1>
      <p>Failed to start the Vue application:</p>
      <pre>${error}</pre>
    </div>
  `
}