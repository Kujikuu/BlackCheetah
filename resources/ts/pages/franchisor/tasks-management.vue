<script setup lang="ts">
// 👉 Imports
import CreateTaskModal from '@/components/dialogs/CreateTaskModal.vue'
import { useTaskUsers } from '@/composables/useTaskUsers'
import { PRIORITY_OPTIONS, STATUS_OPTIONS, TASK_CATEGORIES, TASK_HEADERS } from '@/constants/taskConstants'

// 👉 Router
const router = useRouter()

// 👉 Task users composable
const { getUsersForSelect, initializeUsers, loading: usersLoading } = useTaskUsers()

// 👉 Current tab
const currentTab = ref('franchisee')

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedTask = ref<any>(null)
const taskToDelete = ref<number | null>(null)

// 👉 Tasks data
const franchiseeTasksData = ref<any[]>([])
const salesTasksData = ref<any[]>([])

// 👉 Loading and error states
const franchiseeLoading = ref(false)
const salesLoading = ref(false)
const franchiseeError = ref<string | null>(null)
const salesError = ref<string | null>(null)

// 👉 Modal states
const isAddTaskModalVisible = ref(false)

// 👉 API functions
const loadFranchiseeTasks = async () => {
  franchiseeLoading.value = true
  franchiseeError.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/tasks')

    if (response.success && response.data?.data) {
      // Filter tasks to only show those assigned to franchisee users
      const franchiseeTasks = response.data.data.filter((task: any) => {
        return task.assigned_to?.role === 'franchisee' || task.assigned_to?.role === 'unit_manager'
      })

      franchiseeTasksData.value = franchiseeTasks.map((task: any) => ({
        id: task.id,
        title: task.title,
        description: task.description,
        category: task.type || 'other',
        assignedTo: task.assigned_to?.name || 'Unassigned',
        unitName: task.unit?.unit_name || 'N/A',
        startDate: task.started_at ? new Date(task.started_at).toISOString().split('T')[0] : null,
        dueDate: task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : null,
        priority: task.priority || 'medium',
        status: task.status || 'pending',
        estimatedHours: task.estimated_hours || 0,
        actualHours: task.actual_hours || 0,
        createdAt: task.created_at,
        updatedAt: task.updated_at,
      }))
    }
    else {
      franchiseeTasksData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load franchisee tasks:', err)
    franchiseeError.value = err?.data?.message || 'Failed to load franchisee tasks'
    franchiseeTasksData.value = []
  }
  finally {
    franchiseeLoading.value = false
  }
}

