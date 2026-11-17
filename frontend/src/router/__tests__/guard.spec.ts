import { beforeEach, describe, expect, it } from 'vitest'
import { createMemoryHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import { createAppRouter } from '../index'
import { useAuthStore } from '@/stores/auth'

describe('route guard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('mengarahkan ke login bila belum login', async () => {
    const router = createAppRouter(createMemoryHistory())

    await router.push('/app')

    expect(router.currentRoute.value.name).toBe('login')
    expect(router.currentRoute.value.query.redirect).toBe('/app')
  })

  it('mengizinkan route terproteksi bila sudah login', async () => {
    useAuthStore().setUser({ id: 1, name: 'Uji', email: 'uji@example.com' })
    const router = createAppRouter(createMemoryHistory())

    await router.push('/app')

    expect(router.currentRoute.value.name).toBe('app')
  })
})
