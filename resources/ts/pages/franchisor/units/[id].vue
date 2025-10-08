<script setup lang="ts">
// 👉 Imports
import AddDocumentModal from '@/components/dialogs/AddDocumentModal.vue'
import CreateTaskModal from '@/components/dialogs/CreateTaskModal.vue'
import DocumentActionModal from '@/components/dialogs/DocumentActionModal.vue'

// 👉 Router
const router = useRouter()
const route = useRoute()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Unit ID from route
const unitId = computed(() => route.params.id as string)

// 👉 Mock unit data (in real app, this would be fetched from API)
const unitData = ref({
  id: 1,
  branchName: 'Downtown Coffee Hub',
  franchiseeName: 'John Smith',
  email: 'john.smith@email.com',
  contactNumber: '+1 555-123-4567',
  address: '123 Main St, Downtown',
  city: 'Los Angeles',
  state: 'California',
  country: 'United States',
  royaltyPercentage: 8.5,
  contractStartDate: '2024-01-15',
  renewalDate: '2027-01-15',
  status: 'active',
})

// 👉 Mock tasks data
const tasksData = ref([
  {
    id: 1,
    title: 'Monthly Inventory Check',
    description: 'Complete monthly inventory audit and report',
    category: 'Operations',
    assignedTo: 'Store Manager',
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
    assignedTo: 'HR Manager',
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
    assignedTo: 'Technician',
    startDate: '2024-01-20',
    dueDate: '2024-02-05',
    priority: 'high',
    status: 'pending',
  },
])

// 👉 Mock documents data
const documentsData = ref([
  {
    id: 1,
    title: 'Franchise Disclosure Document',
    description: 'Official FDD for this unit',
    fileName: 'FDD_Unit_001.pdf',
    fileSize: '2.4 MB',
    uploadDate: '2024-01-15',
    type: 'FDD',
    status: 'approved',
    comment: '',
  },
  {
    id: 2,
    title: 'Monthly Sales Report',
    description: 'Sales report for January 2024',
    fileName: 'Sales_Report_Jan_2024.pdf',
    fileSize: '1.2 MB',
    uploadDate: '2024-02-01',
    type: 'Sales Report',
    status: 'pending',
    comment: '',
  },
])

// 👉 Mock staff data
const staffData = ref([
  {
    id: 1,
    name: 'Alice Johnson',
    jobTitle: 'Store Manager',
    email: 'alice.johnson@email.com',
    shiftTime: '9:00 AM - 6:00 PM',
    status: 'working',
  },
  {
    id: 2,
    name: 'Bob Wilson',
    jobTitle: 'Barista',
    email: 'bob.wilson@email.com',
    shiftTime: '6:00 AM - 2:00 PM',
    status: 'working',
  },
  {
    id: 3,
    name: 'Carol Davis',
    jobTitle: 'Cashier',
    email: 'carol.davis@email.com',
    shiftTime: '2:00 PM - 10:00 PM',
    status: 'leave',
  },
])

// 👉 Mock products data
const productsData = ref([
  {
    id: 1,
    name: 'Premium Espresso',
    description: 'High-quality espresso blend',
    unitPrice: 93.75,
    category: 'Coffee',
    status: 'active',
    stock: 150,
  },
  {
    id: 2,
    name: 'Organic House Blend',
    description: 'Organic coffee house blend',
    unitPrice: 75.00,
    category: 'Coffee',
    status: 'active',
    stock: 5, // Low stock
  },
  {
    id: 3,
    name: 'Coffee Mug - Premium',
    description: 'Branded coffee mug with logo',
    unitPrice: 48.75,
    category: 'Merchandise',
    status: 'active',
    stock: 0, // Out of stock
  },
])

// 👉 Mock reviews data
const reviewsData = ref([
  {
    id: 1,
    customerName: 'Emma Thompson',
    rating: 5,
    comment: 'Excellent coffee and great service!',
    date: '2024-01-28',
    sentiment: 'positive',
  },
  {
    id: 2,
    customerName: 'James Miller',
    rating: 4,
    comment: 'Good coffee, but the wait time was a bit long.',
    date: '2024-01-27',
    sentiment: 'positive',
  },
  {
    id: 3,
    customerName: 'Lisa Brown',
    rating: 2,
    comment: 'Coffee was cold and service was slow.',
    date: '2024-01-26',
    sentiment: 'negative',
  },
])

