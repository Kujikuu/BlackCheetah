import { describe, it, expect } from 'vitest'
import { mapBackendErrors, clearFieldError, clearAllErrors } from '../formErrorMapper'

describe('formErrorMapper', () => {
  describe('mapBackendErrors', () => {
    it('should map snake_case to camelCase', () => {
      const apiError = {
        errors: {
          first_name: ['First name is required'],
          last_name: ['Last name is required'],
          email_address: ['Email is invalid'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.firstName).toBe('First name is required')
      expect(mappedErrors.lastName).toBe('Last name is required')
      expect(mappedErrors.emailAddress).toBe('Email is invalid')
    })

    it('should join multiple error messages', () => {
      const apiError = {
        errors: {
          email: ['Email is required', 'Email must be valid'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.email).toBe('Email is required Email must be valid')
    })

    it('should handle nested snake_case keys', () => {
      const apiError = {
        errors: {
          user_profile_data: ['Invalid profile data'],
          payment_method_type: ['Payment method required'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.userProfileData).toBe('Invalid profile data')
      expect(mappedErrors.paymentMethodType).toBe('Payment method required')
    })

    it('should handle empty errors object', () => {
      const apiError = {
        errors: {},
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(Object.keys(mappedErrors).length).toBe(0)
    })

    it('should handle undefined errors', () => {
      const apiError = {}

      const mappedErrors = mapBackendErrors(apiError)

      expect(Object.keys(mappedErrors).length).toBe(0)
    })
  })

  describe('clearFieldError', () => {
    it('should clear specific error', () => {
      const errors = {
        firstName: 'First name is required',
        email: 'Email is invalid',
      }

      clearFieldError(errors, 'firstName')

      expect(errors.firstName).toBeUndefined()
      expect(errors.email).toBe('Email is invalid')
    })

    it('should handle clearing non-existent error', () => {
      const errors = {
        email: 'Email is invalid',
      }

      clearFieldError(errors, 'nonExistent')

      expect(errors.email).toBe('Email is invalid')
    })
  })

  describe('clearAllErrors', () => {
    it('should clear all errors', () => {
      const errors = {
        firstName: 'First name is required',
        lastName: 'Last name is required',
        email: 'Email is invalid',
      }

      clearAllErrors(errors)

      expect(errors.firstName).toBeUndefined()
      expect(errors.lastName).toBeUndefined()
      expect(errors.email).toBeUndefined()
      expect(Object.keys(errors).length).toBe(0)
    })
  })

  describe('snake_case to camelCase conversion', () => {
    it('should convert simple snake_case', () => {
      const apiError = {
        errors: {
          user_name: ['Username required'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.userName).toBe('Username required')
    })

    it('should convert multiple underscores', () => {
      const apiError = {
        errors: {
          user_profile_image_url: ['Invalid URL'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.userProfileImageUrl).toBe('Invalid URL')
    })

    it('should handle already camelCase keys', () => {
      const apiError = {
        errors: {
          firstName: ['First name required'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.firstName).toBe('First name required')
    })

    it('should handle single word keys', () => {
      const apiError = {
        errors: {
          email: ['Email required'],
          phone: ['Phone invalid'],
        },
      }

      const mappedErrors = mapBackendErrors(apiError)

      expect(mappedErrors.email).toBe('Email required')
      expect(mappedErrors.phone).toBe('Phone invalid')
    })
  })
})

