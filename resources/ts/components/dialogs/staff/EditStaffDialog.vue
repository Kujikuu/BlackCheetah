<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitStaff } from '@/services/api'
import { useValidation } from '@/composables/useValidation'

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
const formRef = ref()

// Use validation composable
const { 
  requiredTextRules, 
  requiredEmailRules, 
  phoneRules, 
  requiredTextWithLengthRules,
  positiveNumberRules,
  pastDateRules,
  validateForm 
} = useValidation()

// Watch for changes in selectedStaff and create a copy for editing
watch(() => props.selectedStaff, (newStaff) => {
  if (newStaff) {
    editableStaff.value = { ...newStaff }
  }
}, { immediate: true })

const handleSaveStaff = async () => {
  if (!editableStaff.value) return

  // Validate form before submission
  const isValid = await validateForm(formRef)
  if (!isValid) return

  emit('staffUpdated', editableStaff.value)
  dialogValue.value = false
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
        <VForm ref="formRef">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableStaff.name"
                label="Full Name"
                :rules="requiredTextWithLengthRules(2, 100)"
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
                :rules="requiredEmailRules"
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
                :rules="phoneRules"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableStaff.jobTitle"
                label="Job Title"
                :rules="requiredTextWithLengthRules(2, 100)"
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
                :rules="requiredTextWithLengthRules(2, 100)"
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
                :rules="positiveNumberRules"
                required
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
                :rules="pastDateRules"
                required
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
              :rules="requiredTextRules"
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
              :rules="requiredTextRules"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editableStaff.notes"
              label="Notes"
              rows="2"
              :rules="requiredTextWithLengthRules(0, 500)"
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
          @click="handleSaveStaff"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
