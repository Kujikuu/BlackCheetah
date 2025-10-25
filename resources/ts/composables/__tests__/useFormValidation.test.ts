import { describe, it, expect, beforeEach } from 'vitest'
import { useFormValidation } from '../useFormValidation'

describe('useFormValidation', () => {
  let formValidation: ReturnType<typeof useFormValidation>

  beforeEach(() => {
    formValidation = useFormValidation()
  })

  describe('setBackendErrors', () => {
    it('should set backend errors from API response', () => {
      const apiError = {
        errors: {
          first_name: ['First name is required'],
          email: ['Email is invalid'],
        },
      }

      formValidation.setBackendErrors(apiError as any)

      // Check that errors are reactive
      expect(formValidation.backendErrors).toBeDefined()
      expect(formValidation.backendErrors.firstName).toBe('First name is required')
      expect(formValidation.backendErrors.email).toBe('Email is invalid')
    })

    it('should handle empty errors', () => {
      const apiError = {
        errors: {},
      }

      formValidation.setBackendErrors(apiError as any)

      expect(Object.keys(formValidation.backendErrors).length).toBe(0)
    })
  })

  describe('clearError', () => {
    it('should clear specific error', () => {
      const apiError = {
        errors: {
          first_name: ['First name is required'],
          email: ['Email is invalid'],
        },
      }
      formValidation.setBackendErrors(apiError as any)

      formValidation.clearError('firstName')

      expect(formValidation.backendErrors.firstName).toBeUndefined()
      expect(formValidation.backendErrors.email).toBe('Email is invalid')
    })
  })

  describe('clearErrors', () => {
    it('should clear all errors', () => {
      const apiError = {
        errors: {
          first_name: ['First name is required'],
          last_name: ['Last name is required'],
          email: ['Email is invalid'],
        },
      }
      formValidation.setBackendErrors(apiError as any)

      formValidation.clearErrors()

      expect(Object.keys(formValidation.backendErrors).length).toBe(0)
    })
  })

  describe('integration with error mapper', () => {
    it('should properly integrate with form error mapper', () => {
      const apiError = {
        errors: {
          user_profile: ['Profile incomplete'],
          payment_method: ['Payment required'],
        },
      }

      formValidation.setBackendErrors(apiError as any)

      expect(formValidation.backendErrors.userProfile).toBe('Profile incomplete')
      expect(formValidation.backendErrors.paymentMethod).toBe('Payment required')

      formValidation.clearError('userProfile')

      expect(formValidation.backendErrors.userProfile).toBeUndefined()
      expect(formValidation.backendErrors.paymentMethod).toBe('Payment required')
    })
  })
})

