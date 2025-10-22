<script setup lang="ts">
import { emailValidator, requiredValidator } from '@core/utils/validators'
import { franchiseApi } from '@/services/api'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'franchiseeAdded', data: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// Form data
const currentStep = ref(1)
const loading = ref(false)
const error = ref<string | null>(null)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  nationality: '',
  state: '',
  city: '',
  address: '',
  postalCode: '',
  type: 'store' as const,
  sizeSqft: '',
  capacity: '',
  openingDate: '',
  monthlyRent: '',
})

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(formData.value.state || ''))

// Watch province changes to clear city
watch(() => formData.value.state, () => {
  formData.value.city = ''
})

const unitTypes = [
  { title: 'Store', value: 'store' },
  { title: 'Kiosk', value: 'kiosk' },
  { title: 'Mobile', value: 'mobile' },
  { title: 'Online', value: 'online' },
  { title: 'Warehouse', value: 'warehouse' },
  { title: 'Office', value: 'office' },
]

// Methods
const resetForm = () => {
  currentStep.value = 1
  error.value = null
  formData.value = {
    name: '',
    email: '',
    phone: '',
    nationality: '',
    state: '',
    city: '',
    address: '',
    postalCode: '',
    type: 'store',
    sizeSqft: '',
    capacity: '',
    openingDate: '',
    monthlyRent: '',
  }
}

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

const nextStep = () => {
  if (currentStep.value < 2) {
    currentStep.value++
    error.value = null
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
    error.value = null
  }
}

// Reset form when modal opens
watch(() => props.isDialogVisible, visible => {
  if (visible)
    resetForm()
})

const submitForm = async () => {
  if (!formData.value.name || !formData.value.email || !formData.value.phone) {
    error.value = 'Please fill in all required fields'

    return
  }

  loading.value = true
  error.value = null

  try {
    // Get the franchisor's franchise ID
    const franchiseResponse = await franchiseApi.getFranchises()
    const franchiseId = franchiseResponse.data?.[0]?.id || null

    // Prepare franchisee and unit data
    const franchiseeUnitData = {
      // Franchisee details
      name: formData.value.name,
      email: formData.value.email,
      phone: formData.value.phone,

      // Unit details
      unit_name: formData.value.name, // Use same name for unit
      franchise_id: franchiseId,
      unit_type: formData.value.type || 'store',
      address: formData.value.address,
      city: formData.value.city,
      state_province: formData.value.state,
      postal_code: formData.value.postalCode,
      nationality: formData.value.nationality,
      size_sqft: formData.value.sizeSqft,
      monthly_rent: formData.value.monthlyRent,
      opening_date: formData.value.openingDate,
      status: 'planning',
    }

    // Create franchisee with unit
    const response = await franchiseApi.createFranchiseeWithUnit(franchiseeUnitData)

    if (response.success) {
      // Transform the response to match expected format
      const transformedData = {
        id: response.data.unit.id,
        name: response.data.unit.unit_name,
        email: response.data.franchisee.email,
        phone: response.data.franchisee.phone,
        type: response.data.unit.unit_type,
        sizeSqft: response.data.unit.size_sqft,
        capacity: null, // Not available in unit response
        openingDate: response.data.unit.opening_date,
        monthlyRent: response.data.unit.monthly_rent,
        managerId: response.data.unit.franchisee_id,
        managerName: response.data.franchisee.name,
        nationality: response.data.unit.nationality,
        state: response.data.unit.state_province,
        city: response.data.unit.city,
        address: response.data.unit.address,
        postalCode: response.data.unit.postal_code,
      }

      emit('franchiseeAdded', transformedData)
      updateModelValue(false)
      resetForm()
    }
  }
  catch (err: any) {
    console.error('Error creating franchisee with unit:', err)
    error.value = err.response?.data?.message || 'Failed to create franchisee and unit. Please try again.'
  }
  finally {
    loading.value = false
  }
}

const onDialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    resetForm()
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="800"
    @update:model-value="onDialogModelValueUpdate"
  >
    <VCard>
      <VCardTitle class="text-center">
        <span class="text-h5">Add New Franchisee</span>
      </VCardTitle>

      <VCardText>
        <!-- Error Alert -->
        <VAlert
          v-if="error"
          type="error"
          variant="tonal"
          class="mb-4"
          closable
          @click:close="error = null"
        >
          {{ error }}
        </VAlert>

        <!-- Stepper -->
        <VStepper
          v-model="currentStep"
          alt-labels
        >
          <VStepperHeader>
            <VStepperItem
              :complete="currentStep > 1"
              :value="1"
              title="Basic Info"
            />
            <VDivider />
            <VStepperItem
              :value="2"
              title="Unit Details"
            />
          </VStepperHeader>

          <VStepperWindow>
            <!-- Step 1: Basic Info -->
            <VStepperWindowItem :value="1">
              <VForm>
                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="formData.name"
                      label="Branch Name"
                      placeholder="Enter Branch name"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppTextField
                      v-model="formData.email"
                      label="Email Address"
                      placeholder="Enter email address"
                      :rules="[requiredValidator, emailValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppTextField
                      v-model="formData.phone"
                      label="Contact Number"
                      placeholder="Enter contact number"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppSelect
                      v-model="formData.nationality"
                      :items="nationalityOptions"
                      :loading="isLoadingCountries"
                      label="Nationality"
                      placeholder="Select Nationality"
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppSelect
                      v-model="formData.state"
                      label="Province"
                      placeholder="Select Province"
                      :items="provinces"
                      :loading="isLoadingProvinces"
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppSelect
                      v-model="formData.city"
                      label="City"
                      placeholder="Select City"
                      :items="availableCities"
                      :disabled="!formData.state"
                      :rules="[requiredValidator]"
                      clearable
                    />
                  </VCol>

                  <VCol cols="12">
                    <AppTextarea
                      v-model="formData.address"
                      label="Address"
                      placeholder="Enter full address"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol cols="12">
                    <VAlert
                      type="info"
                      variant="tonal"
                      class="mb-0"
                    >
                      <strong>{{ formData.name || 'New franchisee' }}</strong> will be automatically assigned as the
                      unit manager.
                    </VAlert>
                  </VCol>

                  <!--
                    <VCol cols="12" md="6">
                    <AppTextField v-model="formData.postalCode" label="Postal Code" hidden
                    placeholder="Enter postal code" />
                    </VCol>
                  -->
                </VRow>
              </VForm>
            </VStepperWindowItem>

            <!-- Step 2: Unit Details -->
            <VStepperWindowItem :value="2">
              <VForm>
                <VRow>
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppSelect
                      v-model="formData.type"
                      :items="unitTypes"
                      label="Unit Type"
                      placeholder="Select unit type"
                      :rules="[requiredValidator]"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="6"
                  >
                    <AppDateTimePicker
                      v-model="formData.openingDate"
                      label="Opening Date"
                      placeholder="Select opening date"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppTextField
                      v-model="formData.sizeSqft"
                      label="Size (sq ft)"
                      placeholder="Enter size in square feet"
                      type="number"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppTextField
                      v-model="formData.capacity"
                      label="Capacity"
                      placeholder="Enter capacity"
                      type="number"
                    />
                  </VCol>

                  <VCol
                    cols="12"
                    md="4"
                  >
                    <AppTextField
                      v-model="formData.monthlyRent"
                      label="Monthly Rent"
                      placeholder="Enter monthly rent"
                      type="number"
                      prefix="SAR"
                    />
                  </VCol>
                </VRow>
              </VForm>
            </VStepperWindowItem>
          </VStepperWindow>
        </VStepper>
      </VCardText>

      <VCardActions class="justify-space-between pa-6">
        <VBtn
          v-if="currentStep > 1"
          variant="outlined"
          :disabled="loading"
          @click="prevStep"
        >
          Previous
        </VBtn>
        <VSpacer v-else />

        <div class="d-flex gap-3">
          <VBtn
            variant="outlined"
            :disabled="loading"
            @click="updateModelValue(false)"
          >
            Cancel
          </VBtn>

          <VBtn
            v-if="currentStep < 2"
            color="primary"
            :disabled="loading"
            @click="nextStep"
          >
            Next
          </VBtn>

          <VBtn
            v-else
            color="primary"
            :loading="loading"
            :disabled="loading"
            @click="submitForm"
          >
            Create Unit
          </VBtn>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
