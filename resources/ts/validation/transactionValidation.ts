/**
 * Transaction Validation Rules
 * Maps StoreTransactionRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Transaction type options
export const TRANSACTION_TYPES = [
  'revenue',
  'expense',
  'transfer',
  'refund',
  'adjustment',
] as const

// Transaction status options
export const TRANSACTION_STATUSES = [
  'pending',
  'completed',
  'cancelled',
  'refunded',
] as const

// Recurrence type options
export const TRANSACTION_RECURRENCE_TYPES = [
  'daily',
  'weekly',
  'monthly',
  'quarterly',
  'yearly',
] as const

/**
 * Validation rules for creating a transaction (StoreTransactionRequest)
 */
export function useStoreTransactionValidation() {
  return {
    type: [
      rules.required('Transaction type is required'),
      rules.inArray(TRANSACTION_TYPES as unknown as string[], 'Transaction type must be one of: revenue, expense, transfer, refund, adjustment'),
    ],
    category: [
      rules.required('Category is required'),
      rules.string('Category must be a string'),
      rules.maxLength(100, 'Category cannot exceed 100 characters'),
    ],
    amount: [
      rules.required('Amount is required'),
      rules.numeric('Amount must be a number'),
      rules.min(0, 'Amount must be at least 0'),
    ],
    currency: [
      rules.required('Currency is required'),
      rules.string('Currency must be a string'),
      rules.minLength(3, 'Currency must be exactly 3 characters'),
      rules.maxLength(3, 'Currency must be exactly 3 characters'),
    ],
    description: [
      rules.required('Description is required'),
      rules.string('Description must be a string'),
      rules.maxLength(500, 'Description cannot exceed 500 characters'),
    ],
    transactionDate: [
      rules.required('Transaction date is required'),
      rules.date('Transaction date must be a valid date'),
    ],
    paymentMethod: [
      rules.string('Payment method must be a string'),
      rules.maxLength(50, 'Payment method cannot exceed 50 characters'),
    ],
    referenceNumber: [
      rules.string('Reference number must be a string'),
      rules.maxLength(100, 'Reference number cannot exceed 100 characters'),
    ],
    invoiceNumber: [
      rules.string('Invoice number must be a string'),
      rules.maxLength(100, 'Invoice number cannot exceed 100 characters'),
    ],
    taxAmount: [
      rules.numeric('Tax amount must be a number'),
      rules.min(0, 'Tax amount must be at least 0'),
    ],
    discountAmount: [
      rules.numeric('Discount amount must be a number'),
      rules.min(0, 'Discount amount must be at least 0'),
    ],
    netAmount: [
      rules.numeric('Net amount must be a number'),
    ],
    status: [
      rules.required('Status is required'),
      rules.inArray(TRANSACTION_STATUSES as unknown as string[], 'Status must be one of: pending, completed, cancelled, refunded'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
    recurrenceType: [
      rules.inArray(TRANSACTION_RECURRENCE_TYPES as unknown as string[], 'Recurrence type must be one of: daily, weekly, monthly, quarterly, yearly'),
    ],
    recurrenceInterval: [
      rules.integer('Recurrence interval must be an integer'),
      rules.min(1, 'Recurrence interval must be at least 1'),
    ],
  }
}

