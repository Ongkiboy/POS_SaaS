import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export interface AuthUser {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const isAuthenticated = computed(() => user.value !== null)

  function setUser(next: AuthUser | null): void {
    user.value = next
  }

  function clear(): void {
    user.value = null
  }

  return { user, isAuthenticated, setUser, clear }
})
