import { describe, expect, it } from 'vitest'
import { ApiError } from '../api'
import { useFormErrors } from '../useFormErrors'

describe('useFormErrors', () => {
  it('memetakan details 422 ke field', () => {
    const form = useFormErrors()
    const handled = form.setFromError(
      new ApiError(422, 'VALIDATION_ERROR', 'Input tidak valid', {
        email: ['Email sudah digunakan.'],
      }),
    )

    expect(handled).toBe(true)
    expect(form.first('email')).toBe('Email sudah digunakan.')
    expect(form.first('password')).toBeUndefined()
  })

  it('mengabaikan error selain 422 dan clear() mengosongkan', () => {
    const form = useFormErrors()

    expect(form.setFromError(new ApiError(500, 'INTERNAL_ERROR', 'x'))).toBe(false)
    expect(form.setFromError(new Error('x'))).toBe(false)

    form.setFromError(new ApiError(422, 'VALIDATION_ERROR', 'x', { a: ['b'] }))
    form.clear()
    expect(form.first('a')).toBeUndefined()
  })
})
