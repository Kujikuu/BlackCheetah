<script setup lang="ts">
import { type Task } from '@/services/api/task'

interface Props {
  isDialogVisible: boolean
  task: Task | null
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const resolveStatusVariant = (status: string) => {
  if (status === 'completed')
    return 'success'
  if (status === 'in_progress')
    return 'warning'
  if (status === 'pending')
    return 'info'

  return 'secondary'
}

const resolvePriorityVariant = (priority: string) => {
  if (priority === 'high')
    return 'error'
  if (priority === 'medium')
    return 'warning'
  if (priority === 'low')
    return 'info'

  return 'secondary'
}

const handleClose = () => {
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Task Details">
      <VDivider />

      <VCardText
        v-if="task"
        class="pa-6"
      >
        <VRow>
          <VCol cols="12">
            <h6 class="text-h6 mb-2">
              {{ task.title }}
            </h6>
            <p class="text-body-1 mb-4">
              {{ task.description }}
            </p>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Category
            </div>
            <div class="text-body-1">
              {{ task.category }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Assigned To
            </div>
            <div class="text-body-1">
              {{ task.assignedTo }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Start Date
            </div>
            <div class="text-body-1">
              {{ task.startDate || 'N/A' }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Due Date
            </div>
            <div class="text-body-1">
              {{ task.dueDate }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Priority
            </div>
            <VChip
              :color="resolvePriorityVariant(task.priority)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ task.priority }}
            </VChip>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Status
            </div>
            <VChip
              :color="resolveStatusVariant(task.status)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ task.status }}
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
