import 'vue-router'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    /** Dipakai guard permission di FE-AUTH-003. */
    permission?: string
  }
}
