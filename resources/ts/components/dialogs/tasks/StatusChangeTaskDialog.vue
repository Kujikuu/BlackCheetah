<script setup lang="ts">
import { taskApi, type Task } from '@/services/api'
import { PRIORITY_OPTIONS, STATUS_OPTIONS } from '@/constants/taskConstants'
import { useFormValidation } from '@/composables/useFormValidation'
import { useUpdateTaskStatusValidation } from '@/validation/tasksValidation'

interface Props {
  isDialogVisible: boolean
  task: Task | null
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'status-updated': [updatedTask: Task]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useUpdateTaskStatusValidation()

const newStatus = ref('')
const isLoading = ref(false)

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => {
    emit('update:isDialogVisible', val)
    if (!val)
      newStatus.value = ''
  },
})

watch(() => props.task?.status, (status) => {
  if (status)
    newStatus.value = status
}, { immediate: true })

const statusOptions = [
  { title: 'Pending', value: 'pending' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
  { title: 'On Hold', value: 'on_hold' },
]

const resolveStatusVariant = (status: string) => {
  if (status === 'completed')
    return 'success'
  if (status === 'in_progress')
    return 'warning'
  if (status === 'pending')
    return 'info'
  if (status === 'cancelled')
    return 'error'
  if (status === 'on_hold')
    return 'secondary'

  return 'secondary'
}

const updateTaskStatus = async () => {
  if (!props.task)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true
  try {
    const response = await taskApi.updateTaskStatus(props.task.id, newStatus.value)

    if (response.success) {
      const updatedTask = { ...props.task, status: newStatus.value }
      emit('status-updated', updatedTask)
      dialogValue.value = false
    }
    else {
      console.error('Failed to update task status:', response.message)
    }
  }
  catch (error: any) {
    console.error('Error updating task status:', error)
    setBackendErrors(error)
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard title="Change Task Status">
      <VDivider />

      <VCardText
        v-if="props.task"
        class="pa-6"
      >
        <div class="mb-4">
          <h6 class="text-base font-weight-medium mb-2">
            {{ props.task.title }}
          </h6>
          <div class="text-body-2 text-disabled">
            Current Status:
            <VChip
              :color="resolveStatusVariant(props.task.status)"
              size="small"
              label
              class="text-capitalize ml-2"
            >
              {{ props.task.status }}
            </VChip>
          </div>
        </div>

        <VForm ref="formRef">
          <VSelect
            v-model="newStatus"
            label="New Status"
            :items="statusOptions"
            item-title="title"
            item-value="value"
            :rules="validationRules.status"
            :error-messages="backendErrors.status"
            @update:model-value="clearError('status')"
            required
          />
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="dialogValue = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!newStatus || newStatus === props.task?.status || isLoading"
          :loading="isLoading"
          @click="updateTaskStatus"
        >
          Update Status
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
