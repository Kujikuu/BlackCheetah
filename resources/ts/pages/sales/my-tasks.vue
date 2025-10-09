<script setup lang="ts">
// 👉 Router
const router = useRouter()

// 👉 Modal states
const isViewTaskModalVisible = ref(false)
const isStatusChangeModalVisible = ref(false)
const selectedTask = ref<any>(null)

// 👉 Mock all tasks data (combined franchisee and sales)
const allTasksData = ref([
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



// 👉 Computed stats for all tasks
const totalTasks = computed(() => allTasksData.value.length)
const completedTasks = computed(() => allTasksData.value.filter(task => task.status === 'completed').length)
const inProgressTasks = computed(() => allTasksData.value.filter(task => task.status === 'in_progress').length)
const dueTasks = computed(() => {
    const today = new Date()
    return allTasksData.value.filter(task => {
        const dueDate = new Date(task.dueDate)
        return dueDate <= today && task.status !== 'completed'
    }).length
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

const changeTaskStatus = (task: any) => {
    selectedTask.value = { ...task }
    isStatusChangeModalVisible.value = true
}

const updateTaskStatus = (newStatus: string) => {
    if (!selectedTask.value) return

    // Update task status in combined data array
    const index = allTasksData.value.findIndex(task => task.id === selectedTask.value.id)
    if (index !== -1) {
        allTasksData.value[index].status = newStatus
    }

    isStatusChangeModalVisible.value = false
    selectedTask.value = null
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
            <VCol cols="12" md="3">
                <VCard>
                    <VCardText class="d-flex align-center">
                        <VAvatar size="44" rounded color="primary" variant="tonal">
                            <VIcon icon="tabler-checklist" size="26" />
                        </VAvatar>
                        <div class="ms-4">
                            <div class="text-body-2 text-disabled">Total Tasks</div>
                            <h4 class="text-h4">{{ totalTasks }}</h4>
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
                            <h4 class="text-h4">{{ completedTasks }}</h4>
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
                            <h4 class="text-h4">{{ inProgressTasks }}</h4>
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
                            <h4 class="text-h4">{{ dueTasks }}</h4>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <!-- Tasks Table -->
        <VCard>
            <VCardItem class="pb-4">
                <VCardTitle>My Tasks</VCardTitle>
            </VCardItem>

            <VDivider />

            <VDataTable :items="allTasksData" :headers="taskHeaders" class="text-no-wrap"
                item-value="id">
                        <!-- Task Info -->
                        <template #item.taskInfo="{ item }">
                            <div>
                                <h6 class="text-base font-weight-medium">{{ item.title }}</h6>
                                <div class="text-body-2 text-disabled">{{ item.description }}</div>
                            </div>
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

        <!-- Status Change Modal -->
        <VDialog v-model="isStatusChangeModalVisible" max-width="400">
            <VCard>
                <VCardTitle class="text-h5 pa-6 pb-4">
                    Change Task Status
                </VCardTitle>

                <VDivider />

                <VCardText class="pa-6" v-if="selectedTask">
                    <div class="mb-4">
                        <h6 class="text-base font-weight-medium mb-2">{{ selectedTask.title }}</h6>
                        <div class="text-body-2 text-disabled">Current Status: 
                            <VChip :color="resolveStatusVariant(selectedTask.status)" size="small" label class="text-capitalize ml-2">
                                {{ selectedTask.status }}
                            </VChip>
                        </div>
                    </div>
                    
                    <VSelect 
                        label="New Status" 
                        :items="[
                            { title: 'Pending', value: 'pending' },
                            { title: 'In Progress', value: 'in_progress' },
                            { title: 'Completed', value: 'completed' }
                        ]" 
                        :model-value="selectedTask.status"
                        @update:model-value="updateTaskStatus"
                        required 
                    />
                </VCardText>

                <VDivider />

                <VCardActions class="pa-6">
                    <VSpacer />
                    <VBtn color="secondary" variant="tonal" @click="isStatusChangeModalVisible = false">
                        Cancel
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </section>
</template>
