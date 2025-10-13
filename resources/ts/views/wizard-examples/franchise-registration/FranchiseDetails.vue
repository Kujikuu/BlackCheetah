<script setup lang="ts">
interface FranchiseDetailsData {
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
    country: string
    state: string
    city: string
  }
}

interface Props {
  formData: FranchiseDetailsData
}

interface Emit {
  (e: 'update:formData', value: FranchiseDetailsData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = computed({
  get: () => props.formData,
  set: val => emit('update:formData', val),
})

// 👉 Options
const countries = [
  { title: 'Saudi Arabia', value: 'Saudi Arabia' },
  { title: 'United Arab Emirates', value: 'United Arab Emirates' },
  { title: 'Qatar', value: 'Qatar' },
  { title: 'Kuwait', value: 'Kuwait' },
  { title: 'Oman', value: 'Oman' },
  { title: 'Bahrain', value: 'Bahrain' },
  { title: 'Jordan', value: 'Jordan' },
  { title: 'Lebanon', value: 'Lebanon' },
  { title: 'Egypt', value: 'Egypt' },
  { title: 'Iraq', value: 'Iraq' },
  { title: 'Syria', value: 'Syria' },
  { title: 'Palestine', value: 'Palestine' },
  { title: 'Yemen', value: 'Yemen' },
]

const businessStructures = [
  { title: 'Corporation', value: 'corporation' },
  { title: 'LLC', value: 'llc' },
  { title: 'Partnership', value: 'partnership' },
  { title: 'Sole Proprietorship', value: 'sole_proprietorship' },
]

const industries = [
  { title: 'Food & Beverage', value: 'food_beverage' },
  { title: 'Retail', value: 'retail' },
  { title: 'Services', value: 'services' },
  { title: 'Health & Fitness', value: 'health_fitness' },
  { title: 'Education', value: 'education' },
  { title: 'Technology', value: 'technology' },
  { title: 'Real Estate', value: 'real_estate' },
  { title: 'Automotive', value: 'automotive' },
]

const fundingSources = [
  { title: 'Personal Savings', value: 'personal_savings' },
  { title: 'Bank Loan', value: 'bank_loan' },
  { title: 'Investors', value: 'investors' },
  { title: 'SBA Loan', value: 'sba_loan' },
  { title: 'Other', value: 'other' },
]
</script>

<template>
  <div>
    <div class="text-h4 mb-1">
      Franchise & Legal Details
    </div>
    <p class="text-body-1 mb-6">
      Provide comprehensive information about your franchise
    </p>

    <VForm>
      <!-- Franchise Details -->
      <h4 class="text-h6 mb-3">
        Franchise Information
      </h4>
      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.franchiseDetails.franchiseName" label="Franchise Name"
            placeholder="Enter franchise name" required />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.franchiseDetails.website" label="Website"
            placeholder="https://example.com" />
        </VCol>
        <VCol cols="12">
          <VFileInput v-model="localFormData.franchiseDetails.logo" label="Franchise Logo" accept="image/*"
            prepend-icon="tabler-upload" />
        </VCol>
      </VRow>

      <!-- Legal Details -->
      <h4 class="text-h6 mb-3">
        Legal Information
      </h4>
      <VRow class="mb-4">
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.legalDetails.legalEntityName" label="Legal Entity Name"
            placeholder="Enter legal entity name" required />
        </VCol>
        <VCol cols="12" md="6">
          <AppSelect v-model="localFormData.legalDetails.businessStructure" label="Business Structure"
            :items="businessStructures" placeholder="Select business structure" required />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.legalDetails.taxId" label="Tax ID"
            placeholder="Enter tax identification number" />
        </VCol>
        <VCol cols="12" md="6">
          <AppSelect v-model="localFormData.legalDetails.industry" label="Industry/Sector" :items="industries"
            placeholder="Select industry" />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.legalDetails.fundingAmount" label="Secured Funding Amount"
            placeholder="$0.00" type="number" />
        </VCol>
        <VCol cols="12" md="6">
          <AppSelect v-model="localFormData.legalDetails.fundingSource" label="Source of Funding"
            :items="fundingSources" placeholder="Select funding source" />
        </VCol>
      </VRow>

      <!-- Contact Details -->
      <h4 class="text-h6 mb-3">
        Contact Information
      </h4>
      <VRow>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.contactDetails.contactNumber" label="Business Contact Number"
            placeholder="Enter business contact number" />
        </VCol>
        <VCol cols="12" md="6">
          <AppTextField v-model="localFormData.contactDetails.email" label="Business Email"
            placeholder="business@example.com" type="email" />
        </VCol>
        <VCol cols="12">
          <AppTextarea v-model="localFormData.contactDetails.address" label="Business Address"
            placeholder="Enter business address" rows="3" />
        </VCol>
        <VCol cols="12" md="4">
          <AppSelect v-model="localFormData.contactDetails.country" label="Country" :items="countries"
            placeholder="Select country" />
        </VCol>
        <VCol cols="12" md="4">
          <AppTextField v-model="localFormData.contactDetails.state" label="State/Province" placeholder="Enter state" />
        </VCol>
        <VCol cols="12" md="4">
          <AppTextField v-model="localFormData.contactDetails.city" label="City" placeholder="Enter city" />
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>
