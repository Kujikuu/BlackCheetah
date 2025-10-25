/**
 * Leads Validation Rules
 * Maps StoreLeadRequest and UpdateLeadRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Lead source options
export const LEAD_SOURCES = [
  'website',
  'referral',
  'social_media',
  'advertisement',
  'cold_call',
  'event',
  'other',
] as const

// Lead status options
export const LEAD_STATUSES = [
  'new',
  'contacted',
  'qualified',
  'proposal_sent',
  'negotiating',
  'closed_won',
  'closed_lost',
] as const

// Lead priority options
export const LEAD_PRIORITIES = [
  'low',
  'medium',
  'high',
  'urgent',
] as const

/**
 * Validation rules for creating a new lead (StoreLeadRequest)
 */
export function useStoreLeadValidation() {
  return {
    firstName: [
      rules.required('First name is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    lastName: [
      rules.required('Last name is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    email: [
      rules.required('Email address is required'),
      rules.email('Please provide a valid email address'),
      rules.maxLength(255),
    ],
    phone: [
      rules.required('Phone number is required'),
      rules.string(),
      rules.maxLength(20),
    ],
    company: [
      rules.string(),
      rules.maxLength(255),
    ],
    jobTitle: [
      rules.string(),
      rules.maxLength(100),
    ],
    address: [
      rules.string(),
    ],
    city: [
      rules.required('City is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    nationality: [
      rules.required('Nationality is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    source: [
      rules.required('Lead source is required'),
      rules.inArray(LEAD_SOURCES as unknown as string[], 'Invalid lead source selected'),
    ],
    status: [
      rules.required('Lead status is required'),
      rules.inArray(LEAD_STATUSES as unknown as string[], 'Invalid lead status selected'),
    ],
    priority: [
      rules.required('Lead priority is required'),
      rules.inArray(LEAD_PRIORITIES as unknown as string[], 'Invalid priority level selected'),
    ],
    estimatedInvestment: [
      rules.numeric(),
      rules.min(0),
    ],
    franchiseFeeQuoted: [
      rules.numeric(),
      rules.min(0),
    ],
    notes: [
      rules.string(),
    ],
    expectedDecisionDate: [
      rules.date(),
    ],
    lastContactDate: [
      rules.date(),
    ],
    nextFollowUpDate: [
      rules.date(),
    ],
    contactAttempts: [
      rules.integer(),
      rules.min(0),
    ],
  }
}

/**
 * Validation rules for updating a lead (UpdateLeadRequest)
 * Same as store but with 'sometimes' behavior handled in components
 */
export function useUpdateLeadValidation() {
  return useStoreLeadValidation()
}

/**
 * Validation rules for assigning a lead (AssignLeadRequest)
 */
export function useAssignLeadValidation() {
  return {
    assignedTo: [
      rules.required('Please select a user to assign the lead to'),
    ],
  }
}

/**
 * Validation rules for marking lead as lost (MarkLeadAsLostRequest)
 */
export function useMarkLeadAsLostValidation() {
  return {
    lostReason: [
      rules.required('Please provide a reason for marking the lead as lost'),
      rules.string(),
      rules.maxLength(500, 'Reason cannot exceed 500 characters'),
    ],
    notes: [
      rules.string(),
    ],
  }
}

/**
 * Validation rules for adding a lead note (AddLeadNoteRequest)
 */
export function useAddLeadNoteValidation() {
  return {
    note: [
      rules.required('Note content is required'),
      rules.string(),
      rules.minLength(1),
    ],
  }
}

