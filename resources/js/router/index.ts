import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory('/'),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/pengumpulan',
      name: 'Pengumpulan',
      component: () => import('@/views/PengumpulanView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/distribusi',
      name: 'Distribusi',
      component: () => import('@/views/DistribusiView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang2'] }
    },
    {
      path: '/arsip',
      name: 'Arsip',
      component: () => import('@/views/ArsipView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang4'] }
    },
    {
      path: '/muzakki',
      name: 'Muzakki',
      component: () => import('@/views/MuzakkiView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/upz',
      name: 'UPZ',
      component: () => import('@/views/UpzView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/zis-transactions',
      name: 'ZISTransactions',
      component: () => import('@/views/ZisTransactionsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/mustahiq',
      name: 'Mustahiq',
      component: () => import('@/views/MustahiqView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang2'] }
    },
    {
      path: '/programs',
      name: 'Programs',
      component: () => import('@/views/ProgramsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang2'] }
    },
    {
      path: '/distributions',
      name: 'Distributions',
      component: () => import('@/views/DistributionsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang2'] }
    },
    {
      path: '/documents',
      name: 'Documents',
      component: () => import('@/views/DocumentsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang4'] }
    },
    {
      path: '/reports',
      name: 'Reports',
      component: () => import('@/views/ReportsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/sharia-accounting',
      name: 'ShariaAccounting',
      component: () => import('@/views/ShariaAccountingView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/sharia-transactions',
      name: 'ShariaTransactions',
      component: () => import('@/views/ShariaTransactionsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/baznas-reports',
      name: 'BaznasReports',
      component: () => import('@/views/BaznasReportsView.vue'),
      meta: { requiresAuth: true, roles: ['admin'] }
    },
    {
      path: '/ml-analytics',
      name: 'MLAnalytics',
      component: () => import('@/views/MLAnalyticsView.vue'),
      meta: { requiresAuth: true, roles: ['admin', 'bidang1'] }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('@/views/NotFoundView.vue')
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.roles) {
    // Skip role check for now - will be implemented with proper typing later
    next()
  } else {
    next()
  }
})

export default router