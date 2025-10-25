<script setup lang="ts">
import { leadApi, type Lead } from '@/services/api'
import { useCountries } from '@/composables/useCountries'
import { useSaudiProvinces } from '@/composables/useSaudiProvinces'
import { useFormValidation } from '@/composables/useFormValidation'
import { useUpdateLeadValidation } from '@/validation/leadsValidation'

interface Props {
  isDialogVisible: boolean
  lead: Lead | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'leadUpdated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const isSaving = ref(false)
const formRef = ref()

// Form validation
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useUpdateLeadValidation()

// Form data
const formData = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  company: '',
  jobTitle: '',
  nationality: '',
  state: '',
  city: '',
  source: '',
  status: '',
  priority: '',
  note: '',
})

// Options
const { countries: nationalityOptions, isLoading: isLoadingCountries } = useCountries()
const { provinces, getCitiesForProvince, isLoading: isLoadingProvinces } = useSaudiProvinces()

const availableCities = computed(() => getCitiesForProvince(formData.value.state || ''))

// Watch province changes to clear city only if necessary
watch(() => formData.value.state, (newState, oldState) => {
  if (oldState !== undefined && oldState !== newState) {
    const cities = getCitiesForProvince(newState || '')
    const currentCity = formData.value.city
    if (currentCity && !cities.includes(currentCity)) {
      formData.value.city = ''
    }
  }
})

const leadSources = [
  { title: 'Website', value: 'website' },
  { title: 'Referral', value: 'referral' },
  { title: 'Social Media', value: 'social_media' },
  { title: 'Advertisement', value: 'advertisement' },
  { title: 'Cold Call', value: 'cold_call' },
  { title: 'Event', value: 'event' },
  { title: 'Other', value: 'other' },
]

const leadStatuses = [
  { title: 'New', value: 'new' },
  { title: 'Contacted', value: 'contacted' },
  { title: 'Qualified', value: 'qualified' },
  { title: 'Proposal Sent', value: 'proposal_sent' },
  { title: 'Negotiating', value: 'negotiating' },
  { title: 'Closed Won', value: 'closed_won' },
  { title: 'Closed Lost', value: 'closed_lost' },
]

const priorities = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
]

// Watch for lead changes
watch(() => props.lead, (lead) => {
  if (lead) {
    formData.value = {
      firstName: lead.firstName || '',
      lastName: lead.lastName || '',
      email: lead.email || '',
      phone: lead.phone || '',
      company: lead.company || '',
      jobTitle: lead.jobTitle || '',
      nationality: lead.nationality || '',
      state: lead.state || '',
      city: lead.city || '',
      source: lead.source || '',
      status: lead.status || '',
      priority: lead.priority || 'medium',
      note: lead.note || '',
    }
  }
}, { immediate: true })

const onSubmit = async () => {
  if (!props.lead) return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    isSaving.value = true

    const response = await leadApi.updateLead(props.lead.id, {
      firstName: formData.value.firstName,
      lastName: formData.value.lastName,
      email: formData.value.email,
      phone: formData.value.phone,
      company: formData.value.company || undefined,
      jobTitle: formData.value.jobTitle || undefined,
      nationality: formData.value.nationality || undefined,
      state: formData.value.state || undefined,
      city: formData.value.city || undefined,
      source: formData.value.source || undefined,
      status: formData.value.status as any,
      priority: formData.value.priority || undefined,
      note: formData.value.note || undefined,
    })

    if (response.success) {
      emit('leadUpdated')
      dialogValue.value = false
    }
  }
  catch (error: any) {
    console.error('Error updating lead:', error)
    // Map backend validation errors to form fields
    setBackendErrors(error)
  }
  finally {
    isSaving.value = false
  }
}

const onCancel = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="900"
    scrollable
  >
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Edit Lead">
      <VCardText>
        <VForm
          ref="formRef"
          @submit.prevent="onSubmit"
        >
          <VRow>
            <!-- First Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.firstName"
                label="First Name"
                placeholder="Enter first name"
                :rules="validationRules.firstName"
                :error-messages="backendErrors.firstName"
                @input="clearError('firstName')"
              />
            </VCol>

            <!-- Last Name -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.lastName"
                label="Last Name"
                placeholder="Enter last name"
                :rules="validationRules.lastName"
                :error-messages="backendErrors.lastName"
                @input="clearError('lastName')"
              />
            </VCol>

            <!-- Email -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.email"
                label="Email"
                type="email"
                placeholder="Enter email address"
                :rules="validationRules.email"
                :error-messages="backendErrors.email"
                @input="clearError('email')"
              />
            </VCol>

            <!-- Phone -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.phone"
                label="Phone"
                placeholder="+966 50 123 4567"
                :rules="validationRules.phone"
                :error-messages="backendErrors.phone"
                @input="clearError('phone')"
              />
            </VCol>

            <!-- Company -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.company"
                label="Company Name"
                placeholder="Enter company name"
              />
            </VCol>

            <!-- Job Title -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="formData.jobTitle"
                label="Job Title"
                placeholder="Enter job title"
              />
            </VCol>

            <!-- Nationality -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.nationality"
                label="Nationality"
                placeholder="Select nationality"
                :items="nationalityOptions"
                :loading="isLoadingCountries"
                :rules="validationRules.nationality"
                :error-messages="backendErrors.nationality"
                @update:model-value="clearError('nationality')"
                clearable
              />
            </VCol>

            <!-- Province -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.state"
                label="Province"
                placeholder="Select province"
                :items="provinces"
                :loading="isLoadingProvinces"
                :error-messages="backendErrors.state"
                @update:model-value="clearError('state')"
                clearable
              />
            </VCol>

            <!-- City -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.city"
                label="City"
                placeholder="Select city"
                :items="availableCities"
                :disabled="!formData.state"
                :rules="validationRules.city"
                :error-messages="backendErrors.city"
                @update:model-value="clearError('city')"
                clearable
              />
            </VCol>

            <!-- Source -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.source"
                label="Lead Source"
                :items="leadSources"
                placeholder="Select source"
                :rules="validationRules.source"
                :error-messages="backendErrors.source"
                @update:model-value="clearError('source')"
              />
            </VCol>

            <!-- Status -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.status"
                label="Status"
                :items="leadStatuses"
                placeholder="Select status"
                :rules="validationRules.status"
                :error-messages="backendErrors.status"
                @update:model-value="clearError('status')"
              />
            </VCol>

            <!-- Priority -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.priority"
                label="Priority"
                :items="priorities"
                placeholder="Select priority"
                :rules="validationRules.priority"
                :error-messages="backendErrors.priority"
                @update:model-value="clearError('priority')"
              />
            </VCol>

            <!-- Note -->
            <VCol cols="12">
              <AppTextarea
                v-model="formData.note"
                label="Notes"
                placeholder="Add notes about this lead"
                rows="3"
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isSaving"
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isSaving"
          :disabled="isSaving"
          @click="onSubmit"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
