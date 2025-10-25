<script setup lang="ts">
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'
import { useDisplay } from 'vuetify'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface UserData {
  id: number | null
  fullName: string
  company: string
  username: string
  role: string
  nationality: string
  state: string
  city: string
  contact: string | undefined
  email: string | undefined
  currentPlan: string
  status: string | undefined
  avatar: string
  taskDone: number | null
  projectDone: number | null
  taxId: string
  language: string
}

interface Props {
  userData?: UserData
  isDialogVisible: boolean
}

interface Emit {
  (e: 'submit', value: UserData): void
  (e: 'update:isDialogVisible', val: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
  userData: () => ({
    id: 0,
    fullName: '',
    company: '',
    role: '',
    username: '',
    nationality: '',
    state: '',
    city: '',
    contact: '',
    email: '',
    currentPlan: '',
    status: '',
    avatar: '',
    taskDone: null,
    projectDone: null,
    taxId: '',
    language: '',
  }),
})

const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const userData = ref<UserData>(structuredClone(toRaw(props.userData)))
const isUseAsBillingAddress = ref(false)

watch(() => props, () => {
  userData.value = structuredClone(toRaw(props.userData))
})

const onFormSubmit = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    emit('update:isDialogVisible', false)
    emit('submit', userData.value)
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const onFormReset = () => {
  userData.value = structuredClone(toRaw(props.userData))

  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

// Get countries from composable
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()

// Get Saudi provinces and cities
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

// Available cities based on selected province
const availableCities = computed(() => getCitiesForProvince(userData.value.state || ''))

const { smAndUp } = useDisplay()
</script>

<template>
  <VDialog :width="smAndUp ? 'auto' : 900" :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Edit User Information
        </h4>
        <p class="text-body-1 text-center mb-6">
          Updating user details will receive a privacy audit.
        </p>

        <!-- 👉 Form -->
        <VForm ref="formRef" class="mt-6" @submit.prevent="onFormSubmit">
          <VRow>
            <!-- 👉 Full Name -->
            <VCol cols="12">
              <AppTextField 
                v-model="userData.fullName" 
                label="Full Name" 
                placeholder="John Doe"
                :rules="[rules.required('Full name is required'), rules.maxLength(100)]"
                :error-messages="backendErrors.fullName"
                @input="clearError('fullName')"
              />
            </VCol>

            <!-- 👉 Username -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="userData.username" 
                label="Username" 
                placeholder="Username"
                :rules="[rules.required('Username is required'), rules.maxLength(50)]"
                :error-messages="backendErrors.username"
                @input="clearError('username')"
              />
            </VCol>

            <!-- 👉 Billing Email -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="userData.email" 
                label="Email" 
                placeholder="Email"
                :rules="[rules.required('Email is required'), rules.email()]"
                :error-messages="backendErrors.email"
                @input="clearError('email')"
              />
            </VCol>

            <!-- 👉 Status -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="userData.status" 
                label="Status" 
                placeholder="Active"
                :items="['Active', 'Inactive', 'Pending']"
                :rules="[rules.required('Status is required')]"
                :error-messages="backendErrors.status"
                @update:model-value="clearError('status')"
              />
            </VCol>

            <!-- 👉 Tax Id -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="userData.taxId" 
                label="Tax ID" 
                placeholder="123456789"
                :rules="[rules.maxLength(50)]"
                :error-messages="backendErrors.taxId"
                @input="clearError('taxId')"
              />
            </VCol>

            <!-- 👉 Contact -->
            <VCol cols="12" md="6">
              <AppTextField 
                v-model="userData.contact" 
                label="Phone Number" 
                placeholder="+966 50 123 4567"
                :rules="[rules.phone()]"
                :error-messages="backendErrors.contact"
                @input="clearError('contact')"
              />
            </VCol>

            <!-- 👉 Language -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="userData.language" 
                closable-chips 
                chips 
                multiple 
                label="Language"
                placeholder="English" 
                :items="['English', 'Spanish', 'French']"
                :error-messages="backendErrors.language"
                @update:model-value="clearError('language')"
              />
            </VCol>

            <!-- 👉 Nationality -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="userData.nationality" 
                label="Nationality" 
                placeholder="Select Nationality"
                :items="nationalityOptions" 
                :loading="isLoadingCountries" 
                clearable
                :rules="[rules.required('Nationality is required')]"
                :error-messages="backendErrors.nationality"
                @update:model-value="clearError('nationality')"
              />
            </VCol>

            <!-- 👉 Province -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="userData.state" 
                label="Province" 
                placeholder="Select Province" 
                :items="provinces"
                :loading="isLoadingProvinces" 
                clearable 
                required
                :rules="[rules.required('Province is required')]"
                :error-messages="backendErrors.state"
                @update:model-value="clearError('state')"
              />
            </VCol>

            <!-- 👉 City -->
            <VCol cols="12" md="6">
              <AppSelect 
                v-model="userData.city" 
                label="City" 
                placeholder="Select City" 
                :items="availableCities"
                :disabled="!userData.state" 
                clearable 
                required
                :rules="[rules.required('City is required')]"
                :error-messages="backendErrors.city"
                @update:model-value="clearError('city')"
              />
            </VCol>

            <!-- 👉 Switch -->
            <VCol cols="12">
              <VSwitch v-model="isUseAsBillingAddress" density="compact" label="Use as a billing address?" />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol cols="12" class="d-flex flex-wrap justify-center gap-4">
              <VBtn type="submit">
                Submit
              </VBtn>

              <VBtn color="secondary" variant="tonal" @click="onFormReset">
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
