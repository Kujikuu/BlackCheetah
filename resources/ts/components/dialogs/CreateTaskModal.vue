<script setup lang="ts">
import { useTaskUsers } from '@/composables/useTaskUsers'
import { PRIORITY_OPTIONS, STATUS_OPTIONS, TASK_CATEGORIES } from '@/constants/taskConstants'

interface Props {
  isDialogVisible: boolean
  currentTab?: 'franchisee' | 'sales'
  defaultAssignedTo?: string
  unitId?: string
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'taskCreated', task: any): void
}

interface TaskForm {
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
  completionPercentage: number
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// 👉 Task users composable
const { getUsersForSelect, initializeUsers, loading: usersLoading } = useTaskUsers()

// 👉 Form data
const taskForm = ref<TaskForm>({
  title: '',
  description: '',
  category: '',
  assignedTo: '',
  startDate: '',
  dueDate: '',
  priority: 'medium',
  status: 'pending',
  estimatedHours: 0,
  actualHours: 0,
  completionPercentage: 0,
})

// 👉 Form validation
const isFormValid = ref(false)

// 👉 Computed user options based on current tab
const userOptions = computed(() => {
  const tabType = props.currentTab || 'franchisee'
  return getUsersForSelect(tabType)
})

// 👉 Form rules
const requiredRule = (value: string) => !!value || 'This field is required'

// 👉 Methods
const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

const resetForm = () => {
  taskForm.value = {
    title: '',
    description: '',
    category: '',
    assignedTo: '',
    startDate: '',
    dueDate: '',
    priority: 'medium',
    status: 'pending',
    estimatedHours: 0,
    actualHours: 0,
    completionPercentage: 0,
  }
}

const onSubmit = () => {
  if (isFormValid.value) {
    const newTask = {
      id: Date.now(), // Simple ID generation
      ...taskForm.value,
    }

    emit('taskCreated', newTask)
    resetForm()
    updateModelValue(false)
  }
}

const onCancel = () => {
  resetForm()
  updateModelValue(false)
}

// 👉 Watch for dialog visibility changes
watch(() => props.isDialogVisible, async newVal => {
  if (newVal) {
    // Initialize users when dialog opens
    const tabType = props.currentTab || 'franchisee'
    await initializeUsers(tabType)

    // Set default assigned to if provided
    if (props.defaultAssignedTo) {
      taskForm.value.assignedTo = props.defaultAssignedTo
    }
  } else {
    resetForm()
  }
})

// 👉 Watch for tab changes
watch(() => props.currentTab, async newTab => {
  if (props.isDialogVisible && newTab) {
    await initializeUsers(newTab)
    // Reset assigned to when tab changes
    taskForm.value.assignedTo = ''
  }
})
</script>

<template>
  <VDialog :model-value="props.isDialogVisible" max-width="600" persistent @update:model-value="updateModelValue">
    <VCard>
      <VCardTitle class="text-h5 pa-6 pb-4">
        Create New Task
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <VForm v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow>
            <!-- Title -->
            <VCol cols="12">
              <VTextField v-model="taskForm.title" label="Task Title" placeholder="Enter task title"
                :rules="[requiredRule]" required />
            </VCol>

            <!-- Description -->
            <VCol cols="12">
              <VTextarea v-model="taskForm.description" label="Description" placeholder="Enter task description"
                rows="3" :rules="[requiredRule]" required />
            </VCol>

            <!-- Category -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.category" label="Category" placeholder="Select category"
                :items="TASK_CATEGORIES" :rules="[requiredRule]" required />
            </VCol>

            <!-- Assigned To -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.assignedTo" label="Assigned To" placeholder="Select user" :items="userOptions"
                :loading="usersLoading" :rules="[requiredRule]" item-title="title" item-value="value" required />
            </VCol>

            <!-- Start Date -->
            <VCol cols="12" md="6">
              <VTextField v-model="taskForm.startDate" label="Start Date" type="date" :rules="[requiredRule]"
                required />
            </VCol>

            <!-- Due Date -->
            <VCol cols="12" md="6">
              <VTextField v-model="taskForm.dueDate" label="Due Date" type="date" :rules="[requiredRule]" required />
            </VCol>

            <!-- Priority -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.priority" label="Priority" placeholder="Select priority"
                :items="PRIORITY_OPTIONS" :rules="[requiredRule]" required />
            </VCol>

            <!-- Status -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.status" label="Status" placeholder="Select status" :items="STATUS_OPTIONS"
                :rules="[requiredRule]" required />
            </VCol>
            <!-- Estimated Hours -->
            <VCol cols="12" md="6">
              <VTextField v-model="taskForm.estimatedHours" label="Estimated Hours" type="number" min="0" />
            </VCol>
            <!-- Actual Hours -->
            <VCol cols="12" md="6">
              <VTextField v-model="taskForm.actualHours" label="Actual Hours" type="number" min="0" />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn color="error" variant="text" @click="onCancel">
          Cancel
        </VBtn>
        <VBtn color="primary" :disabled="!isFormValid" @click="onSubmit">
          Create Task
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
