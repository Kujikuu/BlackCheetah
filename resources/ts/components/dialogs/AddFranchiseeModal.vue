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
const formData = ref({
  franchiseeId: '',
  branchName: '',
  email: '',
  contactNumber: '',
  country: '',
  state: '',
  city: '',
  address: '',
  royaltyPercentage: 0,
  contractStartDate: '',
  renewalDate: '',
})

// Mock data
const franchisees = [
  { id: '1', name: 'John Smith', email: 'john@example.com' },
  { id: '2', name: 'Sarah Johnson', email: 'sarah@example.com' },
  { id: '3', name: 'Mike Wilson', email: 'mike@example.com' },
]

const countries = [
  { title: 'United States', value: 'US' },
  { title: 'Canada', value: 'CA' },
  { title: 'United Kingdom', value: 'UK' },
  { title: 'Australia', value: 'AU' },
]

// Methods
const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

const nextStep = () => {
  if (currentStep.value < 2) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const resetForm = () => {
  currentStep.value = 1
  formData.value = {
    franchiseeId: '',
    branchName: '',
    email: '',
    contactNumber: '',
    country: '',
    state: '',
    city: '',
    address: '',
    royaltyPercentage: 0,
    contractStartDate: '',
    renewalDate: '',
  }
}

const submitForm = () => {
  // Find selected franchisee
  const selectedFranchisee = franchisees.find(f => f.id === formData.value.franchiseeId)

  const franchiseeData = {
    id: Date.now().toString(),
    franchisee: selectedFranchisee?.name || '',
    branchName: formData.value.branchName,
    email: formData.value.email,
    contactNumber: formData.value.contactNumber,
    location: `${formData.value.city}, ${formData.value.state}, ${formData.value.country}`,
    address: formData.value.address,
    royaltyPercentage: formData.value.royaltyPercentage,
    contractStartDate: formData.value.contractStartDate,
    renewalDate: formData.value.renewalDate,
    status: 'Active',
    monthlyRoyalty: Math.floor(Math.random() * 5000) + 1000,
  }

  emit('franchisee-added', franchiseeData)
  resetForm()
  updateModelValue(false)
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
        <!-- Stepper -->
        <VStepper v-model="currentStep" alt-labels>
          <VStepperHeader>
            <VStepperItem :complete="currentStep > 1" :value="1" title="Basic Info" />
            <VDivider />
            <VStepperItem :value="2" title="Franchisee Details" />
          </VStepperHeader>

          <VStepperWindow>
            <!-- Step 1: Basic Info -->
            <VStepperWindowItem :value="1">
              <VForm>
                <VRow>
                  <VCol cols="12">
                    <AppSelect v-model="formData.franchiseeId" :items="franchisees" item-title="name" item-value="id"
                      label="Select Franchisee" placeholder="Choose a franchisee" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12">
                    <AppTextField v-model="formData.branchName" label="Branch Name" placeholder="Enter branch name"
                      :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppTextField v-model="formData.email" label="Email Address" placeholder="Enter email address"
                      :rules="[requiredValidator, emailValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppTextField v-model="formData.contactNumber" label="Contact Number"
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
                </VRow>
              </VForm>
            </VStepperWindowItem>

            <!-- Step 2: Franchisee Details -->
            <VStepperWindowItem :value="2">
              <VForm>
                <VRow>
                  <VCol cols="12">
                    <AppTextField v-model="formData.royaltyPercentage" label="Royalty Percentage"
                      placeholder="Enter royalty percentage" type="number" suffix="%" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppDateTimePicker v-model="formData.contractStartDate" label="Contract Start Date"
                      placeholder="Select start date" :rules="[requiredValidator]" />
                  </VCol>

                  <VCol cols="12" md="6">
                    <AppDateTimePicker v-model="formData.renewalDate" label="Renewal Date"
                      placeholder="Select renewal date" :rules="[requiredValidator]" />
                  </VCol>
                </VRow>
              </VForm>
            </VStepperWindowItem>
          </VStepperWindow>
        </VStepper>
      </VCardText>

      <VCardActions class="justify-space-between pa-6">
        <VBtn v-if="currentStep > 1" variant="outlined" @click="prevStep">
          Previous
        </VBtn>
        <VSpacer v-else />

        <div class="d-flex gap-3">
          <VBtn variant="outlined" @click="updateModelValue(false)">
            Cancel
          </VBtn>

          <VBtn v-if="currentStep < 2" color="primary" @click="nextStep">
            Next
          </VBtn>

          <VBtn v-else color="primary" @click="submitForm">
            Create Franchisee
          </VBtn>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
