import type { Router } from 'vue-router'
import { configureApi } from '@/lib/api'
import { useToast } from '@/components/ui/useToast'
import { useAuthStore } from '@/stores/auth'
import { useOutletStore } from '@/stores/outlet'

// Pesan baku 401, SDD §4.3 (OI-10).
export const SESSION_EXPIRED_MESSAGE = 'Sesi tidak valid atau sudah berakhir. Silakan login.'

export function setupApi(router: Router): void {
  configureApi({
    getOutletId: () => useOutletStore().outletId,
    onUnauthenticated: () => {
      useAuthStore().clear()
      if (router.currentRoute.value.name === 'login') return

      useToast().error(SESSION_EXPIRED_MESSAGE)
      void router.push({
        name: 'login',
        query: { redirect: router.currentRoute.value.fullPath },
      })
    },
  })
}
