import { ref } from 'vue'
import { ApiError, type FieldErrors } from './api'

export function useFormErrors() {
  const errors = ref<FieldErrors>({})

  /** Isi errors dari ApiError 422 bila ada details; kembalikan true bila terpetakan. */
  function setFromError(error: unknown): boolean {
    if (error instanceof ApiError && error.status === 422 && error.details) {
      errors.value = error.details
      return true
    }
    return false
  }

  function first(field: string): string | undefined {
    return errors.value[field]?.[0]
  }

  function clear(): void {
    errors.value = {}
  }

  return { errors, setFromError, first, clear }
}
