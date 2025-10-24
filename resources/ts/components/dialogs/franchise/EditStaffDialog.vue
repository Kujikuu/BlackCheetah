<script setup lang="ts">
import { franchiseStaffApi, type FranchiseStaff, type UpdateStaffPayload } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  staff?: FranchiseStaff | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'staffUpdated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formData = ref<UpdateStaffPayload>({
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

watch(() => props.staff, (staff) => {
  if (staff) {
    formData.value = {
      name: staff.name,
      email: staff.email,
      phone: staff.phone || '',
      job_title: staff.jobTitle,
      department: staff.department || '',
      salary: staff.salary || undefined,
      hire_date: staff.hireDate,
      shift_start: staff.shiftStart || '',
      shift_end: staff.shiftEnd || '',
      status: staff.status,
      employment_type: staff.employmentType,
      notes: staff.notes || '',
    }
  }
}, { immediate: true })

const onSubmit = async () => {
  if (!props.staff)
    return

  try {
    const response = await franchiseStaffApi.updateStaff(props.staff.id, formData.value)

    if (response.success) {
      emit('staffUpdated')
      dialogValue.value = false
    }
  }
  catch (error) {
    console.error('Error updating staff:', error)
  }
}

const onCancel = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="800"
  >
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Edit Staff Member">
      <VCardText>
        <VForm @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.name"
                label="Name"
                placeholder="Enter name"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="formData.email"
                label="Email"
                placeholder="Enter email"
                type="email"
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
          Update Staff
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

