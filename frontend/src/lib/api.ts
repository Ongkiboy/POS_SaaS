export const API_PREFIX = '/api/v1'

export type FieldErrors = Record<string, string[]>

export interface ApiSuccess<T> {
  success: true
  data: T
  message: string
  meta?: Record<string, unknown>
  links?: Record<string, string | null>
}

export class ApiError extends Error {
  readonly status: number
  readonly code: string
  readonly details: FieldErrors | null

  constructor(status: number, code: string, message: string, details: FieldErrors | null = null) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
    this.details = details
  }
}

type Method = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export interface ApiRequestOptions {
  method?: Method
  body?: unknown
  query?: Record<string, string | number | boolean | undefined>
  idempotencyKey?: string
  signal?: AbortSignal
}

interface ApiHooks {
  getOutletId: () => string | number | null
  onUnauthenticated: () => void
}

let hooks: ApiHooks = {
  getOutletId: () => null,
  onUnauthenticated: () => {},
}

/** Dipanggil sekali di main.ts supaya api.ts tidak mengimpor router/store (hindari impor melingkar). */
export function configureApi(next: Partial<ApiHooks>): void {
  hooks = { ...hooks, ...next }
}

function readCookie(name: string): string | null {
  const row = document.cookie.split('; ').find((item) => item.startsWith(name + '='))
  return row ? decodeURIComponent(row.slice(name.length + 1)) : null
}

/** Ambil cookie CSRF Sanctum bila belum ada (atau paksa ambil ulang). */
export async function ensureCsrfCookie(force = false): Promise<void> {
  if (!force && readCookie('XSRF-TOKEN')) return
  await fetch('/sanctum/csrf-cookie', {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })
}

function buildUrl(path: string, query?: ApiRequestOptions['query']): string {
  const url = API_PREFIX + path
  if (!query) return url
  const params = new URLSearchParams()
  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined) params.set(key, String(value))
  }
  const qs = params.toString()
  return qs ? `${url}?${qs}` : url
}

async function parseJson(response: Response): Promise<unknown> {
  const text = await response.text()
  if (!text) return null
  try {
    return JSON.parse(text)
  } catch {
    return null
  }
}

function toApiError(status: number, payload: unknown): ApiError {
  const error = (
    payload as { error?: { code?: string; message?: string; details?: FieldErrors } } | null
  )?.error
  if (error?.code) {
    return new ApiError(
      status,
      error.code,
      error.message ?? 'Terjadi kesalahan',
      error.details ?? null,
    )
  }
  // Respons non-envelope (mis. 419 bawaan Laravel, error proxy).
  return new ApiError(status, 'UNKNOWN_ERROR', 'Terjadi kesalahan pada permintaan')
}

async function send<T>(
  path: string,
  options: ApiRequestOptions,
  canRetryCsrf: boolean,
): Promise<ApiSuccess<T>> {
  const method = options.method ?? 'GET'

  if (method !== 'GET') await ensureCsrfCookie()

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  }
  if (options.body !== undefined) headers['Content-Type'] = 'application/json'

  const xsrf = readCookie('XSRF-TOKEN')
  if (xsrf) headers['X-XSRF-TOKEN'] = xsrf

  const outletId = hooks.getOutletId()
  if (outletId !== null) headers['X-Outlet-Id'] = String(outletId)

  if (options.idempotencyKey) headers['Idempotency-Key'] = options.idempotencyKey

  let response: Response
  try {
    response = await fetch(buildUrl(path, options.query), {
      method,
      headers,
      credentials: 'same-origin',
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
      signal: options.signal,
    })
  } catch (cause) {
    if (cause instanceof DOMException && cause.name === 'AbortError') throw cause
    throw new ApiError(
      0,
      'NETWORK_ERROR',
      'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.',
    )
  }

  // 419: token CSRF kedaluwarsa. Ambil ulang sekali lalu coba lagi.
  if (response.status === 419 && canRetryCsrf) {
    await ensureCsrfCookie(true)
    return send<T>(path, options, false)
  }

  const payload = await parseJson(response)

  if (response.ok) {
    // 204 (mis. logout): tidak ada body.
    if (response.status === 204) return { success: true, data: undefined as T, message: '' }
    return payload as ApiSuccess<T>
  }

  const error = toApiError(response.status, payload)
  if (response.status === 401) hooks.onUnauthenticated()
  throw error
}

export function api<T = unknown>(
  path: string,
  options: ApiRequestOptions = {},
): Promise<ApiSuccess<T>> {
  return send<T>(path, options, true)
}
