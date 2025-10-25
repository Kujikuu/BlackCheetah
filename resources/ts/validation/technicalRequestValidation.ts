/**
 * Technical Request Validation Rules
 * Maps StoreTechnicalRequestRequest and UpdateTechnicalRequestRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Technical request category options
export const TECHNICAL_REQUEST_CATEGORIES = [
  'hardware',
  'software',
  'network',
  'pos_system',
  'website',
  'mobile_app',
  'training',
  'other',
] as const

// Technical request priority options
export const TECHNICAL_REQUEST_PRIORITIES = [
  'low',
  'medium',
  'high',
  'urgent',
] as const

// Technical request status options
export const TECHNICAL_REQUEST_STATUSES = [
  'open',
  'in_progress',
  'pending_info',
  'resolved',
  'closed',
  'cancelled',
] as const

/**
 * Validation rules for creating a technical request (StoreTechnicalRequestRequest)
 */
export function useStoreTechnicalRequestValidation() {
  return {
    title: [
      rules.required('Request title is required'),
      rules.string(),
      rules.maxLength(255, 'Title cannot exceed 255 characters'),
    ],
    description: [
      rules.required('Request description is required'),
      rules.string(),
    ],
    category: [
      rules.required('Category is required'),
      rules.inArray(TECHNICAL_REQUEST_CATEGORIES as unknown as string[], 'Invalid category selected'),
    ],
    priority: [
      rules.required('Priority level is required'),
      rules.inArray(TECHNICAL_REQUEST_PRIORITIES as unknown as string[], 'Invalid priority level selected'),
    ],
    status: [
      rules.inArray(TECHNICAL_REQUEST_STATUSES as unknown as string[], 'Invalid status selected'),
    ],
    attachments: [
      // File validation handled separately in form
    ],
  }
}

/**
 * Validation rules for updating a technical request (UpdateTechnicalRequestRequest)
 */
export function useUpdateTechnicalRequestValidation() {
  return {
    title: [
      rules.string(),
      rules.maxLength(255, 'Title cannot exceed 255 characters'),
    ],
    description: [
      rules.string(),
    ],
    category: [
      rules.inArray(TECHNICAL_REQUEST_CATEGORIES as unknown as string[], 'Invalid category selected'),
    ],
    priority: [
      rules.inArray(TECHNICAL_REQUEST_PRIORITIES as unknown as string[], 'Invalid priority level selected'),
    ],
    status: [
      rules.inArray(TECHNICAL_REQUEST_STATUSES as unknown as string[], 'Invalid status selected'),
    ],
  }
}