const loadSalesTasks = async () => {
  salesLoading.value = true
  salesError.value = null

  try {
    // Load franchisor tasks and filter for sales-related categories
    const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/tasks')

    if (response.success && response.data?.data) {
      // Filter tasks that are likely sales-related or assigned to sales team members
      const salesTasks = response.data.data.filter((task: any) => {
        // Check if task category is sales-related or assigned to sales team members
        const salesCategories = ['Marketing', 'Customer Service']

        return salesCategories.includes(task.type)
          || task.assigned_to?.role === 'sales'
          || task.title?.toLowerCase().includes('sales')
          || task.title?.toLowerCase().includes('lead')
          || task.title?.toLowerCase().includes('client')
      })

      salesTasksData.value = salesTasks.map((task: any) => ({
        id: task.id,
        title: task.title,
        description: task.description,
        category: task.type || 'other',
        assignedTo: task.assigned_to?.name || 'Unassigned',
        unitName: task.unit?.unit_name || 'N/A',
        startDate: task.started_at ? new Date(task.started_at).toISOString().split('T')[0] : null,
        dueDate: task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : null,
        priority: task.priority || 'medium',
        status: task.status || 'pending',
        estimatedHours: task.estimated_hours || 0,
        actualHours: task.actual_hours || 0,
        createdAt: task.created_at,
        updatedAt: task.updated_at,
      }))
    }
    else {
      salesTasksData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load sales tasks:', err)
    salesError.value = err?.data?.message || 'Failed to load sales tasks'
    salesTasksData.value = []
  }
  finally {
    salesLoading.value = false
  }
}

// 👉 Computed stats for franchisee tasks
const franchiseeTotalTasks = computed(() => franchiseeTasksData.value.length)
const franchiseeCompletedTasks = computed(() => franchiseeTasksData.value.filter(task => task.status === 'completed').length)
const franchiseeInProgressTasks = computed(() => franchiseeTasksData.value.filter(task => task.status === 'in_progress').length)

const franchiseeDueTasks = computed(() => {
  const today = new Date()

  return franchiseeTasksData.value.filter(task => {
    if (!task.dueDate)
      return false
    const dueDate = new Date(task.dueDate)

    return dueDate <= today && task.status !== 'completed'
  }).length
})

// 👉 Computed stats for sales tasks
const salesTotalTasks = computed(() => salesTasksData.value.length)
const salesCompletedTasks = computed(() => salesTasksData.value.filter(task => task.status === 'completed').length)
const salesInProgressTasks = computed(() => salesTasksData.value.filter(task => task.status === 'in_progress').length)

const salesDueTasks = computed(() => {
  const today = new Date()

  return salesTasksData.value.filter(task => {
    if (!task.dueDate)
      return false
    const dueDate = new Date(task.dueDate)

    return dueDate <= today && task.status !== 'completed'
  }).length
})

// 👉 Current tab data
const currentTasksData = computed(() => {
  return currentTab.value === 'franchisee' ? franchiseeTasksData.value : salesTasksData.value
})

const currentTotalTasks = computed(() => {
  return currentTab.value === 'franchisee' ? franchiseeTotalTasks.value : salesTotalTasks.value
})

const currentCompletedTasks = computed(() => {
  return currentTab.value === 'franchisee' ? franchiseeCompletedTasks.value : salesCompletedTasks.value
})

const currentInProgressTasks = computed(() => {
  return currentTab.value === 'franchisee' ? franchiseeInProgressTasks.value : salesInProgressTasks.value
})

const currentDueTasks = computed(() => {
  return currentTab.value === 'franchisee' ? franchiseeDueTasks.value : salesDueTasks.value
})

// 👉 Computed user options based on current tab
const userOptions = computed(() => {
  return getUsersForSelect(currentTab.value as 'franchisee' | 'sales')
})

// 👉 Functions
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

const onTaskCreated = async (task: any) => {
  try {
    const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/tasks', {
      method: 'POST',
      body: {
        title: task.title,
        description: task.description,
        category: task.category,
        priority: task.priority,
        status: task.status || 'pending',
        due_date: task.dueDate,
        estimated_hours: task.estimatedHours,

        // Note: assigned_to, franchise_id, and unit_id are not provided by CreateTaskModal
        // They will be set by the backend based on the authenticated user
      },
    })

    if (response.success) {
      const newTask = {
        id: response.data.id,
        title: response.data.title,
        description: response.data.description,
        category: response.data.type || 'other',
        assignedTo: response.data.assigned_to?.name || 'Unassigned',
        unitName: response.data.unit?.unit_name || 'N/A',
        startDate: response.data.started_at ? new Date(response.data.started_at).toISOString().split('T')[0] : null,
        dueDate: response.data.due_date ? new Date(response.data.due_date).toISOString().split('T')[0] : null,
        priority: response.data.priority || 'medium',
        status: response.data.status || 'pending',
        estimatedHours: response.data.estimated_hours || 0,
        actualHours: response.data.actual_hours || 0,
        createdAt: response.data.created_at,
        updatedAt: response.data.updated_at,
      }

      // Add task to appropriate data array based on current tab
      if (currentTab.value === 'franchisee')
        franchiseeTasksData.value.unshift(newTask)
      else
        salesTasksData.value.unshift(newTask)
    }
    else {
      console.error('Failed to create task:', response)

      // Show error message (you could add a toast notification here)
    }
  }
  catch (error) {
    console.error('Error creating task:', error)

    // Show error message (you could add a toast notification here)
  }
}

// 👉 Headers (using imported constants)
const taskHeaders = [...TASK_HEADERS]

// 👉 Action handlers
const viewTask = (task: any) => {
  selectedTask.value = task
  isViewTaskModalVisible.value = true
}

const editTask = (task: any) => {
  selectedTask.value = { ...task }
  isEditTaskModalVisible.value = true
}

