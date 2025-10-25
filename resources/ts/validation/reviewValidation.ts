/**
 * Review Validation Rules
 * Maps StoreReviewRequest and UpdateReviewRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Review sentiment options
export const REVIEW_SENTIMENTS = [
  'positive',
  'neutral',
  'negative',
] as const

// Review status options
export const REVIEW_STATUSES = [
  'published',
  'draft',
  'archived',
] as const

/**
 * Validation rules for creating a review (StoreReviewRequest)
 */
export function useStoreReviewValidation() {
  return {
    customerName: [
      rules.required('Customer name is required'),
      rules.string('Customer name must be a string'),
      rules.maxLength(255, 'Customer name cannot exceed 255 characters'),
    ],
    rating: [
      rules.required('Rating is required'),
      rules.integer('Rating must be an integer'),
      rules.min(1, 'Rating must be at least 1'),
      rules.max(5, 'Rating must be at most 5'),
    ],
    comment: [
      rules.required('Comment is required'),
      rules.string('Comment must be a string'),
      rules.maxLength(1000, 'Comment cannot exceed 1000 characters'),
    ],
    date: [
      rules.date('Date must be a valid date'),
    ],
    customerEmail: [
      rules.email('Customer email must be a valid email address'),
      rules.maxLength(255, 'Customer email cannot exceed 255 characters'),
    ],
    sentiment: [
      rules.inArray(REVIEW_SENTIMENTS as unknown as string[], 'Sentiment must be one of: positive, neutral, negative'),
    ],
    status: [
      rules.inArray(REVIEW_STATUSES as unknown as string[], 'Status must be one of: published, draft, archived'),
    ],
  }
}

/**
 * Validation rules for updating a review (UpdateReviewRequest)
 */
export function useUpdateReviewValidation() {
  return useStoreReviewValidation()
}

