<script setup lang="ts">
import { useDisplay } from 'vuetify'
import { leadApi } from '@/services/api/lead'
import AdditionalDetails from '@/views/franchisor/add-lead/AdditionalDetails.vue'
import BasicInfo from '@/views/franchisor/add-lead/BasicInfo.vue'
import type { AddLeadData } from '@/views/franchisor/add-lead/types'

const { mdAndUp } = useDisplay()

const addLeadSteps = [
  {
    title: 'Basic Information',
    subtitle: 'Personal & Contact Details',
    icon: 'tabler-user',
  },
  {
    title: 'Additional Details',
    subtitle: 'Lead Management Info',
    icon: 'tabler-file-info',
  },
]

const currentStep = ref(0)
const router = useRouter()

const addLeadData = ref<AddLeadData>({
  basicInfo: {
    firstName: '',
    lastName: '',
    email: '',
    contactNumber: '',
    nationality: '',
    state: '',
    city: '',
    companyName: '',
  },
  additionalDetails: {
    leadSource: null,
    leadStatus: null,
    leadOwner: null,
    lastContactedDate: '',
    scheduledMeetingDate: '',
    note: '',
    attachments: [],
  },
})

const isLoading = ref(false)

const onSubmit = async () => {
  try {
    isLoading.value = true

    // Map form data to API format
    const leadData = {
      firstName: addLeadData.value.basicInfo.firstName,
      lastName: addLeadData.value.basicInfo.lastName,
      email: addLeadData.value.basicInfo.email,
      phone: addLeadData.value.basicInfo.contactNumber,
      company: addLeadData.value.basicInfo.companyName || '',
      nationality: addLeadData.value.basicInfo.nationality,
      state: addLeadData.value.basicInfo.state,
      city: addLeadData.value.basicInfo.city,
      source: addLeadData.value.additionalDetails.leadSource || 'website',
      status: (addLeadData.value.additionalDetails.leadStatus || 'new') as 'new' | 'qualified' | 'unqualified' | 'contacted' | 'converted' | 'lost',
      priority: 'medium', // Default priority
      note: addLeadData.value.additionalDetails.note || '',

      // assigned_to: addLeadData.value.additionalDetails.leadOwner, // TODO: Map owner ID
    }

    const response = await leadApi.createLead(leadData)

    if (response.success) {
      // Show success message
      // TODO: Add toast notification
      console.log('Lead created successfully:', response.data)

      // Redirect to lead management page
      router.push({ name: 'sales-lead-management' })
    }
  }
  catch (error) {
    console.error('Error adding lead:', error)

    // TODO: Show error toast notification
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VCard>
    <VRow no-gutters>
      <VCol
        cols="12"
        md="4"
        lg="3"
        :class="mdAndUp ? 'border-e' : 'border-b'"
      >
        <VCardText>
          <AppStepper
            v-model:current-step="currentStep"
            direction="vertical"
            :items="addLeadSteps"
            icon-size="22"
            class="stepper-icon-step-bg"
          />
        </VCardText>
      </VCol>

      <VCol
        cols="12"
        md="8"
        lg="9"
      >
        <VCardText>
          <VWindow
            v-model="currentStep"
            class="disable-tab-transition"
          >
            <VWindowItem>
              <BasicInfo v-model:form-data="addLeadData.basicInfo" />
            </VWindowItem>

            <VWindowItem>
              <AdditionalDetails v-model:form-data="addLeadData.additionalDetails" />
            </VWindowItem>
          </VWindow>

          <div class="d-flex flex-wrap gap-4 justify-space-between mt-6">
            <VBtn
              color="secondary"
              variant="tonal"
              :disabled="currentStep === 0"
              @click="currentStep--"
            >
              <VIcon
                icon="tabler-arrow-left"
                start
                class="flip-in-rtl"
              />
              Previous
            </VBtn>

            <VBtn
              v-if="addLeadSteps.length - 1 === currentStep"
              color="success"
              :loading="isLoading"
              :disabled="isLoading"
              @click="onSubmit"
            >
              Submit
            </VBtn>

            <VBtn
              v-else
              @click="currentStep++"
            >
              Next

              <VIcon
                icon="tabler-arrow-right"
                end
                class="flip-in-rtl"
              />
            </VBtn>
          </div>
        </VCardText>
      </VCol>
    </VRow>
  </VCard>
</template>
