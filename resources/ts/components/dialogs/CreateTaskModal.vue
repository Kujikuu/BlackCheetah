<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
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
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

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
})

// 👉 Form validation
const isFormValid = ref(false)

// 👉 Options
const categoryOptions = [
  'Operations',
  'Training',
  'Maintenance',
  'Marketing',
  'Finance',
  'HR',
  'Quality Control',
  'Customer Service',
]

const userOptions = [
  'Store Manager',
  'Assistant Manager',
  'HR Manager',
  'Technician',
  'Marketing Coordinator',
  'Finance Officer',
  'Quality Inspector',
  'Customer Service Rep',
]

const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
]

const statusOptions = [
  { title: 'Pending', value: 'pending' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Completed', value: 'completed' },
]

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
watch(() => props.isDialogVisible, (newVal) => {
  if (!newVal) {
    resetForm()
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
                :items="categoryOptions" :rules="[requiredRule]" required />
            </VCol>

            <!-- Assigned To -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.assignedTo" label="Assigned To" placeholder="Select user" :items="userOptions"
                :rules="[requiredRule]" required />
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
                :items="priorityOptions" :rules="[requiredRule]" required />
            </VCol>

            <!-- Status -->
            <VCol cols="12" md="6">
              <VSelect v-model="taskForm.status" label="Status" placeholder="Select status" :items="statusOptions"
                :rules="[requiredRule]" required />
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
