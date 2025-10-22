<script setup lang="ts">
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'

interface PersonalInfoData {
  contactNumber: string
  nationality: string
  state: string
  city: string
  address: string
}

interface Props {
  formData: PersonalInfoData
}

interface Emit {
  (e: 'update:formData', value: PersonalInfoData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = computed({
  get: () => props.formData,
  set: val => emit('update:formData', val),
})

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
  <div>
    <div class="text-h4 mb-1">
      Personal Information
    </div>
    <p class="text-body-1 mb-6">
      Please provide your personal contact details
    </p>

    <VForm>
      <VRow>
        <VCol
          cols="12"
          md="6"
        >
          <AppTextField
            v-model="localFormData.contactNumber"
            label="Contact Number"
            placeholder="Enter your contact number"
            required
          />
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <AppSelect
            v-model="localFormData.nationality"
            label="Nationality"
            :items="nationalityOptions"
            :loading="isLoadingCountries"
            placeholder="Select nationality"
            clearable
            required
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
            required
          />
        </VCol>
        <VCol cols="12">
          <AppTextarea
            v-model="localFormData.address"
            label="Address"
            placeholder="Enter your full address"
            rows="3"
          />
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>
