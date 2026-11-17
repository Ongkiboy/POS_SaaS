import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createMemoryHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import { createAppRouter } from '@/router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/useToast'
import { api } from '../api'
import { SESSION_EXPIRED_MESSAGE, setupApi } from '../setupApi'

describe('setupApi: 401 mengarahkan ke login', () => {
  const fetchMock = vi.fn<typeof fetch>()

  beforeEach(() => {
    setActivePinia(createPinia())
    fetchMock.mockReset()
    vi.stubGlobal('fetch', fetchMock)
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('menghapus sesi, menampilkan pesan baku, dan pindah ke login dengan redirect', async () => {
    useAuthStore().setUser({ id: 1, name: 'Uji', email: 'uji@example.com' })
    const router = createAppRouter(createMemoryHistory())
    setupApi(router)
    await router.push('/app')
    expect(router.currentRoute.value.name).toBe('app')

    fetchMock.mockResolvedValueOnce(
      new Response(
        JSON.stringify({ success: false, error: { code: 'UNAUTHENTICATED', message: 'x' } }),
        {
          status: 401,
          headers: { 'Content-Type': 'application/json' },
        },
      ),
    )

    await expect(api('/me')).rejects.toMatchObject({ status: 401 })

    await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'))
    expect(router.currentRoute.value.query.redirect).toBe('/app')
    expect(useAuthStore().isAuthenticated).toBe(false)
    expect(useToast().toasts.value.some((toast) => toast.message === SESSION_EXPIRED_MESSAGE)).toBe(
      true,
    )
  })
})
