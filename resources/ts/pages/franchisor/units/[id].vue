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

// 👉 Unit data and loading state
const unitData = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// 👉 Tasks data
const tasksData = ref<any[]>([])
const tasksLoading = ref(false)
const tasksError = ref<string | null>(null)

// 👉 Documents data
const documentsData = ref<any[]>([])
const documentsLoading = ref(false)
const documentsError = ref<string | null>(null)

// 👉 Staff data
const staffData = ref<any[]>([])
const staffLoading = ref(false)
const staffError = ref<string | null>(null)

// 👉 Products data
const productsData = ref<any[]>([])
const productsLoading = ref(false)
const productsError = ref<string | null>(null)

// 👉 Reviews data
const reviewsData = ref<any[]>([])
const reviewsLoading = ref(false)
const reviewsError = ref<string | null>(null)

// 👉 Modal states
const isAddTaskModalVisible = ref(false)
const isAddDocumentModalVisible = ref(false)
const isDocumentActionModalVisible = ref(false)
const selectedDocument = ref<any>(null)
const documentAction = ref<'approve' | 'reject'>('approve')

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
  if (reviewsData.value.length === 0)
    return '0.0'
  const sum = reviewsData.value.reduce((total, review) => total + review.rating, 0)

  return (sum / reviewsData.value.length).toFixed(1)
})

const positiveReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'positive').length)
const negativeReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'negative').length)
const totalReviews = computed(() => reviewsData.value.length)
const publishedReviews = computed(() => reviewsData.value.filter(review => review.status === 'published').length)
const draftReviews = computed(() => reviewsData.value.filter(review => review.status === 'draft').length)
const verifiedReviews = computed(() => reviewsData.value.filter(review => review.verified_purchase).length)

// 👉 Functions
const loadUnitData = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/units/${unitId.value}`)

    if (response.success && response.data) {
      // Transform API data to match frontend structure
      unitData.value = {
        id: response.data.id,
        branchName: response.data.unit_name || 'Unnamed Unit',
        franchiseeName: response.data.franchisee?.name || 'Unassigned',
        email: response.data.franchisee?.email || 'unassigned@example.com',
        contactNumber: response.data.franchisee?.phone || response.data.phone || 'Not available',
        address: response.data.address || 'Address not available',
        city: response.data.city || 'Unknown',
        state: response.data.state_province || 'Unknown',
        country: response.data.country || 'Unknown',
        royaltyPercentage: 8.5, // This would come from franchise relationship
        contractStartDate: response.data.lease_start_date || '2024-01-01',
        renewalDate: response.data.lease_end_date || '2027-01-01',
        status: response.data.status || 'inactive',
        type: response.data.unit_type || 'other',
        sizeSqft: response.data.size_sqft || 0,
        capacity: response.data.capacity || 0,
        openingDate: response.data.opening_date || null,
        monthlyRent: response.data.monthly_rent || 0,
      }
    }
    else {
      error.value = 'Unit not found'
    }
  }
  catch (err: any) {
    console.error('Failed to load unit data:', err)
    error.value = err?.data?.message || 'Failed to load unit data'
    unitData.value = null
  }
  finally {
    loading.value = false
  }
}

const loadTasksData = async () => {
  if (!unitId.value)
    return

  tasksLoading.value = true
  tasksError.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/tasks?unit_id=${unitId.value}`)

    if (response.success && response.data?.data) {
      tasksData.value = response.data.data.map((task: any) => ({
        id: task.id,
        title: task.title,
        description: task.description || '',
        category: task.type || 'General',
        assignedTo: task.assigned_to_user?.name || 'Unassigned',
        startDate: task.started_at ? new Date(task.started_at).toISOString().split('T')[0] : null,
        dueDate: task.due_date || null,
        priority: task.priority || 'medium',
        status: task.status || 'pending',
        estimatedHours: task.estimated_hours || 0,
        actualHours: task.actual_hours || 0,
        completionNotes: task.completion_notes || '',
        attachments: task.attachments || [],
      }))
    }
    else {
      tasksData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load tasks data:', err)
    tasksError.value = err?.data?.message || 'Failed to load tasks data'
    tasksData.value = []
  }
  finally {
    tasksLoading.value = false
  }
}

