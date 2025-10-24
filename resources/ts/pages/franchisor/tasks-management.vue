<script setup lang="ts">
// 👉 Imports
import CreateTaskDialog from '@/components/dialogs/tasks/CreateTaskDialog.vue'
import ViewTaskDialog from '@/components/dialogs/tasks/ViewTaskDialog.vue'
import EditTaskDialog from '@/components/dialogs/tasks/EditTaskDialog.vue'
import DeleteTaskDialog from '@/components/dialogs/tasks/DeleteTaskDialog.vue'
import { useTaskUsers } from '@/composables/useTaskUsers'
import { PRIORITY_OPTIONS, STATUS_OPTIONS, TASK_CATEGORIES, TASK_HEADERS } from '@/constants/taskConstants'
import { taskApi } from '@/services/api'

// 👉 Router
const router = useRouter()

// 👉 Task users composable
const { getUsersForSelect, initializeUsers, loading: usersLoading } = useTaskUsers()

// 👉 Current tab
const currentTab = ref('my-tasks')

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedTask = ref<any>(null)
const taskToDelete = ref<number | null>(null)

// 👉 Tasks data
const myTasksData = ref<any[]>([])
const franchiseeTasksData = ref<any[]>([])
const allTasksData = ref<any[]>([])

// 👉 Loading and error states
const myTasksLoading = ref(false)
const franchiseeLoading = ref(false)
const allTasksLoading = ref(false)
const myTasksError = ref<string | null>(null)
const franchiseeError = ref<string | null>(null)
const allTasksError = ref<string | null>(null)

// 👉 Modal states
const isAddTaskModalVisible = ref(false)

// 👉 API functions
const transformTask = (task: any) => ({
  id: task.id,
  title: task.title,
  description: task.description,
  category: task.type || 'other',
  assignedTo: task.assigned_to?.name || 'Unassigned',
  assignedToRole: task.assigned_to?.role || null,
  createdBy: task.created_by?.name || 'Unknown',
  createdByRole: task.created_by?.role || null,
  unitName: task.unit?.unit_name || 'N/A',
  startDate: task.started_at ? new Date(task.started_at).toISOString().split('T')[0] : null,
  dueDate: task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : null,
  priority: task.priority || 'medium',
  status: task.status || 'pending',
  estimatedHours: task.estimated_hours || 0,
  actualHours: task.actual_hours || 0,
  createdAt: task.created_at,
  updatedAt: task.updated_at,
})

const loadMyTasks = async () => {
  myTasksLoading.value = true
  myTasksError.value = null

  try {
    const response = await taskApi.getFranchisorTasks({ filter: 'assigned' })

    if (response.success && response.data?.data) {
      myTasksData.value = response.data.data.map(transformTask)
    }
    else {
      myTasksData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load my tasks:', err)
    myTasksError.value = err?.data?.message || 'Failed to load my tasks'
    myTasksData.value = []
  }
  finally {
    myTasksLoading.value = false
  }
}

const loadFranchiseeTasks = async () => {
  franchiseeLoading.value = true
  franchiseeError.value = null

  try {
    const response = await taskApi.getFranchisorTasks({ filter: 'created' })

    if (response.success && response.data?.data) {
      // Filter to show only tasks assigned to franchisees
      const franchiseeTasks = response.data.data.filter((task: any) => {
        return task.assigned_to?.role === 'franchisee' || task.assigned_to?.role === 'unit_manager'
      })

      franchiseeTasksData.value = franchiseeTasks.map(transformTask)
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

const loadAllTasks = async () => {
  allTasksLoading.value = true
  allTasksError.value = null

  try {
    const response = await taskApi.getFranchisorTasks() // No filter = all tasks

    if (response.success && response.data?.data) {
      allTasksData.value = response.data.data.map(transformTask)
    }
    else {
      allTasksData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load all tasks:', err)
    allTasksError.value = err?.data?.message || 'Failed to load all tasks'
    allTasksData.value = []
  }
  finally {
    allTasksLoading.value = false
  }
}

// 👉 Computed stats - My Tasks
const myTotalTasks = computed(() => myTasksData.value.length)
const myCompletedTasks = computed(() => myTasksData.value.filter(task => task.status === 'completed').length)
const myInProgressTasks = computed(() => myTasksData.value.filter(task => task.status === 'in_progress').length)
const myDueTasks = computed(() => {
  const today = new Date()
  return myTasksData.value.filter(task => {
    if (!task.dueDate)
      return false
    const dueDate = new Date(task.dueDate)
    return dueDate <= today && task.status !== 'completed'
  }).length
})

// 👉 Computed stats - Franchisee Tasks
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

// 👉 Computed stats - All Tasks
const allTotalTasks = computed(() => allTasksData.value.length)
const allCompletedTasks = computed(() => allTasksData.value.filter(task => task.status === 'completed').length)
const allInProgressTasks = computed(() => allTasksData.value.filter(task => task.status === 'in_progress').length)
const allDueTasks = computed(() => {
  const today = new Date()
  return allTasksData.value.filter(task => {
    if (!task.dueDate)
      return false
    const dueDate = new Date(task.dueDate)
    return dueDate <= today && task.status !== 'completed'
  }).length
})

// 👉 Current tab data
const currentTasksData = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myTasksData.value
  if (currentTab.value === 'franchisee')
    return franchiseeTasksData.value
  return allTasksData.value
})

const currentTotalTasks = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myTotalTasks.value
  if (currentTab.value === 'franchisee')
    return franchiseeTotalTasks.value
  return allTotalTasks.value
})

const currentCompletedTasks = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myCompletedTasks.value
  if (currentTab.value === 'franchisee')
    return franchiseeCompletedTasks.value
  return allCompletedTasks.value
})

