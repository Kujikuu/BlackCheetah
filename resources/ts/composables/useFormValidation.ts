/**
 * Form Validation Composable
 * Manages form validation state and backend error mapping
 */

import { mapBackendErrors, clearAllErrors, clearFieldError, extractErrorMessage, hasValidationErrors } from '@/utils/formErrorMapper'

export interface FormValidationOptions {
  /**
   * Callback when form is valid and submitted
   */
  onSubmit?: () => Promise<void> | void
  
  /**
   * Callback when backend errors are received
   */
  onError?: (errors: Record<string, string>) => void
}

export function useFormValidation(options?: FormValidationOptions) {
  // Track if form is currently valid
  const isValid = ref(false)
  
  // Track if form is submitting
  const isSubmitting = ref(false)
  
  // Backend validation errors (field name => error message)
  const backendErrors = reactive<Record<string, string>>({})

  /**
   * Validates a Vuetify form
   * @param formRef - Reference to the VForm component
   * @returns Promise<boolean> - True if form is valid
   */
  const validate = async (formRef: any): Promise<boolean> => {
    if (!formRef) {
      console.warn('Form reference is null')
      return false
    }

    // Vuetify form validation
    const { valid } = await formRef.validate()
    isValid.value = valid
    
    return valid
  }

  /**
   * Resets form validation state
   * @param formRef - Reference to the VForm component
   */
  const reset = (formRef?: any) => {
    if (formRef) {
      formRef.reset()
      formRef.resetValidation()
    }
    isValid.value = false
    clearAllErrors(backendErrors)
  }

  /**
   * Resets only validation state (doesn't clear form values)
   * @param formRef - Reference to the VForm component
   */
  const resetValidation = (formRef?: any) => {
    if (formRef) {
      formRef.resetValidation()
    }
    clearAllErrors(backendErrors)
  }

  /**
   * Maps backend validation errors to form fields
   * @param error - The error response from API
   */
  const setBackendErrors = (error: any) => {
    // Clear existing errors
    clearAllErrors(backendErrors)
    
    // Map new errors
    const mapped = mapBackendErrors(error)
    Object.assign(backendErrors, mapped)
    
    // Call error callback if provided
    if (options?.onError) {
      options.onError(mapped)
    }
  }

  /**
   * Clears error for a specific field
   * @param fieldName - The field name to clear error for
   */
  const clearError = (fieldName: string) => {
    clearFieldError(backendErrors, fieldName)
  }

  /**
   * Clears all backend errors
   */
  const clearErrors = () => {
    clearAllErrors(backendErrors)
  }

  /**
   * Gets error message for a specific field
   * @param fieldName - The field name
   * @returns Error message or undefined
   */
  const getError = (fieldName: string): string | undefined => {
    return backendErrors[fieldName]
  }

  /**
   * Checks if a specific field has an error
   * @param fieldName - The field name
   * @returns True if field has error
   */
  const hasError = (fieldName: string): boolean => {
    return !!backendErrors[fieldName]
  }

  /**
   * Handles form submission with validation
   * @param formRef - Reference to the VForm component
   * @param submitFn - The async function to call on valid submission
   */
  const handleSubmit = async (formRef: any, submitFn: () => Promise<void>) => {
    // Clear previous backend errors
    clearErrors()
    
    // Validate form
    const valid = await validate(formRef)
    if (!valid) {
      return
    }

    try {
      isSubmitting.value = true
      await submitFn()
    } catch (error: any) {
      // Check if it's a validation error (422)
      if (hasValidationErrors(error)) {
        setBackendErrors(error)
      } else {
        // For non-validation errors, extract and potentially show message
        const message = extractErrorMessage(error)
        console.error('Form submission error:', message)
        
        // Re-throw to let component handle it if needed
        throw error
      }
    } finally {
      isSubmitting.value = false
    }
  }

  /**
   * Watch for field changes to clear backend errors
   * @param formData - The reactive form data object
   */
  const watchFieldChanges = (formData: Record<string, any>) => {
    Object.keys(formData).forEach(fieldName => {
      watch(() => formData[fieldName], () => {
        if (backendErrors[fieldName]) {
          clearError(fieldName)
        }
      })
    })
  }

  return {
    // State
    isValid: readonly(isValid),
    isSubmitting: readonly(isSubmitting),
    backendErrors: readonly(backendErrors),
    
    // Methods
    validate,
    reset,
    resetValidation,
    setBackendErrors,
    clearError,
    clearErrors,
    getError,
    hasError,
    handleSubmit,
    watchFieldChanges,
  }
}

