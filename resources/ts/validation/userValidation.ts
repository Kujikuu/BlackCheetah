/**
 * User Validation Rules
 * Maps CreateUserRequest, UpdateUserRequest, StoreBrokerRequest, UpdateBrokerRequest, CompleteProfileRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// User role options
export const USER_ROLES = [
  'franchisor',
  'franchisee',
  'broker',
] as const

// User status options
export const USER_STATUSES = [
  'active',
  'pending',
  'inactive',
] as const

// Plan options (for franchisors)
export const FRANCHISOR_PLANS = [
  'Basic',
  'Pro',
  'Enterprise',
] as const

/**
 * Validation rules for creating a user (CreateUserRequest)
 */
export function useCreateUserValidation() {
  return {
    fullName: [
      rules.required('Full name is required'),
      rules.string('Full name must be a string'),
      rules.maxLength(255, 'Full name cannot exceed 255 characters'),
    ],
    email: [
      rules.required('Email is required'),
      rules.email('Email must be a valid email address'),
    ],
    role: [
      rules.required('Role is required'),
      rules.inArray(USER_ROLES as unknown as string[], 'Role must be one of: franchisor, franchisee, broker'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    status: [
      rules.required('Status is required'),
      rules.inArray(USER_STATUSES as unknown as string[], 'Status must be one of: active, pending, inactive'),
    ],
    franchiseName: [
      rules.string('Franchise name must be a string'),
      rules.maxLength(255, 'Franchise name cannot exceed 255 characters'),
    ],
    plan: [
      rules.inArray(FRANCHISOR_PLANS as unknown as string[], 'Plan must be one of: Basic, Pro, Enterprise'),
    ],
  }
}

/**
 * Validation rules for updating a user (UpdateUserRequest)
 */
export function useUpdateUserValidation() {
  return {
    fullName: [
      rules.required('Full name is required'),
      rules.string('Full name must be a string'),
      rules.maxLength(255, 'Full name cannot exceed 255 characters'),
    ],
    email: [
      rules.required('Email is required'),
      rules.email('Email must be a valid email address'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    status: [
      rules.required('Status is required'),
      rules.inArray(USER_STATUSES as unknown as string[], 'Status must be one of: active, pending, inactive'),
    ],
    franchiseName: [
      rules.string('Franchise name must be a string'),
      rules.maxLength(255, 'Franchise name cannot exceed 255 characters'),
    ],
    plan: [
      rules.inArray(FRANCHISOR_PLANS as unknown as string[], 'Plan must be one of: Basic, Pro, Enterprise'),
    ],
  }
}

/**
 * Validation rules for creating a broker (StoreBrokerRequest)
 */
export function useStoreBrokerValidation() {
  return {
    name: [
      rules.required('Name is required'),
      rules.string('Name must be a string'),
      rules.maxLength(255, 'Name cannot exceed 255 characters'),
    ],
    email: [
      rules.required('Email is required'),
      rules.email('Email must be a valid email address'),
    ],
    phone: [
      rules.required('Phone is required'),
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    status: [
      rules.required('Status is required'),
      rules.inArray(['active', 'inactive'], 'Status must be either active or inactive'),
    ],
    nationality: [
      rules.string('Nationality must be a string'),
      rules.maxLength(100, 'Nationality cannot exceed 100 characters'),
    ],
    state: [
      rules.string('State must be a string'),
      rules.maxLength(100, 'State cannot exceed 100 characters'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    password: [
      rules.required('Password is required'),
      rules.string('Password must be a string'),
      rules.minLength(8, 'Password must be at least 8 characters'),
    ],
  }
}

/**
 * Validation rules for updating a broker (UpdateBrokerRequest)
 */
export function useUpdateBrokerValidation() {
  return {
    name: [
      rules.string('Name must be a string'),
      rules.maxLength(255, 'Name cannot exceed 255 characters'),
    ],
    email: [
      rules.email('Email must be a valid email address'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    status: [
      rules.inArray(['active', 'inactive'], 'Status must be either active or inactive'),
    ],
    nationality: [
      rules.string('Nationality must be a string'),
      rules.maxLength(100, 'Nationality cannot exceed 100 characters'),
    ],
    state: [
      rules.string('State must be a string'),
      rules.maxLength(100, 'State cannot exceed 100 characters'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
  }
}

/**
 * Validation rules for completing profile (CompleteProfileRequest)
 */
export function useCompleteProfileValidation() {
  return {
    fullName: [
      rules.required('Full name is required'),
      rules.string('Full name must be a string'),
      rules.maxLength(255, 'Full name cannot exceed 255 characters'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    nationality: [
      rules.string('Nationality must be a string'),
      rules.maxLength(100, 'Nationality cannot exceed 100 characters'),
    ],
    state: [
      rules.string('State must be a string'),
      rules.maxLength(100, 'State cannot exceed 100 characters'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    address: [
      rules.string(),
    ],
  }
}

