<script setup lang="ts">
import { propertyApi, type Property, type UpdatePropertyPayload } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useUpdatePropertyValidation } from '@/validation/propertyValidation'

interface Props {
  isDialogVisible: boolean
  property?: Property | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'propertyUpdated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useUpdatePropertyValidation()

// Form state
const formData = ref<UpdatePropertyPayload>({
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
  contact_info: '',
})

const isSubmitting = ref(false)

// Computed for v-model dialog
const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

// Watch for property changes to populate form
watch(() => props.property, (property) => {
  if (property) {
    formData.value = {
      title: property.title,
      description: property.description,
      property_type: property.property_type,
      size_sqm: property.size_sqm,
      state_province: property.state_province,
      city: property.city,
      address: property.address,
      postal_code: property.postal_code,
      monthly_rent: property.monthly_rent,
      deposit_amount: property.deposit_amount,
      lease_term_months: property.lease_term_months,
      available_from: property.available_from,
      status: property.status,
      contact_info: property.contact_info,
    }
  }
}, { immediate: true })

const onSubmit = async () => {
  if (!props.property)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    isSubmitting.value = true

    const response = await propertyApi.updateProperty(
      props.property.id,
      formData.value,
    )

    if (response.success) {
      emit('propertyUpdated')
      dialogValue.value = false
    }
  }
  catch (error: any) {
    console.error('Error updating property:', error)
    setBackendErrors(error)
  }
  finally {
    isSubmitting.value = false
  }
}

const onCancel = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog v-model="dialogValue" max-width="900" persistent>
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Edit Property">
      <VCardText>
        <VForm ref="formRef" @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <AppTextField 
                v-model="formData.title" 
                label="Property Title" 
                placeholder="Enter property title"
                :rules="validationRules.title"
                :error-messages="backendErrors.title"
                @input="clearError('title')"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea 
                v-model="formData.description" 
                label="Description" 
                placeholder="Enter property description"
                rows="3"
                :rules="validationRules.description"
                :error-messages="backendErrors.description"
                @input="clearError('description')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect 
                v-model="formData.property_type" 
                label="Property Type" 
                :items="[
                  { title: 'Retail', value: 'retail' },
                  { title: 'Office', value: 'office' },
                  { title: 'Kiosk', value: 'kiosk' },
                  { title: 'Food Court', value: 'food_court' },
                  { title: 'Standalone', value: 'standalone' },
                ]"
                :rules="validationRules.propertyType"
                :error-messages="backendErrors.propertyType"
                @update:model-value="clearError('propertyType')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField 
                v-model="formData.size_sqm" 
                label="Size (m²)" 
                type="number" 
                placeholder="0"
                :rules="validationRules.sizeSqm"
                :error-messages="backendErrors.sizeSqm"
                @input="clearError('sizeSqm')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField 
                v-model="formData.monthly_rent" 
                label="Monthly Rent (SAR)" 
                type="number" 
                placeholder="0"
                :rules="validationRules.monthlyRent"
                :error-messages="backendErrors.monthlyRent"
                @input="clearError('monthlyRent')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.deposit_amount" label="Deposit Amount (SAR)" type="number"
                placeholder="0" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.lease_term_months" label="Lease Term (months)" type="number"
                placeholder="12" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField v-model="formData.available_from" label="Available From" type="date" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField 
                v-model="formData.state_province" 
                label="State/Province"
                placeholder="Enter state/province"
                :rules="validationRules.stateProvince"
                :error-messages="backendErrors.stateProvince"
                @input="clearError('stateProvince')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField 
                v-model="formData.city" 
                label="City" 
                placeholder="Enter city"
                :rules="validationRules.city"
                :error-messages="backendErrors.city"
                @input="clearError('city')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField 
                v-model="formData.postal_code" 
                label="Postal Code" 
                placeholder="Enter postal code"
                :rules="validationRules.postalCode"
                :error-messages="backendErrors.postalCode"
                @input="clearError('postalCode')"
              />
            </VCol>

            <VCol cols="12">
              <AppTextField 
                v-model="formData.address" 
                label="Address" 
                placeholder="Enter full address"
                :rules="validationRules.address"
                :error-messages="backendErrors.address"
                @input="clearError('address')"
              />
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
          Update Property
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
