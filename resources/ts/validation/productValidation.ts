/**
 * Product Validation Rules
 * Maps StoreProductRequest and UpdateProductRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Product status options
export const PRODUCT_STATUSES = [
  'active',
  'inactive',
  'discontinued',
] as const

/**
 * Validation rules for creating a product (StoreProductRequest)
 */
export function useStoreProductValidation() {
  return {
    name: [
      rules.required('Product name is required'),
      rules.string(),
      rules.maxLength(255, 'Product name must not exceed 255 characters'),
    ],
    description: [
      rules.string(),
      rules.maxLength(1000),
    ],
    category: [
      rules.required('Product category is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    unitPrice: [
      rules.required('Unit price is required'),
      rules.numeric('Unit price must be a valid number'),
      rules.min(0, 'Unit price must be at least 0'),
      rules.max(999999.99),
    ],
    costPrice: [
      rules.numeric(),
      rules.min(0),
      rules.max(999999.99),
    ],
    stock: [
      rules.required('Stock quantity is required'),
      rules.integer('Stock must be a whole number'),
      rules.min(0, 'Stock cannot be negative'),
    ],
    minimumStock: [
      rules.integer(),
      rules.min(0),
    ],
    sku: [
      rules.required('SKU is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    status: [
      rules.required('Product status is required'),
      rules.string(),
      rules.inArray(PRODUCT_STATUSES as unknown as string[], 'Status must be active, inactive, or discontinued'),
    ],
    weight: [
      rules.numeric(),
      rules.min(0),
    ],
    image: [
      rules.fileType(['jpg', 'jpeg', 'png', 'gif', 'webp'], 'The file must be an image'),
      rules.fileSize(5, 'Image must not exceed 5MB'),
    ],
  }
}

/**
 * Validation rules for updating a product (UpdateProductRequest)
 */
export function useUpdateProductValidation() {
  return useStoreProductValidation()
}

