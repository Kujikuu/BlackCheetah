<script setup lang="ts">
import type { LeadAdditionalDetails } from './types'

interface Props {
  formData: LeadAdditionalDetails
}

interface Emit {
  (e: 'update:formData', value: LeadAdditionalDetails): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = ref(props.formData)

watch(localFormData, () => {
  emit('update:formData', localFormData.value)
}, { deep: true })

const leadSources = [
  { title: 'Website', value: 'website' },
  { title: 'Referral', value: 'referral' },
  { title: 'Social Media', value: 'social_media' },
  { title: 'Email Campaign', value: 'email' },
  { title: 'Cold Call', value: 'cold_call' },
]

const leadStatuses = [
  { title: 'New', value: 'new' },
  { title: 'Qualified', value: 'qualified' },
  { title: 'Unqualified', value: 'unqualified' },
  { title: 'Contacted', value: 'contacted' },
]

const leadOwners = [
  { title: 'Sarah Johnson', value: 'sarah_johnson' },
  { title: 'John Smith', value: 'john_smith' },
  { title: 'Michael Brown', value: 'michael_brown' },
]

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    localFormData.value.attachments = Array.from(target.files)
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h5 class="text-h5 mb-1">
        Additional Details
      </h5>
      <p class="text-body-1 mb-6">
        Provide lead management and tracking information
      </p>
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.leadSource"
        label="Lead Source"
        placeholder="Select Source"
        :items="leadSources"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.leadStatus"
        label="Lead Status"
        placeholder="Select Status"
        :items="leadStatuses"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.leadOwner"
        label="Lead Owner"
        placeholder="Select Owner"
        :items="leadOwners"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppDateTimePicker
        v-model="localFormData.lastContactedDate"
        label="Last Contacted Date"
        placeholder="Select Date"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppDateTimePicker
        v-model="localFormData.scheduledMeetingDate"
        label="Scheduled Meeting Date"
        placeholder="Select Date"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        v-model="localFormData.note"
        label="Note"
        placeholder="Add any additional notes about this lead..."
        rows="4"
      />
    </VCol>

    <VCol cols="12">
      <VFileInput
        label="Attachments"
        multiple
        prepend-icon="tabler-paperclip"
        placeholder="Upload files"
        chips
        show-size
        counter
        @change="handleFileUpload"
      />
    </VCol>
  </VRow>
</template>
