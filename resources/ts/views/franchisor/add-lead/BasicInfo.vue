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
  { title: 'United States', value: 'USA' },
  { title: 'Canada', value: 'Canada' },
  { title: 'United Kingdom', value: 'UK' },
  { title: 'Australia', value: 'Australia' },
]

const states = [
  { title: 'California', value: 'California' },
  { title: 'New York', value: 'New York' },
  { title: 'Texas', value: 'Texas' },
  { title: 'Florida', value: 'Florida' },
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
        placeholder="John"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.lastName"
        label="Last Name"
        placeholder="Doe"
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
        placeholder="john.doe@example.com"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.contactNumber"
        label="Contact Number"
        placeholder="+1 234-567-8900"
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
        placeholder="San Francisco"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.companyName"
        label="Company Name"
        placeholder="Tech Corp"
      />
    </VCol>
  </VRow>
</template>