const confirmDelete = (id: number) => {
  taskToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteTask = async () => {
  if (taskToDelete.value === null)
    return

  try {
    const response = await $api<{ success: boolean }>(`/v1/franchisor/tasks/${taskToDelete.value}`, {
      method: 'DELETE',
    })

    if (response.success) {
      // Remove from appropriate data array based on current tab
      if (currentTab.value === 'franchisee') {
        const index = franchiseeTasksData.value.findIndex(task => task.id === taskToDelete.value)
        if (index !== -1)
          franchiseeTasksData.value.splice(index, 1)
      }
      else {
        const index = salesTasksData.value.findIndex(task => task.id === taskToDelete.value)
        if (index !== -1)
          salesTasksData.value.splice(index, 1)
      }
    }
    else {
      console.error('Failed to delete task:', response)

      // Show error message (you could add a toast notification here)
    }
  }
  catch (error) {
    console.error('Error deleting task:', error)

    // Show error message (you could add a toast notification here)
  }

  isDeleteDialogVisible.value = false
  taskToDelete.value = null
}

const saveTask = async () => {
  if (!selectedTask.value)
    return

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/franchisor/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      body: {
        title: selectedTask.value.title,
        description: selectedTask.value.description,
        category: selectedTask.value.category,
        priority: selectedTask.value.priority,
        status: selectedTask.value.status,
        due_date: selectedTask.value.dueDate,
        estimated_hours: selectedTask.value.estimatedHours,
        actual_hours: selectedTask.value.actualHours,
      },
    })

    if (response.success) {
      const updatedTask = {
        id: response.data.id,
        title: response.data.title,
        description: response.data.description,
        category: response.data.type || 'other',
        assignedTo: response.data.assigned_to?.name || selectedTask.value.assignedTo,
        unitName: response.data.unit?.unit_name || selectedTask.value.unitName,
        startDate: response.data.started_at ? new Date(response.data.started_at).toISOString().split('T')[0] : selectedTask.value.startDate,
        dueDate: response.data.due_date ? new Date(response.data.due_date).toISOString().split('T')[0] : selectedTask.value.dueDate,
        priority: response.data.priority || selectedTask.value.priority,
        status: response.data.status || selectedTask.value.status,
        estimatedHours: response.data.estimated_hours || selectedTask.value.estimatedHours,
        actualHours: response.data.actual_hours || selectedTask.value.actualHours,
        createdAt: response.data.created_at,
        updatedAt: response.data.updated_at,
      }

      // Update task in appropriate data array based on current tab
      if (currentTab.value === 'franchisee') {
        const index = franchiseeTasksData.value.findIndex(task => task.id === selectedTask.value.id)
        if (index !== -1)
          franchiseeTasksData.value[index] = updatedTask
      }
      else {
        const index = salesTasksData.value.findIndex(task => task.id === selectedTask.value.id)
        if (index !== -1)
          salesTasksData.value[index] = updatedTask
      }
    }
    else {
      console.error('Failed to update task:', response)

      // Show error message (you could add a toast notification here)
    }
  }
  catch (error) {
    console.error('Error updating task:', error)

    // Show error message (you could add a toast notification here)
  }

  isEditTaskModalVisible.value = false
  selectedTask.value = null
}

// 👉 Watch for tab changes to initialize users
watch(currentTab, async newTab => {
  await initializeUsers(newTab as 'franchisee' | 'sales')
})

// 👉 Watch for edit modal visibility to initialize users
watch(isEditTaskModalVisible, async isVisible => {
  if (isVisible)
    await initializeUsers(currentTab.value as 'franchisee' | 'sales')
})

