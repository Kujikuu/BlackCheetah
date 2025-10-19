<script setup lang="ts">
import { computed } from 'vue'

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
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'editLead', lead: Lead): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'won')
    return 'success'
  if (statLowerCase === 'lost')
    return 'error'

  return 'primary'
}

const handleEditLead = () => {
  if (props.selectedLead) {
    emit('editLead', props.selectedLead)
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
      v-if="selectedLead"
      title="Lead Details"
    >
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedLead.name }}
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Email
              </div>
              <div class="text-body-1">
                {{ selectedLead.email }}
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Phone
              </div>
              <div class="text-body-1">
                {{ selectedLead.phone }}
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Source
              </div>
              <div class="text-body-1 text-capitalize">
                {{ selectedLead.source }}
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Status
              </div>
              <VChip
                :color="resolveStatusVariant(selectedLead.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedLead.status }}
              </VChip>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-sm text-disabled mb-1">
                Created Date
              </div>
              <div class="text-body-1">
                {{ selectedLead.createdDate }}
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleClose"
        >
          Close
        </VBtn>
        <VBtn
          color="primary"
          @click="handleEditLead"
        >
          Edit
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
