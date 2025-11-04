<script setup lang="ts">
import { propertyApi, type CreatePropertyPayload } from '@/services/api'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'
import { useFormValidation } from '@/composables/useFormValidation'
import { useStorePropertyValidation } from '@/validation/propertyValidation'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'propertyCreated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// Validation
const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useStorePropertyValidation()

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
const selectedImages = ref<File[]>([])
const imagePreviews = ref<string[]>([])

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
  selectedImages.value = []
  imagePreviews.value = []
}

// Handle image selection
const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files

  if (files) {
    // Validate max 10 images
    if (selectedImages.value.length + files.length > 10) {
      alert('You can upload a maximum of 10 images')

      return
    }

    Array.from(files).forEach((file) => {
      // Validate file size (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        alert(`${file.name} exceeds the 5MB size limit`)

        return
      }

      // Validate file type
      if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
        alert(`${file.name} is not a valid image format`)

        return
      }

      selectedImages.value.push(file)

      // Create preview
      const reader = new FileReader()

      reader.onload = (e) => {
        imagePreviews.value.push(e.target?.result as string)
      }
      reader.readAsDataURL(file)
    })
  }

  // Reset input
  if (target)
    target.value = ''
}

// Remove image
const removeImage = (index: number) => {
  selectedImages.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
}

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(formData.value.state_province || ''))

const onSubmit = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    isSubmitting.value = true

    const response = await propertyApi.createProperty(formData.value, selectedImages.value)

    if (response.success) {
      emit('propertyCreated')
      dialogValue.value = false
      resetForm()
    }
  }
  catch (error: any) {
    console.error('Error creating property:', error)
    setBackendErrors(error)
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
              <AppSelect 
                v-model="formData.state_province" 
                label="State/Province" 
                placeholder="Select State/Province"
                :items="provinces" 
                :loading="isLoadingProvinces"
                :rules="validationRules.stateProvince"
                :error-messages="backendErrors.stateProvince"
                @update:model-value="clearError('stateProvince')"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect 
                v-model="formData.city" 
                label="City" 
                placeholder="Select City" 
                :items="availableCities"
                :disabled="!formData.state_province"
                :rules="validationRules.city"
                :error-messages="backendErrors.city"
                @update:model-value="clearError('city')"
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

            <!-- Image Upload -->
            <VCol cols="12">
              <div class="text-body-2 font-weight-medium mb-2">
                Property Images (Optional)
              </div>
              <VFileInput
                label="Select Images"
                placeholder="Choose images to upload"
                prepend-icon="tabler-photo"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                multiple
                @change="handleImageUpload"
              />
              <div class="text-caption text-disabled mt-1">
                Upload up to 10 images. Maximum 5MB per image. Supported formats: JPEG, PNG, WEBP
              </div>
            </VCol>

            <!-- Image Previews -->
            <VCol v-if="imagePreviews.length > 0" cols="12">
              <div class="text-body-2 font-weight-medium mb-3">
                Selected Images ({{ imagePreviews.length }})
              </div>
              <VRow>
                <VCol
                  v-for="(preview, index) in imagePreviews"
                  :key="index"
                  cols="6"
                  sm="4"
                  md="3"
                >
                  <VCard class="position-relative">
                    <VImg :src="preview" aspect-ratio="1" cover />
                    <VBtn
                      icon
                      size="x-small"
                      color="error"
                      class="position-absolute"
                      style="top: 8px; right: 8px;"
                      @click="removeImage(index)"
                    >
                      <VIcon icon="tabler-x" />
                    </VBtn>
                  </VCard>
                </VCol>
              </VRow>
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
