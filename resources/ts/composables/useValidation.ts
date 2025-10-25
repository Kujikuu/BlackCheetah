import { ref, computed } from 'vue'
import {
  requiredValidator,
  emailValidator,
  passwordValidator,
  confirmedValidator,
  betweenValidator,
  integerValidator,
  regexValidator,
  alphaValidator,
  urlValidator,
  lengthValidator,
  alphaDashValidator,
  saudiPhoneValidator,
  internationalPhoneValidator,
  positiveNumberValidator,
  minValueValidator,
  maxValueValidator,
  decimalValidator,
  minLengthValidator,
  maxLengthValidator,
  alphanumericValidator,
  futureDateValidator,
  pastDateValidator,
  dateRangeValidator,
  saudiNationalIdValidator,
  priceValidator,
  percentageValidator,
  fileSizeValidator,
  fileTypeValidator,
  requiredTextValidator,
  requiredEmailValidator,
  requiredPhoneValidator,
  requiredPositiveNumberValidator
} from '@core/utils/validators'

export function useValidation() {
  // Common validation rule sets
  const emailRules = computed(() => [
    requiredValidator,
    emailValidator
  ])

  const requiredEmailRules = computed(() => [
    requiredEmailValidator
  ])

  const phoneRules = computed(() => [
    requiredValidator,
    saudiPhoneValidator
  ])

  const internationalPhoneRules = computed(() => [
    requiredValidator,
    internationalPhoneValidator
  ])

  const requiredTextRules = computed(() => [
    requiredValidator
  ])

  const requiredTextWithLengthRules = (minLength: number = 1, maxLength: number = 255) => [
    (value: unknown) => requiredTextValidator(value, minLength, maxLength)
  ]

  const passwordRules = computed(() => [
    requiredValidator,
    passwordValidator
  ])

  const confirmPasswordRules = (targetPassword: string) => [
    requiredValidator,
    (value: string) => confirmedValidator(value, targetPassword)
  ]

  const positiveNumberRules = computed(() => [
    requiredValidator,
    positiveNumberValidator
  ])

  const priceRules = computed(() => [
    requiredValidator,
    priceValidator
  ])

  const percentageRules = computed(() => [
    requiredValidator,
    percentageValidator
  ])

  const saudiNationalIdRules = computed(() => [
    requiredValidator,
    saudiNationalIdValidator
  ])

  const futureDateRules = computed(() => [
    requiredValidator,
    futureDateValidator
  ])

  const pastDateRules = computed(() => [
    requiredValidator,
    pastDateValidator
  ])

  const alphanumericRules = computed(() => [
    requiredValidator,
    alphanumericValidator
  ])

  // File validation rules
  const fileValidationRules = (maxSizeMB: number = 5, allowedTypes: string[] = ['image/*', 'application/pdf']) => [
    (file: File | null) => fileSizeValidator(file, maxSizeMB),
    (file: File | null) => fileTypeValidator(file, allowedTypes)
  ]

  // Numeric validation rules
  const minValueRules = (min: number) => [
    requiredValidator,
    (value: unknown) => minValueValidator(value, min)
  ]

  const maxValueRules = (max: number) => [
    requiredValidator,
    (value: unknown) => maxValueValidator(value, max)
  ]

  const betweenRules = (min: number, max: number) => [
    requiredValidator,
    (value: unknown) => betweenValidator(value, min, max)
  ]

  const decimalRules = (decimals: number = 2) => [
    requiredValidator,
    (value: unknown) => decimalValidator(value, decimals)
  ]

  // String validation rules
  const minLengthRules = (minLength: number) => [
    requiredValidator,
    (value: unknown) => minLengthValidator(value, minLength)
  ]

  const maxLengthRules = (maxLength: number) => [
    requiredValidator,
    (value: unknown) => maxLengthValidator(value, maxLength)
  ]

  const lengthRules = (length: number) => [
    requiredValidator,
    (value: unknown) => lengthValidator(value, length)
  ]

  // Date validation rules
  const dateRangeRules = (startDate: string, endDate: string) => [
    requiredValidator,
    (value: unknown) => dateRangeValidator(value, startDate, endDate)
  ]

  // Business validation rules
  const jobTitleRules = computed(() => [
    requiredValidator,
    (value: unknown) => minLengthValidator(value, 2),
    (value: unknown) => maxLengthValidator(value, 100),
    alphanumericValidator
  ])

  const addressRules = computed(() => [
    requiredValidator,
    (value: unknown) => minLengthValidator(value, 10),
    (value: unknown) => maxLengthValidator(value, 500)
  ])

  const noteRules = computed(() => [
    requiredValidator,
    (value: unknown) => minLengthValidator(value, 10),
    (value: unknown) => maxLengthValidator(value, 1000)
  ])

  const reviewTextRules = computed(() => [
    requiredValidator,
    (value: unknown) => minLengthValidator(value, 20),
    (value: unknown) => maxLengthValidator(value, 1000)
  ])

  const ratingRules = computed(() => [
    requiredValidator,
    (value: unknown) => betweenValidator(value, 1, 5)
  ])

  // Form validation helper methods
  const validateForm = async (formRef: any) => {
    if (!formRef.value) return false
    
    const { valid } = await formRef.value.validate()
    return valid
  }

  const resetForm = (formRef: any) => {
    if (formRef.value) {
      formRef.value.reset()
    }
  }

  const resetValidation = (formRef: any) => {
    if (formRef.value) {
      formRef.value.resetValidation()
    }
  }

  // Backend error handling
  const handleBackendErrors = (errors: Record<string, string[]>, formRef: any) => {
    if (formRef.value) {
      // Set backend errors on form
      Object.keys(errors).forEach(field => {
        if (formRef.value.setFieldError) {
          formRef.value.setFieldError(field, errors[field][0])
        }
      })
    }
  }

  // Validation state management
  const isFormValid = ref(false)
  const validationErrors = ref<Record<string, string>>({})

  const updateValidationState = (field: string, isValid: boolean, error?: string) => {
    if (isValid) {
      delete validationErrors.value[field]
    } else if (error) {
      validationErrors.value[field] = error
    }
    
    // Update overall form validity
    isFormValid.value = Object.keys(validationErrors.value).length === 0
  }

  const clearValidationErrors = () => {
    validationErrors.value = {}
    isFormValid.value = false
  }

  // Common validation patterns
  const createFieldRules = (fieldType: string, options: any = {}) => {
    switch (fieldType) {
      case 'email':
        return options.required ? requiredEmailRules.value : [emailValidator]
      
      case 'phone':
        return options.required ? phoneRules.value : [saudiPhoneValidator]
      
      case 'phone-international':
        return options.required ? internationalPhoneRules.value : [internationalPhoneValidator]
      
      case 'text':
        return options.required ? requiredTextWithLengthRules(options.minLength, options.maxLength) : []
      
      case 'number':
        return options.required ? positiveNumberRules.value : [positiveNumberValidator]
      
      case 'price':
        return options.required ? priceRules.value : [priceValidator]
      
      case 'percentage':
        return options.required ? percentageRules.value : [percentageValidator]
      
      case 'date-future':
        return options.required ? futureDateRules.value : [futureDateValidator]
      
      case 'date-past':
        return options.required ? pastDateRules.value : [pastDateValidator]
      
      case 'national-id':
        return options.required ? saudiNationalIdRules.value : [saudiNationalIdValidator]
      
      case 'password':
        return passwordRules.value
      
      case 'confirm-password':
        return confirmPasswordRules(options.targetPassword)
      
      case 'file':
        return fileValidationRules(options.maxSizeMB, options.allowedTypes)
      
      default:
        return options.required ? requiredTextRules.value : []
    }
  }

  return {
    // Rule sets
    emailRules,
    requiredEmailRules,
    phoneRules,
    internationalPhoneRules,
    requiredTextRules,
    requiredTextWithLengthRules,
    passwordRules,
    confirmPasswordRules,
    positiveNumberRules,
    priceRules,
    percentageRules,
    saudiNationalIdRules,
    futureDateRules,
    pastDateRules,
    alphanumericRules,
    fileValidationRules,
    minValueRules,
    maxValueRules,
    betweenRules,
    decimalRules,
    minLengthRules,
    maxLengthRules,
    lengthRules,
    dateRangeRules,
    jobTitleRules,
    addressRules,
    noteRules,
    reviewTextRules,
    ratingRules,
    
    // Helper methods
    validateForm,
    resetForm,
    resetValidation,
    handleBackendErrors,
    updateValidationState,
    clearValidationErrors,
    createFieldRules,
    
    // State
    isFormValid,
    validationErrors
  }
}