const currentInProgressTasks = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myInProgressTasks.value
  if (currentTab.value === 'franchisee')
    return franchiseeInProgressTasks.value
  return allInProgressTasks.value
})

const currentDueTasks = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myDueTasks.value
  if (currentTab.value === 'franchisee')
    return franchiseeDueTasks.value
  return allDueTasks.value
})

const currentLoading = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myTasksLoading.value
  if (currentTab.value === 'franchisee')
    return franchiseeLoading.value
  return allTasksLoading.value
})

const currentError = computed(() => {
  if (currentTab.value === 'my-tasks')
    return myTasksError.value
  if (currentTab.value === 'franchisee')
    return franchiseeError.value
  return allTasksError.value
})

// 👉 Computed user options for dialogs (always use franchisee list for now)
const userOptions = computed(() => {
  return getUsersForSelect('franchisee').map(user => ({
    title: user.title,
    value: String(user.value),
  }))
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
    const response = await taskApi.createFranchisorTask({
      title: task.title,
      description: task.description,
      category: task.category,
      priority: task.priority,
      status: task.status || 'pending',
      due_date: task.dueDate,
      estimated_hours: task.estimatedHours,
    })

    if (response.success) {
      // Reload all tabs to ensure consistency
      await Promise.all([
        loadMyTasks(),
        loadFranchiseeTasks(),
        loadAllTasks(),
      ])
    }
    else {
      console.error('Failed to create task:', response)
    }
  }
  catch (error) {
    console.error('Error creating task:', error)
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
      // Reload all tabs to ensure consistency
      await Promise.all([
        loadMyTasks(),
        loadFranchiseeTasks(),
        loadAllTasks(),
      ])
    }
    else {
      console.error('Failed to delete task:', response)
    }
  }
  catch (error) {
    console.error('Error deleting task:', error)
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
      // Reload all tabs to ensure consistency
      await Promise.all([
        loadMyTasks(),
        loadFranchiseeTasks(),
        loadAllTasks(),
      ])
    }
    else {
      console.error('Failed to update task:', response)
    }
  }
  catch (error) {
    console.error('Error updating task:', error)
  }

  isEditTaskModalVisible.value = false
  selectedTask.value = null
}

// 👉 Dialog event handlers
const onTaskUpdated = async (updatedTask: any) => {
  // Reload all tabs to ensure consistency
  await Promise.all([
    loadMyTasks(),
    loadFranchiseeTasks(),
    loadAllTasks(),
  ])
  
  isEditTaskModalVisible.value = false
  selectedTask.value = null
}

const onTaskDeleted = async (taskId: number) => {
  // Reload all tabs to ensure consistency
  await Promise.all([
    loadMyTasks(),
    loadFranchiseeTasks(),
    loadAllTasks(),
  ])
  
  isDeleteDialogVisible.value = false
  taskToDelete.value = null
}

// 👉 Watch for tab changes to load data
watch(currentTab, async newTab => {
  // Load tab data if not already loaded
  if (newTab === 'my-tasks' && myTasksData.value.length === 0)
    await loadMyTasks()
  else if (newTab === 'franchisee' && franchiseeTasksData.value.length === 0)
    await loadFranchiseeTasks()
  else if (newTab === 'all-tasks' && allTasksData.value.length === 0)
    await loadAllTasks()
    
  await initializeUsers('franchisee')
})

