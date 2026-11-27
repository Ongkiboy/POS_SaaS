import { createRouter, createWebHistory, type RouterHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export const routes = [
  { path: '/', name: 'home', component: () => import('@/views/HomeView.vue') },
  { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue') },
  {
    path: '/app',
    name: 'app',
    component: () => import('@/views/AppPlaceholderView.vue'),
    meta: { requiresAuth: true },
  },
]

export function createAppRouter(
  history: RouterHistory = createWebHistory(import.meta.env.BASE_URL),
) {
  const router = createRouter({ history, routes })

  router.beforeEach((to) => {
    const auth = useAuthStore()
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } }
    }
    return true
  })

  return router
}

export default createAppRouter()
