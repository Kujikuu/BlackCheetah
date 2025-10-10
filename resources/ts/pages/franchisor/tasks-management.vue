<script setup lang="ts">
// 👉 Imports
import CreateTaskModal from '@/components/dialogs/CreateTaskModal.vue'

// 👉 Router
const router = useRouter()

// 👉 Current tab
const currentTab = ref('franchisee')

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedTask = ref<any>(null)
const taskToDelete = ref<number | null>(null)

// 👉 Mock franchisee tasks data
const franchiseeTasksData = ref([
    {
        id: 1,
        title: 'Monthly Inventory Check',
        description: 'Complete monthly inventory audit and report',
        category: 'Operations',
        assignedTo: 'John Smith (Downtown Coffee Hub)',
        unitName: 'Downtown Coffee Hub',
        startDate: '2024-01-01',
        dueDate: '2024-01-31',
        priority: 'high',
        status: 'completed',
    },
    {
        id: 2,
        title: 'Staff Training Session',
        description: 'Conduct quarterly staff training on new procedures',
        category: 'Training',
        assignedTo: 'Sarah Johnson (Uptown Cafe)',
        unitName: 'Uptown Cafe',
        startDate: '2024-01-15',
        dueDate: '2024-01-30',
        priority: 'medium',
        status: 'in_progress',
    },
    {
        id: 3,
        title: 'Equipment Maintenance',
        description: 'Schedule and complete equipment maintenance',
        category: 'Maintenance',
        assignedTo: 'Mike Wilson (Central Plaza)',
        unitName: 'Central Plaza',
        startDate: '2024-01-20',
        dueDate: '2024-02-05',
        priority: 'high',
        status: 'pending',
    },
    {
        id: 4,
        title: 'Marketing Campaign Review',
        description: 'Review and approve local marketing materials',
        category: 'Marketing',
        assignedTo: 'Lisa Brown (Westside Branch)',
        unitName: 'Westside Branch',
        startDate: '2024-01-25',
        dueDate: '2024-02-10',
        priority: 'medium',
        status: 'pending',
    },
])

// 👉 Mock sales tasks data
const salesTasksData = ref([
    {
        id: 5,
        title: 'Lead Follow-up Campaign',
        description: 'Follow up with potential franchisees from last quarter',
        category: 'Lead Management',
        assignedTo: 'Alex Rodriguez (Sales Associate)',
        unitName: 'N/A',
        startDate: '2024-01-10',
        dueDate: '2024-01-25',
        priority: 'high',
        status: 'in_progress',
    },
    {
        id: 6,
        title: 'Franchise Presentation Prep',
        description: 'Prepare presentation materials for upcoming franchise expo',
        category: 'Sales',
        assignedTo: 'Emma Thompson (Senior Sales)',
        unitName: 'N/A',
        startDate: '2024-01-12',
        dueDate: '2024-02-01',
        priority: 'high',
        status: 'pending',
    },
    {
        id: 7,
        title: 'Territory Analysis',
        description: 'Analyze potential markets for franchise expansion',
        category: 'Market Research',
        assignedTo: 'David Chen (Sales Manager)',
        unitName: 'N/A',
        startDate: '2024-01-18',
        dueDate: '2024-02-15',
        priority: 'medium',
        status: 'pending',
    },
    {
        id: 8,
        title: 'Client Onboarding',
        description: 'Complete onboarding process for new franchisee',
        category: 'Onboarding',
        assignedTo: 'Rachel Green (Sales Associate)',
        unitName: 'N/A',
        startDate: '2024-01-05',
        dueDate: '2024-01-20',
        priority: 'high',
        status: 'completed',
    },
])

// 👉 Modal states
const isAddTaskModalVisible = ref(false)

