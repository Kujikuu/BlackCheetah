<script setup lang="ts">
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'

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

// Get nationalities from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(localFormData.value.contactDetails.state || ''))

// Watch province changes to clear city
watch(() => localFormData.value.contactDetails.state, () => {
  localFormData.value.contactDetails.city = ''
})

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
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.franchiseDetails.franchiseName"
            label="Franchise Name"
            placeholder="Enter franchise name"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.franchiseDetails.website"
            label="Website"
            placeholder="https://example.com"
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.franchiseDetails.logo"
            label="Franchise Logo"
            accept="image/*"
            prepend-icon="tabler-upload"
          />
        </VCol>
      </VRow>

      <!-- Legal Details -->
      <h4 class="text-h6 mb-3">
        Legal Information
      </h4>
      <VRow class="mb-4">
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.legalDetails.legalEntityName"
            label="Legal Entity Name"
            placeholder="Enter legal entity name"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppSelect
            v-model="localFormData.legalDetails.businessStructure"
            label="Business Structure"
            :items="businessStructures"
            placeholder="Select business structure"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.legalDetails.taxId"
            label="Tax ID"
            placeholder="Enter tax identification number"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppSelect
            v-model="localFormData.legalDetails.industry"
            label="Industry/Sector"
            :items="industries"
            placeholder="Select industry"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.legalDetails.fundingAmount"
            label="Secured Funding Amount"
            placeholder="0.00"
            prefix="SAR"
            type="number"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppSelect
            v-model="localFormData.legalDetails.fundingSource"
            label="Source of Funding"
            :items="fundingSources"
            placeholder="Select funding source"
          />
        </VCol>
      </VRow>

      <!-- Contact Details -->
      <h4 class="text-h6 mb-3">
        Contact Information
      </h4>
      <VRow>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.contactDetails.contactNumber"
            label="Business Contact Number"
            placeholder="Enter business contact number"
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.contactDetails.email"
            label="Business Email"
            placeholder="business@example.com"
            type="email"
          />
        </VCol>
        <VCol cols="12">
          <AppTextarea
            v-model="localFormData.contactDetails.address"
            label="Business Address"
            placeholder="Enter business address"
            rows="3"
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <AppSelect
            v-model="localFormData.contactDetails.nationality"
            label="Nationality"
            :items="nationalityOptions"
            :loading="isLoadingCountries"
            placeholder="Select nationality"
            clearable
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <AppSelect
            v-model="localFormData.contactDetails.state"
            label="Province"
            placeholder="Select Province"
            :items="provinces"
            :loading="isLoadingProvinces"
            clearable
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <AppSelect
            v-model="localFormData.contactDetails.city"
            label="City"
            placeholder="Select City"
            :items="availableCities"
            :disabled="!localFormData.contactDetails.state"
            clearable
          />
        </VCol>
      </VRow>

      <!-- Financial Details Section -->
      <h6 class="text-h6 my-4">
        Financial Details
      </h6>
      <VRow>
        <VCol
          cols="12"
          md="4"
        >
          <AppTextField
            v-model="localFormData.financialDetails.franchiseFee"
            label="Franchise Fee"
            placeholder="Enter franchise fee"
            type="number"
            prefix="$"
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <AppTextField
            v-model="localFormData.financialDetails.royaltyPercentage"
            label="Royalty Percentage"
            placeholder="Enter royalty percentage"
            type="number"
            suffix="%"
          />
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <AppTextField
            v-model="localFormData.financialDetails.marketingFeePercentage"
            label="Marketing Fee Percentage"
            placeholder="Enter marketing fee percentage"
            type="number"
            suffix="%"
          />
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>
