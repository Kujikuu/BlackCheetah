/**
 * Franchise Validation Rules
 * Maps StoreFranchiseRequest, UpdateFranchiseRequest, and RegisterFranchiseRequest backend validation rules
 */

import { useValidationRules } from '@/composables/useValidationRules'

const rules = useValidationRules()

// Business type options
export const BUSINESS_TYPES = [
  'corporation',
  'llc',
  'partnership',
  'sole_proprietorship',
] as const

// Franchise status options
export const FRANCHISE_STATUSES = [
  'active',
  'inactive',
  'pending_approval',
  'suspended',
] as const

/**
 * Validation rules for creating a franchise (StoreFranchiseRequest)
 */
export function useStoreFranchiseValidation() {
  return {
    businessName: [
      rules.required('Business name is required'),
      rules.string(),
      rules.maxLength(255, 'Business name must not exceed 255 characters'),
    ],
    brandName: [
      rules.string(),
      rules.maxLength(255),
    ],
    industry: [
      rules.required('Industry is required'),
      rules.string(),
      rules.maxLength(255),
    ],
    description: [
      rules.string(),
    ],
    website: [
      rules.url(),
    ],
    logo: [
      rules.string(),
      rules.maxLength(255),
    ],
    businessRegistrationNumber: [
      rules.required('Business registration number is required'),
      rules.string(),
    ],
    taxId: [
      rules.string(),
      rules.maxLength(255),
    ],
    businessType: [
      rules.required(),
      rules.inArray(BUSINESS_TYPES as unknown as string[], 'Invalid business type'),
    ],
    establishedDate: [
      rules.date(),
    ],
    headquartersCountry: [
      rules.required('Headquarters country is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    headquartersCity: [
      rules.required('Headquarters city is required'),
      rules.string(),
      rules.maxLength(100),
    ],
    headquartersAddress: [
      rules.required('Headquarters address is required'),
      rules.string(),
    ],
    contactPhone: [
      rules.required('Contact phone is required'),
      rules.string(),
      rules.maxLength(20),
    ],
    contactEmail: [
      rules.required('Contact email is required'),
      rules.email('Contact email must be a valid email address'),
      rules.maxLength(255),
    ],
    franchiseFee: [
      rules.numeric(),
      rules.min(0),
    ],
    royaltyPercentage: [
      rules.numeric(),
      rules.min(0),
      rules.max(100, 'Royalty percentage cannot exceed 100%'),
    ],
    marketingFeePercentage: [
      rules.numeric(),
      rules.min(0),
      rules.max(100, 'Marketing fee percentage cannot exceed 100%'),
    ],
    status: [
      rules.required(),
      rules.inArray(FRANCHISE_STATUSES as unknown as string[], 'Invalid status'),
    ],
    plan: [
      rules.string(),
      rules.maxLength(255),
    ],
  }
}

/**
 * Validation rules for updating a franchise (UpdateFranchiseRequest)
 */
export function useUpdateFranchiseValidation() {
  return useStoreFranchiseValidation()
}

/**
 * Validation rules for franchise registration wizard (RegisterFranchiseRequest)
 */
export function useRegisterFranchiseValidation() {
  return {
    // Personal Info
    personalInfo: {
      contactNumber: [
        rules.required('Contact number is required'),
        rules.string(),
        rules.maxLength(20),
      ],
      nationality: [
        rules.required('Nationality is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      state: [
        rules.required('State is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      city: [
        rules.required('City is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      address: [
        rules.required('Address is required'),
        rules.string(),
        rules.maxLength(500),
      ],
    },
    
    // Franchise Details
    franchiseDetails: {
      franchiseName: [
        rules.required('Franchise name is required'),
        rules.string(),
        rules.maxLength(255),
      ],
      website: [
        rules.url(),
        rules.maxLength(255),
      ],
      legalEntityName: [
        rules.required('Legal entity name is required'),
        rules.string(),
        rules.maxLength(255),
      ],
      businessStructure: [
        rules.required('Business structure is required'),
        rules.inArray(BUSINESS_TYPES as unknown as string[], 'Invalid business structure'),
      ],
      taxId: [
        rules.string(),
        rules.maxLength(50),
      ],
      industry: [
        rules.required('Industry is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      fundingAmount: [
        rules.string(),
        rules.maxLength(100),
      ],
      fundingSource: [
        rules.string(),
        rules.maxLength(100),
      ],
      contactNumber: [
        rules.required('Contact number is required'),
        rules.string(),
        rules.maxLength(20),
      ],
      email: [
        rules.required('Email is required'),
        rules.email(),
        rules.maxLength(255),
      ],
      address: [
        rules.required('Address is required'),
        rules.string(),
        rules.maxLength(500),
      ],
      nationality: [
        rules.required('Nationality is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      state: [
        rules.required('State is required'),
        rules.string(),
        rules.maxLength(100),
      ],
      city: [
        rules.required('City is required'),
        rules.string(),
        rules.maxLength(100),
      ],
    },

    // Review Complete
    termsAccepted: [
      rules.required('You must accept the terms and conditions'),
    ],
  }
}