// 👉 Computed stats for franchisee tasks
const franchiseeTotalTasks = computed(() => franchiseeTasksData.value.length)
const franchiseeCompletedTasks = computed(() => franchiseeTasksData.value.filter(task => task.status === 'completed').length)
const franchiseeInProgressTasks = computed(() => franchiseeTasksData.value.filter(task => task.status === 'in_progress').length)
const franchiseeDueTasks = computed(() => {
    const today = new Date()
    return franchiseeTasksData.value.filter(task => {
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

// 👉 Functions
const resolveStatusVariant = (status: string) => {
    if (status === 'completed') return 'success'
    if (status === 'in_progress') return 'warning'
    if (status === 'pending') return 'info'
    return 'secondary'
}

const resolvePriorityVariant = (priority: string) => {
    if (priority === 'high') return 'error'
    if (priority === 'medium') return 'warning'
    if (priority === 'low') return 'info'
    return 'secondary'
}

const onTaskCreated = (task: any) => {
    // Add task to appropriate data array based on current tab
    if (currentTab.value === 'franchisee') {
        franchiseeTasksData.value.push({
            ...task,
            id: Math.max(...franchiseeTasksData.value.map(t => t.id)) + 1,
        })
    } else {
        salesTasksData.value.push({
            ...task,
            id: Math.max(...salesTasksData.value.map(t => t.id)) + 1,
        })
    }
}

// 👉 Headers
const taskHeaders = [
    { title: 'Task Info', key: 'taskInfo' },
    { title: 'Category', key: 'category' },
    { title: 'Assigned To', key: 'assignedTo' },
    { title: 'Unit', key: 'unitName' },
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

const editTask = (task: any) => {
    selectedTask.value = { ...task }
    isEditTaskModalVisible.value = true
}

const confirmDelete = (id: number) => {
    taskToDelete.value = id
    isDeleteDialogVisible.value = true
}

const deleteTask = () => {
    if (taskToDelete.value === null) return

    // Remove from appropriate data array based on current tab
    if (currentTab.value === 'franchisee') {
        const index = franchiseeTasksData.value.findIndex(task => task.id === taskToDelete.value)
        if (index !== -1) {
            franchiseeTasksData.value.splice(index, 1)
        }
    } else {
        const index = salesTasksData.value.findIndex(task => task.id === taskToDelete.value)
        if (index !== -1) {
            salesTasksData.value.splice(index, 1)
        }
    }

    isDeleteDialogVisible.value = false
    taskToDelete.value = null
}

const saveTask = () => {
    if (!selectedTask.value) return

    // Update task in appropriate data array based on current tab
    if (currentTab.value === 'franchisee') {
        const index = franchiseeTasksData.value.findIndex(task => task.id === selectedTask.value.id)
        if (index !== -1) {
            franchiseeTasksData.value[index] = { ...selectedTask.value }
        }
    } else {
        const index = salesTasksData.value.findIndex(task => task.id === selectedTask.value.id)
        if (index !== -1) {
            salesTasksData.value[index] = { ...selectedTask.value }
        }
    }

    isEditTaskModalVisible.value = false
    selectedTask.value = null
}
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
                    <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddTaskModalVisible = true">
                        Create Task
                    </VBtn>
                </div>
            </VCol>
        </VRow>

        <!-- Tabs -->
        <VTabs v-model="currentTab" class="mb-6">
            <VTab value="franchisee">
                <VIcon icon="tabler-building-store" start />
                Franchisee Tasks
            </VTab>
            <VTab value="sales">
                <VIcon icon="tabler-user-star" start />
                Sales Tasks
            </VTab>
        </VTabs>

        <VWindow v-model="currentTab" class="disable-tab-transition">
            <!-- Franchisee Tasks Tab -->
            <VWindowItem value="franchisee">
                <!-- Stats Cards -->
                <VRow class="mb-6">
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="primary" variant="tonal">
                                    <VIcon icon="tabler-checklist" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Total Tasks</div>
                                    <h4 class="text-h4">{{ franchiseeTotalTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="success" variant="tonal">
                                    <VIcon icon="tabler-check" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Completed</div>
                                    <h4 class="text-h4">{{ franchiseeCompletedTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="warning" variant="tonal">
                                    <VIcon icon="tabler-clock" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">In Progress</div>
                                    <h4 class="text-h4">{{ franchiseeInProgressTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="error" variant="tonal">
                                    <VIcon icon="tabler-alert-circle" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Due</div>
                                    <h4 class="text-h4">{{ franchiseeDueTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>

                <!-- Franchisee Tasks Table -->
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle>Franchisee Tasks</VCardTitle>
                    </VCardItem>

                    <VDivider />

                    <VDataTable :items="franchiseeTasksData" :headers="taskHeaders" class="text-no-wrap"
                        item-value="id">
                        <!-- Task Info -->
                        <template #item.taskInfo="{ item }">
                            <div>
                                <h6 class="text-base font-weight-medium">{{ item.title }}</h6>
                                <div class="text-body-2 text-disabled">{{ item.description }}</div>
                            </div>
                        </template>

                        <!-- Unit -->
                        <template #item.unitName="{ item }">
                            <div class="text-body-1">{{ item.unitName }}</div>
                        </template>

                        <!-- Priority -->
                        <template #item.priority="{ item }">
                            <VChip :color="resolvePriorityVariant(item.priority)" size="small" label
                                class="text-capitalize">
                                {{ item.priority }}
                            </VChip>
                        </template>

                        <!-- Status -->
                        <template #item.status="{ item }">
                            <VChip :color="resolveStatusVariant(item.status)" size="small" label
                                class="text-capitalize">
                                {{ item.status }}
                            </VChip>
                        </template>

                        <!-- Actions -->
                        <template #item.actions="{ item }">
                            <VBtn icon variant="text" color="medium-emphasis" size="small">
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
                </VCard>
            </VWindowItem>

            <!-- Sales Tasks Tab -->
            <VWindowItem value="sales">
                <!-- Stats Cards -->
                <VRow class="mb-6">
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="primary" variant="tonal">
                                    <VIcon icon="tabler-checklist" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Total Tasks</div>
                                    <h4 class="text-h4">{{ salesTotalTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="success" variant="tonal">
                                    <VIcon icon="tabler-check" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Completed</div>
                                    <h4 class="text-h4">{{ salesCompletedTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="warning" variant="tonal">
                                    <VIcon icon="tabler-clock" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">In Progress</div>
                                    <h4 class="text-h4">{{ salesInProgressTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="error" variant="tonal">
                                    <VIcon icon="tabler-alert-circle" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">Due</div>
                                    <h4 class="text-h4">{{ salesDueTasks }}</h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>

                <!-- Sales Tasks Table -->
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle>Sales Tasks</VCardTitle>
                    </VCardItem>

                    <VDivider />

                    <VDataTable :items="salesTasksData" :headers="taskHeaders" class="text-no-wrap" item-value="id">
                        <!-- Task Info -->
                        <template #item.taskInfo="{ item }">
                            <div>
                                <h6 class="text-base font-weight-medium">{{ item.title }}</h6>
                                <div class="text-body-2 text-disabled">{{ item.description }}</div>
                            </div>
                        </template>

                        <!-- Unit -->
                        <template #item.unitName="{ item }">
                            <div class="text-body-1">{{ item.unitName }}</div>
                        </template>

                        <!-- Priority -->
                        <template #item.priority="{ item }">
                            <VChip :color="resolvePriorityVariant(item.priority)" size="small" label
                                class="text-capitalize">
                                {{ item.priority }}
                            </VChip>
                        </template>

                        <!-- Status -->
                        <template #item.status="{ item }">
                            <VChip :color="resolveStatusVariant(item.status)" size="small" label
                                class="text-capitalize">
                                {{ item.status }}
                            </VChip>
                        </template>

                        <!-- Actions -->
                        <template #item.actions="{ item }">
                            <VBtn icon variant="text" color="medium-emphasis" size="small">
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
                </VCard>
            </VWindowItem>
        </VWindow>

        <!-- Create Task Modal -->
        <CreateTaskModal v-model:is-dialog-visible="isAddTaskModalVisible" @task-created="onTaskCreated" />

        <!-- View Task Modal -->
        <VDialog v-model="isViewTaskModalVisible" max-width="600">
            <VCard>
                <VCardTitle class="text-h5 pa-6 pb-4">
                    Task Details
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-6" v-if="selectedTask">
                    <VRow>
                        <VCol cols="12">
                            <h6 class="text-h6 mb-2">{{ selectedTask.title }}</h6>
                            <p class="text-body-1 mb-4">{{ selectedTask.description }}</p>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Category</div>
                            <div class="text-body-1">{{ selectedTask.category }}</div>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Assigned To</div>
                            <div class="text-body-1">{{ selectedTask.assignedTo }}</div>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Start Date</div>
                            <div class="text-body-1">{{ selectedTask.startDate }}</div>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Due Date</div>
                            <div class="text-body-1">{{ selectedTask.dueDate }}</div>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Priority</div>
                            <VChip :color="resolvePriorityVariant(selectedTask.priority)" size="small" label
                                class="text-capitalize">
                                {{ selectedTask.priority }}
                            </VChip>
                        </VCol>
                        <VCol cols="12" md="6">
                            <div class="text-body-2 text-disabled mb-1">Status</div>
                            <VChip :color="resolveStatusVariant(selectedTask.status)" size="small" label
                                class="text-capitalize">
                                {{ selectedTask.status }}
                            </VChip>
                        </VCol>
                    </VRow>
                </VCardText>

                <VDivider />

                <VCardActions class="pa-6">
                    <VSpacer />
                    <VBtn color="secondary" variant="tonal" @click="isViewTaskModalVisible = false">
                        Close
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>

        <!-- Edit Task Modal -->
        <VDialog v-model="isEditTaskModalVisible" max-width="600" persistent>
            <VCard>
                <VCardTitle class="text-h5 pa-6 pb-4">
                    Edit Task
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-6" v-if="selectedTask">
                    <VRow>
                        <VCol cols="12">
                            <VTextField v-model="selectedTask.title" label="Task Title" placeholder="Enter task title"
                                required />
                        </VCol>
                        <VCol cols="12">
                            <VTextarea v-model="selectedTask.description" label="Description"
                                placeholder="Enter task description" rows="3" required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField v-model="selectedTask.category" label="Category" placeholder="Enter category"
                                required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField v-model="selectedTask.assignedTo" label="Assigned To"
                                placeholder="Enter assignee" required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField v-model="selectedTask.startDate" label="Start Date" type="date" required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField v-model="selectedTask.dueDate" label="Due Date" type="date" required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VSelect v-model="selectedTask.priority" label="Priority" :items="[
                                { title: 'Low', value: 'low' },
                                { title: 'Medium', value: 'medium' },
                                { title: 'High', value: 'high' }
                            ]" required />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VSelect v-model="selectedTask.status" label="Status" :items="[
                                { title: 'Pending', value: 'pending' },
                                { title: 'In Progress', value: 'in_progress' },
                                { title: 'Completed', value: 'completed' }
                            ]" required />
                        </VCol>
                    </VRow>
                </VCardText>

                <VDivider />

                <VCardActions class="pa-6">
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

        <!-- Delete Confirmation Dialog -->
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