// 👉 Modal states
const isAddTaskModalVisible = ref(false)
const isAddDocumentModalVisible = ref(false)
const isDocumentActionModalVisible = ref(false)
const selectedDocument = ref(null)
const documentAction = ref('')

// 👉 Task modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedTask = ref<any>(null)
const taskToDelete = ref<number | null>(null)

// 👉 Computed stats
const totalTasks = computed(() => tasksData.value.length)
const completedTasks = computed(() => tasksData.value.filter(task => task.status === 'completed').length)
const inProgressTasks = computed(() => tasksData.value.filter(task => task.status === 'in_progress').length)
const dueTasks = computed(() => {
  const today = new Date()
  return tasksData.value.filter(task => {
    const dueDate = new Date(task.dueDate)
    return dueDate <= today && task.status !== 'completed'
  }).length
})

const totalStaff = computed(() => staffData.value.length)
const workingStaff = computed(() => staffData.value.filter(staff => staff.status === 'working').length)
const staffOnLeave = computed(() => staffData.value.filter(staff => staff.status === 'leave').length)

const totalProducts = computed(() => productsData.value.length)
const totalStock = computed(() => productsData.value.reduce((sum, product) => sum + product.stock, 0))
const lowStockProducts = computed(() => productsData.value.filter(product => product.stock > 0 && product.stock <= 10).length)
const outOfStockProducts = computed(() => productsData.value.filter(product => product.stock === 0).length)

const avgRating = computed(() => {
  if (reviewsData.value.length === 0) return 0
  const sum = reviewsData.value.reduce((total, review) => total + review.rating, 0)
  return (sum / reviewsData.value.length).toFixed(1)
})
const positiveReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'positive').length)
const negativeReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'negative').length)

// 👉 Functions
const goBack = () => {
  router.push('/franchisor/my-units')
}

const resolveStatusVariant = (status: string) => {
  if (status === 'active' || status === 'completed' || status === 'approved') return 'success'
  if (status === 'pending' || status === 'in_progress') return 'warning'
  if (status === 'inactive' || status === 'rejected') return 'error'
  return 'secondary'
}

const resolvePriorityVariant = (priority: string) => {
  if (priority === 'high') return 'error'
  if (priority === 'medium') return 'warning'
  if (priority === 'low') return 'info'
  return 'secondary'
}

const openDocumentActionModal = (document: any, action: 'approve' | 'reject') => {
  selectedDocument.value = document
  documentAction.value = action
  isDocumentActionModalVisible.value = true
}

const onTaskCreated = (task: any) => {
  tasksData.value.push(task)
}

const onDocumentAdded = (document: any) => {
  documentsData.value.push(document)
}

const onDocumentActionConfirmed = (data: { document: any; action: string; comment: string }) => {
  const documentIndex = documentsData.value.findIndex(doc => doc.id === data.document.id)
  if (documentIndex !== -1) {
    documentsData.value[documentIndex].status = data.action === 'approve' ? 'approved' : 'rejected'
    documentsData.value[documentIndex].comment = data.comment
  }
}

// 👉 Task action handlers
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

  const index = tasksData.value.findIndex(task => task.id === taskToDelete.value)
  if (index !== -1) {
    tasksData.value.splice(index, 1)
  }

  isDeleteDialogVisible.value = false
  taskToDelete.value = null
}

const saveTask = () => {
  if (!selectedTask.value) return

  const index = tasksData.value.findIndex(task => task.id === selectedTask.value.id)
  if (index !== -1) {
    tasksData.value[index] = { ...selectedTask.value }
  }

  isEditTaskModalVisible.value = false
  selectedTask.value = null
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

const staffHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Job Title', key: 'jobTitle' },
  { title: 'Email', key: 'email' },
  { title: 'Shift Time', key: 'shiftTime' },
  { title: 'Status', key: 'status' },
]

