/**
 * Property Validation Rules
 * Maps StorePropertyRequest and UpdatePropertyRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Property type options
export const PROPERTY_TYPES = [
  'retail',
  'office',
  'kiosk',
  'food_court',
  'standalone',
] as const

// Property status options
export const PROPERTY_STATUSES = [
  'available',
  'under_negotiation',
  'leased',
  'unavailable',
] as const

/**
 * Validation rules for creating a property (StorePropertyRequest)
 */
export function useStorePropertyValidation() {
  return {
    title: [
      rules.required('Property title is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    description: [
      rules.required('Property description is required'),
      rules.string(),
      rules.maxLength(5000),
    ],
    propertyType: [
      rules.required('Property type is required'),
      rules.string(),
      rules.inArray(PROPERTY_TYPES as unknown as string[], 'Invalid property type selected'),
    ],
    sizeSqm: [
      rules.required('Property size is required'),
      rules.numeric(),
      rules.min(0),
    ],
    stateProvince: [
      rules.required('State/Province is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    city: [
      rules.required('City is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    address: [
      rules.required('Address is required'),
      rules.string(),
      rules.maxLength(500),
    ],
    postalCode: [
      rules.string(),
      rules.maxLength(20),
    ],
    latitude: [
      rules.numeric(),
      rules.between(-90, 90),
    ],
    longitude: [
      rules.numeric(),
      rules.between(-180, 180),
    ],
    monthlyRent: [
      rules.required('Monthly rent is required'),
      rules.numeric(),
      rules.min(0),
    ],
    depositAmount: [
      rules.numeric(),
      rules.min(0),
    ],
    leaseTermMonths: [
      rules.integer(),
      rules.min(1),
    ],
    availableFrom: [
      rules.date(),
    ],
    contactInfo: [
      rules.string(),
      rules.maxLength(1000),
    ],
    status: [
      rules.string(),
      rules.inArray(PROPERTY_STATUSES as unknown as string[], 'Invalid status selected'),
    ],
  }
}

/**
 * Validation rules for updating a property (UpdatePropertyRequest)
 * Same as store but with optional fields (sometimes behavior)
 */
export function useUpdatePropertyValidation() {
  return useStorePropertyValidation()
}

