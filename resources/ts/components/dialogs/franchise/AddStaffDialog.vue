<script setup lang="ts">
import { franchiseStaffApi, type CreateStaffPayload } from '@/services/api'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'staffCreated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formData = ref<CreateStaffPayload>({
  name: '',
  email: '',
  phone: '',
  job_title: '',
  department: '',
  salary: undefined,
  hire_date: '',
  shift_start: '',
  shift_end: '',
  status: 'active',
  employment_type: 'full_time',
  notes: '',
})

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'On Leave', value: 'on_leave' },
  { title: 'Terminated', value: 'terminated' },
  { title: 'Inactive', value: 'inactive' },
]

const employmentTypeOptions = [
  { title: 'Full Time', value: 'full_time' },
  { title: 'Part Time', value: 'part_time' },
  { title: 'Contract', value: 'contract' },
  { title: 'Temporary', value: 'temporary' },
]

const resetForm = () => {
  formData.value = {
    name: '',
    email: '',
    phone: '',
    job_title: '',
    department: '',
    salary: undefined,
    hire_date: '',
    shift_start: '',
    shift_end: '',
    status: 'active',
    employment_type: 'full_time',
    notes: '',
  }
}

const onSubmit = async () => {
  try {
    const response = await franchiseStaffApi.createStaff(formData.value)

    if (response.success) {
      emit('staffCreated')
      dialogValue.value = false
      resetForm()
    }
  }
  catch (error) {
    console.error('Error creating staff:', error)
  }
}

const onCancel = () => {
  dialogValue.value = false
  resetForm()
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="800"
  >
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Add Staff Member">
      <VCardText>
        <VForm @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.name"
                label="Name"
                placeholder="Enter name"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.email"
                label="Email"
                placeholder="Enter email"
                type="email"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.phone"
                label="Phone"
                placeholder="Enter phone number"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.job_title"
                label="Job Title"
                placeholder="Enter job title"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.department"
                label="Department"
                placeholder="Enter department"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model.number="formData.salary"
                label="Salary"
                placeholder="Enter salary"
                type="number"
                prefix="SAR"
              />
            </VCol>

            <VCol cols="12" md="4">
              <AppDateTimePicker
                v-model="formData.hire_date"
                label="Hire Date"
                placeholder="Select hire date"
                required
              />
            </VCol>

            <VCol cols="12" md="4">
              <AppTextField
                v-model="formData.shift_start"
                label="Shift Start"
                placeholder="HH:MM"
                type="time"
              />
            </VCol>

            <VCol cols="12" md="4">
              <AppTextField
                v-model="formData.shift_end"
                label="Shift End"
                placeholder="HH:MM"
                type="time"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="formData.status"
                label="Status"
                :items="statusOptions"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="formData.employment_type"
                label="Employment Type"
                :items="employmentTypeOptions"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="formData.notes"
                label="Notes"
                placeholder="Enter any additional notes"
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
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="onSubmit"
        >
          Add Staff
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

