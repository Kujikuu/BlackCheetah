<script setup lang="ts">
import { computed } from 'vue'
import type { UnitStaff } from '@/services/api'
import { franchiseeDashboardApi } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  selectedStaff: UnitStaff | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const resolveStatusVariant = (status: string) => {
  if (status === 'active' || status === 'completed' || status === 'approved' || status === 'working')
    return 'success'
  if (status === 'pending' || status === 'in_progress')
    return 'warning'
  if (status === 'inactive' || status === 'cancelled' || status === 'rejected')
    return 'error'

  return 'default'
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
    <VCard title="Staff Details">
      <VCardText
        v-if="selectedStaff"
        class="pa-6"
      >
        <VRow>
          <VCol
            cols="12"
            class="text-center pb-6"
          >
            <VAvatar
              size="80"
              color="primary"
              variant="tonal"
            >
              <span class="text-h4">{{ selectedStaff.name.split(' ').map((n: string) => n[0]).join('') }}</span>
            </VAvatar>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Full Name
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedStaff.name }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Email
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedStaff.email }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Job Title
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedStaff.jobTitle }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Shift Time
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedStaff.shiftTime || 'N/A' }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Shift Time
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedStaff.shiftTime }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Status
            </div>
            <VChip
              :color="resolveStatusVariant(selectedStaff.status)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ selectedStaff.status }}
            </VChip>
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
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
