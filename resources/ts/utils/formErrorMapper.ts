/**
 * Form Error Mapper Utility
 * Maps Laravel validation errors to frontend form fields
 */

export interface ValidationErrors {
  [field: string]: string[]
}

export interface BackendErrorResponse {
  errors?: ValidationErrors
  message?: string
  _data?: {
    errors?: ValidationErrors
    message?: string
  }
}

/**
 * Converts snake_case to camelCase
 */
function snakeToCamel(str: string): string {
  return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase())
}

/**
 * Maps Laravel validation errors to camelCase field names
 * Combines multiple error messages into a single string
 * 
 * @param errorResponse - The error response from the API
 * @returns Object with camelCase field names and combined error messages
 */
export function mapBackendErrors(errorResponse: any): Record<string, string> {
  const mappedErrors: Record<string, string> = {}

  // Extract errors from various possible response structures
  let errors: ValidationErrors | undefined

  if (errorResponse?.errors) {
    errors = errorResponse.errors
  } else if (errorResponse?._data?.errors) {
    errors = errorResponse._data.errors
  } else if (errorResponse?.response?._data?.errors) {
    errors = errorResponse.response._data.errors
  } else if (errorResponse?.response?.data?.errors) {
    errors = errorResponse.response.data.errors
  }

  if (!errors || typeof errors !== 'object') {
    return mappedErrors
  }

  // Map each error field to camelCase and combine messages
  Object.entries(errors).forEach(([field, messages]) => {
    const camelField = snakeToCamel(field)
    
    if (Array.isArray(messages)) {
      // Combine multiple messages with a space
      mappedErrors[camelField] = messages.join(' ')
    } else if (typeof messages === 'string') {
      mappedErrors[camelField] = messages
    }
  })

  return mappedErrors
}

/**
 * Extracts a general error message from the backend response
 */
export function extractErrorMessage(errorResponse: any): string {
  if (typeof errorResponse === 'string') {
    return errorResponse
  }

  // Try various message locations
  if (errorResponse?.message) {
    return errorResponse.message
  }
  if (errorResponse?._data?.message) {
    return errorResponse._data.message
  }
  if (errorResponse?.response?._data?.message) {
    return errorResponse.response._data.message
  }
  if (errorResponse?.response?.data?.message) {
    return errorResponse.response.data.message
  }

  return 'An error occurred'
}

/**
 * Checks if an error response contains validation errors
 */
export function hasValidationErrors(errorResponse: any): boolean {
  const errors = errorResponse?.errors || 
                 errorResponse?._data?.errors || 
                 errorResponse?.response?._data?.errors ||
                 errorResponse?.response?.data?.errors

  return errors && typeof errors === 'object' && Object.keys(errors).length > 0
}

/**
 * Gets error message for a specific field
 */
export function getFieldError(mappedErrors: Record<string, string>, fieldName: string): string | undefined {
  return mappedErrors[fieldName]
}

/**
 * Creates a reactive error object for use with Vue
 */
export function createReactiveErrors(errorResponse: any) {
  return reactive(mapBackendErrors(errorResponse))
}

/**
 * Clears a specific field error from the errors object
 */
export function clearFieldError(errors: Record<string, string>, fieldName: string): void {
  delete errors[fieldName]
}

/**
 * Clears all errors from the errors object
 */
export function clearAllErrors(errors: Record<string, string>): void {
  Object.keys(errors).forEach(key => {
    delete errors[key]
  })
}

