/**
 * Royalty Validation Rules
 * Maps StoreRoyaltyRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Royalty type options
export const ROYALTY_TYPES = [
  'royalty',
  'marketing_fee',
  'technology_fee',
  'other',
] as const

// Billing frequency options
export const BILLING_FREQUENCIES = [
  'monthly',
  'quarterly',
] as const

// Royalty status options
export const ROYALTY_STATUSES = [
  'pending',
  'paid',
  'overdue',
] as const

/**
 * Validation rules for creating a royalty (StoreRoyaltyRequest)
 */
export function useStoreRoyaltyValidation() {
  const currentYear = new Date().getFullYear()
  
  return {
    franchiseId: [
      rules.required('Franchise selection is required'),
      rules.integer(),
    ],
    franchiseeId: [
      rules.required('Franchisee selection is required'),
      rules.integer(),
    ],
    unitId: [
      rules.integer(),
    ],
    type: [
      rules.required(),
      rules.string(),
      rules.inArray(ROYALTY_TYPES as unknown as string[], 'Invalid royalty type'),
    ],
    billingFrequency: [
      rules.required('Billing frequency is required'),
      rules.string(),
      rules.inArray(BILLING_FREQUENCIES as unknown as string[], 'Billing frequency must be either monthly or quarterly'),
    ],
    periodYear: [
      rules.required('Period year is required'),
      rules.integer(),
      rules.min(2020, 'Period year must be at least 2020'),
      rules.max(currentYear + 1, 'Period year cannot be more than next year'),
    ],
    periodMonth: [
      rules.integer(),
      rules.min(1, 'Period month must be between 1 and 12'),
      rules.max(12, 'Period month must be between 1 and 12'),
    ],
    periodQuarter: [
      rules.integer(),
      rules.min(1, 'Period quarter must be between 1 and 4'),
      rules.max(4, 'Period quarter must be between 1 and 4'),
    ],
    baseRevenue: [
      rules.required('Base revenue is required'),
      rules.numeric('Base revenue must be a valid number'),
      rules.min(0, 'Base revenue cannot be negative'),
      rules.max(999999999.99, 'Base revenue is too large'),
    ],
    royaltyRate: [
      rules.required('Royalty rate is required'),
      rules.numeric('Royalty rate must be a valid number'),
      rules.min(0, 'Royalty rate cannot be negative'),
      rules.max(100, 'Royalty rate cannot exceed 100%'),
    ],
    royaltyAmount: [
      rules.numeric(),
      rules.min(0),
    ],
    marketingFeeRate: [
      rules.required('Marketing fee rate is required'),
      rules.numeric('Marketing fee rate must be a valid number'),
      rules.min(0, 'Marketing fee rate cannot be negative'),
      rules.max(100, 'Marketing fee rate cannot exceed 100%'),
    ],
    marketingFeeAmount: [
      rules.numeric(),
      rules.min(0),
    ],
    technologyFeeRate: [
      rules.numeric(),
      rules.min(0),
      rules.max(100),
    ],
    technologyFeeAmount: [
      rules.numeric(),
      rules.min(0),
    ],
    otherFees: [
      rules.numeric(),
      rules.min(0),
    ],
    adjustments: [
      rules.numeric(),
    ],
    totalAmount: [
      rules.numeric(),
      rules.min(0),
    ],
    dueDate: [
      rules.required('Due date is required'),
      rules.date('Due date must be a valid date'),
      rules.afterOrEqualToday('Due date cannot be in the past'),
    ],
    status: [
      rules.required('Status is required'),
      rules.string(),
      rules.inArray(ROYALTY_STATUSES as unknown as string[], 'Status must be pending, paid, or overdue'),
    ],
    description: [
      rules.string(),
      rules.maxLength(1000, 'Description cannot exceed 1000 characters'),
    ],
    notes: [
      rules.string(),
      rules.maxLength(2000, 'Notes cannot exceed 2000 characters'),
    ],
  }
}

