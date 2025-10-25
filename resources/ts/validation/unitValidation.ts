/**
 * Unit Validation Rules
 * Maps StoreUnitRequest and UpdateUnitRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Unit type options
export const UNIT_TYPES = [
  'store',
  'kiosk',
  'mobile',
  'online',
  'warehouse',
  'office',
] as const

// Unit status options
export const UNIT_STATUSES = [
  'planning',
  'construction',
  'training',
  'active',
  'temporarily_closed',
  'permanently_closed',
] as const

/**
 * Validation rules for creating a unit (StoreUnitRequest)
 */
export function useStoreUnitValidation() {
  return {
    unitName: [
      rules.required('Unit name is required'),
      rules.string('Unit name must be a string'),
      rules.maxLength(255, 'Unit name cannot exceed 255 characters'),
    ],
    unitCode: [
      rules.string('Unit code must be a string'),
      rules.maxLength(50, 'Unit code cannot exceed 50 characters'),
    ],
    unitType: [
      rules.inArray(UNIT_TYPES as unknown as string[], 'Unit type must be one of: store, kiosk, mobile, online, warehouse, office'),
    ],
    address: [
      rules.required('Address is required'),
      rules.string('Address must be a string'),
    ],
    city: [
      rules.required('City is required'),
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    stateProvince: [
      rules.required('State/Province is required'),
      rules.string('State/Province must be a string'),
      rules.maxLength(100, 'State/Province cannot exceed 100 characters'),
    ],
    postalCode: [
      rules.required('Postal code is required'),
      rules.string('Postal code must be a string'),
      rules.maxLength(20, 'Postal code cannot exceed 20 characters'),
    ],
    nationality: [
      rules.required('Nationality is required'),
      rules.string('Nationality must be a string'),
      rules.maxLength(100, 'Nationality cannot exceed 100 characters'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    email: [
      rules.email('Email must be a valid email address'),
      rules.maxLength(255, 'Email cannot exceed 255 characters'),
    ],
    sizeSqft: [
      rules.numeric('Size must be a number'),
      rules.min(0, 'Size must be at least 0'),
    ],
    initialInvestment: [
      rules.numeric('Initial investment must be a number'),
      rules.min(0, 'Initial investment must be at least 0'),
    ],
    leaseStartDate: [
      rules.date('Lease start date must be a valid date'),
    ],
    leaseEndDate: [
      rules.date('Lease end date must be a valid date'),
    ],
    openingDate: [
      rules.date('Opening date must be a valid date'),
    ],
    status: [
      rules.required('Status is required'),
      rules.inArray(UNIT_STATUSES as unknown as string[], 'Status must be one of: planning, construction, training, active, temporarily_closed, permanently_closed'),
    ],
    employeeCount: [
      rules.integer('Employee count must be an integer'),
      rules.min(0, 'Employee count must be at least 0'),
    ],
    monthlyRevenue: [
      rules.numeric('Monthly revenue must be a number'),
      rules.min(0, 'Monthly revenue must be at least 0'),
    ],
    monthlyExpenses: [
      rules.numeric('Monthly expenses must be a number'),
      rules.min(0, 'Monthly expenses must be at least 0'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
  }
}

/**
 * Validation rules for updating a unit (UpdateUnitRequest)
 */
export function useUpdateUnitValidation() {
  return {
    unitName: [
      rules.string('Unit name must be a string'),
      rules.maxLength(255, 'Unit name cannot exceed 255 characters'),
    ],
    unitType: [
      rules.inArray(UNIT_TYPES as unknown as string[], 'Unit type must be one of: store, kiosk, mobile, online, warehouse, office'),
    ],
    address: [
      rules.string('Address must be a string'),
    ],
    city: [
      rules.string('City must be a string'),
      rules.maxLength(100, 'City cannot exceed 100 characters'),
    ],
    stateProvince: [
      rules.string('State/Province must be a string'),
      rules.maxLength(100, 'State/Province cannot exceed 100 characters'),
    ],
    postalCode: [
      rules.string('Postal code must be a string'),
      rules.maxLength(20, 'Postal code cannot exceed 20 characters'),
    ],
    nationality: [
      rules.string('Nationality must be a string'),
      rules.maxLength(100, 'Nationality cannot exceed 100 characters'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    email: [
      rules.email('Email must be a valid email address'),
      rules.maxLength(255, 'Email cannot exceed 255 characters'),
    ],
    sizeSqft: [
      rules.numeric('Size must be a number'),
      rules.min(0, 'Size must be at least 0'),
    ],
    initialInvestment: [
      rules.numeric('Initial investment must be a number'),
      rules.min(0, 'Initial investment must be at least 0'),
    ],
    leaseStartDate: [
      rules.date('Lease start date must be a valid date'),
    ],
    leaseEndDate: [
      rules.date('Lease end date must be a valid date'),
    ],
    openingDate: [
      rules.date('Opening date must be a valid date'),
    ],
    status: [
      rules.inArray(UNIT_STATUSES as unknown as string[], 'Status must be one of: planning, construction, training, active, temporarily_closed, permanently_closed'),
    ],
    employeeCount: [
      rules.integer('Employee count must be an integer'),
      rules.min(0, 'Employee count must be at least 0'),
    ],
    monthlyRevenue: [
      rules.numeric('Monthly revenue must be a number'),
      rules.min(0, 'Monthly revenue must be at least 0'),
    ],
    monthlyExpenses: [
      rules.numeric('Monthly expenses must be a number'),
      rules.min(0, 'Monthly expenses must be at least 0'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
  }
}

