<script setup lang="ts">
// 👉 Imports
import AddDocumentModal from '@/components/dialogs/AddDocumentModal.vue'
import CreateTaskModal from '@/components/dialogs/CreateTaskModal.vue'
import DocumentActionModal from '@/components/dialogs/DocumentActionModal.vue'
import type {
  UnitDetails,
  UnitDocument,
  UnitProduct,
  UnitReview,
  UnitStaff,
  UnitTask,
} from '@/services/api/franchisee-dashboard'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'
import { computed, onMounted, ref } from 'vue'

// 👉 Router
const router = useRouter()
const route = useRoute()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Unit ID from route
const unitId = computed(() => route.params.id as string)

// 👉 Loading and error states
const loading = ref(false)
const error = ref<string | null>(null)

// Format currency
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'SAR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

// 👉 Unit data (fetched from API)
const unitData = ref<UnitDetails | null>(null)

// 👉 Tasks data (fetched from API)
const tasksData = ref<UnitTask[]>([])

// 👉 Documents data (fetched from API)
const documentsData = ref<UnitDocument[]>([])

// 👉 Staff data (fetched from API)
const staffData = ref<UnitStaff[]>([])

// 👉 Products data (fetched from API)
const productsData = ref<UnitProduct[]>([])

// 👉 Reviews data (fetched from API)
const reviewsData = ref<UnitReview[]>([])

// 👉 Available franchise products for inventory
const availableFranchiseProducts = ref<UnitProduct[]>([])
const selectedFranchiseProduct = ref<number | null>(null)

// 👉 New inventory form
const newInventoryForm = ref({
  quantity: 0,
  reorderLevel: 5,
})

// 👉 Computed selected product preview
const selectedProductPreview = computed(() => {
  if (!selectedFranchiseProduct.value) return null
  return availableFranchiseProducts.value.find(p => p.id === selectedFranchiseProduct.value)
})

// 👉 API Loading Functions
const loadUnitData = async () => {
  try {
    loading.value = true
    error.value = null

    // Load unit details
    const unitDetailsResponse = await franchiseeDashboardApi.getUnitDetails(unitId.value)
    if (unitDetailsResponse.success) {
      unitData.value = unitDetailsResponse.data
    }

    // Load tasks
    const tasksResponse = await franchiseeDashboardApi.getUnitTasks(unitId.value)
    if (tasksResponse.success) {
      tasksData.value = tasksResponse.data
    }

    // Load staff
    const staffResponse = await franchiseeDashboardApi.getUnitStaff(unitId.value)
    if (staffResponse.success) {
      staffData.value = staffResponse.data
    }

    // Load products
    const productsResponse = await franchiseeDashboardApi.getUnitProducts(unitId.value)
    if (productsResponse.success) {
      productsData.value = productsResponse.data
    }

    // Load reviews
    const reviewsResponse = await franchiseeDashboardApi.getUnitReviews(unitId.value)
    if (reviewsResponse.success) {
      reviewsData.value = reviewsResponse.data
    }

    // Load documents
    const documentsResponse = await franchiseeDashboardApi.getUnitDocuments(unitId.value)
    if (documentsResponse.success) {
      documentsData.value = documentsResponse.data
    }
  }
  catch (err) {
    console.error('Error loading unit data:', err)
    error.value = 'Failed to load unit data. Please try again.'
  }
  finally {
    loading.value = false
  }
}

// Load data when component mounts
onMounted(() => {
  loadUnitData()
})


// 👉 Modal states
const isAddTaskModalVisible = ref(false)
const isAddDocumentModalVisible = ref(false)
const isEditUnitModalVisible = ref(false)
const isAddStaffModalVisible = ref(false)
const isAddProductModalVisible = ref(false)
const isAddReviewModalVisible = ref(false)

// 👉 Task modal states
const isViewTaskModalVisible = ref(false)
const isEditTaskModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedTask = ref<any>(null)
const taskToDelete = ref<number | null>(null)

