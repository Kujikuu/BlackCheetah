<script setup lang="ts">
import type { LeadBasicInfo } from './types'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'

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

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(localFormData.value.state || ''))

// Watch province changes to clear city
watch(() => localFormData.value.state, () => {
  localFormData.value.city = ''
})
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
        v-model="localFormData.nationality"
        label="Nationality"
        placeholder="Select Nationality"
        :items="nationalityOptions"
        :loading="isLoadingCountries"
        clearable
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.state"
        label="Province"
        placeholder="Select Province"
        :items="provinces"
        :loading="isLoadingProvinces"
        clearable
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <AppSelect
        v-model="localFormData.city"
        label="City"
        placeholder="Select City"
        :items="availableCities"
        :disabled="!localFormData.state"
        clearable
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

    <VCol
      cols="12"
      md="6"
    >
      <AppTextField
        v-model="localFormData.jobTitle"
        label="Job Title"
        placeholder="Job Title"
      />
    </VCol>
  </VRow>
</template>
