import { isEmpty, isEmptyArray, isNullOrUndefined } from './helpers'

// 👉 Required Validator
export const requiredValidator = (value: unknown) => {
  if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
    return 'This field is required'

  return !!String(value).trim().length || 'This field is required'
}

// 👉 Email Validator
export const emailValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const re = /^(?:[^<>()[\]\\.,;:\s@"]+(?:\.[^<>()[\]\\.,;:\s@"]+)*|".+")@(?:\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]|(?:[a-z\-\d]+\.)+[a-z]{2,})$/i

  if (Array.isArray(value))
    return value.every(val => re.test(String(val))) || 'The Email field must be a valid email'

  return re.test(String(value)) || 'The Email field must be a valid email'
}

// 👉 Password Validator
export const passwordValidator = (password: string) => {
  const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/

  const validPassword = regExp.test(password)

  return validPassword || 'Field must contain at least one uppercase, lowercase, special character and digit with min 8 chars'
}

// 👉 Confirm Password Validator
export const confirmedValidator = (value: string, target: string) =>

  value === target || 'The Confirm Password field confirmation does not match'

// 👉 Between Validator
export const betweenValidator = (value: unknown, min: number, max: number) => {
  const valueAsNumber = Number(value)

  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
}

// 👉 Integer Validator
export const integerValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  if (Array.isArray(value))
    return value.every(val => /^-?\d+$/.test(String(val))) || 'This field must be an integer'

  return /^-?\d+$/.test(String(value)) || 'This field must be an integer'
}

// 👉 Regex Validator
export const regexValidator = (value: unknown, regex: RegExp | string): string | boolean => {
  if (isEmpty(value))
    return true

  let regeX = regex
  if (typeof regeX === 'string')
    regeX = new RegExp(regeX)

  if (Array.isArray(value))
    return value.every(val => regexValidator(val, regeX))

  return regeX.test(String(value)) || 'The Regex field format is invalid'
}

// 👉 Alpha Validator
export const alphaValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  return /^[A-Z]*$/i.test(String(value)) || 'The Alpha field may only contain alphabetic characters'
}

// 👉 URL Validator
export const urlValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const re = /^https?:\/\/[^\s$.?#].\S*$/

  return re.test(String(value)) || 'URL is invalid'
}

// 👉 Length Validator
export const lengthValidator = (value: unknown, length: number) => {
  if (isEmpty(value))
    return true

  return String(value).length === length || `"The length of the Characters field must be ${length} characters."`
}

// 👉 Alpha-dash Validator
export const alphaDashValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const valueAsString = String(value)

  return /^[\w-]*$/.test(valueAsString) || 'All Character are not valid'
}

// ===== PHONE VALIDATORS =====

// 👉 Saudi Phone Validator (+966 format)
export const saudiPhoneValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const phoneRegex = /^(\+966|966|0)?[5-9][0-9]{8}$/
  return phoneRegex.test(String(value).replace(/\s/g, '')) || 'Please enter a valid Saudi phone number'
}

// 👉 International Phone Validator
export const internationalPhoneValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const phoneRegex = /^(\+)?[1-9]\d{1,14}$/
  return phoneRegex.test(String(value).replace(/\s/g, '')) || 'Please enter a valid international phone number'
}

// ===== NUMERIC VALIDATORS =====

// 👉 Positive Number Validator
export const positiveNumberValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const num = Number(value)
  return (!isNaN(num) && num > 0) || 'Please enter a positive number'
}

// 👉 Min Value Validator
export const minValueValidator = (value: unknown, min: number) => {
  if (isEmpty(value))
    return true

  const num = Number(value)
  return (!isNaN(num) && num >= min) || `Value must be at least ${min}`
}

// 👉 Max Value Validator
export const maxValueValidator = (value: unknown, max: number) => {
  if (isEmpty(value))
    return true

  const num = Number(value)
  return (!isNaN(num) && num <= max) || `Value must not exceed ${max}`
}

// 👉 Decimal Validator
export const decimalValidator = (value: unknown, decimals: number = 2) => {
  if (isEmpty(value))
    return true

  const regex = new RegExp(`^\\d+(\\.\\d{1,${decimals}})?$`)
  return regex.test(String(value)) || `Please enter a valid decimal number with up to ${decimals} decimal places`
}

// ===== STRING VALIDATORS =====

