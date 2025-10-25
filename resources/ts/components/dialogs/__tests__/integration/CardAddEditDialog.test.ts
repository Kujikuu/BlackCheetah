import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import CardAddEditDialog from '../../CardAddEditDialog.vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

describe('CardAddEditDialog.vue - Credit Card Validation', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(CardAddEditDialog, {
      props: {
        isDialogVisible: true,
        cardDetails: {
          number: '',
          name: '',
          expiry: '',
          cvv: '',
          isPrimary: false,
          type: '',
        },
      },
      global: {
        plugins: [vuetify],
        stubs: {
          DialogCloseBtn: true,
          AppTextField: true,
        },
      },
    })
  })

  describe('Card Number Validation', () => {
    it('should accept valid card numbers', () => {
      const rule = wrapper.vm.cardNumberRule

      expect(rule('4532015112830366')).toBe(true) // 16 digits
      expect(rule('378282246310005')).toBe(true) // 15 digits (AmEx)
      expect(rule('6011111111111117')).toBe(true) // 16 digits
    })

    it('should reject invalid card numbers', () => {
      const rule = wrapper.vm.cardNumberRule

      expect(rule('')).toBe('Card number is required')
      expect(rule('123')).toBe('Card number must be 13-19 digits')
      expect(rule('12345')).toBe('Card number must be 13-19 digits')
      expect(rule('12345678901234567890')).toBe('Card number must be 13-19 digits')
    })

    it('should handle card numbers with spaces', () => {
      const rule = wrapper.vm.cardNumberRule

      expect(rule('4532 0151 1283 0366')).toBe(true)
      expect(rule('4532-0151-1283-0366')).toBe(true)
    })
  })

  describe('Expiry Date Validation', () => {
    it('should accept valid expiry dates', () => {
      const rule = wrapper.vm.expiryRule

      expect(rule('12/25')).toBe(true)
      expect(rule('01/30')).toBe(true)
      expect(rule('06/29')).toBe(true)
    })

    it('should reject invalid expiry dates', () => {
      const rule = wrapper.vm.expiryRule

      expect(rule('')).toBe('Expiry date is required')
      expect(rule('13/25')).toBe('Expiry must be in MM/YY format') // Invalid month
      expect(rule('12/2025')).toBe('Expiry must be in MM/YY format') // Wrong format
      expect(rule('1/25')).toBe('Expiry must be in MM/YY format') // Single digit month
      expect(rule('12-25')).toBe('Expiry must be in MM/YY format') // Wrong separator
    })
  })

  describe('CVV Validation', () => {
    it('should accept valid CVV codes', () => {
      const rule = wrapper.vm.cvvRule

      expect(rule('123')).toBe(true) // 3 digits
      expect(rule('1234')).toBe(true) // 4 digits (AmEx)
    })

    it('should reject invalid CVV codes', () => {
      const rule = wrapper.vm.cvvRule

      expect(rule('')).toBe('CVV is required')
      expect(rule('12')).toBe('CVV must be 3 or 4 digits')
      expect(rule('12345')).toBe('CVV must be 3 or 4 digits')
      expect(rule('abc')).toBe('CVV must be 3 or 4 digits')
    })
  })

  describe('Cardholder Name Validation', () => {
    it('should require cardholder name', () => {
      const nameRules = wrapper.vm.rules.required('Name is required')

      expect(nameRules('')).toBe('Name is required')
      expect(nameRules('John Doe')).toBe(true)
    })

    it('should enforce max length', () => {
      const maxLengthRule = wrapper.vm.rules.maxLength(100)

      expect(maxLengthRule('John Doe')).toBe(true)
      expect(maxLengthRule('a'.repeat(101))).toBe('Must be no more than 100 characters')
    })
  })

  describe('Form Submission', () => {
    it('should not submit with empty fields', async () => {
      const formRef = wrapper.vm.formRef
      if (formRef && formRef.validate) {
        const result = await formRef.validate()
        expect(result.valid).toBe(false)
      }
    })

    it('should submit with valid card details', async () => {
      wrapper.vm.cardDetails.number = '4532015112830366'
      wrapper.vm.cardDetails.name = 'John Doe'
      wrapper.vm.cardDetails.expiry = '12/25'
      wrapper.vm.cardDetails.cvv = '123'

      const formRef = wrapper.vm.formRef
      if (formRef && formRef.validate) {
        const result = await formRef.validate()
        expect(result.valid).toBe(true)
      }
    })
  })

  describe('Error Handling', () => {
    it('should display backend errors', () => {
      const backendError = {
        errors: {
          card_number: ['Card number is invalid'],
          expiry: ['Card has expired'],
        },
      }

      wrapper.vm.setBackendErrors(backendError)

      expect(wrapper.vm.backendErrors.cardNumber).toBe('Card number is invalid')
      expect(wrapper.vm.backendErrors.expiry).toBe('Card has expired')
    })

    it('should clear errors on input change', () => {
      wrapper.vm.backendErrors.cardNumber = 'Invalid card'

      wrapper.vm.clearError('cardNumber')

      expect(wrapper.vm.backendErrors.cardNumber).toBeUndefined()
    })
  })
})

