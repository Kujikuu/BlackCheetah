<script setup lang="ts">
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
const selectedRows = ref([])

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

// Mock data - Replace with actual API call
const tasksData = ref({
  franchisee: {
    tasks: [
      {
        id: 1,
        task: 'Complete monthly report',
        assignedTo: 'Downtown Store',
        priority: 'high',
        status: 'in_progress',
        dueDate: '2024-01-20',
      },
      {
        id: 2,
        task: 'Staff training completion',
        assignedTo: 'Mall Location',
        priority: 'medium',
        status: 'completed',
        dueDate: '2024-01-18',
      },
    ],
    total: 2,
  },
  sales: {
    tasks: [
      {
        id: 3,
        task: 'Follow up with leads',
        assignedTo: 'John Smith',
        priority: 'high',
        status: 'in_progress',
        dueDate: '2024-01-19',
      },
      {
        id: 4,
        task: 'Prepare sales presentation',
        assignedTo: 'Sarah Johnson',
        priority: 'medium',
        status: 'due',
        dueDate: '2024-01-17',
      },
    ],
    total: 2,
  },
  staff: {
    tasks: [
      {
        id: 5,
        task: 'Update inventory system',
        assignedTo: 'Michael Brown',
        priority: 'low',
        status: 'completed',
        dueDate: '2024-01-16',
      },
      {
        id: 6,
        task: 'Customer service training',
        assignedTo: 'Emily Davis',
        priority: 'medium',
        status: 'in_progress',
        dueDate: '2024-01-21',
      },
    ],
    total: 2,
  },
})

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

  // TODO: Implement API call
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
const selectedTask = ref<any>(null)

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

  // TODO: Implement API call
  const taskList = currentTab.value === 'franchisee' 
    ? tasksData.value.franchisee.tasks 
    : currentTab.value === 'sales' 
      ? tasksData.value.sales.tasks 
      : tasksData.value.staff.tasks

  const index = taskList.findIndex(task => task.id === selectedTask.value.id)
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

const widgetData = computed(() => {
  // In real implementation, these would be calculated based on currentTab
  return [
    { title: 'Total', value: '156', change: 12, desc: 'All tasks', icon: 'tabler-list-check', iconColor: 'primary' },
    { title: 'Completed', value: '89', change: 18, desc: 'Finished tasks', icon: 'tabler-circle-check', iconColor: 'success' },
    { title: 'In Progress', value: '45', change: 5, desc: 'Active tasks', icon: 'tabler-progress', iconColor: 'info' },
    { title: 'Due', value: '22', change: -3, desc: 'Overdue tasks', icon: 'tabler-alert-circle', iconColor: 'error' },
  ]
})

const tabs = [
  { title: 'Franchisee', value: 'franchisee', icon: 'tabler-building-store' },
  { title: 'Sales Associate', value: 'sales', icon: 'tabler-users' },
  { title: 'Staff', value: 'staff', icon: 'tabler-user' },
]
</script>

<template>
  <section>
    <!-- 👉 Tabs -->
    <VTabs
      v-model="currentTab"
      class="mb-6"
    >
      <VTab
        v-for="tab in tabs"
        :key="tab.value"
        :value="tab.value"
      >
        <VIcon
          :icon="tab.icon"
          start
        />
        {{ tab.title }}
      </VTab>
    </VTabs>

    <VWindow
      v-model="currentTab"
      class="disable-tab-transition"
    >
      <VWindowItem
        v-for="tab in tabs"
        :key="tab.value"
        :value="tab.value"
      >
        <!-- 👉 Widgets -->
        <div class="d-flex mb-6">
          <VRow>
            <template
              v-for="(data, id) in widgetData"
              :key="id"
            >
              <VCol
                cols="12"
                md="3"
                sm="6"
              >
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
                          <div
                            class="text-base"
                            :class="data.change > 0 ? 'text-success' : 'text-error'"
                          >
                            ({{ prefixWithPlus(data.change) }}%)
                          </div>
                        </div>
                        <div class="text-sm">
                          {{ data.desc }}
                        </div>
                      </div>
                      <VAvatar
                        :color="data.iconColor"
                        variant="tonal"
                        rounded
                        size="42"
                      >
                        <VIcon
                          :icon="data.icon"
                          size="26"
                        />
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
              <VCol
                cols="12"
                sm="6"
              >
                <AppSelect
                  v-model="selectedPriority"
                  placeholder="Select Priority"
                  :items="priorities"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
              <!-- 👉 Select Status -->
              <VCol
                cols="12"
                sm="6"
              >
                <AppSelect
                  v-model="selectedStatus"
                  placeholder="Select Status"
                  :items="statuses"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex flex-wrap gap-4">
            <div class="me-3 d-flex gap-3">
              <AppSelect
                :model-value="itemsPerPage"
                :items="[
                  { value: 10, title: '10' },
                  { value: 25, title: '25' },
                  { value: 50, title: '50' },
                  { value: 100, title: '100' },
                  { value: -1, title: 'All' },
                ]"
                style="inline-size: 6.25rem;"
                @update:model-value="itemsPerPage = parseInt($event, 10)"
              />
            </div>
            <VSpacer />

            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="inline-size: 15.625rem;">
                <AppTextField
                  v-model="searchQuery"
                  placeholder="Search Task"
                />
              </div>

              <!-- 👉 Export button -->
              <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-upload"
                @click="exportTasks"
              >
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
          <VDataTableServer
            v-model:items-per-page="itemsPerPage"
            v-model:model-value="selectedRows"
            v-model:page="page"
            :items="currentTasks"
            item-value="id"
            :items-length="totalTasks"
            :headers="headers"
            class="text-no-wrap"
            show-select
            @update:options="updateOptions"
          >
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
                <VAvatar
                  size="34"
                  variant="tonal"
                  color="primary"
                >
                  <span>{{ avatarText(item.assignedTo) }}</span>
                </VAvatar>
                <div class="text-body-1">
                  {{ item.assignedTo }}
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
              <VBtn
                icon
                variant="text"
                color="medium-emphasis"
              >
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
              <TablePagination
                v-model:page="page"
                :items-per-page="itemsPerPage"
                :total-items="totalTasks"
              />
            </template>
          </VDataTableServer>
          <!-- SECTION -->
        </VCard>
      </VWindowItem>
    </VWindow>

    <!-- 👉 View Task Modal -->
    <VDialog
      v-model="isViewTaskModalVisible"
      max-width="600"
    >
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
                <VChip
                  :color="resolvePriorityVariant(selectedTask.priority)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ selectedTask.priority }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Status</div>
                <VChip
                  :color="resolveStatusVariant(selectedTask.status)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ selectedTask.status.replace('_', ' ') }}
                </VChip>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewTaskModalVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            color="primary"
            @click="editTask(selectedTask.id)"
          >
            Edit
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Task Modal -->
    <VDialog
      v-model="isEditTaskModalVisible"
      max-width="700"
    >
      <VCard v-if="selectedTask">
        <VCardItem>
          <VCardTitle>Edit Task</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="saveTask">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="selectedTask.task"
                  label="Task"
                  placeholder="Enter task description"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedTask.assignedTo"
                  label="Assigned To"
                  placeholder="Enter assignee name"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedTask.dueDate"
                  label="Due Date"
                  type="date"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedTask.priority"
                  label="Priority"
                  :items="priorities"
                  placeholder="Select priority"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedTask.status"
                  label="Status"
                  :items="statuses"
                  placeholder="Select status"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
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

    <!-- 👉 Delete Confirmation Dialog -->
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
