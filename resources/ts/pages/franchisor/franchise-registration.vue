<script setup lang="ts">
import DocumentUpload from '@/views/wizard-examples/franchise-registration/DocumentUpload.vue'
import FranchiseDetails from '@/views/wizard-examples/franchise-registration/FranchiseDetails.vue'
import PersonalInfo from '@/views/wizard-examples/franchise-registration/PersonalInfo.vue'
import ReviewComplete from '@/views/wizard-examples/franchise-registration/ReviewComplete.vue'

import { useFranchisorDashboard } from '@/composables/useFranchisorDashboard'
import { $api } from '@/utils/api'
import type { FranchiseRegistrationData } from '@/views/wizard-examples/franchise-registration/types'

// 👉 Router
const router = useRouter()

// 👉 Route protection - check if user already has a franchise
const { checkFranchiseExists } = useFranchisorDashboard()

onMounted(async () => {
  const hasFranchise = await checkFranchiseExists()
  if (hasFranchise) {
    // User already has a franchise, redirect to dashboard
    await router.push('/franchisor/my-franchise')
  }
})

// 👉 Snackbar for notifications
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
})

const showSnackbar = (message: string, color: string = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
  }
}

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

  try {
    console.log('Submitting franchise registration:', franchiseRegistrationData.value)

    // Call the real API endpoint
    const response = await $api('/v1/franchisor/franchise/register', {
      method: 'POST',
      body: franchiseRegistrationData.value,
    })

    if (response.success) {
      showSnackbar('Franchise registered successfully! Redirecting to dashboard...', 'success')

      // Wait a moment to show the success message
      await new Promise(resolve => setTimeout(resolve, 1500))

      // Redirect to dashboard
      router.push('/franchisor')
    } else {
      showSnackbar(response.message || 'Failed to register franchise', 'error')
    }
  } catch (error: any) {
    console.error('Registration error:', error)

    // Handle validation errors
    if (error.status === 422 && error.data?.errors) {
      const errorMessages = Object.values(error.data.errors).flat()
      showSnackbar(`Validation failed: ${errorMessages.join(', ')}`, 'error')
    } else if (error.status === 400 && error.data?.message) {
      showSnackbar(error.data.message, 'error')
    } else {
      showSnackbar('Failed to register franchise. Please try again.', 'error')
    }
  } finally {
    isCompleting.value = false
  }
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

  <!-- Snackbar for notifications -->
  <VSnackbar v-model="snackbar.show" :color="snackbar.color" location="top end" timeout="4000">
    {{ snackbar.message }}

    <template #actions>
      <VBtn color="white" variant="text" @click="snackbar.show = false">
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
