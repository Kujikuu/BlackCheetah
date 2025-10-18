<script setup lang="ts">
import { type Task, type TaskStatistics, taskApi } from '@/services/api/task'

// 👉 Router
const router = useRouter()

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isStatusChangeModalVisible = ref(false)
const selectedTask = ref<Task | null>(null)

// 👉 Data and loading states
const allTasksData = ref<Task[]>([])

const statistics = ref<TaskStatistics>({
  totalTasks: 0,
  completedTasks: 0,
  inProgressTasks: 0,
  dueTasks: 0,
})

const isLoading = ref(false)
const error = ref<string | null>(null)

// 👉 Computed stats for all tasks
const totalTasks = computed(() => statistics.value.totalTasks)
const completedTasks = computed(() => statistics.value.completedTasks)
const inProgressTasks = computed(() => statistics.value.inProgressTasks)
const dueTasks = computed(() => statistics.value.dueTasks)

// 👉 Fetch tasks and statistics
const fetchTasks = async () => {
  try {
    isLoading.value = true
    error.value = null

    const [tasksResponse, statsResponse] = await Promise.all([
      taskApi.getMyTasks(),
      taskApi.getStatistics(),
    ])

    if (tasksResponse.success)
      allTasksData.value = tasksResponse.data

    if (statsResponse.success)
      statistics.value = statsResponse.data
  }
  catch (err: any) {
    console.error('Error fetching tasks:', err)
    error.value = err.message || 'Failed to load tasks'
  }
  finally {
    isLoading.value = false
  }
}

// 👉 Fetch on mount
onMounted(() => {
  fetchTasks()
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

// 👉 Headers
const taskHeaders = [
  { title: 'Task Info', key: 'taskInfo' },
  { title: 'Category', key: 'category' },
  { title: 'Start Date', key: 'startDate' },
  { title: 'Due Date', key: 'dueDate' },
  { title: 'Priority', key: 'priority' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// 👉 Action handlers
const viewTask = (task: any) => {
  selectedTask.value = task
  isViewTaskModalVisible.value = true
}

const changeTaskStatus = (task: Task) => {
  selectedTask.value = { ...task }
  isStatusChangeModalVisible.value = true
}

const updateTaskStatus = async (newStatus: string) => {
  if (!selectedTask.value)
    return

  try {
    // Update task status via API
    const response = await taskApi.updateTaskStatus(selectedTask.value.id, newStatus)

    if (response.success) {
      // Update local data
      const index = allTasksData.value.findIndex(task => task.id === selectedTask.value!.id)
      if (index !== -1)
        allTasksData.value[index].status = newStatus as any

      // Refresh statistics
      await fetchTasks()

      isStatusChangeModalVisible.value = false
      selectedTask.value = null
    }
    else {
      error.value = response.message || 'Failed to update task status'
    }
  }
  catch (err: any) {
    console.error('Error updating task status:', err)
    error.value = err.message || 'Failed to update task status'
  }
}
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div>
          <h2 class="text-h2 mb-1">
            My Tasks
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            Track and manage tasks assigned to you
          </p>
        </div>
      </VCol>
    </VRow>

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
                {{ totalTasks }}
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
                {{ completedTasks }}
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
                {{ inProgressTasks }}
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
                {{ dueTasks }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Error Alert -->
    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      closable
      class="mb-6"
      @click:close="error = null"
    >
      {{ error }}
    </VAlert>

    <!-- Tasks Table -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>My Tasks</VCardTitle>
      </VCardItem>

      <VDivider />

      <VDataTable
        :items="allTasksData"
        :headers="taskHeaders"
        :loading="isLoading"
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
                <VListItem @click="changeTaskStatus(item)">
                  <template #prepend>
                    <VIcon icon="tabler-refresh" />
                  </template>
                  <VListItemTitle>Change Status</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

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

    <!-- Status Change Modal -->
    <VDialog
      v-model="isStatusChangeModalVisible"
      max-width="400"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Change Task Status
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedTask"
          class="pa-6"
        >
          <div class="mb-4">
            <h6 class="text-base font-weight-medium mb-2">
              {{ selectedTask.title }}
            </h6>
            <div class="text-body-2 text-disabled">
              Current Status:
              <VChip
                :color="resolveStatusVariant(selectedTask.status)"
                size="small"
                label
                class="text-capitalize ml-2"
              >
                {{ selectedTask.status }}
              </VChip>
            </div>
          </div>

          <VSelect
            label="New Status"
            :items="[
              { title: 'Pending', value: 'pending' },
              { title: 'In Progress', value: 'in_progress' },
              { title: 'Completed', value: 'completed' },
            ]"
            :model-value="selectedTask.status"
            required
            @update:model-value="updateTaskStatus"
          />
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isStatusChangeModalVisible = false"
          >
            Cancel
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