// 👉 Min Length Validator
export const minLengthValidator = (value: unknown, minLength: number) => {
  if (isEmpty(value))
    return true

  return String(value).length >= minLength || `Minimum length is ${minLength} characters`
}

// 👉 Max Length Validator
export const maxLengthValidator = (value: unknown, maxLength: number) => {
  if (isEmpty(value))
    return true

  return String(value).length <= maxLength || `Maximum length is ${maxLength} characters`
}

// 👉 Alphanumeric Validator
export const alphanumericValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  return /^[a-zA-Z0-9\s]*$/.test(String(value)) || 'Only letters, numbers, and spaces are allowed'
}

// ===== DATE VALIDATORS =====

// 👉 Future Date Validator
export const futureDateValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const date = new Date(String(value))
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  return date > today || 'Date must be in the future'
}

// 👉 Past Date Validator
export const pastDateValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const date = new Date(String(value))
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  return date < today || 'Date must be in the past'
}

// 👉 Date Range Validator
export const dateRangeValidator = (value: unknown, startDate: string, endDate: string) => {
  if (isEmpty(value))
    return true

  const date = new Date(String(value))
  const start = new Date(startDate)
  const end = new Date(endDate)

  return (date >= start && date <= end) || `Date must be between ${startDate} and ${endDate}`
}

// ===== BUSINESS VALIDATORS =====

// 👉 Saudi National ID Validator
export const saudiNationalIdValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const id = String(value).replace(/\s/g, '')
  if (!/^\d{10}$/.test(id))
    return 'National ID must be exactly 10 digits'

  // Saudi National ID checksum validation
  let sum = 0
  for (let i = 0; i < 9; i++) {
    const digit = parseInt(id[i])
    if (i % 2 === 0) {
      const doubled = digit * 2
      sum += doubled > 9 ? doubled - 9 : doubled
    } else {
      sum += digit
    }
  }

  const checkDigit = (10 - (sum % 10)) % 10
  return checkDigit === parseInt(id[9]) || 'Invalid Saudi National ID'
}

// 👉 Price Validator
export const priceValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const num = Number(value)
  return (!isNaN(num) && num >= 0 && num <= 999999.99) || 'Please enter a valid price (0-999999.99)'
}

// 👉 Percentage Validator
export const percentageValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const num = Number(value)
  return (!isNaN(num) && num >= 0 && num <= 100) || 'Percentage must be between 0 and 100'
}

// ===== FILE VALIDATORS =====

// 👉 File Size Validator (in MB)
export const fileSizeValidator = (file: File | null, maxSizeMB: number) => {
  if (!file)
    return true

  const maxSizeBytes = maxSizeMB * 1024 * 1024
  return file.size <= maxSizeBytes || `File size must not exceed ${maxSizeMB}MB`
}

// 👉 File Type Validator
export const fileTypeValidator = (file: File | null, allowedTypes: string[]) => {
  if (!file)
    return true

  const fileType = file.type
  const isValidType = allowedTypes.some(type => {
    if (type.includes('*')) {
      const baseType = type.split('/')[0]
      return fileType.startsWith(baseType + '/')
    }
    return fileType === type
  })

  return isValidType || `File type must be one of: ${allowedTypes.join(', ')}`
}

// ===== COMBINED VALIDATORS =====

// 👉 Required Text with Length Validator
export const requiredTextValidator = (value: unknown, minLength: number = 1, maxLength: number = 255) => {
  const required = requiredValidator(value)
  if (required !== true) return required

  const minLengthCheck = minLengthValidator(value, minLength)
  if (minLengthCheck !== true) return minLengthCheck

  const maxLengthCheck = maxLengthValidator(value, maxLength)
  if (maxLengthCheck !== true) return maxLengthCheck

  return true
}

// 👉 Required Email Validator
export const requiredEmailValidator = (value: unknown) => {
  const required = requiredValidator(value)
  if (required !== true) return required

  return emailValidator(value)
}

// 👉 Required Phone Validator
export const requiredPhoneValidator = (value: unknown, isSaudi: boolean = true) => {
  const required = requiredValidator(value)
  if (required !== true) return required

  return isSaudi ? saudiPhoneValidator(value) : internationalPhoneValidator(value)
}

// 👉 Required Positive Number Validator
export const requiredPositiveNumberValidator = (value: unknown) => {
  const required = requiredValidator(value)
  if (required !== true) return required

  return positiveNumberValidator(value)
}