const productHeaders = [
  { title: 'Product Name', key: 'name' },
  { title: 'Description', key: 'description' },
  { title: 'Category', key: 'category' },
  { title: 'Unit Price', key: 'unitPrice' },
  { title: 'Stock', key: 'stock' },
  { title: 'Status', key: 'status' },
]

const reviewHeaders = [
  { title: 'Customer', key: 'customerName' },
  { title: 'Rating', key: 'rating' },
  { title: 'Comment', key: 'comment' },
  { title: 'Date', key: 'date' },
  { title: 'Sentiment', key: 'sentiment' },
]
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-3">
            <VBtn icon variant="text" color="default" @click="goBack">
              <VIcon icon="tabler-arrow-left" />
            </VBtn>
            <div>
              <h2 class="text-h2 mb-1">
                {{ unitData.branchName }}
              </h2>
              <p class="text-body-1 text-medium-emphasis">
                Managed by {{ unitData.franchiseeName }} • {{ unitData.city }}, {{ unitData.state }}
              </p>
            </div>
          </div>
          <VChip :color="resolveStatusVariant(unitData.status)" size="large" label class="text-capitalize">
            {{ unitData.status }}
          </VChip>
        </div>
      </VCol>
    </VRow>

    <!-- Tabs -->
    <VTabs v-model="currentTab" class="mb-6">
      <VTab value="overview">
        <VIcon icon="tabler-info-circle" start />
        Overview
      </VTab>
      <VTab value="tasks">
        <VIcon icon="tabler-checklist" start />
        Tasks
      </VTab>
      <VTab value="documents">
        <VIcon icon="tabler-files" start />
        Documents
      </VTab>
      <VTab value="staffs">
        <VIcon icon="tabler-users" start />
        Staffs
      </VTab>
      <VTab value="inventory">
        <VIcon icon="tabler-package" start />
        Inventory Management
      </VTab>
      <VTab value="reviews">
        <VIcon icon="tabler-star" start />
        Customer Reviews
      </VTab>
    </VTabs>

    <VWindow v-model="currentTab" class="disable-tab-transition">
      <!-- Overview Tab -->
      <VWindowItem value="overview">
        <VCard>
          <VCardText>
            <VRow>
              <!-- Basic Information -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Basic Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Branch Name</div>
                          <div class="text-body-1 font-weight-medium">{{ unitData.branchName }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Franchisee Name</div>
                          <div class="text-body-1">{{ unitData.franchiseeName }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Email Address</div>
                          <div class="text-body-1">
                            <a :href="`mailto:${unitData.email}`" class="text-primary">
                              {{ unitData.email }}
                            </a>
                          </div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Contact Number</div>
                          <div class="text-body-1">{{ unitData.contactNumber }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Address</div>
                          <div class="text-body-1">{{ unitData.address }}, {{ unitData.city }}, {{ unitData.state }}, {{
                            unitData.country }}</div>
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Franchisee Details -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Franchisee Details</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Royalty Percentage</div>
                          <div class="text-body-1 font-weight-medium text-success">{{ unitData.royaltyPercentage }}%
                          </div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Contract Start Date</div>
                          <div class="text-body-1">{{ unitData.contractStartDate }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Renewal Date</div>
                          <div class="text-body-1">{{ unitData.renewalDate }}</div>
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VWindowItem>

      <!-- Tasks Tab -->
      <VWindowItem value="tasks">
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
            <VCardTitle>Tasks</VCardTitle>
          </VCardItem>

          <VDivider />

          <VDataTable :items="tasksData" :headers="taskHeaders" class="text-no-wrap" item-value="id">
            <!-- Task Info -->
            <template #item.taskInfo="{ item }">
              <div>
                <h6 class="text-base font-weight-medium">{{ item.title }}</h6>
                <div class="text-body-2 text-disabled">{{ item.description }}</div>
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

      <!-- Documents Tab -->
      <VWindowItem value="documents">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Unit Documents</VCardTitle>
            <template #append>
              <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddDocumentModalVisible = true">
                Add Document
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <VRow>
              <template v-for="document in documentsData" :key="document.id">
                <VCol cols="12" md="6" lg="4">
                  <VCard>
                    <VCardText>
                      <div class="d-flex align-center mb-3">
                        <VIcon icon="tabler-file-text" size="24" color="primary" class="me-3" />
                        <div>
                          <h6 class="text-h6">{{ document.title }}</h6>
                          <p class="text-body-2 text-disabled mb-0">{{ document.type }}</p>
                        </div>
                      </div>

                      <p class="text-body-2 mb-3">{{ document.description }}</p>

                      <div class="d-flex align-center justify-space-between mb-3">
                        <span class="text-body-2 text-disabled">{{ document.fileName }}</span>
                        <VChip size="small" color="secondary">{{ document.fileSize }}</VChip>
                      </div>

                      <div class="d-flex align-center justify-space-between mb-3">
                        <span class="text-body-2 text-disabled">{{ document.uploadDate }}</span>
                        <VChip :color="resolveStatusVariant(document.status)" size="small" label
                          class="text-capitalize">
                          {{ document.status }}
                        </VChip>
                      </div>
                    </VCardText>

                    <VCardActions>
                      <VBtn size="small" variant="text" color="primary" prepend-icon="tabler-download">
                        Download
                      </VBtn>
                      <VSpacer />
                      <VBtn v-if="document.status === 'pending'" size="small" variant="text" color="success"
                        @click="openDocumentActionModal(document, 'approve')">
                        Approve
                      </VBtn>
                      <VBtn v-if="document.status === 'pending'" size="small" variant="text" color="error"
                        @click="openDocumentActionModal(document, 'reject')">
                        Reject
                      </VBtn>
                    </VCardActions>
                  </VCard>
                </VCol>
              </template>
            </VRow>
          </VCardText>
        </VCard>
      </VWindowItem>

      <!-- Staff Tab -->
      <VWindowItem value="staffs">
        <!-- Stats Cards -->
        <VRow class="mb-6">
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="primary" variant="tonal">
                  <VIcon icon="tabler-users" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Total Employees</div>
                  <h4 class="text-h4">{{ totalStaff }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="success" variant="tonal">
                  <VIcon icon="tabler-user-check" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Working</div>
                  <h4 class="text-h4">{{ workingStaff }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="warning" variant="tonal">
                  <VIcon icon="tabler-user-minus" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">On Leave</div>
                  <h4 class="text-h4">{{ staffOnLeave }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <!-- Staff Table -->
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Staff Members</VCardTitle>
          </VCardItem>

          <VDivider />

          <VDataTable :items="staffData" :headers="staffHeaders" class="text-no-wrap" item-value="id">
            <!-- Name -->
            <template #item.name="{ item }">
              <div class="d-flex align-center gap-x-3">
                <VAvatar size="34" color="primary" variant="tonal">
                  <span>{{item.name.split(' ').map((n: string) => n[0]).join('')}}</span>
                </VAvatar>
                <div>
                  <h6 class="text-base font-weight-medium">{{ item.name }}</h6>
                  <div class="text-body-2 text-disabled">{{ item.email }}</div>
                </div>
              </div>
            </template>

            <!-- Status -->
            <template #item.status="{ item }">
              <VChip :color="resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
                {{ item.status }}
              </VChip>
            </template>
          </VDataTable>
        </VCard>
      </VWindowItem>

      <!-- Inventory Tab -->
      <VWindowItem value="inventory">
        <!-- Stats Cards -->
        <VRow class="mb-6">
          <VCol cols="12" md="3">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="primary" variant="tonal">
                  <VIcon icon="tabler-package" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Total Products</div>
                  <h4 class="text-h4">{{ totalProducts }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="3">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="info" variant="tonal">
                  <VIcon icon="tabler-stack" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Total Stock</div>
                  <h4 class="text-h4">{{ totalStock }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="3">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="warning" variant="tonal">
                  <VIcon icon="tabler-alert-triangle" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Low Stock</div>
                  <h4 class="text-h4">{{ lowStockProducts }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="3">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="error" variant="tonal">
                  <VIcon icon="tabler-x" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Out of Stock</div>
                  <h4 class="text-h4">{{ outOfStockProducts }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <!-- Products Table -->
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Inventory</VCardTitle>
          </VCardItem>

          <VDivider />

          <VDataTable :items="productsData" :headers="productHeaders" class="text-no-wrap" item-value="id">
            <!-- Unit Price -->
            <template #item.unitPrice="{ item }">
              <div class="text-body-1 font-weight-medium">
                SAR {{ item.unitPrice.toFixed(2) }}
              </div>
            </template>

            <!-- Stock -->
            <template #item.stock="{ item }">
              <VChip :color="item.stock === 0 ? 'error' : item.stock <= 10 ? 'warning' : 'success'" size="small" label>
                {{ item.stock }}
              </VChip>
            </template>

            <!-- Status -->
            <template #item.status="{ item }">
              <VChip :color="resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
                {{ item.status }}
              </VChip>
            </template>
          </VDataTable>
        </VCard>
      </VWindowItem>

      <!-- Reviews Tab -->
      <VWindowItem value="reviews">
        <!-- Stats Cards -->
        <VRow class="mb-6">
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="primary" variant="tonal">
                  <VIcon icon="tabler-star" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Average Rating</div>
                  <h4 class="text-h4">{{ avgRating }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="success" variant="tonal">
                  <VIcon icon="tabler-thumb-up" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Positive Reviews</div>
                  <h4 class="text-h4">{{ positiveReviews }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar size="44" rounded color="error" variant="tonal">
                  <VIcon icon="tabler-thumb-down" size="26" />
                </VAvatar>
                <div class="ms-4">
                  <div class="text-body-2 text-disabled">Negative Reviews</div>
                  <h4 class="text-h4">{{ negativeReviews }}</h4>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <!-- Reviews Table -->
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Customer Reviews</VCardTitle>
          </VCardItem>

          <VDivider />

          <VDataTable :items="reviewsData" :headers="reviewHeaders" class="text-no-wrap" item-value="id">
            <!-- Rating -->
            <template #item.rating="{ item }">
              <div class="d-flex align-center gap-1">
                <VRating :model-value="item.rating" readonly size="small" color="warning" />
                <span class="text-body-2">({{ item.rating }})</span>
              </div>
            </template>

            <!-- Comment -->
            <template #item.comment="{ item }">
              <div class="text-body-2" style="max-width: 300px;">
                {{ item.comment }}
              </div>
            </template>

            <!-- Sentiment -->
            <template #item.sentiment="{ item }">
              <VChip :color="item.sentiment === 'positive' ? 'success' : 'error'" size="small" label
                class="text-capitalize">
                {{ item.sentiment }}
              </VChip>
            </template>
          </VDataTable>
        </VCard>
      </VWindowItem>
    </VWindow>

    <!-- Modals -->
    <CreateTaskModal v-model:is-dialog-visible="isAddTaskModalVisible" @task-created="onTaskCreated" />

    <AddDocumentModal v-model:is-dialog-visible="isAddDocumentModalVisible" @document-added="onDocumentAdded" />

    <DocumentActionModal v-model:is-dialog-visible="isDocumentActionModalVisible" :document="selectedDocument"
      :action="documentAction" @document-action-confirmed="onDocumentActionConfirmed" />

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
              <VChip :color="resolvePriorityVariant(selectedTask.priority)" size="small" label class="text-capitalize">
                {{ selectedTask.priority }}
              </VChip>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">Status</div>
              <VChip :color="resolveStatusVariant(selectedTask.status)" size="small" label class="text-capitalize">
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
              <VTextField v-model="selectedTask.title" label="Task Title" placeholder="Enter task title" required />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="selectedTask.description" label="Description" placeholder="Enter task description"
                rows="3" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="selectedTask.category" label="Category" placeholder="Enter category" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="selectedTask.assignedTo" label="Assigned To" placeholder="Enter assignee" required />
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
