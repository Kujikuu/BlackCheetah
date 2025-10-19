<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Lead {
  id: number
  name: string
  email: string
  phone: string
  source: string
  status: string
  createdDate: string
}

interface Props {
  isDialogVisible: boolean
  selectedLead: Lead | null
  sources: { title: string; value: string }[]
  statuses: { title: string; value: string }[]
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'saveLead', lead: Lead): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const editableLead = ref<Lead | null>(null)

// Watch for changes in selectedLead and create a copy for editing
watch(() => props.selectedLead, (newLead) => {
  if (newLead) {
    editableLead.value = { ...newLead }
  }
}, { immediate: true })

const handleSave = () => {
  if (editableLead.value) {
    emit('saveLead', editableLead.value)
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
    <VCard
      v-if="editableLead"
      title="Edit Lead"
    >
      <VCardText>
        <VForm @submit.prevent="handleSave">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="editableLead.name"
                label="Name"
                placeholder="Enter lead name"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="editableLead.email"
                label="Email"
                type="email"
                placeholder="Enter email address"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="editableLead.phone"
                label="Phone"
                placeholder="+966 50 123 4567"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="editableLead.source"
                label="Source"
                :items="sources"
                placeholder="Select source"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="editableLead.status"
                label="Status"
                :items="statuses"
                placeholder="Select status"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="editableLead.createdDate"
                label="Created Date"
                type="date"
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
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="handleSave"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