const loadDocumentsData = async () => {
  if (!unitData.value?.franchise?.id)
    return

  documentsLoading.value = true
  documentsError.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/documents?franchise_id=${unitData.value.franchise.id}`)

    if (response.success && response.data?.data) {
      documentsData.value = response.data.data.map((doc: any) => ({
        id: doc.id,
        title: doc.name,
        description: doc.description || '',
        fileName: doc.file_name,
        fileSize: formatFileSize(doc.file_size || 0),
        uploadDate: doc.created_at ? new Date(doc.created_at).toISOString().split('T')[0] : '',
        type: doc.type || 'Document',
        status: doc.status || 'active',
        comment: '',
        filePath: doc.file_path,
        franchiseId: doc.franchise_id,
      }))
    }
    else {
      documentsData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load documents data:', err)
    documentsError.value = err?.data?.message || 'Failed to load documents data'
    documentsData.value = []
  }
  finally {
    documentsLoading.value = false
  }
}

const loadProductsData = async () => {
  if (!unitData.value?.franchise?.id)
    return

  productsLoading.value = true
  productsError.value = null

  try {
    const response = await $api<{ data: any }>(`/v1/units/${unitId.value}/inventory`)

    if (response.success && response.data?.data) {
      productsData.value = response.data.data.map((product: any) => ({
        id: product.id,
        name: product.name,
        description: product.description || '',
        unitPrice: product.unit_price || 0,
        category: product.category || 'General',
        status: 'active',
        stock: product.quantity || 0,
        sku: '',
        minimumStock: product.reorder_level || 0,
      }))
    }
    else {
      productsData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load products data:', err)
    productsError.value = err?.data?.message || 'Failed to load products data'
    productsData.value = []
  }
  finally {
    productsLoading.value = false
  }
}

const loadStaffData = async () => {
  if (!unitData.value?.franchise?.id)
    return

  staffLoading.value = true
  staffError.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/users?franchise_id=${unitData.value.franchise.id}`)

    if (response.success && response.data?.data) {
      staffData.value = response.data.data.map((user: any) => ({
        id: user.id,
        name: user.name,
        jobTitle: user.role === 'franchisor' ? 'Franchisor' : user.role === 'franchisee' ? 'Franchisee' : 'Staff Member',
        email: user.email,
        shiftTime: user.role === 'franchisor' ? 'Full Time' : '9:00 AM - 6:00 PM', // Default shift time
        status: user.status === 'active' ? 'working' : 'leave',
        phone: user.phone || '',
        avatar: user.avatar || '',
        lastLogin: user.last_login_at || null,
      }))
    }
    else {
      staffData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load staff data:', err)
    staffError.value = err?.data?.message || 'Failed to load staff data'
    staffData.value = []
  }
  finally {
    staffLoading.value = false
  }
}

const loadReviewsData = async () => {
  if (!unitId.value)
    return

  reviewsLoading.value = true
  reviewsError.value = null

  try {
    const response = await $api<{ success: boolean; data: any }>(`/v1/reviews?unit_id=${unitId.value}`)

    if (response.success && response.data?.data) {
      reviewsData.value = response.data.data.map((review: any) => ({
        id: review.id,
        customerName: review.customer_name,
        customerEmail: review.customer_email || '',
        customerPhone: review.customer_phone || '',
        rating: review.rating,
        comment: review.comment || '',
        date: review.review_date ? new Date(review.review_date).toISOString().split('T')[0] : '',
        sentiment: review.sentiment,
        status: review.status,
        source: review.review_source,
        verifiedPurchase: review.verified_purchase,
        internalNotes: review.internal_notes || '',
        franchiseeName: review.franchisee?.name || 'Unknown',
        addedDate: review.created_at ? new Date(review.created_at).toISOString().split('T')[0] : '',
      }))
    }
    else {
      reviewsData.value = []
    }
  }
  catch (err: any) {
    console.error('Failed to load reviews data:', err)
    reviewsError.value = err?.data?.message || 'Failed to load reviews data'
    reviewsData.value = []
  }
  finally {
    reviewsLoading.value = false
  }
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0)
    return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return `${Number.parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`
}

