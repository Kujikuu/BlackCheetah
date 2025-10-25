<script setup lang="ts">
import { propertyApi, type CreatePropertyPayload } from '@/services/api'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'
import { useValidation } from '@/composables/useValidation'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'propertyCreated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// Form state
const formData = ref<CreatePropertyPayload>({
  title: '',
  description: '',
  property_type: 'retail',
  size_sqm: 0,
  state_province: '',
  city: '',
  address: '',
  postal_code: '11111',
  monthly_rent: 0,
  deposit_amount: 0,
  lease_term_months: 12,
  available_from: '',
  status: 'available',
  amenities: [],
  images: [],
  contact_info: '',
})

const isSubmitting = ref(false)
const formRef = ref()

// Use validation composable
const { 
  requiredTextRules, 
  requiredTextWithLengthRules,
  positiveNumberRules,
  addressRules,
  validateForm 
} = useValidation()

// Computed for v-model dialog
const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const resetForm = () => {
  formData.value = {
    title: '',
    description: '',
    property_type: 'retail',
    size_sqm: 0,
    state_province: '',
    city: '',
    address: '',
    postal_code: '11111',
    monthly_rent: 0,
    deposit_amount: 0,
    lease_term_months: 12,
    available_from: '',
    status: 'available',
    amenities: [],
    images: [],
    contact_info: '',
  }
}

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(formData.value.state_province || ''))

const onSubmit = async () => {
  // Validate form before submission
  const isValid = await validateForm(formRef)
  if (!isValid) return

  try {
    isSubmitting.value = true

    const response = await propertyApi.createProperty(formData.value)

    if (response.success) {
      emit('propertyCreated')
      dialogValue.value = false
      resetForm()
    }
  }
  catch (error) {
    console.error('Error creating property:', error)
  }
  finally {
    isSubmitting.value = false
  }
}

const onCancel = () => {
  dialogValue.value = false
  resetForm()
}
</script>

<template>
  <VDialog v-model="dialogValue" max-width="900" persistent>
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Add New Property">
      <VCardText>
        <VForm ref="formRef" @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <AppTextField v-model="formData.title" label="Property Title" placeholder="Enter property title"
                :rules="requiredTextWithLengthRules(5, 100)" required />
            </VCol>

            <VCol cols="12">
              <AppTextarea v-model="formData.description" label="Description" placeholder="Enter property description"
                rows="3" :rules="requiredTextWithLengthRules(10, 1000)" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect v-model="formData.property_type" label="Property Type" :items="[
                { title: 'Retail', value: 'retail' },
                { title: 'Office', value: 'office' },
                { title: 'Kiosk', value: 'kiosk' },
                { title: 'Food Court', value: 'food_court' },
                { title: 'Standalone', value: 'standalone' },
              ]" :rules="requiredTextRules" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.size_sqm" label="Size (m²)" type="number" placeholder="0"
                :rules="positiveNumberRules" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.monthly_rent" label="Monthly Rent (SAR)" type="number" placeholder="0"
                :rules="positiveNumberRules" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.deposit_amount" label="Deposit Amount (SAR)" type="number"
                placeholder="0" :rules="positiveNumberRules" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.lease_term_months" label="Lease Term (months)" type="number"
                placeholder="12" :rules="positiveNumberRules" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.available_from" label="Available From" type="date" />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect v-model="formData.state_province" label="State/Province" placeholder="Select State/Province"
                :items="provinces" :loading="isLoadingProvinces"
                :rules="requiredTextRules" required />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect v-model="formData.city" label="City" placeholder="Select City" :items="availableCities"
                :disabled="!formData.state_province" :rules="requiredTextRules" required />
            </VCol>

            <VCol cols="12">
              <AppTextField v-model="formData.address" label="Address" placeholder="Enter full address"
                :rules="addressRules" required />
            </VCol>

            <VCol cols="12">
              <AppSelect v-model="formData.status" label="Status" :items="[
                { title: 'Available', value: 'available' },
                { title: 'Under Negotiation', value: 'under_negotiation' },
                { title: 'Leased', value: 'leased' },
                { title: 'Unavailable', value: 'unavailable' },
              ]" />
            </VCol>

            <VCol cols="12">
              <AppTextarea v-model="formData.contact_info" label="Contact Information"
                placeholder="Enter contact information" rows="2" />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn color="secondary" variant="tonal" :disabled="isSubmitting" @click="onCancel">
          Cancel
        </VBtn>
        <VBtn color="primary" :loading="isSubmitting" @click="onSubmit">
          Create Property
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
