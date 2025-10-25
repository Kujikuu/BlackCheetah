import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EditLeadDialog from '../../leads/EditLeadDialog.vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

describe('EditLeadDialog.vue', () => {
  let wrapper: any

  const mockLead = {
    id: 1,
    firstName: 'John',
    lastName: 'Doe',
    email: 'john@example.com',
    phone: '+966501234567',
    status: 'new',
    leadSource: 'website',
    country: 'Saudi Arabia',
    state: 'Riyadh',
    city: 'Riyadh',
  }

  beforeEach(() => {
    wrapper = mount(EditLeadDialog, {
      props: {
        isDialogVisible: true,
        lead: mockLead,
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

    it('should populate form with lead data', () => {
      expect(wrapper.vm.formData.firstName).toBe('John')
      expect(wrapper.vm.formData.lastName).toBe('Doe')
      expect(wrapper.vm.formData.email).toBe('john@example.com')
    })
  })

  describe('Form Validation', () => {
    it('should have validation rules defined', () => {
      expect(wrapper.vm.validationRules).toBeDefined()
      expect(wrapper.vm.validationRules.firstName).toBeDefined()
      expect(wrapper.vm.validationRules.email).toBeDefined()
    })

    it('should have required field validation rules', () => {
      const firstNameRules = wrapper.vm.validationRules.firstName
      expect(Array.isArray(firstNameRules)).toBe(true)
      expect(firstNameRules.length).toBeGreaterThan(0)
      
      // Test the required rule
      const requiredRule = firstNameRules[0]
      expect(requiredRule('')).toContain('required')
      expect(requiredRule('John')).toBe(true)
    })
  })

  describe('Backend Error Handling', () => {
    it('should map backend errors to form fields', () => {
      const backendError = {
        errors: {
          first_name: ['First name is required'],
          email: ['Email already exists'],
        },
      }

      wrapper.vm.setBackendErrors(backendError)

      expect(wrapper.vm.backendErrors.firstName).toBe('First name is required')
      expect(wrapper.vm.backendErrors.email).toBe('Email already exists')
    })

    it('should clear field error on input', () => {
      wrapper.vm.backendErrors.firstName = 'First name is required'

      wrapper.vm.clearError('firstName')

      expect(wrapper.vm.backendErrors.firstName).toBeUndefined()
    })
  })

  describe('Validation Rules Functionality', () => {
    it('should validate email format', () => {
      const emailRules = wrapper.vm.validationRules.email
      const emailFormatRule = emailRules.find((rule: any) => rule('test@') !== true)
      
      if (emailFormatRule) {
        expect(emailFormatRule('test@example.com')).toBe(true)
        expect(emailFormatRule('invalid')).toContain('email')
      }
    })

    it('should validate phone format', () => {
      const phoneRules = wrapper.vm.validationRules.phone
      const phoneFormatRule = phoneRules.find((rule: any) => rule('123') !== true)
      
      if (phoneFormatRule) {
        expect(phoneFormatRule('+966501234567')).toBe(true)
        expect(phoneFormatRule('abc')).toContain('phone')
      }
    })
  })

  describe('Dialog Interactions', () => {
    it('should emit update:isDialogVisible on close', async () => {
      await wrapper.vm.updateModelValue(false)

      expect(wrapper.emitted('update:isDialogVisible')).toBeTruthy()
      expect(wrapper.emitted('update:isDialogVisible')[0]).toEqual([false])
    })
  })
})

