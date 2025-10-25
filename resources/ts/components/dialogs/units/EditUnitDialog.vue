<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitDetails } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useUpdateUnitValidation } from '@/validation/unitValidation'

interface Props {
  isDialogVisible: boolean
  unitData: UnitDetails | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'unitUpdated', unitData: UnitDetails): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useUpdateUnitValidation()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const editableUnitForm = ref<any>({})

// Watch for changes in unitData and create a copy for editing
watch(() => props.unitData, (newUnitData) => {
  if (newUnitData) {
    editableUnitForm.value = { ...newUnitData }
  }
}, { immediate: true })

const handleSaveUnitDetails = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    if (editableUnitForm.value && props.unitData) {
      emit('unitUpdated', editableUnitForm.value)
      dialogValue.value = false
    }
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const handleClose = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Edit Unit Details">
      <VCardText class="pa-6">
        <VForm ref="formRef">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableUnitForm.branchName"
                label="Branch Name"
                :rules="validationRules.unitName"
                :error-messages="backendErrors.unitName"
                @input="clearError('unitName')"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableUnitForm.city"
                label="City"
                :rules="validationRules.city"
                :error-messages="backendErrors.city"
                @input="clearError('city')"
                required
              />
            </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableUnitForm.franchiseeName"
              label="Franchisee"
              disabled
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableUnitForm.contactNumber"
              label="Contact Number"
              :rules="validationRules.phone"
              :error-messages="backendErrors.phone"
              @input="clearError('phone')"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editableUnitForm.address"
              label="Address"
              rows="3"
              :rules="validationRules.address"
              :error-messages="backendErrors.address"
              @input="clearError('address')"
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
          :disabled="!editableUnitForm.branchName || !editableUnitForm.city || !editableUnitForm.contactNumber"
          @click="handleSaveUnitDetails"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
