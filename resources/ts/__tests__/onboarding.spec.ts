import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, jest } from '@jest/globals'
import { nextTick } from 'vue'

// Mock the onboarding page
const OnboardingPage = {
  template: `
    <div class="onboarding-page">
      <form @submit.prevent="completeProfile">
        <input 
          v-model="form.name" 
          data-testid="name-input" 
          placeholder="Full Name"
          required
        />
        <input 
          v-model="form.phone" 
          data-testid="phone-input" 
          placeholder="Phone Number"
          required
        />
        <input 
          v-model="form.country" 
          data-testid="country-input" 
          placeholder="Country"
          required
        />
        <input 
          v-model="form.state" 
          data-testid="state-input" 
          placeholder="State"
          required
        />
        <input 
          v-model="form.city" 
          data-testid="city-input" 
          placeholder="City"
          required
        />
        <textarea 
          v-model="form.address" 
          data-testid="address-input" 
          placeholder="Address"
          required
        />
        <button 
          type="submit" 
          data-testid="submit-button"
          :disabled="loading || !isFormValid"
        >
          {{ loading ? 'Loading...' : 'Complete Profile' }}
        </button>
      </form>
      <div v-if="errorMessages?.general" data-testid="error-message">
        {{ errorMessages.general[0] }}
      </div>
    </div>
  `,
  setup() {
    const form = ref({
      name: '',
      phone: '',
      country: '',
      state: '',
      city: '',
      address: '',
    })

    const loading = ref(false)
    const errorMessages = ref<{ [key: string]: string[] } | null>(null)

    const isFormValid = computed(() => {
      return form.value.name && 
             form.value.phone && 
             form.value.country && 
             form.value.state && 
             form.value.city && 
             form.value.address
    })

    const completeProfile = async () => {
      loading.value = true
      errorMessages.value = null
      
      try {
        await $api('/v1/onboarding/complete', {
          method: 'POST',
          body: form.value,
        })
        // Success - would redirect in real app
      } catch (e: any) {
        const data = e?.data || e?.response?._data || null
        if (data?.errors) {
          errorMessages.value = data.errors
        } else {
          errorMessages.value = { 
            general: [data?.message || 'Failed to complete profile. Please try again.'] 
          }
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      errorMessages,
      isFormValid,
      completeProfile,
    }
  },
}

// Mock the global composables and functions
const mockApi = jest.fn()
;(global as any).$api = mockApi

const mockRef = jest.fn((value: any) => ({ value }))
const mockComputed = jest.fn((fn: () => any) => ({ value: fn() }))
;(global as any).ref = mockRef
;(global as any).computed = mockComputed

describe('Onboarding Component', () => {
  let wrapper: any

  beforeEach(() => {
    jest.clearAllMocks()
    mockApi.mockClear()
  })

  it('renders the onboarding form correctly', () => {
    wrapper = mount(OnboardingPage)

    expect(wrapper.find('[data-testid="name-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="phone-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="country-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="state-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="city-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="address-input"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="submit-button"]').exists()).toBe(true)
  })

  it('disables submit button when form is invalid', () => {
    wrapper = mount(OnboardingPage)

    const submitButton = wrapper.find('[data-testid="submit-button"]')
    expect(submitButton.attributes('disabled')).toBeDefined()
  })

  it('enables submit button when all fields are filled', async () => {
    wrapper = mount(OnboardingPage)

    // Fill all form fields
    await wrapper.find('[data-testid="name-input"]').setValue('John Doe')
    await wrapper.find('[data-testid="phone-input"]').setValue('+1234567890')
    await wrapper.find('[data-testid="country-input"]').setValue('USA')
    await wrapper.find('[data-testid="state-input"]').setValue('California')
    await wrapper.find('[data-testid="city-input"]').setValue('Los Angeles')
    await wrapper.find('[data-testid="address-input"]').setValue('123 Main St')

    await nextTick()

    const submitButton = wrapper.find('[data-testid="submit-button"]')
    expect(submitButton.attributes('disabled')).toBeUndefined()
  })

  it('calls API when form is submitted with valid data', async () => {
    mockApi.mockResolvedValue({ success: true } as any)
    wrapper = mount(OnboardingPage)

    // Fill form
    await wrapper.find('[data-testid="name-input"]').setValue('John Doe')
    await wrapper.find('[data-testid="phone-input"]').setValue('+1234567890')
    await wrapper.find('[data-testid="country-input"]').setValue('USA')
    await wrapper.find('[data-testid="state-input"]').setValue('California')
    await wrapper.find('[data-testid="city-input"]').setValue('Los Angeles')
    await wrapper.find('[data-testid="address-input"]').setValue('123 Main St')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')

    expect(mockApi).toHaveBeenCalledWith('/v1/onboarding/complete', {
      method: 'POST',
      body: {
        name: 'John Doe',
        phone: '+1234567890',
        country: 'USA',
        state: 'California',
        city: 'Los Angeles',
        address: '123 Main St',
      },
    })
  })

  it('displays error message when API call fails', async () => {
    const errorResponse = {
      data: {
        message: 'Validation failed',
        errors: {
          name: ['The name field is required.'],
        },
      },
    }
    mockApi.mockRejectedValue(errorResponse as any)
    wrapper = mount(OnboardingPage)

    // Fill form with invalid data
    await wrapper.find('[data-testid="name-input"]').setValue('')
    await wrapper.find('[data-testid="phone-input"]').setValue('+1234567890')
    await wrapper.find('[data-testid="country-input"]').setValue('USA')
    await wrapper.find('[data-testid="state-input"]').setValue('California')
    await wrapper.find('[data-testid="city-input"]').setValue('Los Angeles')
    await wrapper.find('[data-testid="address-input"]').setValue('123 Main St')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(wrapper.find('[data-testid="error-message"]').exists()).toBe(true)
  })

  it('shows loading state during form submission', async () => {
    let resolvePromise: (value: any) => void
    const pendingPromise = new Promise((resolve) => {
      resolvePromise = resolve
    })
    mockApi.mockReturnValue(pendingPromise)

    wrapper = mount(OnboardingPage)

    // Fill form
    await wrapper.find('[data-testid="name-input"]').setValue('John Doe')
    await wrapper.find('[data-testid="phone-input"]').setValue('+1234567890')
    await wrapper.find('[data-testid="country-input"]').setValue('USA')
    await wrapper.find('[data-testid="state-input"]').setValue('California')
    await wrapper.find('[data-testid="city-input"]').setValue('Los Angeles')
    await wrapper.find('[data-testid="address-input"]').setValue('123 Main St')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    const submitButton = wrapper.find('[data-testid="submit-button"]')
    expect(submitButton.text()).toBe('Loading...')
    expect(submitButton.attributes('disabled')).toBeDefined()

    // Resolve the promise
    resolvePromise!({ success: true })
    await nextTick()

    expect(submitButton.text()).toBe('Complete Profile')
  })

  it('validates required fields', async () => {
    wrapper = mount(OnboardingPage)

    const nameInput = wrapper.find('[data-testid="name-input"]')
    const phoneInput = wrapper.find('[data-testid="phone-input"]')
    const countryInput = wrapper.find('[data-testid="country-input"]')
    const stateInput = wrapper.find('[data-testid="state-input"]')
    const cityInput = wrapper.find('[data-testid="city-input"]')
    const addressInput = wrapper.find('[data-testid="address-input"]')

    expect(nameInput.attributes('required')).toBeDefined()
    expect(phoneInput.attributes('required')).toBeDefined()
    expect(countryInput.attributes('required')).toBeDefined()
    expect(stateInput.attributes('required')).toBeDefined()
    expect(cityInput.attributes('required')).toBeDefined()
    expect(addressInput.attributes('required')).toBeDefined()
  })
})