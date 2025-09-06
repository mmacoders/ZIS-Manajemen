import '../css/app.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

try {
  const app = createApp(App)

  app.use(createPinia())
  app.use(router)

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