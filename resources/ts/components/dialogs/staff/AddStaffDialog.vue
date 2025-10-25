<script setup lang="ts">
import { ref, computed } from 'vue'
import { useFormValidation } from '@/composables/useFormValidation'
import { useStoreStaffValidation } from '@/validation/staffValidation'

interface StaffForm {
  name: string
  email: string
  phone: string
  jobTitle: string
  department: string
  salary: number
  hireDate: string
  shiftStart: string
  shiftEnd: string
  status: string
  employmentType: string
  notes: string
}

interface Props {
  isDialogVisible: boolean
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'staffAdded', staff: StaffForm): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useStoreStaffValidation()

const staffForm = ref<StaffForm>({
  name: '',
  email: '',
  phone: '',
  jobTitle: '',
  department: '',
  salary: 0,
  hireDate: '',
  shiftStart: '',
  shiftEnd: '',
  status: 'Active',
  employmentType: 'full_time',
  notes: '',
})

const handleAddStaff = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    emit('staffAdded', { ...staffForm.value })
    
    // Reset form
    staffForm.value = {
      name: '',
      email: '',
      phone: '',
      jobTitle: '',
      department: '',
      salary: 0,
      hireDate: '',
      shiftStart: '',
      shiftEnd: '',
      status: 'Active',
      employmentType: 'full_time',
      notes: '',
    }
    
    dialogValue.value = false
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const handleClose = () => {
  dialogValue.value = false
  // Reset form on close
  staffForm.value = {
    name: '',
    email: '',
    phone: '',
    jobTitle: '',
    department: '',
    salary: 0,
    hireDate: '',
    shiftStart: '',
    shiftEnd: '',
    status: 'Active',
    employmentType: 'full_time',
    notes: '',
  }
}

// Status options for select
const statusOptions = [
  'Active',
  'On Leave',
  'Terminated',
  'Inactive'
]

// Employment type options for select
const employmentTypeOptions = [
  { title: 'Full Time', value: 'full_time' },
  { title: 'Part Time', value: 'part_time' },
  { title: 'Contract', value: 'contract' },
  { title: 'Temporary', value: 'temporary' },
]
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Add New Staff Member">
      <VCardText class="pa-6">
        <VForm ref="formRef">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="staffForm.name"
                label="Full Name"
                :rules="validationRules.name"
                :error-messages="backendErrors.name"
                @input="clearError('name')"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="staffForm.email"
                label="Email"
                type="email"
                :rules="validationRules.email"
                :error-messages="backendErrors.email"
                @input="clearError('email')"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="staffForm.phone"
                label="Phone"
                :rules="validationRules.phone"
                :error-messages="backendErrors.phone"
                @input="clearError('phone')"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="staffForm.jobTitle"
                label="Job Title"
                :rules="validationRules.jobTitle"
                :error-messages="backendErrors.jobTitle"
                @input="clearError('jobTitle')"
                required
              />
            </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="staffForm.department"
              label="Department"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="staffForm.salary"
              label="Salary"
              type="number"
              prefix="SAR"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="staffForm.hireDate"
              label="Hire Date"
              type="date"
              :rules="validationRules.hireDate"
              :error-messages="backendErrors.hireDate"
              @input="clearError('hireDate')"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="staffForm.shiftStart"
              label="Shift Start"
              type="time"
              :rules="validationRules.shiftStart"
              :error-messages="backendErrors.shiftStart"
              @input="clearError('shiftStart')"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="staffForm.shiftEnd"
              label="Shift End"
              type="time"
              :rules="validationRules.shiftEnd"
              :error-messages="backendErrors.shiftEnd"
              @input="clearError('shiftEnd')"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="staffForm.status"
              label="Status"
              :items="statusOptions"
              :rules="validationRules.status"
              :error-messages="backendErrors.status"
              @update:model-value="clearError('status')"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="staffForm.employmentType"
              label="Employment Type"
              :items="employmentTypeOptions"
              :rules="validationRules.employmentType"
              :error-messages="backendErrors.employmentType"
              @update:model-value="clearError('employmentType')"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="staffForm.notes"
              label="Notes"
              rows="2"
              :rules="validationRules.notes"
              :error-messages="backendErrors.notes"
              @input="clearError('notes')"
            />
          </VCol>
        </VRow>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!staffForm.name || !staffForm.email || !staffForm.jobTitle || !staffForm.hireDate || !staffForm.status"
          @click="handleAddStaff"
        >
          Add Staff
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
