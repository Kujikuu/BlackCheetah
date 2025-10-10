<script setup lang="ts">

// 👉 API composable
const { data: operationsApiData, execute: fetchOperationsData, isFetching: isLoading } = useApi('/v1/franchisor/dashboard/operations')

// 👉 Store
const currentTab = ref('franchisee')
const searchQuery = ref('')
const selectedStatus = ref()
const selectedPriority = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref<number[]>([])

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  { title: 'Task', key: 'task' },
  { title: 'Assigned To', key: 'assignedTo' },
  { title: 'Priority', key: 'priority' },
  { title: 'Status', key: 'status' },
  { title: 'Due Date', key: 'dueDate' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// 👉 Task Interface
interface Task {
  id: number
  task: string
  assignedTo: string
  priority: string
  status: string
  dueDate: string
}

// 👉 Widget Interface
interface Widget {
  title: string
  value: string
  change: number
  desc: string
  icon: string
  iconColor: string
}

// 👉 API Response Interface
interface ApiResponse {
  success: boolean
  data: {
    stats: {
      franchisee: {
        total: number
        total_change: number
        completed: number
        completed_change: number
        in_progress: number
        in_progress_change: number
        due: number
        due_change: number
      }
      sales: {
        total: number
        total_change: number
        completed: number
        completed_change: number
        in_progress: number
        in_progress_change: number
        due: number
        due_change: number
      }
      staff: {
        total: number
        total_change: number
        completed: number
        completed_change: number
        in_progress: number
        in_progress_change: number
        due: number
        due_change: number
      }
    }
    tasks: {
      franchisee: Array<{
        id: number
        task: string
        assigned_to: string
        priority: string
        status: string
        due_date: string
      }>
      sales: Array<{
        id: number
        task: string
        assigned_to: string
        priority: string
        status: string
        due_date: string
      }>
      staff: Array<{
        id: number
        task: string
        assigned_to: string
        priority: string
        status: string
        due_date: string
      }>
    }
  }
}

// 👉 Reactive data
const tasksData = ref<{
  franchisee: { tasks: Task[], total: number }
  sales: { tasks: Task[], total: number }
  staff: { tasks: Task[], total: number }
}>({
  franchisee: { tasks: [], total: 0 },
  sales: { tasks: [], total: 0 },
  staff: { tasks: [], total: 0 },
})

const statsData = ref<{
  franchisee: Widget[]
  sales: Widget[]
  staff: Widget[]
}>({
  franchisee: [
    { title: 'Total', value: '0', change: 0, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
    { title: 'Completed', value: '0', change: 0, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
    { title: 'In Progress', value: '0', change: 0, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
    { title: 'Due', value: '0', change: 0, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
  ],
  sales: [
    { title: 'Total', value: '0', change: 0, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
    { title: 'Completed', value: '0', change: 0, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
    { title: 'In Progress', value: '0', change: 0, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
    { title: 'Due', value: '0', change: 0, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
  ],
  staff: [
    { title: 'Total', value: '0', change: 0, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
    { title: 'Completed', value: '0', change: 0, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
    { title: 'In Progress', value: '0', change: 0, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
    { title: 'Due', value: '0', change: 0, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
  ],
})

// 👉 Watch for API data changes
watch(operationsApiData, (newData) => {
  const apiData = newData as ApiResponse
  if (apiData?.success && apiData?.data) {
    const data = apiData.data

    // Helper function to map tasks with null/undefined checks
    const mapTasks = (tasks: any[]): Task[] => {
      return Array.isArray(tasks) ? tasks.map(task => ({
        id: task.id,
        task: task.task,
        assignedTo: task.assigned_to,
        priority: task.priority,
        status: task.status,
        dueDate: new Date(task.due_date).toLocaleDateString(),
      })) : []
    }

    // Create fallback objects for safe access
    const tasks = data.tasks ?? { franchisee: [], sales: [], staff: [] }
    const stats = data.stats ?? {
      franchisee: { total: 0, total_change: 0, completed: 0, completed_change: 0, in_progress: 0, in_progress_change: 0, due: 0, due_change: 0 },
      sales: { total: 0, total_change: 0, completed: 0, completed_change: 0, in_progress: 0, in_progress_change: 0, due: 0, due_change: 0 },
      staff: { total: 0, total_change: 0, completed: 0, completed_change: 0, in_progress: 0, in_progress_change: 0, due: 0, due_change: 0 }
    }

    // Update tasks data
    tasksData.value = {
      franchisee: {
        tasks: mapTasks(tasks.franchisee),
        total: stats.franchisee.total,
      },
      sales: {
        tasks: mapTasks(tasks.sales),
        total: stats.sales.total,
      },
      staff: {
        tasks: mapTasks(tasks.staff),
        total: stats.staff.total,
      },
    }

    // Update stats data
    statsData.value = {
      franchisee: [
        { title: 'Total', value: stats.franchisee.total.toLocaleString(), change: stats.franchisee.total_change, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
        { title: 'Completed', value: stats.franchisee.completed.toLocaleString(), change: stats.franchisee.completed_change, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
        { title: 'In Progress', value: stats.franchisee.in_progress.toLocaleString(), change: stats.franchisee.in_progress_change, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
        { title: 'Due', value: stats.franchisee.due.toLocaleString(), change: stats.franchisee.due_change, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
      ],
      sales: [
        { title: 'Total', value: stats.sales.total.toLocaleString(), change: stats.sales.total_change, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
        { title: 'Completed', value: stats.sales.completed.toLocaleString(), change: stats.sales.completed_change, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
        { title: 'In Progress', value: stats.sales.in_progress.toLocaleString(), change: stats.sales.in_progress_change, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
        { title: 'Due', value: stats.sales.due.toLocaleString(), change: stats.sales.due_change, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
      ],
      staff: [
        { title: 'Total', value: stats.staff.total.toLocaleString(), change: stats.staff.total_change, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
        { title: 'Completed', value: stats.staff.completed.toLocaleString(), change: stats.staff.completed_change, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
        { title: 'In Progress', value: stats.staff.in_progress.toLocaleString(), change: stats.staff.in_progress_change, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
        { title: 'Due', value: stats.staff.due.toLocaleString(), change: stats.staff.due_change, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
      ],
    }
  }
}, { immediate: true })

const currentTasks = computed(() => {
  if (currentTab.value === 'franchisee')
    return tasksData.value.franchisee.tasks
  if (currentTab.value === 'sales')
    return tasksData.value.sales.tasks
  return tasksData.value.staff.tasks
})

const totalTasks = computed(() => {
  if (currentTab.value === 'franchisee')
    return tasksData.value.franchisee.total
  if (currentTab.value === 'sales')
    return tasksData.value.sales.total
  return tasksData.value.staff.total
})

const widgetData = computed(() => {
  if (currentTab.value === 'franchisee')
    return statsData.value.franchisee
  if (currentTab.value === 'sales')
    return statsData.value.sales
  return statsData.value.staff
})

// 👉 Fetch data on component mount
onMounted(() => {
  fetchOperationsData()
})

// 👉 search filters
const priorities = [
  { title: 'High', value: 'high' },
  { title: 'Medium', value: 'medium' },
  { title: 'Low', value: 'low' },
]

const statuses = [
  { title: 'Completed', value: 'completed' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Due', value: 'due' },
]

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'completed')
    return 'success'
  if (statLowerCase === 'in_progress')
    return 'info'
  if (statLowerCase === 'due')
    return 'error'

  return 'primary'
}

const resolvePriorityVariant = (priority: string) => {
  const priorityLowerCase = priority.toLowerCase()
  if (priorityLowerCase === 'high')
    return 'error'
  if (priorityLowerCase === 'medium')
    return 'warning'
  if (priorityLowerCase === 'low')
    return 'success'

  return 'primary'
}

// 👉 Delete task with confirmation
const isDeleteDialogVisible = ref(false)
const taskToDelete = ref<number | null>(null)

const confirmDelete = (id: number) => {
  taskToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteTask = async () => {
  if (taskToDelete.value === null) return

  // TODO: Implement API call for delete
  const taskList = currentTab.value === 'franchisee'
    ? tasksData.value.franchisee.tasks
    : currentTab.value === 'sales'
      ? tasksData.value.sales.tasks
      : tasksData.value.staff.tasks

  const index = taskList.findIndex(task => task.id === taskToDelete.value)
  if (index !== -1)
    taskList.splice(index, 1)

  // Delete from selectedRows
  const selectedIndex = selectedRows.value.findIndex(row => row === taskToDelete.value)
  if (selectedIndex !== -1)
    selectedRows.value.splice(selectedIndex, 1)

  isDeleteDialogVisible.value = false
  taskToDelete.value = null
}

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const selectedTask = ref<Task | null>(null)

// 👉 View task
const viewTask = (id: number) => {
  const taskList = currentTab.value === 'franchisee'
    ? tasksData.value.franchisee.tasks
    : currentTab.value === 'sales'
      ? tasksData.value.sales.tasks
      : tasksData.value.staff.tasks

  const task = taskList.find(t => t.id === id)
  if (task) {
    selectedTask.value = task
    isViewTaskModalVisible.value = true
  }
}

// 👉 Edit task
const editTask = (id: number) => {
  const taskList = currentTab.value === 'franchisee'
    ? tasksData.value.franchisee.tasks
    : currentTab.value === 'sales'
      ? tasksData.value.sales.tasks
      : tasksData.value.staff.tasks

  const task = taskList.find(t => t.id === id)
  if (task) {
    selectedTask.value = { ...task }
    isEditTaskModalVisible.value = true
  }
}

// 👉 Save edited task
const saveTask = async () => {
  if (!selectedTask.value) return

  // TODO: Implement API call for update
  const taskList = currentTab.value === 'franchisee'
    ? tasksData.value.franchisee.tasks
    : currentTab.value === 'sales'
      ? tasksData.value.sales.tasks
      : tasksData.value.staff.tasks

  const index = taskList.findIndex(task => task.id === selectedTask.value!.id)
  if (index !== -1) {
    taskList[index] = { ...selectedTask.value }
  }

  isEditTaskModalVisible.value = false
  selectedTask.value = null
}

// 👉 Export functionality
const exportTasks = () => {
  const dataToExport = selectedRows.value.length > 0
    ? currentTasks.value.filter(task => selectedRows.value.includes(task.id))
    : currentTasks.value

  const csvContent = [
    'Task,Assigned To,Priority,Status,Due Date',
    ...dataToExport.map(task =>
      `"${task.task}","${task.assignedTo}","${task.priority}","${task.status}","${task.dueDate}"`
    )
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${currentTab.value}_tasks_${selectedRows.value.length > 0 ? 'selected' : 'all'}_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

// 👉 Utility functions
const prefixWithPlus = (phone: string) => {
  return phone.startsWith('+') ? phone : `+${phone}`
}

const prefixWithPlusNumber = (num: number) => {
  return num > 0 ? `+${num}` : `${num}`
}

const avatarText = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const tabs = [
  { title: 'Franchisee', value: 'franchisee', icon: 'tabler-building-store' },
  { title: 'Sales Associate', value: 'sales', icon: 'tabler-users' },
  { title: 'Staff', value: 'staff', icon: 'tabler-user' },
]
</script>

<template>
  <section>
    <!-- 👉 Tabs -->
    <VTabs v-model="currentTab" class="mb-6">
      <VTab v-for="tab in tabs" :key="tab.value" :value="tab.value">
        <VIcon :icon="tab.icon" start />
        {{ tab.title }}
      </VTab>
    </VTabs>

    <VWindow v-model="currentTab" class="disable-tab-transition">
      <VWindowItem v-for="tab in tabs" :key="tab.value" :value="tab.value">
        <!-- 👉 Widgets -->
        <div class="d-flex mb-6">
          <VRow>
            <template v-for="(data, id) in widgetData" :key="id">
              <VCol cols="12" md="3" sm="6">
                <VCard>
                  <VCardText>
                    <div class="d-flex justify-space-between">
                      <div class="d-flex flex-column gap-y-1">
                        <div class="text-body-1 text-high-emphasis">
                          {{ data.title }}
                        </div>
                        <div class="d-flex gap-x-2 align-center">
                          <h4 class="text-h4">
                            {{ data.value }}
                          </h4>
                          <div class="text-base" :class="data.change > 0 ? 'text-success' : 'text-error'">
                            ({{ prefixWithPlusNumber(data.change) }}%)
                          </div>
                        </div>
                        <div class="text-sm">
                          {{ data.desc }}
                        </div>
                      </div>
                      <VAvatar :color="data.iconColor" variant="tonal" rounded size="42">
                        <VIcon :icon="data.icon" size="26" />
                      </VAvatar>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </template>
          </VRow>
        </div>

        <VCard class="mb-6">
          <VCardItem class="pb-4">
            <VCardTitle>Filters</VCardTitle>
          </VCardItem>

          <VCardText>
            <VRow>
              <!-- 👉 Select Priority -->
              <VCol cols="12" sm="6">
                <AppSelect v-model="selectedPriority" placeholder="Select Priority" :items="priorities" clearable
                  clear-icon="tabler-x" />
              </VCol>
              <!-- 👉 Select Status -->
              <VCol cols="12" sm="6">
                <AppSelect v-model="selectedStatus" placeholder="Select Status" :items="statuses" clearable
                  clear-icon="tabler-x" />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex flex-wrap gap-4">
            <div class="me-3 d-flex gap-3">
              <AppSelect :model-value="itemsPerPage" :items="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'All' },
              ]" style="inline-size: 6.25rem;" @update:model-value="itemsPerPage = parseInt($event, 10)" />
            </div>
            <VSpacer />

            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="inline-size: 15.625rem;">
                <AppTextField v-model="searchQuery" placeholder="Search Task" />
              </div>

              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="secondary" prepend-icon="tabler-upload" @click="exportTasks">
                Export {{ selectedRows.length > 0 ? `(${selectedRows.length})` : 'All' }}
              </VBtn>

              <!-- 👉 Add task button - Commented out as requested -->
              <!-- <VBtn prepend-icon="tabler-plus">
                Add New Task
              </VBtn> -->
            </div>
          </VCardText>

          <VDivider />

          <!-- SECTION datatable -->
          <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
            :items="currentTasks" item-value="id" :items-length="totalTasks" :headers="headers" class="text-no-wrap"
            show-select @update:options="updateOptions">
            <!-- Task -->
            <template #item.task="{ item }">
              <div class="d-flex flex-column">
                <h6 class="text-base font-weight-medium">
                  {{ item.task }}
                </h6>
              </div>
            </template>

            <!-- Assigned To -->
            <template #item.assignedTo="{ item }">
              <div class="d-flex align-center gap-x-4">
                <VAvatar size="34" variant="tonal" color="primary">
                  <span>{{ avatarText(item.assignedTo) }}</span>
                </VAvatar>
                <div class="text-body-1">
                  {{ item.assignedTo }}
                </div>
              </div>
            </template>

            <!-- Priority -->
            <template #item.priority="{ item }">
              <VChip :color="resolvePriorityVariant(item.priority)" size="small" label class="text-capitalize">
                {{ item.priority }}
              </VChip>
            </template>

            <!-- Status -->
            <template #item.status="{ item }">
              <VChip :color="resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
                {{ item.status.replace('_', ' ') }}
              </VChip>
            </template>

            <!-- Due Date -->
            <template #item.dueDate="{ item }">
              <div class="text-body-1">
                {{ item.dueDate }}
              </div>
            </template>

            <!-- Actions -->
            <template #item.actions="{ item }">
              <VBtn icon variant="text" color="medium-emphasis">
                <VIcon icon="tabler-dots-vertical" />
                <VMenu activator="parent">
                  <VList>
                    <VListItem @click="viewTask(item.id)">
                      <template #prepend>
                        <VIcon icon="tabler-eye" />
                      </template>
                      <VListItemTitle>View</VListItemTitle>
                    </VListItem>

                    <VListItem @click="editTask(item.id)">
                      <template #prepend>
                        <VIcon icon="tabler-pencil" />
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

            <!-- pagination -->
            <template #bottom>
              <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalTasks" />
            </template>
          </VDataTableServer>
          <!-- SECTION -->
        </VCard>
      </VWindowItem>
    </VWindow>

    <!-- 👉 View Task Modal -->
    <VDialog v-model="isViewTaskModalVisible" max-width="600">
      <VCard v-if="selectedTask">
        <VCardItem>
          <VCardTitle>Task Details</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol cols="12">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Task</div>
                <div class="text-body-1 font-weight-medium">{{ selectedTask.task }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Assigned To</div>
                <div class="text-body-1">{{ selectedTask.assignedTo }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Due Date</div>
                <div class="text-body-1">{{ selectedTask.dueDate }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Priority</div>
                <VChip :color="resolvePriorityVariant(selectedTask.priority)" size="small" label
                  class="text-capitalize">
                  {{ selectedTask.priority }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Status</div>
                <VChip :color="resolveStatusVariant(selectedTask.status)" size="small" label class="text-capitalize">
                  {{ selectedTask.status.replace('_', ' ') }}
                </VChip>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isViewTaskModalVisible = false">
            Close
          </VBtn>
          <VBtn color="primary" @click="editTask(selectedTask.id)">
            Edit
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Task Modal -->
    <VDialog v-model="isEditTaskModalVisible" max-width="700">
      <VCard v-if="selectedTask">
        <VCardItem>
          <VCardTitle>Edit Task</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="saveTask">
            <VRow>
              <VCol cols="12">
                <AppTextField v-model="selectedTask.task" label="Task" placeholder="Enter task description" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="selectedTask.assignedTo" label="Assigned To" placeholder="Enter assignee name" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="selectedTask.dueDate" label="Due Date" type="date" />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="selectedTask.priority" label="Priority" :items="priorities"
                  placeholder="Select priority" />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="selectedTask.status" label="Status" :items="statuses" placeholder="Select status" />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isEditTaskModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="saveTask">
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Confirmation Dialog -->
    <VDialog v-model="isDeleteDialogVisible" max-width="500">
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete this task? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isDeleteDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="error" @click="deleteTask">
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
