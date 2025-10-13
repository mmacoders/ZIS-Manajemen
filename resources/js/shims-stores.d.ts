declare module '@/stores/auth' {
  import type { Store } from 'pinia'
  
  interface User {
    id: number
    name: string
    email: string
    role: {
      name: string
    }
  }
  
  interface AuthStore extends Store {
    user: User | null
    token: string | null
    isLoading: boolean
    isAuthenticated: boolean
    login: (credentials: { email: string; password: string }) => Promise<any>
    logout: () => Promise<void>
    getMe: () => Promise<User | null>
  }
  
  export function useAuthStore(): AuthStore
}

declare module '@/stores/dashboard' {
  import type { Store } from 'pinia'
  
  interface DashboardStore extends Store {
    // Add dashboard store properties here as needed
  }
  
  export function useDashboardStore(): DashboardStore
}

declare module '@/stores/ui' {
  import type { Store } from 'pinia'
  
  interface UIStore extends Store {
    // Add UI store properties here as needed
  }
  
  export function useUIStore(): UIStore
}