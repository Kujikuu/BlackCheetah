<script setup lang="ts">
import DocumentUpload from '@/views/wizard-examples/franchise-registration/DocumentUpload.vue'
import FranchiseDetails from '@/views/wizard-examples/franchise-registration/FranchiseDetails.vue'
import PersonalInfo from '@/views/wizard-examples/franchise-registration/PersonalInfo.vue'
import ReviewComplete from '@/views/wizard-examples/franchise-registration/ReviewComplete.vue'

import type { FranchiseRegistrationData } from '@/views/wizard-examples/franchise-registration/types'

// 👉 Router
const router = useRouter()

const franchiseRegistrationSteps = [
  {
    title: 'Personal Info',
    subtitle: 'Contact details',
    icon: 'tabler-user',
  },
  {
    title: 'Franchise Details',
    subtitle: 'Business information',
    icon: 'tabler-building-store',
  },
  {
    title: 'Documents',
    subtitle: 'Upload required files',
    icon: 'tabler-files',
  },
  {
    title: 'Review & Complete',
    subtitle: 'Confirm & submit',
    icon: 'tabler-checkbox',
  },
]

const currentStep = ref(0)
const isCompleting = ref(false)

const franchiseRegistrationData = ref<FranchiseRegistrationData>({
  personalInfo: {
    contactNumber: '',
    country: '',
    state: '',
    city: '',
    address: '',
  },
  franchiseDetails: {
    franchiseDetails: {
      franchiseName: '',
      website: '',
      logo: null,
    },
    legalDetails: {
      legalEntityName: '',
      businessStructure: '',
      taxId: '',
      industry: '',
      fundingAmount: '',
      fundingSource: '',
    },
    contactDetails: {
      contactNumber: '',
      email: '',
      address: '',
      country: '',
      state: '',
      city: '',
    },
  },
  documents: {
    fdd: null,
    franchiseAgreement: null,
    operationsManual: null,
    brandGuidelines: null,
    legalDocuments: null,
  },
  reviewComplete: {
    termsAccepted: false,
  },
})

const onSubmit = async () => {
  isCompleting.value = true

  // TODO: Implement API call to save franchise registration
  console.log('Submitting franchise registration:', franchiseRegistrationData.value)

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 2000))

  isCompleting.value = false

  // Redirect to dashboard
  router.push('/franchisor')
}
</script>

<template>
  <VCard>
    <VRow no-gutters>
      <VCol cols="12" md="4" lg="3" :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'">
        <VCardText>
          <AppStepper v-model:current-step="currentStep" direction="vertical" :items="franchiseRegistrationSteps"
            icon-size="22" class="stepper-icon-step-bg" />
        </VCardText>
      </VCol>

      <VCol cols="12" md="8" lg="9">
        <VCardText>
          <VWindow v-model="currentStep" class="disable-tab-transition">
            <VWindowItem>
              <PersonalInfo v-model:form-data="franchiseRegistrationData.personalInfo" />
            </VWindowItem>

            <VWindowItem>
              <FranchiseDetails v-model:form-data="franchiseRegistrationData.franchiseDetails" />
            </VWindowItem>

            <VWindowItem>
              <DocumentUpload v-model:form-data="franchiseRegistrationData.documents" />
            </VWindowItem>

            <VWindowItem>
              <ReviewComplete v-model:form-data="franchiseRegistrationData.reviewComplete"
                :all-form-data="franchiseRegistrationData" />
            </VWindowItem>
          </VWindow>

          <div class="d-flex flex-wrap gap-4 justify-space-between mt-6">
            <VBtn color="secondary" variant="tonal" :disabled="currentStep === 0" @click="currentStep--">
              <VIcon icon="tabler-arrow-left" start class="flip-in-rtl" />
              Previous
            </VBtn>

            <VBtn v-if="franchiseRegistrationSteps.length - 1 === currentStep" color="success" :loading="isCompleting"
              @click="onSubmit">
              Complete Registration
            </VBtn>

            <VBtn v-else @click="currentStep++">
              Next

              <VIcon icon="tabler-arrow-right" end class="flip-in-rtl" />
            </VBtn>
          </div>
        </VCardText>
      </VCol>
    </VRow>
  </VCard>
</template>
