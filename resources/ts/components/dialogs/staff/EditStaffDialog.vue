<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitStaff } from '@/services/api/franchisee-dashboard'

interface Props {
  isDialogVisible: boolean
  selectedStaff: UnitStaff | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'staffUpdated', staff: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const editableStaff = ref<any>(null)

// Watch for changes in selectedStaff and create a copy for editing
watch(() => props.selectedStaff, (newStaff) => {
  if (newStaff) {
    editableStaff.value = { ...newStaff }
  }
}, { immediate: true })

const handleSaveStaff = () => {
  if (editableStaff.value) {
    emit('staffUpdated', editableStaff.value)
    dialogValue.value = false
  }
}

const handleClose = () => {
  dialogValue.value = false
}

// Status options for select
const statusOptions = [
  'working',
  'leave', 
  'terminated',
  'inactive'
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
    <VCard title="Edit Staff Member">
      <VCardText
        v-if="editableStaff"
        class="pa-6"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.name"
              label="Full Name"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.email"
              label="Email"
              type="email"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.phone"
              label="Phone"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.jobTitle"
              label="Job Title"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.department"
              label="Department"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.salary"
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
              v-model="editableStaff.hireDate"
              label="Hire Date"
              type="date"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.shiftStart"
              label="Shift Start"
              type="time"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableStaff.shiftEnd"
              label="Shift End"
              type="time"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editableStaff.status"
              label="Status"
              :items="statusOptions"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editableStaff.employmentType"
              label="Employment Type"
              :items="employmentTypeOptions"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editableStaff.notes"
              label="Notes"
              rows="2"
            />
          </VCol>
        </VRow>
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
          :disabled="!editableStaff?.name || !editableStaff?.email || !editableStaff?.jobTitle || !editableStaff?.status"
          @click="handleSaveStaff"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
