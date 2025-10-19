<script setup lang="ts">
import { PRIORITY_OPTIONS, STATUS_OPTIONS, TASK_CATEGORIES } from '@/constants/taskConstants'

interface Task {
  id: number
  title: string
  description: string
  category: string
  assignedTo: string
  startDate: string
  dueDate: string
  priority: string
  status: string
  estimatedHours: number
  actualHours: number
}

interface Props {
  isDialogVisible: boolean
  task: Task | null
  userOptions: Array<{ title: string; value: string }>
  usersLoading?: boolean
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'task-updated': [task: Task]
}

const props = withDefaults(defineProps<Props>(), {
  usersLoading: false
})

const emit = defineEmits<Emits>()

const editedTask = ref<Task | null>(null)
const isLoading = ref(false)

// Watch for task changes to create a copy for editing
watch(() => props.task, (newTask) => {
  if (newTask) {
    editedTask.value = { ...newTask }
  }
}, { immediate: true })

const saveTask = async () => {
  if (!editedTask.value) return

  try {
    isLoading.value = true
    
    const response = await $api<{ success: boolean; data: any }>(`/v1/franchisor/tasks/${editedTask.value.id}`, {
      method: 'PUT',
      body: {
        title: editedTask.value.title,
        description: editedTask.value.description,
        category: editedTask.value.category,
        priority: editedTask.value.priority,
        status: editedTask.value.status,
        due_date: editedTask.value.dueDate,
        estimated_hours: editedTask.value.estimatedHours,
        actual_hours: editedTask.value.actualHours,
      },
    })

    if (response.success) {
      const updatedTask: Task = {
        id: response.data.id,
        title: response.data.title,
        description: response.data.description,
        category: response.data.type || 'other',
        assignedTo: response.data.assigned_to?.name || editedTask.value.assignedTo,
        startDate: response.data.started_at ? new Date(response.data.started_at).toISOString().split('T')[0] : editedTask.value.startDate,
        dueDate: response.data.due_date ? new Date(response.data.due_date).toISOString().split('T')[0] : editedTask.value.dueDate,
        priority: response.data.priority || editedTask.value.priority,
        status: response.data.status || editedTask.value.status,
        estimatedHours: response.data.estimated_hours || editedTask.value.estimatedHours,
        actualHours: response.data.actual_hours || editedTask.value.actualHours,
      }
      
      emit('task-updated', updatedTask)
      emit('update:isDialogVisible', false)
    } else {
      console.error('Failed to update task:', response)
    }
  } catch (error) {
    console.error('Error updating task:', error)
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    persistent
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard title="Edit Task">
      <VDivider />

      <VCardText
        v-if="editedTask"
        class="pa-6"
      >
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="editedTask.title"
              label="Task Title"
              placeholder="Enter task title"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editedTask.description"
              label="Description"
              placeholder="Enter task description"
              rows="3"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editedTask.category"
              label="Category"
              placeholder="Select category"
              :items="TASK_CATEGORIES"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editedTask.assignedTo"
              label="Assigned To"
              placeholder="Select user"
              :items="userOptions"
              :loading="usersLoading"
              item-title="title"
              item-value="value"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editedTask.startDate"
              label="Start Date"
              type="date"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editedTask.dueDate"
              label="Due Date"
              type="date"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editedTask.priority"
              label="Priority"
              :items="PRIORITY_OPTIONS"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="editedTask.status"
              label="Status"
              :items="STATUS_OPTIONS"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editedTask.estimatedHours"
              label="Estimated Hours"
              type="number"
              min="0"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editedTask.actualHours"
              label="Actual Hours"
              type="number"
              min="0"
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
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isLoading"
          @click="saveTask"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
