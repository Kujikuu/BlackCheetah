/**
 * Notes Validation Rules
 * Maps AddLeadNoteRequest and other note-related requests
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

/**
 * Validation rules for adding a note (AddLeadNoteRequest)
 */
export function useAddNoteValidation() {
  return {
    note: [
      rules.required('Note content is required'),
      rules.string(),
      rules.minLength(1),
    ],
    content: [
      rules.required('Note content is required'),
      rules.string(),
      rules.minLength(1),
    ],
  }
}

/**
 * Validation rules for editing a note
 */
export function useEditNoteValidation() {
  return useAddNoteValidation()
}

