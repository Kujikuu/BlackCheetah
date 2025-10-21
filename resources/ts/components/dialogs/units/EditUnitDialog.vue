<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitDetails } from '@/services/api'

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

const handleSaveUnitDetails = () => {
  if (editableUnitForm.value && props.unitData) {
    emit('unitUpdated', editableUnitForm.value)
    dialogValue.value = false
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
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableUnitForm.branchName"
              label="Branch Name"
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
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editableUnitForm.address"
              label="Address"
              rows="3"
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
          :disabled="!editableUnitForm.branchName || !editableUnitForm.city || !editableUnitForm.contactNumber"
          @click="handleSaveUnitDetails"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
