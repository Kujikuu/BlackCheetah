export interface PersonalInfoData {
  contactNumber: string
  nationality: string
  state: string
  city: string
  address: string
}

export interface FranchiseDetailsData {
  franchiseDetails: {
    franchiseName: string
    website: string
    logo: File | null
  }
  legalDetails: {
    legalEntityName: string
    businessStructure: string
    taxId: string
    industry: string
    fundingAmount: string
    fundingSource: string
  }
  contactDetails: {
    contactNumber: string
    email: string
    address: string
    nationality: string
    state: string
    city: string
  }
  financialDetails: {
    franchiseFee: string
    royaltyPercentage: string
    marketingFeePercentage: string
  }
}

export interface DocumentUploadData {
  fdd: File | null
  franchiseAgreement: File | null
  financialStudy: File | null
  franchiseKit: File | null
  additionalDocuments: File[] | null
}

export interface ReviewCompleteData {
  termsAccepted: boolean
}

export interface FranchiseRegistrationData {
  personalInfo: PersonalInfoData
  franchiseDetails: FranchiseDetailsData
  documents: DocumentUploadData
  reviewComplete: ReviewCompleteData
}