const goBack = () => {
  router.push('/franchisor/my-units')
}

const resolveStatusVariant = (status: string) => {
  if (status === 'active' || status === 'completed' || status === 'approved')
    return 'success'
  if (status === 'pending' || status === 'in_progress')
    return 'warning'
  if (status === 'inactive' || status === 'rejected')
    return 'error'

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

const onDocumentActionConfirmed = async (data: { document: any; action: string; comment: string }) => {
  try {
    // Get franchise ID from the documents data or unit data
    // Since documents are loaded with franchise_id, we can use that
    const franchiseId = unitData.value.franchise?.id || documentsData.value[0]?.franchiseId

    if (!franchiseId) {
      console.error('Franchise ID not found for document approval')

      return
    }

    // Call the API to approve/reject the document
    const endpoint = data.action === 'approve'
      ? `/v1/franchises/${franchiseId}/documents/${data.document.id}/approve`
      : `/v1/franchises/${franchiseId}/documents/${data.document.id}/reject`

    const response = await $api<{ success: boolean; data: any }>(endpoint, {
      method: 'PATCH',
      body: {
        comment: data.comment,
      },
    })

    if (response.success) {
      // Update the local state with the response from the API
      const documentIndex = documentsData.value.findIndex(doc => doc.id === data.document.id)
      if (documentIndex !== -1) {
        documentsData.value[documentIndex].status = data.action === 'approve' ? 'approved' : 'rejected'
        documentsData.value[documentIndex].comment = data.comment
      }
    }
    else {
      console.error(`Failed to ${data.action} document:`, response)

      // Show error message (you could add a toast notification here)
    }
  }
  catch (error) {
    console.error(`Error ${data.action}ing document:`, error)

    // Show error message (you could add a toast notification here)
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
  if (taskToDelete.value === null)
    return

  const index = tasksData.value.findIndex(task => task.id === taskToDelete.value)
  if (index !== -1)
    tasksData.value.splice(index, 1)

  isDeleteDialogVisible.value = false
  taskToDelete.value = null
}

const saveTask = () => {
  if (!selectedTask.value)
    return

  const index = tasksData.value.findIndex(task => task.id === selectedTask.value.id)
  if (index !== -1)
    tasksData.value[index] = { ...selectedTask.value }

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
  { title: 'Source', key: 'source' },
  { title: 'Status', key: 'status' },
  { title: 'Added By', key: 'franchiseeName' },
]

// Load data on component mount and when unit ID changes
watch(() => unitId.value, () => {
  if (unitId.value)
    loadUnitData()
}, { immediate: true })

// Load additional data when unit data is loaded
watch(() => unitData.value, () => {
  if (unitData.value) {
    loadTasksData()
    loadDocumentsData()
    loadStaffData()
    loadProductsData()
  }
}, { immediate: true })

// Load reviews data when unit ID is available
watch(() => unitId.value, () => {
  if (unitId.value)
    loadReviewsData()
}, { immediate: true })
</script>

<template>
  <section>
    <!-- Loading State -->
    <div
      v-if="loading"
      class="text-center py-12"
    >
      <VProgressCircular
        indeterminate
        size="64"
        class="mb-4"
      />
      <h3 class="text-h3 mb-2">
        Loading Unit Details...
      </h3>
      <p class="text-body-1 text-medium-emphasis">
        Please wait while we fetch the unit information.
      </p>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error || !unitData"
      class="text-center py-12"
    >
      <VIcon
        icon="tabler-alert-circle"
        size="64"
        class="text-error mb-4"
      />
      <h3 class="text-h3 mb-2">
        Error Loading Unit
      </h3>
      <p class="text-body-1 text-medium-emphasis mb-6">
        {{ error || 'Unit not found or you don\'t have permission to view it.' }}
      </p>
      <VBtn
        color="primary"
        prepend-icon="tabler-arrow-left"
        @click="goBack"
      >
        Back to Units
      </VBtn>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Page Header -->
      <VRow class="mb-6">
        <VCol cols="12">
          <div class="d-flex align-center justify-space-between">
            <div class="d-flex align-center gap-3">
              <VBtn
                icon
                variant="text"
                color="default"
                @click="goBack"
              >
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
            <VChip
              :color="resolveStatusVariant(unitData.status)"
              size="large"
              label
              class="text-capitalize"
            >
              {{ unitData.status }}
            </VChip>
          </div>
        </VCol>
      </VRow>

      <!-- Tabs -->
      <VTabs
        v-model="currentTab"
        class="mb-6"
      >
        <VTab value="overview">
          <VIcon
            icon="tabler-info-circle"
            start
          />
          Overview
        </VTab>
        <VTab value="tasks">
          <VIcon
            icon="tabler-checklist"
            start
          />
          Tasks
        </VTab>
        <VTab value="documents">
          <VIcon
            icon="tabler-files"
            start
          />
          Documents
        </VTab>
        <VTab value="staffs">
          <VIcon
            icon="tabler-users"
            start
          />
          Staffs
        </VTab>
        <VTab value="inventory">
          <VIcon
            icon="tabler-package"
            start
          />
          Inventory Management
        </VTab>
        <VTab value="reviews">
          <VIcon
            icon="tabler-star"
            start
          />
          Customer Reviews
        </VTab>
      </VTabs>

      <VWindow
        v-model="currentTab"
        class="disable-tab-transition"
      >
        <!-- Overview Tab -->
        <VWindowItem value="overview">
          <VCard>
            <VCardText>
              <VRow>
                <!-- Basic Information -->
                <VCol cols="12">
                  <h4 class="text-h6 mb-4">
                    Basic Information
                  </h4>
                  <VCard variant="outlined">
                    <VCardText>
                      <VRow>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Branch Name
                            </div>
                            <div class="text-body-1 font-weight-medium">
                              {{ unitData.branchName }}
                            </div>
                          </div>
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Franchisee Name
                            </div>
                            <div class="text-body-1">
                              {{ unitData.franchiseeName }}
                            </div>
                          </div>
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Email Address
                            </div>
                            <div class="text-body-1">
                              <a
                                :href="`mailto:${unitData.email}`"
                                class="text-primary"
                              >
                                {{ unitData.email }}
                              </a>
                            </div>
                          </div>
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Contact Number
                            </div>
                            <div class="text-body-1">
                              {{ unitData.contactNumber }}
                            </div>
                          </div>
                        </VCol>
                        <VCol cols="12">
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Address
                            </div>
                            <div class="text-body-1">
                              {{ unitData.address }}, {{ unitData.city }}, {{ unitData.state }},
                              {{
                                unitData.country }}
                            </div>
                          </div>
                        </VCol>
                      </VRow>
                    </VCardText>
                  </VCard>
                </VCol>

                <!-- Franchisee Details -->
                <VCol cols="12">
                  <h4 class="text-h6 mb-4">
                    Franchisee Details
                  </h4>
                  <VCard variant="outlined">
                    <VCardText>
                      <VRow>
                        <VCol
                          cols="12"
                          md="4"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Royalty Percentage
                            </div>
                            <div class="text-body-1 font-weight-medium text-success">
                              {{ unitData.royaltyPercentage }}%
                            </div>
                          </div>
                        </VCol>
                        <VCol
                          cols="12"
                          md="4"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Contract Start Date
                            </div>
                            <div class="text-body-1">
                              {{ unitData.contractStartDate }}
                            </div>
                          </div>
                        </VCol>
                        <VCol
                          cols="12"
                          md="4"
                        >
                          <div class="mb-4">
                            <div class="text-sm text-disabled mb-1">
                              Renewal Date
                            </div>
                            <div class="text-body-1">
                              {{ unitData.renewalDate }}
                            </div>
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
          <!-- Error Alert -->
          <VAlert
            v-if="tasksError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="tasksError = null"
          >
            {{ tasksError }}
          </VAlert>

          <!-- Loading State -->
          <VCard
            v-if="tasksLoading"
            class="mb-6"
          >
            <VCardText class="py-8">
              <div class="text-center">
                <VProgressCircular
                  indeterminate
                  size="48"
                  class="mb-4"
                />
                <h4 class="text-h4 mb-2">
                  Loading Tasks...
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  Please wait while we fetch the task information.
                </p>
              </div>
            </VCardText>
          </VCard>

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

            <!-- Tasks Table -->
            <VCard>
              <VCardItem class="pb-4">
                <VCardTitle>Tasks</VCardTitle>
                <VCardSubtitle class="text-body-2 text-disabled">
                  View-only access - Task management is handled by franchisees
                </VCardSubtitle>
              </VCardItem>

              <VDivider />

              <VDataTable
                v-if="tasksData.length > 0"
                :items="tasksData"
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
                    There are no tasks assigned to this unit yet. The franchisee can create tasks to manage operations.
                  </p>
                </div>
              </VCardText>
            </VCard>
          </template>
        </VWindowItem>

        <!-- Documents Tab -->
        <VWindowItem value="documents">
          <!-- Error Alert -->
          <VAlert
            v-if="documentsError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="documentsError = null"
          >
            {{ documentsError }}
          </VAlert>

          <!-- Loading State -->
          <VCard v-if="documentsLoading">
            <VCardText class="py-8">
              <div class="text-center">
                <VProgressCircular
                  indeterminate
                  size="48"
                  class="mb-4"
                />
                <h4 class="text-h4 mb-2">
                  Loading Documents...
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  Please wait while we fetch the document information.
                </p>
              </div>
            </VCardText>
          </VCard>

          <!-- Documents Content -->
          <VCard v-else>
            <VCardItem class="pb-4">
              <VCardTitle>Unit Documents</VCardTitle>
              <VCardSubtitle class="text-body-2 text-disabled">
                Review and approve documents uploaded by franchisees
              </VCardSubtitle>
            </VCardItem>

            <VDivider />

            <VCardText v-if="documentsData.length > 0">
              <VRow>
                <template
                  v-for="document in documentsData"
                  :key="document.id"
                >
                  <VCol
                    cols="12"
                    md="6"
                    lg="4"
                  >
                    <VCard>
                      <VCardText>
                        <div class="d-flex align-center mb-3">
                          <VIcon
                            icon="tabler-file-text"
                            size="24"
                            color="primary"
                            class="me-3"
                          />
                          <div>
                            <h6 class="text-h6">
                              {{ document.title }}
                            </h6>
                            <p class="text-body-2 text-disabled mb-0">
                              {{ document.type }}
                            </p>
                          </div>
                        </div>

                        <p class="text-body-2 mb-3">
                          {{ document.description }}
                        </p>

                        <div class="d-flex align-center justify-space-between mb-3">
                          <span class="text-body-2 text-disabled">{{ document.fileName }}</span>
                          <VChip
                            size="small"
                            color="secondary"
                          >
                            {{ document.fileSize }}
                          </VChip>
                        </div>

                        <div class="d-flex align-center justify-space-between mb-3">
                          <span class="text-body-2 text-disabled">{{ document.uploadDate }}</span>
                          <VChip
                            :color="resolveStatusVariant(document.status)"
                            size="small"
                            label
                            class="text-capitalize"
                          >
                            {{ document.status }}
                          </VChip>
                        </div>
                      </VCardText>

                      <VCardActions>
                        <VBtn
                          size="small"
                          variant="text"
                          color="primary"
                          prepend-icon="tabler-download"
                        >
                          Download
                        </VBtn>
                        <VSpacer />
                        <VBtn
                          v-if="document.status === 'active'"
                          size="small"
                          variant="text"
                          color="success"
                          @click="openDocumentActionModal(document, 'approve')"
                        >
                          Approve
                        </VBtn>
                        <VBtn
                          v-if="document.status === 'active'"
                          size="small"
                          variant="text"
                          color="error"
                          @click="openDocumentActionModal(document, 'reject')"
                        >
                          Reject
                        </VBtn>
                      </VCardActions>
                    </VCard>
                  </VCol>
                </template>
              </VRow>
            </VCardText>

            <!-- Empty State -->
            <VCardText
              v-else
              class="py-8"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-files"
                  size="64"
                  class="text-disabled mb-4"
                />
                <h4 class="text-h4 mb-2">
                  No Documents Found
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  There are no documents uploaded for this unit yet. The franchisee can upload important documents and files.
                </p>
              </div>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- Staff Tab -->
        <VWindowItem value="staffs">
          <!-- Error Alert -->
          <VAlert
            v-if="staffError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="staffError = null"
          >
            {{ staffError }}
          </VAlert>

          <!-- Loading State -->
          <VCard
            v-if="staffLoading"
            class="mb-6"
          >
            <VCardText class="py-8">
              <div class="text-center">
                <VProgressCircular
                  indeterminate
                  size="48"
                  class="mb-4"
                />
                <h4 class="text-h4 mb-2">
                  Loading Staff...
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  Please wait while we fetch the staff information.
                </p>
              </div>
            </VCardText>
          </VCard>

          <!-- Staff Content -->
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
                        icon="tabler-users"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Total Staff
                      </div>
                      <h4 class="text-h4">
                        {{ totalStaff }}
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
                        icon="tabler-user-check"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Working
                      </div>
                      <h4 class="text-h4">
                        {{ workingStaff }}
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
                        icon="tabler-calendar-off"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        On Leave
                      </div>
                      <h4 class="text-h4">
                        {{ staffOnLeave }}
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
                      color="info"
                      variant="tonal"
                    >
                      <VIcon
                        icon="tabler-user-plus"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Active
                      </div>
                      <h4 class="text-h4">
                        {{ staffData.filter(s => s.status === 'working').length }}
                      </h4>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <!-- Staff Table -->
            <VCard>
              <VCardItem class="pb-4">
                <VCardTitle>Staff Members</VCardTitle>
                <VCardSubtitle class="text-body-2 text-disabled">
                  View-only access - Staff management is handled by franchisees
                </VCardSubtitle>
              </VCardItem>

              <VDivider />

              <VDataTable
                v-if="staffData.length > 0"
                :items="staffData"
                :headers="staffHeaders"
                class="text-no-wrap"
                item-value="id"
              >
                <!-- Name -->
                <template #item.name="{ item }">
                  <div class="d-flex align-center">
                    <VAvatar
                      size="32"
                      :image="item.avatar"
                      class="me-3"
                    >
                      <VIcon
                        v-if="!item.avatar"
                        icon="tabler-user"
                      />
                    </VAvatar>
                    <div>
                      <div class="text-body-1 font-weight-medium">
                        {{ item.name }}
                      </div>
                      <div class="text-body-2 text-disabled">
                        {{ item.phone || 'No phone' }}
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Job Title -->
                <template #item.jobTitle="{ item }">
                  <VChip
                    size="small"
                    color="primary"
                    variant="tonal"
                    label
                  >
                    {{ item.jobTitle }}
                  </VChip>
                </template>

                <!-- Status -->
                <template #item.status="{ item }">
                  <VChip
                    :color="item.status === 'working' ? 'success' : 'warning'"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ item.status }}
                  </VChip>
                </template>
              </VDataTable>

              <!-- Empty State -->
              <VCardText
                v-else
                class="py-8"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-users"
                    size="64"
                    class="text-disabled mb-4"
                  />
                  <h4 class="text-h4 mb-2">
                    No Staff Members Found
                  </h4>
                  <p class="text-body-1 text-medium-emphasis">
                    There are no staff members assigned to this franchise yet. The franchisee can manage staff assignments and roles.
                  </p>
                </div>
              </VCardText>
            </VCard>
          </template>
        </VWindowItem>

        <!-- Inventory Tab -->
        <VWindowItem value="inventory">
          <!-- Error Alert -->
          <VAlert
            v-if="productsError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="productsError = null"
          >
            {{ productsError }}
          </VAlert>

          <!-- Loading State -->
          <VCard
            v-if="productsLoading"
            class="mb-6"
          >
            <VCardText class="py-8">
              <div class="text-center">
                <VProgressCircular
                  indeterminate
                  size="48"
                  class="mb-4"
                />
                <h4 class="text-h4 mb-2">
                  Loading Inventory...
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  Please wait while we fetch the inventory information.
                </p>
              </div>
            </VCardText>
          </VCard>

          <!-- Inventory Content -->
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
                        icon="tabler-package"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Total Products
                      </div>
                      <h4 class="text-h4">
                        {{ totalProducts }}
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
                      color="info"
                      variant="tonal"
                    >
                      <VIcon
                        icon="tabler-stack"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Total Stock
                      </div>
                      <h4 class="text-h4">
                        {{ totalStock }}
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
                        icon="tabler-alert-triangle"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Low Stock
                      </div>
                      <h4 class="text-h4">
                        {{ lowStockProducts }}
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
                        icon="tabler-x"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Out of Stock
                      </div>
                      <h4 class="text-h4">
                        {{ outOfStockProducts }}
                      </h4>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <!-- Products Table -->
            <VCard>
              <VCardItem class="pb-4">
                <VCardTitle>Inventory</VCardTitle>
                <VCardSubtitle class="text-body-2 text-disabled">
                  View-only access - Product management is handled by franchisees
                </VCardSubtitle>
              </VCardItem>

              <VDivider />

              <VDataTable
                v-if="productsData.length > 0"
                :items="productsData"
                :headers="productHeaders"
                class="text-no-wrap"
                item-value="id"
              >
                <!-- Unit Price -->
                <template #item.unitPrice="{ item }">
                  <div class="text-body-1 font-weight-medium">
                    SAR {{ item.unitPrice.toFixed(2) }}
                  </div>
                </template>

                <!-- Stock -->
                <template #item.stock="{ item }">
                  <VChip
                    :color="item.stock === 0 ? 'error' : item.stock <= 10 ? 'warning' : 'success'"
                    size="small"
                    label
                  >
                    {{ item.stock }}
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
              </VDataTable>

              <!-- Empty State -->
              <VCardText
                v-else
                class="py-8"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-package"
                    size="64"
                    class="text-disabled mb-4"
                  />
                  <h4 class="text-h4 mb-2">
                    No Products Found
                  </h4>
                  <p class="text-body-1 text-medium-emphasis">
                    There are no products in the inventory for this unit yet. The franchisee can manage products and stock levels.
                  </p>
                </div>
              </VCardText>
            </VCard>
          </template>
        </VWindowItem>

        <!-- Reviews Tab -->
        <VWindowItem value="reviews">
          <!-- Error Alert -->
          <VAlert
            v-if="reviewsError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="reviewsError = null"
          >
            {{ reviewsError }}
          </VAlert>

          <!-- Loading State -->
          <VCard
            v-if="reviewsLoading"
            class="mb-6"
          >
            <VCardText class="py-8">
              <div class="text-center">
                <VProgressCircular
                  indeterminate
                  size="48"
                  class="mb-4"
                />
                <h4 class="text-h4 mb-2">
                  Loading Reviews...
                </h4>
                <p class="text-body-1 text-medium-emphasis">
                  Please wait while we fetch the review information.
                </p>
              </div>
            </VCardText>
          </VCard>

          <!-- Reviews Content -->
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
                        icon="tabler-star"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Total Reviews
                      </div>
                      <h4 class="text-h4">
                        {{ totalReviews }}
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
                        icon="tabler-star"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Avg Rating
                      </div>
                      <h4 class="text-h4">
                        {{ avgRating }}
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
                        icon="tabler-file-text"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Draft
                      </div>
                      <h4 class="text-h4">
                        {{ draftReviews }}
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
                      color="info"
                      variant="tonal"
                    >
                      <VIcon
                        icon="tabler-check"
                        size="26"
                      />
                    </VAvatar>
                    <div class="ms-4">
                      <div class="text-body-2 text-disabled">
                        Verified
                      </div>
                      <h4 class="text-h4">
                        {{ verifiedReviews }}
                      </h4>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <!-- Reviews Table -->
            <VCard>
              <VCardItem class="pb-4">
                <VCardTitle>Customer Reviews</VCardTitle>
                <VCardSubtitle class="text-body-2 text-disabled">
                  View-only access - Customer review management is handled by franchisees
                </VCardSubtitle>
              </VCardItem>

              <VDivider />

              <VDataTable
                v-if="reviewsData.length > 0"
                :items="reviewsData"
                :headers="reviewHeaders"
                class="text-no-wrap"
                item-value="id"
              >
                <!-- Customer Name -->
                <template #item.customerName="{ item }">
                  <div>
                    <div class="text-body-1 font-weight-medium">
                      {{ item.customerName }}
                    </div>
                    <div class="text-body-2 text-disabled">
                      {{ item.customerEmail || 'No email' }}
                    </div>
                    <div
                      v-if="item.customerPhone"
                      class="text-body-2 text-disabled"
                    >
                      {{ item.customerPhone }}
                    </div>
                  </div>
                </template>

                <!-- Rating -->
                <template #item.rating="{ item }">
                  <div class="d-flex align-center">
                    <VRating
                      :model-value="item.rating"
                      density="compact"
                      size="small"
                      readonly
                      color="warning"
                    />
                    <span class="ms-2 text-body-2">({{ item.rating }})</span>
                  </div>
                </template>

                <!-- Comment -->
                <template #item.comment="{ item }">
                  <div class="max-width-200">
                    <p class="text-body-2 text-truncate mb-0">
                      {{ item.comment || 'No comment' }}
                    </p>
                  </div>
                </template>

                <!-- Source -->
                <template #item.source="{ item }">
                  <VChip
                    size="small"
                    color="secondary"
                    variant="tonal"
                    label
                    class="text-capitalize"
                  >
                    {{ item.source?.replace('_', ' ') }}
                  </VChip>
                </template>

                <!-- Status -->
                <template #item.status="{ item }">
                  <VChip
                    :color="item.status === 'published' ? 'success' : item.status === 'draft' ? 'warning' : 'secondary'"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ item.status }}
                  </VChip>
                </template>

                <!-- Added By -->
                <template #item.franchiseeName="{ item }">
                  <div>
                    <div class="text-body-2">
                      {{ item.franchiseeName }}
                    </div>
                    <div class="text-body-2 text-disabled">
                      {{ item.addedDate }}
                    </div>
                  </div>
                </template>
              </VDataTable>

              <!-- Empty State -->
              <VCardText
                v-else
                class="py-8"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-star"
                    size="64"
                    class="text-disabled mb-4"
                  />
                  <h4 class="text-h4 mb-2">
                    No Customer Reviews Found
                  </h4>
                  <p class="text-body-1 text-medium-emphasis">
                    There are no customer reviews for this unit yet. The franchisee can add and manage customer feedback to build reputation.
                  </p>
                </div>
              </VCardText>
            </VCard>
          </template>
        </VWindowItem>
      </VWindow>

      <!-- Modals -->
      <CreateTaskModal
        v-model:is-dialog-visible="isAddTaskModalVisible"
        current-tab="franchisee"
        @task-created="onTaskCreated"
      />

      <AddDocumentModal
        v-model:is-dialog-visible="isAddDocumentModalVisible"
        @document-added="onDocumentAdded"
      />

      <DocumentActionModal
        v-model:is-dialog-visible="isDocumentActionModalVisible"
        :document="selectedDocument"
        :action="documentAction"
        @document-action-confirmed="onDocumentActionConfirmed"
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
                <VTextField
                  v-model="selectedTask.category"
                  label="Category"
                  placeholder="Enter category"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="selectedTask.assignedTo"
                  label="Assigned To"
                  placeholder="Enter assignee"
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
                  :items="[
                    { title: 'Low', value: 'low' },
                    { title: 'Medium', value: 'medium' },
                    { title: 'High', value: 'high' },
                  ]"
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
                  :items="[
                    { title: 'Pending', value: 'pending' },
                    { title: 'In Progress', value: 'in_progress' },
                    { title: 'Completed', value: 'completed' },
                  ]"
                  required
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
    </div> <!-- End of content div -->
  </section>
</template>