// 👉 Staff modal states
const isViewStaffModalVisible = ref(false)
const isEditStaffModalVisible = ref(false)
const selectedStaff = ref<any>(null)
const staffToDelete = ref<number | null>(null)

// 👉 Product modal states
const isViewProductModalVisible = ref(false)
const isEditProductModalVisible = ref(false)
const selectedProduct = ref<any>(null)
const productToDelete = ref<number | null>(null)

// 👉 Review modal states
const isViewReviewModalVisible = ref(false)
const isEditReviewModalVisible = ref(false)
const selectedReview = ref<any>(null)
const reviewToDelete = ref<number | null>(null)

// 👉 Edit unit form
const editUnitForm = ref({ ...unitData.value })

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
    return 0
  const sum = reviewsData.value.reduce((total, review) => total + review.rating, 0)

  return (sum / reviewsData.value.length).toFixed(1)
})

const positiveReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'positive').length)
const negativeReviews = computed(() => reviewsData.value.filter(review => review.sentiment === 'negative').length)

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

// 👉 Unit edit handlers
const openEditUnitModal = () => {
  editUnitForm.value = { ...unitData.value }
  isEditUnitModalVisible.value = true
}

const saveUnitDetails = async () => {
  try {
    const response = await franchiseeDashboardApi.updateUnitDetails(parseInt(unitId.value), editUnitForm.value)
    if (response.success) {
      unitData.value = response.data
      isEditUnitModalVisible.value = false
    }
  }
  catch (error) {
    console.error('Error updating unit details:', error)
  }
}

const onTaskCreated = async (task: any) => {
  try {
    const response = await franchiseeDashboardApi.createTask(parseInt(unitId.value), task)
    if (response.success) {
      tasksData.value.push(response.data)
    }
  }
  catch (error) {
    console.error('Error creating task:', error)
  }
}

const onDocumentAdded = async (document: any) => {
  try {
    const response = await franchiseeDashboardApi.createDocument(parseInt(unitId.value), document)
    if (response.success) {
      documentsData.value.push(response.data)
    }
  }
  catch (error) {
    console.error('Error creating document:', error)
  }
}

// 👉 Staff handlers
const viewStaff = (staff: any) => {
  selectedStaff.value = staff
  isViewStaffModalVisible.value = true
}

const editStaff = (staff: any) => {
  selectedStaff.value = { ...staff }
  isEditStaffModalVisible.value = true
}

