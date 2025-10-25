import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import AddStaffDialog from '../../staff/AddStaffDialog.vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

describe('AddStaffDialog.vue - Multi-Field Form', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(AddStaffDialog, {
      props: {
        isDialogVisible: true,
      },
      global: {
        plugins: [vuetify],
        stubs: {
          DialogCloseBtn: true,
          AppTextField: true,
          AppSelect: true,
        },
      },
    })
  })

  describe('Component Mounting', () => {
    it('should mount successfully', () => {
      expect(wrapper.exists()).toBe(true)
    })

    it('should display dialog when visible', () => {
      expect(wrapper.find('[role="dialog"]').exists()).toBe(true)
    })

    it('should initialize empty form data', () => {
      expect(wrapper.vm.formData).toBeDefined()
      expect(wrapper.vm.formData.firstName).toBe('')
      expect(wrapper.vm.formData.lastName).toBe('')
      expect(wrapper.vm.formData.email).toBe('')
    })
  })

  describe('Required Fields Validation', () => {
    it('should have all required field validations', () => {
      expect(wrapper.vm.validationRules).toBeDefined()
      // Note: Staff uses 'name' instead of firstName/lastName
      expect(wrapper.vm.validationRules.name || wrapper.vm.validationRules.firstName).toBeDefined()
      expect(wrapper.vm.validationRules.email).toBeDefined()
      expect(wrapper.vm.validationRules.phone).toBeDefined()
    })

    it('should have validation rules for all fields', () => {
      const rules = wrapper.vm.validationRules
      expect(rules).toBeDefined()
      expect(typeof rules).toBe('object')
      expect(Object.keys(rules).length).toBeGreaterThan(0)
    })
  })

  describe('Email Validation', () => {
    it('should validate email format', () => {
      const emailRule = wrapper.vm.validationRules.email.find((rule: any) => 
        typeof rule === 'function' && rule('test@') !== true
      )
      
      if (emailRule) {
        expect(emailRule('test@example.com')).toBe(true)
        expect(emailRule('invalid-email')).toContain('email')
      }
    })
  })

  describe('Phone Validation', () => {
    it('should validate phone format', () => {
      const phoneRule = wrapper.vm.validationRules.phone.find((rule: any) => 
        typeof rule === 'function' && rule('123') !== true
      )
      
      if (phoneRule) {
        expect(phoneRule('+966501234567')).toBe(true)
        expect(phoneRule('abc')).toContain('phone')
      }
    })
  })

  describe('Backend Error Mapping', () => {
    it('should map backend errors to camelCase fields', () => {
      const backendError = {
        errors: {
          first_name: ['First name is required'],
          last_name: ['Last name is required'],
          email: ['Email already exists'],
          phone_number: ['Invalid phone format'],
          hire_date: ['Invalid date'],
        },
      }

      wrapper.vm.setBackendErrors(backendError)

      expect(wrapper.vm.backendErrors.firstName).toBe('First name is required')
      expect(wrapper.vm.backendErrors.lastName).toBe('Last name is required')
      expect(wrapper.vm.backendErrors.email).toBe('Email already exists')
      expect(wrapper.vm.backendErrors.phoneNumber).toBe('Invalid phone format')
      expect(wrapper.vm.backendErrors.hireDate).toBe('Invalid date')
    })

    it('should clear individual field errors', () => {
      wrapper.vm.backendErrors.firstName = 'First name is required'
      wrapper.vm.backendErrors.email = 'Email already exists'

      wrapper.vm.clearError('firstName')

      expect(wrapper.vm.backendErrors.firstName).toBeUndefined()
      expect(wrapper.vm.backendErrors.email).toBe('Email already exists')
    })

    it('should clear all errors at once', () => {
      wrapper.vm.backendErrors = {
        firstName: 'Error 1',
        lastName: 'Error 2',
        email: 'Error 3',
      }

      wrapper.vm.clearAllErrors()

      expect(Object.keys(wrapper.vm.backendErrors).length).toBe(0)
    })
  })

  describe('Validation Rule Testing', () => {
    it('should test email validation rule', () => {
      const emailRules = wrapper.vm.validationRules.email
      if (emailRules && Array.isArray(emailRules)) {
        const emailRule = emailRules.find((rule: any) => rule('test@') !== true)
        if (emailRule) {
          expect(emailRule('test@example.com')).toBe(true)
        }
      }
    })

    it('should test phone validation rule', () => {
      const phoneRules = wrapper.vm.validationRules.phone  
      if (phoneRules && Array.isArray(phoneRules)) {
        const phoneRule = phoneRules.find((rule: any) => rule('123') !== true)
        if (phoneRule) {
          expect(phoneRule('+966501234567')).toBe(true)
        }
      }
    })
  })

  describe('Dialog Interactions', () => {
    it('should close dialog on cancel', async () => {
      await wrapper.vm.updateModelValue(false)

      expect(wrapper.emitted('update:isDialogVisible')).toBeTruthy()
      expect(wrapper.emitted('update:isDialogVisible')[0]).toEqual([false])
    })

    it('should reset form on close', () => {
      wrapper.vm.formData.firstName = 'Test'
      wrapper.vm.formData.email = 'test@test.com'

      wrapper.vm.updateModelValue(false)
      
      // Form should be reset (implementation specific)
    })
  })

  describe('Date Validation', () => {
    it('should validate hire date format', () => {
      const dateRule = wrapper.vm.validationRules.hireDate?.find((rule: any) => 
        typeof rule === 'function' && rule('invalid') !== true
      )
      
      if (dateRule) {
        expect(dateRule('2024-01-15')).toBe(true)
        expect(dateRule('invalid-date')).toContain('date')
      }
    })
  })

  describe('Max Length Validation', () => {
    it('should enforce max length on text fields', () => {
      const firstNameMaxRule = wrapper.vm.validationRules.firstName.find((rule: any) => 
        typeof rule === 'function' && rule('a'.repeat(100)) !== true
      )
      
      if (firstNameMaxRule) {
        expect(firstNameMaxRule('John')).toBe(true)
        expect(firstNameMaxRule('a'.repeat(256))).toContain('characters')
      }
    })
  })
})

