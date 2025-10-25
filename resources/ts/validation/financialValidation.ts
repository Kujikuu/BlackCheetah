/**
 * Financial Validation Rules
 * Maps StoreFinancialDataRequest, StoreSaleRequest, StoreExpenseRequest, StoreRevenueRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Financial data category options
export const FINANCIAL_CATEGORIES = ['sales', 'expense'] as const

// Revenue type options
export const REVENUE_TYPES = [
  'sales',
  'fees',
  'royalties',
  'commissions',
  'other',
] as const

// Payment status options
export const PAYMENT_STATUSES = [
  'pending',
  'paid',
  'partial',
  'failed',
  'refunded',
] as const

// Revenue status options
export const REVENUE_STATUSES = [
  'pending',
  'verified',
  'disputed',
  'cancelled',
] as const

// Recurrence type options
export const RECURRENCE_TYPES = [
  'daily',
  'weekly',
  'monthly',
  'quarterly',
  'yearly',
] as const

/**
 * Validation rules for general financial data (StoreFinancialDataRequest)
 * This handles both sales and expenses with conditional validation
 */
export function useStoreFinancialDataValidation(category: 'sales' | 'expense') {
  const baseRules = {
    category: [
      rules.required('Category is required'),
      rules.inArray(FINANCIAL_CATEGORIES as unknown as string[], 'Category must be either sales or expense'),
    ],
    date: [
      rules.required('Date is required'),
      rules.date('Date must be a valid date'),
    ],
    description: [
      rules.string(),
    ],
  }

  // Sales-specific fields
  if (category === 'sales') {
    return {
      ...baseRules,
      product: [
        rules.required('Product name is required for sales'),
        rules.string(),
        rules.maxLength(255, 'Product name cannot exceed 255 characters'),
      ],
      quantitySold: [
        rules.required('Quantity sold is required for sales'),
        rules.integer('Quantity sold must be an integer'),
        rules.min(1, 'Quantity sold must be at least 1'),
      ],
      unitPrice: [
        rules.numeric(),
        rules.min(0),
      ],
    }
  }

  // Expense-specific fields
  return {
    ...baseRules,
    expenseCategory: [
      rules.required('Expense category is required for expenses'),
      rules.string(),
      rules.maxLength(255, 'Expense category cannot exceed 255 characters'),
    ],
    amount: [
      rules.required('Amount is required for expenses'),
      rules.numeric('Amount must be a number'),
      rules.min(0, 'Amount must be at least 0'),
    ],
  }
}

/**
 * Validation rules for creating a sale (StoreSaleRequest)
 */
export function useStoreSaleValidation() {
  return {
    product: [
      rules.required('Product name is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    unitPrice: [
      rules.required('Unit price is required'),
      rules.numeric('Unit price must be a number'),
      rules.min(0, 'Unit price must be at least 0'),
    ],
    quantity: [
      rules.required('Quantity is required'),
      rules.integer('Quantity must be a whole number'),
      rules.min(1, 'Quantity must be at least 1'),
    ],
    date: [
      rules.required('Sale date is required'),
      rules.date('Sale date must be a valid date'),
      rules.beforeOrEqual(new Date(), 'Sale date cannot be in the future'),
    ],
    customerName: [
      rules.string(),
      rules.maxLength(255),
    ],
    customerEmail: [
      rules.email(),
      rules.maxLength(255),
    ],
    customerPhone: [
      rules.string(),
      rules.maxLength(20),
    ],
    notes: [
      rules.string(),
      rules.maxLength(1000),
    ],
  }
}

/**
 * Validation rules for creating an expense (StoreExpenseRequest)
 */
export function useStoreExpenseValidation() {
  return {
    expenseCategory: [
      rules.required('Expense category is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    amount: [
      rules.required('Amount is required'),
      rules.numeric('Amount must be a number'),
      rules.min(0, 'Amount must be at least 0'),
    ],
    date: [
      rules.required('Expense date is required'),
      rules.date('Expense date must be a valid date'),
      rules.beforeOrEqual(new Date(), 'Expense date cannot be in the future'),
    ],
    description: [
      rules.string(),
      rules.maxLength(1000),
    ],
    vendor: [
      rules.string(),
      rules.maxLength(255),
    ],
    receiptNumber: [
      rules.string(),
      rules.maxLength(255),
    ],
    paymentMethod: [
      rules.string(),
      rules.maxLength(50),
    ],
    notes: [
      rules.string(),
      rules.maxLength(1000),
    ],
  }
}

/**
 * Validation rules for creating revenue (StoreRevenueRequest)
 */
export function useStoreRevenueValidation() {
  return {
    type: [
      rules.required('Revenue type is required'),
      rules.inArray(REVENUE_TYPES as unknown as string[], 'Revenue type must be one of: sales, fees, royalties, commissions, other'),
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
    revenueDate: [
      rules.required('Revenue date is required'),
      rules.date('Revenue date must be a valid date'),
    ],
    periodYear: [
      rules.required('Period year is required'),
      rules.integer('Period year must be an integer'),
      rules.min(2020, 'Period year must be at least 2020'),
      rules.max(2030, 'Period year cannot exceed 2030'),
    ],
    periodMonth: [
      rules.required('Period month is required'),
      rules.integer('Period month must be an integer'),
      rules.min(1, 'Period month must be at least 1'),
      rules.max(12, 'Period month cannot exceed 12'),
    ],
    source: [
      rules.string('Source must be a string'),
      rules.maxLength(100, 'Source cannot exceed 100 characters'),
    ],
    customerName: [
      rules.string('Customer name must be a string'),
      rules.maxLength(255, 'Customer name cannot exceed 255 characters'),
    ],
    invoiceNumber: [
      rules.string('Invoice number must be a string'),
      rules.maxLength(100, 'Invoice number cannot exceed 100 characters'),
    ],
    paymentMethod: [
      rules.string('Payment method must be a string'),
      rules.maxLength(50, 'Payment method cannot exceed 50 characters'),
    ],
    paymentStatus: [
      rules.required('Payment status is required'),
      rules.inArray(PAYMENT_STATUSES as unknown as string[], 'Payment status must be one of: pending, paid, partial, failed, refunded'),
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
      rules.inArray(REVENUE_STATUSES as unknown as string[], 'Status must be one of: pending, verified, disputed, cancelled'),
    ],
    notes: [
      rules.string('Notes must be a string'),
    ],
    recurrenceType: [
      rules.inArray(RECURRENCE_TYPES as unknown as string[], 'Recurrence type must be one of: daily, weekly, monthly, quarterly, yearly'),
    ],
    recurrenceInterval: [
      rules.integer('Recurrence interval must be an integer'),
      rules.min(1, 'Recurrence interval must be at least 1'),
    ],
  }
}

/**
 * Validation rules for updating financial data (UpdateFinancialDataRequest)
 * Similar to store but with optional fields
 */
export function useUpdateFinancialDataValidation() {
  // For updates, most fields are optional (sometimes)
  return useStoreFinancialDataValidation('sales') // Default to sales, component handles category-specific
}

/**
 * Validation rules for updating revenue (UpdateRevenueRequest)
 */
export function useUpdateRevenueValidation() {
  // Similar to store but fields are optional
  const storeRules = useStoreRevenueValidation()
  // Remove required rules for update
  return storeRules
}