// 👉 Load data on component mount
onMounted(async () => {
  loadFranchiseeTasks()
  loadSalesTasks()

  // Initialize users for the default tab
  await initializeUsers(currentTab.value as 'franchisee' | 'sales')
})
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div>
            <h2 class="text-h2 mb-1">
              Tasks Management
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage and track tasks across franchisees and sales teams
            </p>
          </div>
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="isAddTaskModalVisible = true"
          >
            Create Task
          </VBtn>
        </div>
      </VCol>
    </VRow>

    <!-- Tabs -->
    <VTabs
      v-model="currentTab"
      class="mb-6"
    >
      <VTab value="franchisee">
        <VIcon
          icon="tabler-building-store"
          start
        />
        Franchisee Tasks
      </VTab>
      <VTab value="sales">
        <VIcon
          icon="tabler-user-star"
          start
        />
        Sales Tasks
      </VTab>
    </VTabs>

    <VWindow
      v-model="currentTab"
      class="disable-tab-transition"
    >
      <!-- Franchisee Tasks Tab -->
      <VWindowItem value="franchisee">
        <!-- Error Alert -->
        <VAlert
          v-if="franchiseeError"
          type="error"
          variant="tonal"
          class="mb-4"
          closable
          @click:close="franchiseeError = null"
        >
          {{ franchiseeError }}
        </VAlert>

        <!-- Loading State -->
        <div
          v-if="franchiseeLoading"
          class="text-center py-12"
        >
          <VProgressCircular
            indeterminate
            size="64"
            class="mb-4"
          />
          <h3 class="text-h3 mb-2">
            Loading Franchisee Tasks...
          </h3>
          <p class="text-body-1 text-medium-emphasis">
            Please wait while we fetch the task information.
          </p>
        </div>

        <!-- Franchisee Tasks Content -->
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-checklist"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Total Tasks
                    </div>
                    <h4 class="text-h4">
                      {{ franchiseeTotalTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="success"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-check"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Completed
                    </div>
                    <h4 class="text-h4">
                      {{ franchiseeCompletedTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="warning"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-clock"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      In Progress
                    </div>
                    <h4 class="text-h4">
                      {{ franchiseeInProgressTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="error"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-alert-circle"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Due
                    </div>
                    <h4 class="text-h4">
                      {{ franchiseeDueTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>

          <!-- Franchisee Tasks Table -->
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Franchisee Tasks</VCardTitle>
              <VCardSubtitle class="text-body-2 text-disabled">
                View and manage tasks assigned to your franchisees
              </VCardSubtitle>
            </VCardItem>

            <VDivider />

            <VDataTable
              v-if="franchiseeTasksData.length > 0"
              :items="franchiseeTasksData"
              :headers="taskHeaders"
              class="text-no-wrap"
              item-value="id"
            >
              <!-- Task Info -->
              <template #item.taskInfo="{ item }">
                <div>
                  <h6 class="text-base font-weight-medium">
                    {{ item.title }}
                  </h6>
                  <div class="text-body-2 text-disabled">
                    {{ item.description }}
                  </div>
                </div>
              </template>

              <!-- Unit -->
              <template #item.unitName="{ item }">
                <div class="text-body-1">
                  {{ item.unitName }}
                </div>
              </template>

              <!-- Priority -->
              <template #item.priority="{ item }">
                <VChip
                  :color="resolvePriorityVariant(item.priority)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.priority }}
                </VChip>
              </template>

              <!-- Status -->
              <template #item.status="{ item }">
                <VChip
                  :color="resolveStatusVariant(item.status)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.status }}
                </VChip>
              </template>

              <!-- Actions -->
              <template #item.actions="{ item }">
                <VBtn
                  icon
                  variant="text"
                  color="medium-emphasis"
                  size="small"
                >
                  <VIcon icon="tabler-dots-vertical" />
                  <VMenu activator="parent">
                    <VList>
                      <VListItem @click="viewTask(item)">
                        <template #prepend>
                          <VIcon icon="tabler-eye" />
                        </template>
                        <VListItemTitle>View</VListItemTitle>
                      </VListItem>
                      <VListItem @click="editTask(item)">
                        <template #prepend>
                          <VIcon icon="tabler-edit" />
                        </template>
                        <VListItemTitle>Edit</VListItemTitle>
                      </VListItem>
                      <VListItem @click="confirmDelete(item.id)">
                        <template #prepend>
                          <VIcon icon="tabler-trash" />
                        </template>
                        <VListItemTitle>Delete</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </template>
            </VDataTable>

            <!-- Empty State -->
            <VCardText
              v-else
              class="py-8"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-checklist"
                  size="64"
                  class="text-disabled mb-4"
                />
                <h4 class="text-h4 mb-2">
                  No Franchisee Tasks Found
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  There are no tasks assigned to franchisees yet. Create tasks to manage operations across your
                  franchise network.
                </p>
              </div>
            </VCardText>
          </VCard>
        </template>
      </VWindowItem>

      <!-- Sales Tasks Tab -->
      <VWindowItem value="sales">
        <!-- Error Alert -->
        <VAlert
          v-if="salesError"
          type="error"
          variant="tonal"
          class="mb-4"
          closable
          @click:close="salesError = null"
        >
          {{ salesError }}
        </VAlert>

        <!-- Loading State -->
        <div
          v-if="salesLoading"
          class="text-center py-12"
        >
          <VProgressCircular
            indeterminate
            size="64"
            class="mb-4"
          />
          <h3 class="text-h3 mb-2">
            Loading Sales Tasks...
          </h3>
          <p class="text-body-1 text-medium-emphasis">
            Please wait while we fetch the sales team task information.
          </p>
        </div>

        <!-- Sales Tasks Content -->
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-checklist"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Total Tasks
                    </div>
                    <h4 class="text-h4">
                      {{ salesTotalTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="success"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-check"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Completed
                    </div>
                    <h4 class="text-h4">
                      {{ salesCompletedTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="warning"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-clock"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      In Progress
                    </div>
                    <h4 class="text-h4">
                      {{ salesInProgressTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar
                    size="44"
                    rounded
                    color="error"
                    variant="tonal"
                  >
                    <VIcon
                      icon="tabler-alert-circle"
                      size="26"
                    />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Due
                    </div>
                    <h4 class="text-h4">
                      {{ salesDueTasks }}
                    </h4>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>

          <!-- Sales Tasks Table -->
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Sales Team Tasks</VCardTitle>
              <VCardSubtitle class="text-body-2 text-disabled">
                View and manage tasks assigned to your sales team
              </VCardSubtitle>
            </VCardItem>

            <VDivider />

            <VDataTable
              v-if="salesTasksData.length > 0"
              :items="salesTasksData"
              :headers="taskHeaders"
              class="text-no-wrap"
              item-value="id"
            >
              <!-- Task Info -->
              <template #item.taskInfo="{ item }">
                <div>
                  <h6 class="text-base font-weight-medium">
                    {{ item.title }}
                  </h6>
                  <div class="text-body-2 text-disabled">
                    {{ item.description }}
                  </div>
                </div>
              </template>

              <!-- Unit -->
              <template #item.unitName="{ item }">
                <div class="text-body-1">
                  {{ item.unitName }}
                </div>
              </template>

              <!-- Priority -->
              <template #item.priority="{ item }">
                <VChip
                  :color="resolvePriorityVariant(item.priority)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.priority }}
                </VChip>
              </template>

              <!-- Status -->
              <template #item.status="{ item }">
                <VChip
                  :color="resolveStatusVariant(item.status)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.status }}
                </VChip>
              </template>

              <!-- Actions -->
              <template #item.actions="{ item }">
                <VBtn
                  icon
                  variant="text"
                  color="medium-emphasis"
                  size="small"
                >
                  <VIcon icon="tabler-dots-vertical" />
                  <VMenu activator="parent">
                    <VList>
                      <VListItem @click="viewTask(item)">
                        <template #prepend>
                          <VIcon icon="tabler-eye" />
                        </template>
                        <VListItemTitle>View</VListItemTitle>
                      </VListItem>
                      <VListItem @click="editTask(item)">
                        <template #prepend>
                          <VIcon icon="tabler-edit" />
                        </template>
                        <VListItemTitle>Edit</VListItemTitle>
                      </VListItem>
                      <VListItem @click="confirmDelete(item.id)">
                        <template #prepend>
                          <VIcon icon="tabler-trash" />
                        </template>
                        <VListItemTitle>Delete</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </template>
            </VDataTable>

            <!-- Empty State -->
            <VCardText
              v-else
              class="py-8"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-user-star"
                  size="64"
                  class="text-disabled mb-4"
                />
                <h4 class="text-h4 mb-2">
                  No Sales Tasks Found
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  There are no tasks assigned to the sales team yet. Create tasks to track sales activities and goals.
                </p>
              </div>
            </VCardText>
          </VCard>
        </template>
      </VWindowItem>
    </VWindow>

    <!-- Create Task Modal -->
    <CreateTaskModal
      v-model:is-dialog-visible="isAddTaskModalVisible"
      :current-tab="currentTab"
      @task-created="onTaskCreated"
    />

    <!-- View Task Modal -->
    <VDialog
      v-model="isViewTaskModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Task Details
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedTask"
          class="pa-6"
        >
          <VRow>
            <VCol cols="12">
              <h6 class="text-h6 mb-2">
                {{ selectedTask.title }}
              </h6>
              <p class="text-body-1 mb-4">
                {{ selectedTask.description }}
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
                {{ selectedTask.category }}
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
                {{ selectedTask.assignedTo }}
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
                {{ selectedTask.startDate }}
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
                {{ selectedTask.dueDate }}
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
                :color="resolvePriorityVariant(selectedTask.priority)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedTask.priority }}
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
                :color="resolveStatusVariant(selectedTask.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedTask.status }}
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
            @click="isViewTaskModalVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Task Modal -->
    <VDialog
      v-model="isEditTaskModalVisible"
      max-width="600"
      persistent
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Task
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedTask"
          class="pa-6"
        >
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="selectedTask.title"
                label="Task Title"
                placeholder="Enter task title"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="selectedTask.description"
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
                v-model="selectedTask.category"
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
                v-model="selectedTask.assignedTo"
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
                v-model="selectedTask.startDate"
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
                v-model="selectedTask.dueDate"
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
                v-model="selectedTask.priority"
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
                v-model="selectedTask.status"
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
                v-model="selectedTask.estimatedHours"
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
                v-model="selectedTask.actualHours"
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
            @click="isEditTaskModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveTask"
          >
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Confirmation Dialog -->
    <VDialog
      v-model="isDeleteDialogVisible"
      max-width="500"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete this task? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDeleteDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            @click="deleteTask"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
