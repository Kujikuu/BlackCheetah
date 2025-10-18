<script setup lang="ts">
import type { LeadBasicInfo } from './types'

interface Props {
  formData: LeadBasicInfo
}

interface Emit {
  (e: 'update:formData', value: LeadBasicInfo): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = ref(props.formData)

watch(localFormData, () => {
  emit('update:formData', localFormData.value)
}, { deep: true })

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

const states = [
  { title: 'Riyadh', value: 'Riyadh' },
  { title: 'Makkah', value: 'Makkah' },
  { title: 'Medina', value: 'Medina' },
  { title: 'Eastern Province', value: 'Eastern Province' },
  { title: 'Asir', value: 'Asir' },
  { title: 'Tabuk', value: 'Tabuk' },
  { title: 'Hail', value: 'Hail' },
  { title: 'Northern Borders', value: 'Northern Borders' },
  { title: 'Jazan', value: 'Jazan' },
  { title: 'Najran', value: 'Najran' },
  { title: 'Al Bahah', value: 'Al Bahah' },
  { title: 'Al Jawf', value: 'Al Jawf' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h5 class="text-h5 mb-1">
        Basic Information
      </h5>
      <p class="text-body-1 mb-6">
        Enter lead's personal and contact details
      </p>
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.firstName"
        label="First Name"
        placeholder="First Name"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.lastName"
        label="Last Name"
        placeholder="Last Name"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.email"
        label="Email Address"
        type="email"
        placeholder="Email Address"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.contactNumber"
        label="Contact Number"
        placeholder="Contact Number"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.country"
        label="Country"
        placeholder="Select Country"
        :items="countries"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.state"
        label="State"
        placeholder="Select State"
        :items="states"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.city"
        label="City"
        placeholder="City"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.companyName"
        label="Company Name"
        placeholder="Company Name"
      />
    </VCol>
  </VRow>
</template>
