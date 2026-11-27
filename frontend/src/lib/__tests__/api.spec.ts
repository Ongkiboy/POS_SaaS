import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { ApiError, api, configureApi } from '../api'

function jsonResponse(status: number, body: unknown): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json' },
  })
}

const okBody = { success: true, data: { ok: true }, message: 'Berhasil' }

function clearXsrf(): void {
  document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
}

function csrfResponder() {
  return async () => {
    document.cookie = 'XSRF-TOKEN=abc%3D; path=/'
    return new Response(null, { status: 204 })
  }
}

describe('api client', () => {
  const fetchMock = vi.fn<typeof fetch>()
  const onUnauthenticated = vi.fn<() => void>()

  /** Header dari panggilan fetch ke-`call` (hindari optional chaining di dalam tanda kurung: Oxlint no-unsafe-optional-chaining). */
  function sentHeaders(call: number): Record<string, string> {
    const init = fetchMock.mock.calls[call]?.[1]
    return (init?.headers ?? {}) as Record<string, string>
  }

  beforeEach(() => {
    fetchMock.mockReset()
    onUnauthenticated.mockReset()
    vi.stubGlobal('fetch', fetchMock)
    clearXsrf()
    configureApi({ getOutletId: () => 7, onUnauthenticated })
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('mengambil /sanctum/csrf-cookie sebelum POST dan mengirim X-XSRF-TOKEN + X-Outlet-Id', async () => {
    fetchMock.mockImplementationOnce(csrfResponder())
    fetchMock.mockResolvedValueOnce(jsonResponse(200, okBody))

    await api('/orders', { method: 'POST', body: { a: 1 } })

    expect(fetchMock.mock.calls[0]?.[0]).toBe('/sanctum/csrf-cookie')
    expect(fetchMock.mock.calls[1]?.[0]).toBe('/api/v1/orders')
    const headers = sentHeaders(1)
    expect(headers['X-XSRF-TOKEN']).toBe('abc=')
    expect(headers['X-Outlet-Id']).toBe('7')
    expect(headers['Content-Type']).toBe('application/json')
  })

  it('tidak mengambil CSRF untuk GET, tetap menyertakan X-Outlet-Id', async () => {
    fetchMock.mockResolvedValueOnce(jsonResponse(200, okBody))

    await api('/ping')

    expect(fetchMock).toHaveBeenCalledTimes(1)
    const headers = sentHeaders(0)
    expect(headers['X-Outlet-Id']).toBe('7')
  })

  it('tidak mengirim X-Outlet-Id bila outlet belum dipilih', async () => {
    configureApi({ getOutletId: () => null })
    fetchMock.mockResolvedValueOnce(jsonResponse(200, okBody))

    await api('/ping')

    const headers = sentHeaders(0)
    expect(headers['X-Outlet-Id']).toBeUndefined()
  })

  it('401: memanggil handler dan melempar ApiError UNAUTHENTICATED', async () => {
    fetchMock.mockResolvedValueOnce(
      jsonResponse(401, { success: false, error: { code: 'UNAUTHENTICATED', message: 'x' } }),
    )

    await expect(api('/me')).rejects.toMatchObject({ status: 401, code: 'UNAUTHENTICATED' })
    expect(onUnauthenticated).toHaveBeenCalledTimes(1)
  })

  it('422: melempar ApiError dengan details per field', async () => {
    fetchMock.mockImplementationOnce(csrfResponder())
    fetchMock.mockResolvedValueOnce(
      jsonResponse(422, {
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Input tidak valid',
          details: { email: ['Wajib diisi.'] },
        },
      }),
    )

    const error = await api('/auth/login', { method: 'POST', body: {} }).catch((e: unknown) => e)

    expect(error).toBeInstanceOf(ApiError)
    expect((error as ApiError).details).toEqual({ email: ['Wajib diisi.'] })
    expect(onUnauthenticated).not.toHaveBeenCalled()
  })

  it('419: ambil ulang CSRF lalu coba lagi satu kali', async () => {
    fetchMock.mockImplementationOnce(csrfResponder()) // csrf awal
    fetchMock.mockResolvedValueOnce(jsonResponse(419, { message: 'CSRF token mismatch.' }))
    fetchMock.mockImplementationOnce(csrfResponder()) // csrf ulang
    fetchMock.mockResolvedValueOnce(jsonResponse(200, okBody))

    await api('/orders', { method: 'POST', body: {} })

    expect(fetchMock).toHaveBeenCalledTimes(4)
  })

  it('419 dua kali: berhenti dan melempar ApiError (tidak loop)', async () => {
    fetchMock.mockImplementationOnce(csrfResponder())
    fetchMock.mockResolvedValueOnce(jsonResponse(419, { message: 'x' }))
    fetchMock.mockImplementationOnce(csrfResponder())
    fetchMock.mockResolvedValueOnce(jsonResponse(419, { message: 'x' }))

    await expect(api('/orders', { method: 'POST', body: {} })).rejects.toMatchObject({
      status: 419,
    })
    expect(fetchMock).toHaveBeenCalledTimes(4)
  })

  it('204: mengembalikan objek sukses tanpa data (logout)', async () => {
    fetchMock.mockImplementationOnce(csrfResponder())
    fetchMock.mockResolvedValueOnce(new Response(null, { status: 204 }))

    const response = await api('/auth/logout', { method: 'POST' })

    expect(response.success).toBe(true)
    expect(response.data).toBeUndefined()
  })

  it('mengirim Idempotency-Key dan menyusun query string tanpa nilai undefined', async () => {
    fetchMock.mockResolvedValueOnce(jsonResponse(200, okBody))

    await api('/orders', {
      query: { page: 2, search: undefined, active: true },
      idempotencyKey: 'key-1',
    })

    expect(fetchMock.mock.calls[0]?.[0]).toBe('/api/v1/orders?page=2&active=true')
    const headers = sentHeaders(0)
    expect(headers['Idempotency-Key']).toBe('key-1')
  })

  it('kegagalan jaringan menjadi ApiError NETWORK_ERROR', async () => {
    fetchMock.mockRejectedValueOnce(new TypeError('Failed to fetch'))

    await expect(api('/ping')).rejects.toMatchObject({ status: 0, code: 'NETWORK_ERROR' })
  })

  it('respons error non-envelope menjadi ApiError UNKNOWN_ERROR', async () => {
    fetchMock.mockResolvedValueOnce(new Response('<html>Bad gateway</html>', { status: 502 }))

    await expect(api('/ping')).rejects.toMatchObject({ status: 502, code: 'UNKNOWN_ERROR' })
  })
})