const confirmDeleteStaff = (id: number) => {
  staffToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteStaff = async () => {
  if (staffToDelete.value === null)
    return

  try {
    const response = await franchiseeDashboardApi.deleteStaff(parseInt(unitId.value), staffToDelete.value)
    if (response.success) {
      const index = staffData.value.findIndex(staff => staff.id === staffToDelete.value)
      if (index !== -1)
        staffData.value.splice(index, 1)
    }
  }
  catch (error) {
    console.error('Error deleting staff:', error)
  }
  finally {
    isDeleteDialogVisible.value = false
    staffToDelete.value = null
  }
}

const saveStaff = async () => {
  if (!selectedStaff.value)
    return

  try {
    const response = await franchiseeDashboardApi.updateStaff(parseInt(unitId.value), selectedStaff.value.id, selectedStaff.value)
    if (response.success) {
      const index = staffData.value.findIndex(staff => staff.id === selectedStaff.value.id)
      if (index !== -1)
        staffData.value[index] = response.data
    }
  }
  catch (error) {
    console.error('Error updating staff:', error)
  }
  finally {
    isEditStaffModalVisible.value = false
    selectedStaff.value = null
  }
}

const onStaffCreated = async (staff: any) => {
  try {
    const response = await franchiseeDashboardApi.createStaff(parseInt(unitId.value), staff)
    if (response.success) {
      staffData.value.push(response.data)
    }
  }
  catch (error) {
    console.error('Error creating staff:', error)
  }
}

// 👉 Product handlers
const viewProduct = (product: any) => {
  selectedProduct.value = product
  isViewProductModalVisible.value = true
}

const editProduct = (product: any) => {
  selectedProduct.value = { ...product }
  isEditProductModalVisible.value = true
}

const confirmDeleteProduct = (id: number) => {
  productToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteProduct = async () => {
  if (productToDelete.value === null)
    return

  try {
    const response = await franchiseeDashboardApi.removeProductFromInventory(parseInt(unitId.value), productToDelete.value)
    if (response.success) {
      const index = productsData.value.findIndex(product => product.id === productToDelete.value)
      if (index !== -1)
        productsData.value.splice(index, 1)
    }
  }
  catch (error) {
    console.error('Error removing product from inventory:', error)
  }
  finally {
    isDeleteDialogVisible.value = false
    productToDelete.value = null
  }
}

const saveProduct = async () => {
  if (!selectedProduct.value)
    return

  try {
    const response = await franchiseeDashboardApi.updateProduct(parseInt(unitId.value), selectedProduct.value.id, selectedProduct.value)
    if (response.success) {
      const index = productsData.value.findIndex(product => product.id === selectedProduct.value.id)
      if (index !== -1)
        productsData.value[index] = response.data
    }
  }
  catch (error) {
    console.error('Error updating product:', error)
  }
  finally {
    isEditProductModalVisible.value = false
    selectedProduct.value = null
  }
}

// 👉 Load available franchise products for inventory
const loadAvailableFranchiseProducts = async () => {
  try {
    // Get unit ID - if no route param, use the unit ID from loaded unit data
    const currentUnitId = unitId.value ? parseInt(unitId.value) : unitData.value?.id
    
    if (!currentUnitId || isNaN(currentUnitId)) {
      console.error('Invalid unit ID for loading products')
      availableFranchiseProducts.value = []
      return
    }

    // This would call an API to get franchise products not yet in this unit's inventory
    const response = await franchiseeDashboardApi.getAvailableFranchiseProducts(currentUnitId)
    if (response.success) {
      availableFranchiseProducts.value = response.data
    }
  }
  catch (error) {
    console.error('Error loading franchise products:', error)
    // For now, we'll simulate some available products
    availableFranchiseProducts.value = []
  }
}

// 👉 Add product to unit inventory 
const addProductToInventory = async () => {
  if (!selectedFranchiseProduct.value) return

  try {
    const inventoryData = {
      productId: selectedFranchiseProduct.value,
      quantity: parseInt(newInventoryForm.value.quantity.toString()),
      reorderLevel: parseInt(newInventoryForm.value.reorderLevel.toString()),
    }

    const response = await franchiseeDashboardApi.addProductToInventory(parseInt(unitId.value), inventoryData)
    if (response.success) {
      // Add the product with inventory data to our products list
      const selectedProduct = availableFranchiseProducts.value.find(p => p.id === selectedFranchiseProduct.value)
      if (selectedProduct) {
        productsData.value.push({
          ...selectedProduct,
          stock: inventoryData.quantity
        })
      }
      closeAddProductModal()
    }
  }
  catch (error) {
    console.error('Error adding product to inventory:', error)
  }
}

// 👉 Close add product modal and reset form
const closeAddProductModal = () => {
  isAddProductModalVisible.value = false
  selectedFranchiseProduct.value = null
  newInventoryForm.value = {
    quantity: 0,
    reorderLevel: 5,
  }
}

// 👉 Open add product modal and load available products
const openAddProductModal = () => {
  loadAvailableFranchiseProducts()
  isAddProductModalVisible.value = true
}

// 👉 Review handlers
const viewReview = (review: any) => {
  selectedReview.value = review
  isViewReviewModalVisible.value = true
}

const editReview = (review: any) => {
  selectedReview.value = { ...review }
  isEditReviewModalVisible.value = true
}

const confirmDeleteReview = (id: number) => {
  reviewToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteReview = async () => {
  if (reviewToDelete.value === null)
    return

  try {
    const response = await franchiseeDashboardApi.deleteReview(parseInt(unitId.value), reviewToDelete.value)
    if (response.success) {
      const index = reviewsData.value.findIndex(review => review.id === reviewToDelete.value)
      if (index !== -1)
        reviewsData.value.splice(index, 1)
    }
  }
  catch (error) {
    console.error('Error deleting review:', error)
  }
  finally {
    isDeleteDialogVisible.value = false
    reviewToDelete.value = null
  }
}

const saveReview = async () => {
  if (!selectedReview.value)
    return

  try {
    const response = await franchiseeDashboardApi.updateReview(parseInt(unitId.value), selectedReview.value.id, selectedReview.value)
    if (response.success) {
      const index = reviewsData.value.findIndex(review => review.id === selectedReview.value.id)
      if (index !== -1)
        reviewsData.value[index] = response.data
    }
  }
  catch (error) {
    console.error('Error updating review:', error)
  }
  finally {
    isEditReviewModalVisible.value = false
    selectedReview.value = null
  }
}

const onReviewCreated = async (review: any) => {
  try {
    const response = await franchiseeDashboardApi.createReview(parseInt(unitId.value), review)
    if (response.success) {
      reviewsData.value.push(response.data)
    }
  }
  catch (error) {
    console.error('Error creating review:', error)
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

const deleteTask = async () => {
  if (taskToDelete.value !== null) {
    try {
      const response = await franchiseeDashboardApi.deleteTask(parseInt(unitId.value), taskToDelete.value)
      if (response.success) {
        const index = tasksData.value.findIndex(task => task.id === taskToDelete.value)
        if (index !== -1)
          tasksData.value.splice(index, 1)
      }
    }
    catch (error) {
      console.error('Error deleting task:', error)
    }
    finally {
      taskToDelete.value = null
      isDeleteDialogVisible.value = false
    }
  }
  else if (staffToDelete.value !== null) {
    await deleteStaff()
    return
  }
  else if (productToDelete.value !== null) {
    await deleteProduct()
    return
  }
  else if (reviewToDelete.value !== null) {
    await deleteReview()
    return
  }

  isDeleteDialogVisible.value = false
}

const saveTask = async () => {
  if (!selectedTask.value)
    return

  try {
    const response = await franchiseeDashboardApi.updateTask(parseInt(unitId.value), selectedTask.value.id, selectedTask.value)
    if (response.success) {
      const index = tasksData.value.findIndex(task => task.id === selectedTask.value.id)
      if (index !== -1)
        tasksData.value[index] = response.data
    }
  }
  catch (error) {
    console.error('Error updating task:', error)
  }
  finally {
    isEditTaskModalVisible.value = false
    selectedTask.value = null
  }
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
            <div>
              <h2 class="text-h2 mb-1">
                {{ unitData?.branchName || 'Loading...' }}
              </h2>
              <p class="text-body-1 text-medium-emphasis">
                {{ unitData ? `Managed by ${unitData.franchiseeName} • ${unitData.city}, ${unitData.state}` :
                  'Loading unit information...' }}
              </p>
            </div>
          </div>
          <VChip v-if="unitData" :color="resolveStatusVariant(unitData.status)" size="large" label
            class="text-capitalize">
            {{ unitData.status }}
          </VChip>
          <VSkeleton v-else type="chip" width="80" height="32" />
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
          <VCardItem class="pb-4">
            <VCardTitle>Unit Information</VCardTitle>
            <template #append>
              <VBtn color="primary" prepend-icon="tabler-edit" :disabled="!unitData" @click="openEditUnitModal">
                Edit Details
              </VBtn>
            </template>
          </VCardItem>
          <VDivider />
          <VCardText>
            <div v-if="loading" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
              <div class="mt-3 text-body-1">
                Loading unit information...
              </div>
            </div>
            <div v-else-if="error" class="text-center py-8">
              <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
              <div class="text-body-1 text-error">
                {{ error }}
              </div>
              <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
                Retry
              </VBtn>
            </div>
            <VRow v-else-if="unitData">
              <!-- Basic Information -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">
                  Basic Information
                </h4>
                <VRow>
                  <VCol cols="12" md="6">
                    <div class="mb-4">
                      <div class="text-sm text-disabled mb-1">
                        Branch Name
                      </div>
                      <div class="text-body-1 font-weight-medium">
                        {{ unitData.branchName }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="12" md="6">
                    <div class="mb-4">
                      <div class="text-sm text-disabled mb-1">
                        Franchisee Name
                      </div>
                      <div class="text-body-1">
                        {{ unitData.franchiseeName }}
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="12" md="6">
                    <div class="mb-4">
                      <div class="text-sm text-disabled mb-1">
                        Email Address
                      </div>
                      <div class="text-body-1">
                        <a :href="`mailto:${unitData.email}`" class="text-primary">
                          {{ unitData.email }}
                        </a>
                      </div>
                    </div>
                  </VCol>
                  <VCol cols="12" md="6">
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
                        {{ unitData.address }}, {{ unitData.city }}, {{ unitData.state }}, {{
                          unitData.country }}
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VWindowItem>

      <!-- Tasks Tab -->
      <VWindowItem value="tasks">
        <div v-if="loading" class="text-center py-8">
          <VProgressCircular indeterminate color="primary" />
          <div class="mt-3 text-body-1">
            Loading tasks data...
          </div>
        </div>
        <div v-else-if="error" class="text-center py-8">
          <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="primary" variant="tonal">
                    <VIcon icon="tabler-checklist" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="success" variant="tonal">
                    <VIcon icon="tabler-check" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="warning" variant="tonal">
                    <VIcon icon="tabler-clock" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="error" variant="tonal">
                    <VIcon icon="tabler-alert-circle" size="26" />
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
              <template #append>
                <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddTaskModalVisible = true">
                  Create Task
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable :items="tasksData" :headers="taskHeaders" class="text-no-wrap" item-value="id">
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
        </template>
      </VWindowItem>

      <!-- Documents Tab -->
      <VWindowItem value="documents">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Unit Documents</VCardTitle>
            <template #append>
              <VBtn color="primary" prepend-icon="tabler-plus" :disabled="loading"
                @click="isAddDocumentModalVisible = true">
                Add Document
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <div v-if="loading" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
              <div class="mt-3 text-body-1">
                Loading documents...
              </div>
            </div>
            <div v-else-if="error" class="text-center py-8">
              <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
              <div class="text-body-1 text-error">
                {{ error }}
              </div>
              <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
                Retry
              </VBtn>
            </div>
            <div v-else-if="documentsData.length === 0" class="text-center py-8">
              <VIcon icon="tabler-folder-open" size="48" class="text-disabled mb-3" />
              <div class="text-body-1 text-disabled">
                No documents found for this unit
              </div>
            </div>
            <VRow v-else>
              <template v-for="document in documentsData" :key="document.id">
                <VCol cols="12" md="6" lg="4">
                  <VCard>
                    <VCardText>
                      <div class="d-flex align-center mb-3">
                        <VIcon icon="tabler-file-text" size="24" color="primary" class="me-3" />
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
                        <VChip size="small" color="secondary">
                          {{ document.fileSize }}
                        </VChip>
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
        <div v-if="loading" class="text-center py-8">
          <VProgressCircular indeterminate color="primary" />
          <div class="mt-3 text-body-1">
            Loading staff data...
          </div>
        </div>
        <div v-else-if="error" class="text-center py-8">
          <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol cols="12" md="4">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="primary" variant="tonal">
                    <VIcon icon="tabler-users" size="26" />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Total Employees
                    </div>
                    <h4 class="text-h4">
                      {{ totalStaff }}
                    </h4>
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
            <VCol cols="12" md="4">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="warning" variant="tonal">
                    <VIcon icon="tabler-user-minus" size="26" />
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
          </VRow>

          <!-- Staff Table -->
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Staff Members</VCardTitle>
              <template #append>
                <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddStaffModalVisible = true">
                  Add Staff
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable :items="staffData"
              :headers="[...staffHeaders, { title: 'Actions', key: 'actions', sortable: false }]" class="text-no-wrap"
              item-value="id">
              <!-- Name -->
              <template #item.name="{ item }">
                <div class="d-flex align-center gap-x-3">
                  <VAvatar size="34" color="primary" variant="tonal">
                    <span>{{item.name.split(' ').map((n: string) => n[0]).join('')}}</span>
                  </VAvatar>
                  <div>
                    <h6 class="text-base font-weight-medium">
                      {{ item.name }}
                    </h6>
                    <div class="text-body-2 text-disabled">
                      {{ item.email }}
                    </div>
                  </div>
                </div>
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
                      <VListItem @click="viewStaff(item)">
                        <template #prepend>
                          <VIcon icon="tabler-eye" />
                        </template>
                        <VListItemTitle>View</VListItemTitle>
                      </VListItem>
                      <VListItem @click="editStaff(item)">
                        <template #prepend>
                          <VIcon icon="tabler-edit" />
                        </template>
                        <VListItemTitle>Edit</VListItemTitle>
                      </VListItem>
                      <VListItem @click="confirmDeleteStaff(item.id)">
                        <template #prepend>
                          <VIcon icon="tabler-trash" />
                        </template>
                        <VListItemTitle>Remove</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </template>
            </VDataTable>
          </VCard>
        </template>
      </VWindowItem>

      <!-- Inventory Tab -->
      <VWindowItem value="inventory">
        <div v-if="loading" class="text-center py-8">
          <VProgressCircular indeterminate color="primary" />
          <div class="mt-3 text-body-1">
            Loading inventory data...
          </div>
        </div>
        <div v-else-if="error" class="text-center py-8">
          <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="primary" variant="tonal">
                    <VIcon icon="tabler-package" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="info" variant="tonal">
                    <VIcon icon="tabler-stack" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="warning" variant="tonal">
                    <VIcon icon="tabler-alert-triangle" size="26" />
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
            <VCol cols="12" md="3">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="error" variant="tonal">
                    <VIcon icon="tabler-x" size="26" />
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
              <VCardSubtitle>Manage stock levels for franchise products</VCardSubtitle>
              <template #append>
                <VBtn color="primary" prepend-icon="tabler-plus" @click="openAddProductModal">
                  Add to Inventory
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable :items="productsData"
              :headers="[...productHeaders, { title: 'Actions', key: 'actions', sortable: false }]" class="text-no-wrap"
              item-value="id">
              <!-- Unit Price -->
              <template #item.unitPrice="{ item }">
                <div class="text-body-1 font-weight-medium">
                  SAR {{ item.unitPrice.toFixed(2) }}
                </div>
              </template>

              <!-- Stock -->
              <template #item.stock="{ item }">
                <VChip :color="item.stock === 0 ? 'error' : item.stock <= 10 ? 'warning' : 'success'" size="small"
                  label>
                  {{ item.stock }}
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
                      <VListItem @click="viewProduct(item)">
                        <template #prepend>
                          <VIcon icon="tabler-eye" />
                        </template>
                        <VListItemTitle>View</VListItemTitle>
                      </VListItem>
                      <VListItem @click="editProduct(item)">
                        <template #prepend>
                          <VIcon icon="tabler-edit" />
                        </template>
                        <VListItemTitle>Edit</VListItemTitle>
                      </VListItem>
                      <VListItem @click="confirmDeleteProduct(item.id)">
                        <template #prepend>
                          <VIcon icon="tabler-trash" />
                        </template>
                        <VListItemTitle>Remove from Inventory</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </template>
            </VDataTable>
          </VCard>
        </template>
      </VWindowItem>

      <!-- Reviews Tab -->
      <VWindowItem value="reviews">
        <div v-if="loading" class="text-center py-8">
          <VProgressCircular indeterminate color="primary" />
          <div class="mt-3 text-body-1">
            Loading reviews data...
          </div>
        </div>
        <div v-else-if="error" class="text-center py-8">
          <VIcon icon="tabler-alert-circle" color="error" size="48" class="mb-3" />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn color="primary" variant="tonal" class="mt-3" @click="loadUnitData">
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol cols="12" md="4">
              <VCard>
                <VCardText class="d-flex align-center">
                  <VAvatar size="44" rounded color="primary" variant="tonal">
                    <VIcon icon="tabler-star" size="26" />
                  </VAvatar>
                  <div class="ms-4">
                    <div class="text-body-2 text-disabled">
                      Average Rating
                    </div>
                    <h4 class="text-h4">
                      {{ avgRating }}
                    </h4>
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
                    <div class="text-body-2 text-disabled">
                      Positive Reviews
                    </div>
                    <h4 class="text-h4">
                      {{ positiveReviews }}
                    </h4>
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
                    <div class="text-body-2 text-disabled">
                      Negative Reviews
                    </div>
                    <h4 class="text-h4">
                      {{ negativeReviews }}
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
              <template #append>
                <VBtn color="primary" prepend-icon="tabler-plus" @click="isAddReviewModalVisible = true">
                  Add Review
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable :items="reviewsData"
              :headers="[...reviewHeaders, { title: 'Actions', key: 'actions', sortable: false }]" class="text-no-wrap"
              item-value="id">
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

              <!-- Actions -->
              <template #item.actions="{ item }">
                <VBtn icon variant="text" color="medium-emphasis" size="small">
                  <VIcon icon="tabler-dots-vertical" />
                  <VMenu activator="parent">
                    <VList>
                      <VListItem @click="viewReview(item)">
                        <template #prepend>
                          <VIcon icon="tabler-eye" />
                        </template>
                        <VListItemTitle>View</VListItemTitle>
                      </VListItem>
                      <VListItem @click="editReview(item)">
                        <template #prepend>
                          <VIcon icon="tabler-edit" />
                        </template>
                        <VListItemTitle>Edit</VListItemTitle>
                      </VListItem>
                      <VListItem @click="confirmDeleteReview(item.id)">
                        <template #prepend>
                          <VIcon icon="tabler-trash" />
                        </template>
                        <VListItemTitle>Remove</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>
                </VBtn>
              </template>
            </VDataTable>
          </VCard>
        </template>
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

        <VCardText v-if="selectedTask" class="pa-6">
          <VRow>
            <VCol cols="12">
              <h6 class="text-h6 mb-2">
                {{ selectedTask.title }}
              </h6>
              <p class="text-body-1 mb-4">
                {{ selectedTask.description }}
              </p>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Category
              </div>
              <div class="text-body-1">
                {{ selectedTask.category }}
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Assigned To
              </div>
              <div class="text-body-1">
                {{ selectedTask.assignedTo }}
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Start Date
              </div>
              <div class="text-body-1">
                {{ selectedTask.startDate }}
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Due Date
              </div>
              <div class="text-body-1">
                {{ selectedTask.dueDate }}
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Priority
              </div>
              <VChip :color="resolvePriorityVariant(selectedTask.priority)" size="small" label class="text-capitalize">
                {{ selectedTask.priority }}
              </VChip>
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-1">
                Status
              </div>
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

        <VCardText v-if="selectedTask" class="pa-6">
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
                { title: 'High', value: 'high' },
              ]" required />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect v-model="selectedTask.status" label="Status" :items="[
                { title: 'Pending', value: 'pending' },
                { title: 'In Progress', value: 'in_progress' },
                { title: 'Completed', value: 'completed' },
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

    <!-- Edit Unit Details Modal -->
    <VDialog v-model="isEditUnitModalVisible" max-width="600">
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Unit Details
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="editUnitForm.branchName" label="Branch Name" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="editUnitForm.city" label="City" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="editUnitForm.franchiseeName" label="Franchisee" disabled />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="editUnitForm.contactNumber" label="Contact Number" required />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="editUnitForm.address" label="Address" rows="3" />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isEditUnitModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="saveUnitDetails">
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Staff Modal -->
    <VDialog v-model="isAddStaffModalVisible" max-width="600">
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Add New Staff Member
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField label="Full Name" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Email" type="email" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Phone" required />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect label="Position" :items="['Manager', 'Cashier', 'Cook', 'Server', 'Cleaner']" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Salary" type="number" prefix="SAR" required />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect label="Status" :items="['Active', 'On Leave', 'Inactive']" required />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isAddStaffModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="addStaff">
            Add Staff
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Product to Inventory Modal -->
    <VDialog v-model="isAddProductModalVisible" max-width="600">
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Add Product to Inventory
        </VCardTitle>
        <VCardSubtitle class="pa-6 pt-0 pb-4">
          Select an existing franchise product and set inventory levels for this unit
        </VCardSubtitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12">
              <VSelect label="Select Product" :items="availableFranchiseProducts" item-title="name" item-value="id"
                v-model="selectedFranchiseProduct" required placeholder="Choose a product from franchise catalog"
                :no-data-text="availableFranchiseProducts.length === 0 ? 'No available franchise products to add' : 'No products match your search'">
                <template #item="{ item, props }">
                  <VListItem v-bind="props">
                    <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
                    <VListItemSubtitle>
                      {{ item.raw.category }} • SAR {{ item.raw.unitPrice.toFixed(2) }}
                    </VListItemSubtitle>
                  </VListItem>
                </template>
              </VSelect>

              <div v-if="availableFranchiseProducts.length === 0" class="text-center py-4">
                <VIcon icon="tabler-package-off" size="48" class="text-disabled mb-2" />
                <div class="text-body-2 text-disabled">
                  All franchise products are already in this unit's inventory
                </div>
              </div>
            </VCol>

            <!-- Product Preview (when product is selected) -->
            <VCol v-if="selectedProductPreview" cols="12">
              <VCard variant="tonal" color="primary">
                <VCardText>
                  <div class="d-flex align-center gap-3">
                    <VIcon icon="tabler-package" size="24" />
                    <div>
                      <h6 class="text-h6">{{ selectedProductPreview.name }}</h6>
                      <p class="text-body-2 mb-0">
                        {{ selectedProductPreview.description }}
                      </p>
                      <div class="text-body-2 text-medium-emphasis">
                        Category: {{ selectedProductPreview.category }} • Price: SAR {{
                          selectedProductPreview.unitPrice.toFixed(2) }}
                      </div>
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" md="6">
              <VTextField label="Initial Stock Quantity" type="number" min="0" v-model="newInventoryForm.quantity"
                required placeholder="Enter quantity to add" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Reorder Level" type="number" min="0" v-model="newInventoryForm.reorderLevel" required
                placeholder="Minimum stock alert level" />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="closeAddProductModal">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="addProductToInventory"
            :disabled="!selectedFranchiseProduct || !newInventoryForm.quantity">
            Add to Inventory
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Review Modal -->
    <VDialog v-model="isAddReviewModalVisible" max-width="600">
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Add Customer Review
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField label="Customer Name" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Customer Email" type="email" />
            </VCol>
            <VCol cols="12" md="6">
              <div class="text-body-2 text-disabled mb-2">
                Rating
              </div>
              <VRating size="large" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField label="Date" type="date" required />
            </VCol>
            <VCol cols="12">
              <VTextarea label="Review Comment" rows="4" required />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isAddReviewModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="addReview">
            Add Review
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
