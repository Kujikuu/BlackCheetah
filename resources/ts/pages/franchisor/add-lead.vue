<script setup lang="ts">
import BasicInfo from '@/views/franchisor/add-lead/BasicInfo.vue'
import AdditionalDetails from '@/views/franchisor/add-lead/AdditionalDetails.vue'
import type { AddLeadData } from '@/views/franchisor/add-lead/types'

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
    country: null,
    state: null,
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

const onSubmit = async () => {
  try {
    // TODO: Implement API call
    console.log('addLeadData :>> ', addLeadData.value)

    // Show success message
    // Redirect to lead management page
    router.push({ name: 'franchisor-lead-management' })
  }
  catch (error) {
    console.error('Error adding lead:', error)
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
        :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'"
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
