/**
 * Centralized Validation Rules Composable
 * Mirrors Laravel validation rules for consistent frontend/backend validation
 */

export interface ValidationRule {
  (value: any): boolean | string
}

export function useValidationRules() {
  /**
   * Required field validation
   */
  const required = (message = 'This field is required'): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined) return message
      if (typeof value === 'string' && value.trim() === '') return message
      if (Array.isArray(value) && value.length === 0) return message
      return true
    }
  }

  /**
   * Email validation with optional max length
   */
  const email = (message = 'Please provide a valid email address'): ValidationRule => {
    return (value: any) => {
      if (!value) return true // Allow empty (use required separately)
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(value) || message
    }
  }

  /**
   * Phone number validation (supports Saudi format: +966 5X XXX XXXX)
   */
  const phone = (message = 'Please provide a valid phone number'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      // Saudi format or general international format
      const phoneRegex = /^(\+?966|0)?5\d{8}$|^\+?\d{10,15}$/
      return phoneRegex.test(value.replace(/[\s-]/g, '')) || message
    }
  }

  /**
   * Minimum length validation
   */
  const minLength = (min: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const length = typeof value === 'string' ? value.length : String(value).length
      return length >= min || message || `Minimum length is ${min} characters`
    }
  }

  /**
   * Maximum length validation
   */
  const maxLength = (max: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const length = typeof value === 'string' ? value.length : String(value).length
      return length <= max || message || `Maximum length is ${max} characters`
    }
  }

  /**
   * String validation
   */
  const string = (message = 'This field must be a string'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      return typeof value === 'string' || message
    }
  }

  /**
   * Numeric validation
   */
  const numeric = (message = 'This field must be a number'): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined || value === '') return true
      return !isNaN(Number(value)) || message
    }
  }

  /**
   * Minimum value validation
   */
  const min = (minValue: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined || value === '') return true
      const num = Number(value)
      return num >= minValue || message || `Minimum value is ${minValue}`
    }
  }

  /**
   * Maximum value validation
   */
  const max = (maxValue: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined || value === '') return true
      const num = Number(value)
      return num <= maxValue || message || `Maximum value is ${maxValue}`
    }
  }

  /**
   * Between validation (inclusive)
   */
  const between = (minValue: number, maxValue: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined || value === '') return true
      const num = Number(value)
      return (num >= minValue && num <= maxValue) || message || `Value must be between ${minValue} and ${maxValue}`
    }
  }

  /**
   * Integer validation
   */
  const integer = (message = 'This field must be an integer'): ValidationRule => {
    return (value: any) => {
      if (value === null || value === undefined || value === '') return true
      return Number.isInteger(Number(value)) || message
    }
  }

  /**
   * Date validation
   */
  const date = (message = 'Please provide a valid date'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const parsedDate = new Date(value)
      return !isNaN(parsedDate.getTime()) || message
    }
  }

  /**
   * After date validation
   */
  const after = (compareDate: string | Date, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      const compareDateTime = new Date(compareDate)
      return valueDate > compareDateTime || message || `Date must be after ${compareDate}`
    }
  }

  /**
   * Before date validation
   */
  const before = (compareDate: string | Date, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      const compareDateTime = new Date(compareDate)
      return valueDate < compareDateTime || message || `Date must be before ${compareDate}`
    }
  }

  /**
   * After or equal date validation
   */
  const afterOrEqual = (compareDate: string | Date, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      const compareDateTime = new Date(compareDate)
      return valueDate >= compareDateTime || message || `Date must be on or after ${compareDate}`
    }
  }

  /**
   * Before or equal date validation
   */
  const beforeOrEqual = (compareDate: string | Date, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      const compareDateTime = new Date(compareDate)
      return valueDate <= compareDateTime || message || `Date must be on or before ${compareDate}`
    }
  }

  /**
   * After today validation
   */
  const afterToday = (message = 'Date must be in the future'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return valueDate > today || message
    }
  }

  /**
   * After or equal to today validation
   */
  const afterOrEqualToday = (message = 'Date must be today or in the future'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const valueDate = new Date(value)
      valueDate.setHours(0, 0, 0, 0)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return valueDate >= today || message
    }
  }

  /**
   * URL validation
   */
  const url = (message = 'Please provide a valid URL'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      try {
        new URL(value)
        return true
      } catch {
        return message
      }
    }
  }

  /**
   * Array validation
   */
  const array = (message = 'This field must be an array'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      return Array.isArray(value) || message
    }
  }

  /**
   * In/Enum validation (value must be one of the specified options)
   */
  const inArray = (options: any[], message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      return options.includes(value) || message || `Value must be one of: ${options.join(', ')}`
    }
  }

  /**
   * Regex pattern validation
   */
  const regex = (pattern: RegExp, message = 'Invalid format'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      return pattern.test(value) || message
    }
  }

  /**
   * Date format validation (HH:MM for time fields)
   */
  const timeFormat = (message = 'Time must be in HH:MM format'): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/
      return timeRegex.test(value) || message
    }
  }

  /**
   * Conditional required validation (required if another field has a specific value)
   */
  const requiredIf = (condition: () => boolean, message = 'This field is required'): ValidationRule => {
    return (value: any) => {
      if (!condition()) return true
      return required(message)(value)
    }
  }

  /**
   * File validation (for file uploads)
   */
  const file = (message = 'Please select a file'): ValidationRule => {
    return (value: any) => {
      if (!value) return message
      return (value instanceof File || (value && value.name)) || message
    }
  }

  /**
   * File type validation
   */
  const fileType = (allowedTypes: string[], message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      if (!(value instanceof File)) return true
      
      const fileExtension = value.name.split('.').pop()?.toLowerCase()
      const mimeType = value.type.toLowerCase()
      
      const isValid = allowedTypes.some(type => {
        // Check by extension or mime type
        return fileExtension === type.toLowerCase() || mimeType.includes(type.toLowerCase())
      })
      
      return isValid || message || `Allowed file types: ${allowedTypes.join(', ')}`
    }
  }

  /**
   * File size validation (in MB)
   */
  const fileSize = (maxSizeMB: number, message?: string): ValidationRule => {
    return (value: any) => {
      if (!value) return true
      if (!(value instanceof File)) return true
      
      const fileSizeMB = value.size / (1024 * 1024)
      return fileSizeMB <= maxSizeMB || message || `File size must not exceed ${maxSizeMB}MB`
    }
  }

  /**
   * Confirmed validation (e.g., password confirmation)
   */
  const confirmed = (confirmValue: any, message = 'Fields do not match'): ValidationRule => {
    return (value: any) => {
      return value === confirmValue || message
    }
  }

  /**
   * Unique validation helper (requires async check, used differently)
   * This is a placeholder - actual unique validation happens on backend
   */
  const unique = (message = 'This value already exists'): ValidationRule => {
    return () => {
      // Frontend can't truly validate uniqueness without backend call
      // This is here for consistency with Laravel rules
      return true
    }
  }

  return {
    required,
    email,
    phone,
    minLength,
    maxLength,
    string,
    numeric,
    min,
    max,
    between,
    integer,
    date,
    after,
    before,
    afterOrEqual,
    beforeOrEqual,
    afterToday,
    afterOrEqualToday,
    url,
    array,
    inArray,
    regex,
    timeFormat,
    requiredIf,
    file,
    fileType,
    fileSize,
    confirmed,
    unique,
  }
}

