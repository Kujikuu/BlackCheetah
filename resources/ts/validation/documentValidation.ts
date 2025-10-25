/**
 * Document Validation Rules
 * Maps StoreDocumentRequest and UpdateDocumentRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Document type options
export const DOCUMENT_TYPES = [
  'fdd',
  'franchise_agreement',
  'financial_study',
  'franchise_kit',
  'contract',
  'agreement',
  'manual',
  'certificate',
  'report',
  'other',
] as const

/**
 * Validation rules for uploading a document (StoreDocumentRequest)
 */
export function useStoreDocumentValidation() {
  return {
    file: [
      rules.file('A document file is required'),
      rules.fileSize(10, 'The document file must not exceed 10MB'),
    ],
    name: [
      rules.required('Document name is required'),
      rules.string(),
      rules.maxLength(255, 'Document name must not exceed 255 characters'),
    ],
    description: [
      rules.string(),
      rules.maxLength(1000),
    ],
    type: [
      rules.required('Document type is required'),
      rules.string(),
      rules.inArray(DOCUMENT_TYPES as unknown as string[], 'Document type must be one of: fdd, franchise_agreement, financial_study, franchise_kit, contract, agreement, manual, certificate, report, or other'),
    ],
    expiryDate: [
      rules.date(),
      rules.afterToday('Expiry date must be in the future'),
    ],
  }
}

/**
 * Validation rules for updating a document (UpdateDocumentRequest)
 */
export function useUpdateDocumentValidation() {
  return {
    name: [
      rules.string(),
      rules.maxLength(255, 'Document name must not exceed 255 characters'),
    ],
    description: [
      rules.string(),
      rules.maxLength(1000),
    ],
    type: [
      rules.string(),
      rules.inArray(DOCUMENT_TYPES as unknown as string[], 'Invalid document type'),
    ],
    expiryDate: [
      rules.date(),
      rules.afterToday('Expiry date must be in the future'),
    ],
  }
}

