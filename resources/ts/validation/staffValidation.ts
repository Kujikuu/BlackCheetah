/**
 * Staff Validation Rules
 * Maps StoreStaffRequest and UpdateStaffRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Staff status options (from StoreStaffRequest)
export const STAFF_STATUSES = [
  'Active',
  'On Leave',
  'Terminated',
  'Inactive',
] as const

// Staff status options (from UpdateStaffRequest - different format)
export const STAFF_STATUSES_UPDATE = [
  'working',
  'leave',
  'terminated',
  'inactive',
] as const

// Employment type options
export const EMPLOYMENT_TYPES = [
  'full_time',
  'part_time',
  'contract',
  'temporary',
] as const

/**
 * Validation rules for creating a new staff member (StoreStaffRequest)
 */
export function useStoreStaffValidation() {
  return {
    name: [
      rules.required('Staff name is required'),
      rules.string('Name must be a string'),
      rules.maxLength(255, 'Name cannot exceed 255 characters'),
    ],
    email: [
      rules.required('Email is required'),
      rules.email('Email must be a valid email address'),
      rules.maxLength(255, 'Email cannot exceed 255 characters'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    jobTitle: [
      rules.required('Job title is required'),
      rules.string('Job title must be a string'),
      rules.maxLength(100, 'Job title cannot exceed 100 characters'),
    ],
    department: [
      rules.string('Department must be a string'),
      rules.maxLength(100, 'Department cannot exceed 100 characters'),
    ],
    salary: [
      rules.numeric('Salary must be a number'),
      rules.min(0, 'Salary must be at least 0'),
    ],
    hireDate: [
      rules.required('Hire date is required'),
      rules.date('Hire date must be a valid date'),
    ],
    shiftStart: [
      rules.timeFormat('Shift start time must be in HH:MM format'),
    ],
    shiftEnd: [
      rules.timeFormat('Shift end time must be in HH:MM format'),
    ],
    status: [
      rules.inArray(STAFF_STATUSES as unknown as string[], 'Status must be one of: Active, On Leave, Terminated, Inactive'),
    ],
    employmentType: [
      rules.inArray(EMPLOYMENT_TYPES as unknown as string[], 'Employment type must be one of: full_time, part_time, contract, temporary'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
  }
}

/**
 * Validation rules for updating a staff member (UpdateStaffRequest)
 */
export function useUpdateStaffValidation() {
  return {
    name: [
      rules.string('Name must be a string'),
      rules.maxLength(255, 'Name cannot exceed 255 characters'),
    ],
    email: [
      rules.email('Email must be a valid email address'),
      rules.maxLength(255, 'Email cannot exceed 255 characters'),
    ],
    phone: [
      rules.string('Phone must be a string'),
      rules.maxLength(20, 'Phone cannot exceed 20 characters'),
    ],
    jobTitle: [
      rules.string('Job title must be a string'),
      rules.maxLength(100, 'Job title cannot exceed 100 characters'),
    ],
    department: [
      rules.string('Department must be a string'),
      rules.maxLength(100, 'Department cannot exceed 100 characters'),
    ],
    salary: [
      rules.numeric('Salary must be a number'),
      rules.min(0, 'Salary must be at least 0'),
    ],
    hireDate: [
      rules.date('Hire date must be a valid date'),
    ],
    shiftStart: [
      rules.timeFormat('Shift start time must be in HH:MM format'),
    ],
    shiftEnd: [
      rules.timeFormat('Shift end time must be in HH:MM format'),
    ],
    status: [
      rules.inArray(STAFF_STATUSES_UPDATE as unknown as string[], 'Status must be one of: working, leave, terminated, inactive'),
    ],
    employmentType: [
      rules.inArray(EMPLOYMENT_TYPES as unknown as string[], 'Employment type must be one of: full_time, part_time, contract, temporary'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
  }
}

/**
 * Validation rules for franchise staff (StoreFranchiseStaffRequest / UpdateFranchiseStaffRequest)
 * These typically follow the same rules as regular staff
 */
export function useStoreFranchiseStaffValidation() {
  return useStoreStaffValidation()
}

export function useUpdateFranchiseStaffValidation() {
  return useUpdateStaffValidation()
}

