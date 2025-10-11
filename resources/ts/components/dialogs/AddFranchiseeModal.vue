<script setup lang="ts">
import { emailValidator, requiredValidator } from '@core/utils/validators';

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'franchisee-added', data: any): void
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
  country: '',
  state: '',
  city: '',
  address: '',
  postalCode: '',
  type: 'store' as const,
  sizeSqft: '',
  capacity: '',
  openingDate: '',
  monthlyRent: '',
  managerId: null as string | null,
})

// Data
const franchisees = ref([])
const countries = [
  { title: 'United States', value: 'US' },
  { title: 'Canada', value: 'CA' },
  { title: 'United Kingdom', value: 'UK' },
  { title: 'Australia', value: 'AU' },
  { title: 'Saudi Arabia', value: 'SA' },
]

const unitTypes = [
  { title: 'Store', value: 'store' },
  { title: 'Kiosk', value: 'kiosk' },
  { title: 'Mobile', value: 'mobile' },
  { title: 'Online', value: 'online' },
  { title: 'Warehouse', value: 'warehouse' },
  { title: 'Office', value: 'office' },
]

// Load available franchisees (users with franchisee role)
const loadFranchisees = async () => {
  try {
    const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/franchisees')
    if (response.success && response.data.data) {
      franchisees.value = response.data.data.map((franchisee: any) => ({
        id: franchisee.id,
        name: franchisee.name,
        email: franchisee.email,
        phone: franchisee.phone,
      }))
    }
  } catch (err: any) {
    console.error('Failed to load franchisees:', err)
  }
}

// Load franchisees when modal opens
watch(() => props.isDialogVisible, (visible) => {
  if (visible) {
    loadFranchisees()
    resetForm()
  }
})

// Methods
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

const resetForm = () => {
  currentStep.value = 1
  error.value = null
  formData.value = {
    name: '',
    email: '',
    phone: '',
    country: '',
    state: '',
    city: '',
    address: '',
    postalCode: '',
    type: 'store',
    sizeSqft: '',
    capacity: '',
    openingDate: '',
    monthlyRent: '',
    managerId: null,
  }
}

const submitForm = async () => {
  loading.value = true
  error.value = null

  try {
    // Get franchise ID from current user's franchise
    const franchiseResponse = await $api<{ success: boolean; data: any }>('/v1/franchisor/franchise')
    if (!franchiseResponse.success || !franchiseResponse.data) {
      throw new Error('Franchise not found')
    }

    const franchiseId = franchiseResponse.data.id

    // Prepare unit data
    const unitData = {
      unit_name: formData.value.name,
      franchise_id: franchiseId,
      unit_type: formData.value.type,
      address: formData.value.address,
      city: formData.value.city,
      state_province: formData.value.state,
      postal_code: formData.value.postalCode,
      country: formData.value.country,
      phone: formData.value.phone,
      email: formData.value.email,
      franchisee_id: formData.value.managerId,
      size_sqft: formData.value.sizeSqft ? Number(formData.value.sizeSqft) : null,
      opening_date: formData.value.openingDate || null,
      monthly_rent: formData.value.monthlyRent ? Number(formData.value.monthlyRent) : null,
      status: 'planning', // Start as planning until activated
    }

    // Create the unit
    const response = await $api<{ success: boolean; data: any }>('/v1/units', {
      method: 'POST',
      body: unitData,
    })

    if (response.success && response.data) {
      // Find selected manager info
      const selectedManager = franchisees.value.find(f => f.id === formData.value.managerId)

      // Transform data for frontend compatibility
      const franchiseeData = {
        branchName: response.data.unit_name,
        franchiseeName: selectedManager?.name || 'Unassigned',
        email: formData.value.email,
        contactNumber: formData.value.phone,
        address: formData.value.address,
        city: formData.value.city,
        state: formData.value.state,
        country: formData.value.country,
        royaltyPercentage: 8.5, // Default - would come from franchise settings
        contractStartDate: response.data.opening_date || new Date().toISOString().split('T')[0],
        renewalDate: '', // Would be calculated based on contract terms
        status: 'planning',
      }

      emit('franchisee-added', franchiseeData)
      resetForm()
      updateModelValue(false)
    } else {
      throw new Error('Failed to create unit')
    }
  } catch (err: any) {
    console.error('Failed to create unit:', err)
    error.value = err?.data?.message || 'Failed to create unit. Please try again.'
  } finally {
    loading.value = false
  }
}

const onDialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val) {
    resetForm()
  }
}
</script>

<template>
  <VDialog :model-value="props.isDialogVisible" max-width="800" @update:model-value="onDialogModelValueUpdate">
    <VCard>
      <VCardTitle class="text-center">
        <span class="text-h5">Add New Franchisee</span>
      </VCardTitle>

      <VCardText>
        <!-- Error Alert -->
        <VAlert v-if="error" type="error" variant="tonal" class="mb-4" closable @click:close="error = null">
          {{ error }}
        </VAlert>

        <!-- Stepper -->
        <VStepper v-model="currentStep" alt-labels>
          <VStepperHeader>
            <VStepperItem :complete="currentStep > 1" :value="1" title="Basic Info" />
            <VDivider />
            <VStepperItem :value="2" title="Unit Details" />
          </VStepperHeader>

          <VStepperWindow>
            <!-- Step 1: Basic Info -->
            <VStepperWindowItem :value="1">
              <VForm>
                <VRow>
                  <VCol cols="12">
                    <AppTextField v-model="formData.name" label="Unit Name" placeholder="Enter unit name"
                      :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppTextField v-model="formData.email" label="Email Address" placeholder="Enter email address"
                      :rules="[requiredValidator, emailValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppTextField v-model="formData.phone" label="Contact Number"
                      placeholder="Enter contact number" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppSelect v-model="formData.country" :items="countries" label="Country"
                      placeholder="Select country" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppTextField v-model="formData.state" label="State" placeholder="Enter state"
                      :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppTextField v-model="formData.city" label="City" placeholder="Enter city"
                      :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12">
                    <AppTextarea v-model="formData.address" label="Address" placeholder="Enter full address"
                      :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppSelect v-model="formData.managerId" :items="franchisees" item-title="name" item-value="id"
                      label="Assign Manager (Franchisee)" placeholder="Choose a manager" clearable />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppTextField v-model="formData.postalCode" label="Postal Code" placeholder="Enter postal code" />
                  </VCol>
                </VRow>
              </VForm>
            </VStepperWindowItem>

            <!-- Step 2: Unit Details -->
            <VStepperWindowItem :value="2">
              <VForm>
                <VRow>
                  <VCol cols="12" md="6">
                    <AppSelect v-model="formData.type" :items="unitTypes" label="Unit Type"
                      placeholder="Select unit type" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppDateTimePicker v-model="formData.openingDate" label="Opening Date"
                      placeholder="Select opening date" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppTextField v-model="formData.sizeSqft" label="Size (sq ft)" placeholder="Enter size in square feet"
                      type="number" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppTextField v-model="formData.capacity" label="Capacity" placeholder="Enter capacity"
                      type="number" />
                  </VCol>

                  <VCol cols="12" md="4">
                    <AppTextField v-model="formData.monthlyRent" label="Monthly Rent" placeholder="Enter monthly rent"
                      type="number" prefix="$" />
                  </VCol>
                </VRow>
              </VForm>
            </VStepperWindowItem>
          </VStepperWindow>
        </VStepper>
      </VCardText>

      <VCardActions class="justify-space-between pa-6">
        <VBtn v-if="currentStep > 1" variant="outlined" @click="prevStep" :disabled="loading">
          Previous
        </VBtn>
        <VSpacer v-else />

        <div class="d-flex gap-3">
          <VBtn variant="outlined" @click="updateModelValue(false)" :disabled="loading">
            Cancel
          </VBtn>

          <VBtn v-if="currentStep < 2" color="primary" @click="nextStep" :disabled="loading">
            Next
          </VBtn>

          <VBtn v-else color="primary" @click="submitForm" :loading="loading" :disabled="loading">
            Create Unit
          </VBtn>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