// 👉 Watch for edit modal visibility to initialize users
watch(isEditTaskModalVisible, async isVisible => {
  if (isVisible)
    await initializeUsers('franchisee')
})

// 👉 Load data on component mount
onMounted(async () => {
  // Load all tabs data initially
  await Promise.all([
    loadMyTasks(),
    loadFranchiseeTasks(),
    loadAllTasks(),
  ])

  // Initialize users
  await initializeUsers('franchisee')
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
              Manage and track tasks - bidirectional between you and your franchisees
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
      <VTab value="my-tasks">
        <VIcon
          icon="tabler-user"
          start
        />
        My Tasks
      </VTab>
      <VTab value="franchisee">
        <VIcon
          icon="tabler-building-store"
          start
        />
        Franchisee Tasks
      </VTab>
      <VTab value="all-tasks">
        <VIcon
          icon="tabler-list"
          start
        />
        All Tasks
      </VTab>
    </VTabs>

    <!-- Error Alert -->
    <VAlert
      v-if="currentError"
      type="error"
      variant="tonal"
      class="mb-4"
      closable
      @click:close="currentError = null"
    >
      {{ currentError }}
    </VAlert>

    <!-- Loading State -->
    <div
      v-if="currentLoading"
      class="text-center py-12"
    >
      <VProgressCircular
        indeterminate
        size="64"
        class="mb-4"
      />
      <h3 class="text-h3 mb-2">
        Loading Tasks...
      </h3>
      <p class="text-body-1 text-medium-emphasis">
        Please wait while we fetch the task information.
      </p>
    </div>

    <!-- Tasks Content -->
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
                  {{ currentTotalTasks }}
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
                  {{ currentCompletedTasks }}
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
                  {{ currentInProgressTasks }}
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
                  {{ currentDueTasks }}
                </h4>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- Tasks Table -->
      <VCard>
        <VCardItem class="pb-4">
          <VCardTitle>
            {{ currentTab === 'my-tasks' ? 'My Tasks' : currentTab === 'franchisee' ? 'Franchisee Tasks' : 'All Tasks' }}
          </VCardTitle>
          <VCardSubtitle class="text-body-2 text-disabled">
            {{
              currentTab === 'my-tasks'
                ? 'Tasks assigned to you (including from franchisees)'
                : currentTab === 'franchisee'
                  ? 'Tasks you created for franchisees'
                  : 'All tasks in your franchise network'
            }}
          </VCardSubtitle>
        </VCardItem>

        <VDivider />

        <VDataTable
          v-if="currentTasksData.length > 0"
          :items="currentTasksData"
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
              <div v-if="currentTab === 'my-tasks' && item.createdByRole === 'franchisee'" class="text-caption text-info mt-1">
                <VIcon icon="tabler-arrow-up" size="12" />
                Created by franchisee
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
              No Tasks Found
            </h4>
            <p class="text-body-1 text-medium-emphasis">
              {{
                currentTab === 'my-tasks'
                  ? 'You have no tasks assigned to you yet.'
                  : currentTab === 'franchisee'
                    ? 'There are no tasks assigned to franchisees yet. Create tasks to manage operations across your franchise network.'
                    : 'There are no tasks in your franchise network yet.'
              }}
            </p>
          </div>
        </VCardText>
      </VCard>
    </template>

    <!-- Create Task Dialog -->
    <CreateTaskDialog
      v-model:is-dialog-visible="isAddTaskModalVisible"
      current-tab="franchisee"
      :is-franchisee="false"
      @task-created="onTaskCreated"
    />

    <!-- Edit Task Dialog -->
    <EditTaskDialog
      v-model:is-dialog-visible="isEditTaskModalVisible"
      :task="selectedTask"
      :user-options="userOptions"
      :users-loading="usersLoading"
      @task-updated="onTaskUpdated"
    />

    <!-- Delete Task Dialog -->
    <DeleteTaskDialog
      v-model:is-dialog-visible="isDeleteDialogVisible"
      :task-id="taskToDelete"
      @task-deleted="onTaskDeleted"
    />

    <!-- View Task Dialog -->
    <ViewTaskDialog
      v-model:is-dialog-visible="isViewTaskModalVisible"
      :task="selectedTask"
    />

  </section>
</template>
