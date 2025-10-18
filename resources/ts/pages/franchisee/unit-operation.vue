<script setup lang="ts">

// 👉 Imports
import { computed, onMounted, ref } from 'vue'
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

// 👉 Router
const route = useRoute()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Unit ID from route
const unitId = computed<string>(() => {
  const id = route.params.id

  return typeof id === 'string' ? id : ''
})

// 👉 Safely parsed unit ID (number)
const currentUnitId = computed<number | null>(() => {
  if (unitId.value) {
    const parsed = Number.parseInt(unitId.value)
    if (!Number.isNaN(parsed))
      return parsed
  }

  return null
})

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

// 👉 Helper to get the current unit ID safely
const getUnitId = (): number => {
  const id = unitData.value?.id || currentUnitId.value
  if (!id)
    throw new Error('Unit ID is not available')

  return id
}

// 👉 Computed selected product preview
const selectedProductPreview = computed(() => {
  if (!selectedFranchiseProduct.value)
    return null

  return availableFranchiseProducts.value.find(p => p.id === selectedFranchiseProduct.value)
})

// 👉 API Loading Functions
const loadUnitData = async () => {
  try {
    loading.value = true
    error.value = null

    // unitId is optional - if not provided, API will fetch the franchisee's unit
    // Use currentUnitId.value directly here (might be null), API accepts optional parameter
    const unitIdParam = currentUnitId.value || undefined

    // Load unit details
    const unitDetailsResponse = await franchiseeDashboardApi.getUnitDetails(unitIdParam)
    if (unitDetailsResponse.success)
      unitData.value = unitDetailsResponse.data

    // Load tasks
    const tasksResponse = await franchiseeDashboardApi.getUnitTasks(unitIdParam)
    if (tasksResponse.success)
      tasksData.value = tasksResponse.data

    // Load staff
    const staffResponse = await franchiseeDashboardApi.getUnitStaff(unitIdParam)
    if (staffResponse.success)
      staffData.value = staffResponse.data

    // Load products
    const productsResponse = await franchiseeDashboardApi.getUnitProducts(unitIdParam)
    if (productsResponse.success)
      productsData.value = productsResponse.data

    // Load reviews
    const reviewsResponse = await franchiseeDashboardApi.getUnitReviews(unitIdParam)
    if (reviewsResponse.success)
      reviewsData.value = reviewsResponse.data

    // Load documents
    const documentsResponse = await franchiseeDashboardApi.getUnitDocuments(unitIdParam)
    if (documentsResponse.success)
      documentsData.value = documentsResponse.data
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

// 👉 Document modal states
const isDocumentActionModalVisible = ref(false)
const selectedDocument = ref<any>(null)
const documentAction = ref<'view' | 'download' | 'delete' | null>(null)

// 👉 Form data
const newStaffForm = ref({
  name: '',
  email: '',
  phone: '',
  jobTitle: '',
  department: '',
  salary: 0,
  hireDate: '',
  shiftStart: '',
  shiftEnd: '',
  status: 'Active',
  employmentType: 'full_time',
  notes: '',
})

const newReviewForm = ref({
  customerName: '',
  customerEmail: '',
  rating: 0,
  comment: '',
  date: '',
})

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
  if (status === 'active' || status === 'completed' || status === 'approved' || status === 'working')
    return 'success'
  if (status === 'pending' || status === 'in_progress' || status === 'leave')
    return 'warning'
  if (status === 'inactive' || status === 'rejected' || status === 'terminated')
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
    const response = await franchiseeDashboardApi.updateUnitDetails(getUnitId(), editUnitForm.value)
    if (response.success) {
      unitData.value = response.data
      isEditUnitModalVisible.value = false
    }
  }
  catch (err) {
    console.error('Error updating unit details:', err)
  }
}

const onTaskCreated = async (task: any) => {
  try {
    const response = await franchiseeDashboardApi.createTask(getUnitId(), task)
    if (response.success)
      tasksData.value.push(response.data)
  }
  catch (error) {
    console.error('Error creating task:', error)
  }
}

const onDocumentAdded = async (document: any) => {
  try {
    // Create FormData to send file
    const formData = new FormData()

    formData.append('title', document.title)
    formData.append('description', document.description)
    formData.append('type', document.type)
    formData.append('status', document.status || 'pending')
    formData.append('comment', document.comment || '')
    if (document.file)
      formData.append('file', document.file)

    const response = await franchiseeDashboardApi.createDocument(getUnitId(), formData)
    if (response.success)
      documentsData.value.push(response.data)
  }
  catch (error) {
    console.error('Error creating document:', error)
  }
}

const onDocumentActionConfirmed = async (action: string, document: any) => {
  try {
    if (action === 'delete') {
      const response = await franchiseeDashboardApi.deleteDocument(getUnitId(), document.id)
      if (response.success) {
        const index = documentsData.value.findIndex(doc => doc.id === document.id)
        if (index !== -1)
          documentsData.value.splice(index, 1)
      }
    }
    else if (action === 'download') {
      // Handle document download
      downloadDocument(document)
    }
  }
  catch (error) {
    console.error('Error performing document action:', error)
  }
  finally {
    isDocumentActionModalVisible.value = false
    selectedDocument.value = null
    documentAction.value = undefined
  }
}

// 👉 Download document handler
const downloadDocument = async (document: any) => {
  try {
    const unitIdToUse = unitData.value?.id || getUnitId()

    // Get the access token from cookie
    const accessToken = useCookie('accessToken').value

    if (!accessToken) {
      console.error('No access token found')

      return
    }

    // Fetch the document with authentication using native fetch
    const response = await fetch(`/api/v1/unit-manager/units/documents/${unitIdToUse}/${document.id}/download`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Accept': 'application/octet-stream',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
    })

    if (!response.ok) {
      const errorText = await response.text()

      console.error('Download failed:', response.status, errorText)
      throw new Error(`Failed to download document: ${response.status}`)
    }

    // Get the blob from response
    const blob = await response.blob()

    // Create object URL and trigger download
    const url = window.URL.createObjectURL(blob)
    const link = window.document.createElement('a')

    link.href = url
    link.download = document.fileName
    window.document.body.appendChild(link)
    link.click()
    window.document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  }
  catch (error) {
    console.error('Error downloading document:', error)
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
    const response = await franchiseeDashboardApi.deleteStaff(getUnitId(), staffToDelete.value)
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
    const response = await franchiseeDashboardApi.updateStaff(getUnitId(), selectedStaff.value.id, selectedStaff.value)
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
    const response = await franchiseeDashboardApi.createStaff(getUnitId(), staff)
    if (response.success)
      staffData.value.push(response.data)
  }
  catch (error) {
    console.error('Error creating staff:', error)
  }
}

const addStaff = async () => {
  try {
    const response = await franchiseeDashboardApi.createStaff(getUnitId(), newStaffForm.value)
    if (response.success) {
      staffData.value.push(response.data)
      isAddStaffModalVisible.value = false

      // Reset form
      newStaffForm.value = {
        name: '',
        email: '',
        phone: '',
        position: '',
        salary: 0,
        status: 'Active',
      }
    }
  }
  catch (error) {
    console.error('Error adding staff:', error)
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
    const response = await franchiseeDashboardApi.removeProductFromInventory(getUnitId(), productToDelete.value)
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
    const response = await franchiseeDashboardApi.updateProduct(getUnitId(), selectedProduct.value.id, selectedProduct.value)
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
    // Get unit ID from loaded data or route params
    const unitIdToUse = unitData.value?.id || getUnitId()

    if (!unitIdToUse) {
      console.error('Unit ID not available yet. Please wait for unit data to load.')
      availableFranchiseProducts.value = []

      return
    }

    // This would call an API to get franchise products not yet in this unit's inventory
    const response = await franchiseeDashboardApi.getAvailableFranchiseProducts(unitIdToUse)
    if (response.success)
      availableFranchiseProducts.value = response.data
  }
  catch (error) {
    console.error('Error loading franchise products:', error)
    availableFranchiseProducts.value = []
  }
}

// 👉 Add product to unit inventory
const addProductToInventory = async () => {
  if (!selectedFranchiseProduct.value)
    return

  try {
    // Get unit ID from loaded data or route params
    const unitIdToUse = unitData.value?.id || getUnitId()

    if (!unitIdToUse) {
      console.error('Unit ID not available for adding product')

      return
    }

    const inventoryData = {
      productId: selectedFranchiseProduct.value,
      quantity: Number.parseInt(newInventoryForm.value.quantity.toString()),
      reorderLevel: Number.parseInt(newInventoryForm.value.reorderLevel.toString()),
    }

    const response = await franchiseeDashboardApi.addProductToInventory(unitIdToUse, inventoryData)
    if (response.success) {
      // Add the product with inventory data to our products list
      const selectedProduct = availableFranchiseProducts.value.find(p => p.id === selectedFranchiseProduct.value)
      if (selectedProduct) {
        productsData.value.push({
          ...selectedProduct,
          stock: inventoryData.quantity,
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
    const response = await franchiseeDashboardApi.deleteReview(getUnitId(), reviewToDelete.value)
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
    const response = await franchiseeDashboardApi.updateReview(getUnitId(), selectedReview.value.id, selectedReview.value)
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
    const response = await franchiseeDashboardApi.createReview(getUnitId(), review)
    if (response.success)
      reviewsData.value.push(response.data)
  }
  catch (error) {
    console.error('Error creating review:', error)
  }
}

const addReview = async () => {
  try {
    const response = await franchiseeDashboardApi.createReview(getUnitId(), newReviewForm.value)
    if (response.success) {
      reviewsData.value.push(response.data)
      isAddReviewModalVisible.value = false

      // Reset form
      newReviewForm.value = {
        customerName: '',
        customerEmail: '',
        rating: 0,
        comment: '',
        date: '',
      }
    }
  }
  catch (error) {
    console.error('Error adding review:', error)
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
      const response = await franchiseeDashboardApi.deleteTask(getUnitId(), taskToDelete.value)
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
    const response = await franchiseeDashboardApi.updateTask(getUnitId(), selectedTask.value.id, selectedTask.value)
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
                {{ unitData ? `Managed by ${unitData.franchiseeName} • ${unitData.city}, ${unitData.state}`
                  : 'Loading unit information...' }}
              </p>
            </div>
          </div>
          <VChip
            v-if="unitData"
            :color="resolveStatusVariant(unitData.status)"
            size="large"
            label
            class="text-capitalize"
          >
            {{ unitData.status }}
          </VChip>
          <VSkeletonLoader
            v-else
            type="chip"
            width="80"
            height="32"
          />
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
      <VTab
        value="tasks"
        hidden
      >
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
          <VCardItem class="pb-4">
            <VCardTitle>Unit Information</VCardTitle>
            <template #append>
              <VBtn
                color="primary"
                prepend-icon="tabler-edit"
                :disabled="!unitData"
                @click="openEditUnitModal"
              >
                Edit Details
              </VBtn>
            </template>
          </VCardItem>
          <VDivider />
          <VCardText>
            <div
              v-if="loading"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
              <div class="mt-3 text-body-1">
                Loading unit information...
              </div>
            </div>
            <div
              v-else-if="error"
              class="text-center py-8"
            >
              <VIcon
                icon="tabler-alert-circle"
                color="error"
                size="48"
                class="mb-3"
              />
              <div class="text-body-1 text-error">
                {{ error }}
              </div>
              <VBtn
                color="primary"
                variant="tonal"
                class="mt-3"
                @click="loadUnitData"
              >
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
        <div
          v-if="loading"
          class="text-center py-8"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
          <div class="mt-3 text-body-1">
            Loading tasks data...
          </div>
        </div>
        <div
          v-else-if="error"
          class="text-center py-8"
        >
          <VIcon
            icon="tabler-alert-circle"
            color="error"
            size="48"
            class="mb-3"
          />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn
            color="primary"
            variant="tonal"
            class="mt-3"
            @click="loadUnitData"
          >
            Retry
          </VBtn>
        </div>
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
              <template #append>
                <VBtn
                  color="primary"
                  prepend-icon="tabler-plus"
                  @click="isAddTaskModalVisible = true"
                >
                  Create Task
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable
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
          </VCard>
        </template>
      </VWindowItem>

      <!-- Documents Tab -->
      <VWindowItem value="documents">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Unit Documents</VCardTitle>
            <template #append>
              <VBtn
                color="primary"
                prepend-icon="tabler-plus"
                :disabled="loading"
                @click="isAddDocumentModalVisible = true"
              >
                Add Document
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <div
              v-if="loading"
              class="text-center py-8"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
              <div class="mt-3 text-body-1">
                Loading documents...
              </div>
            </div>
            <div
              v-else-if="error"
              class="text-center py-8"
            >
              <VIcon
                icon="tabler-alert-circle"
                color="error"
                size="48"
                class="mb-3"
              />
              <div class="text-body-1 text-error">
                {{ error }}
              </div>
              <VBtn
                color="primary"
                variant="tonal"
                class="mt-3"
                @click="loadUnitData"
              >
                Retry
              </VBtn>
            </div>
            <div
              v-else-if="documentsData.length === 0"
              class="text-center py-8"
            >
              <VIcon
                icon="tabler-folder-open"
                size="48"
                class="text-disabled mb-3"
              />
              <div class="text-body-1 text-disabled">
                No documents found for this unit
              </div>
            </div>
            <VRow v-else>
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
                        @click="downloadDocument(document)"
                      >
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
        <div
          v-if="loading"
          class="text-center py-8"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
          <div class="mt-3 text-body-1">
            Loading staff data...
          </div>
        </div>
        <div
          v-else-if="error"
          class="text-center py-8"
        >
          <VIcon
            icon="tabler-alert-circle"
            color="error"
            size="48"
            class="mb-3"
          />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn
            color="primary"
            variant="tonal"
            class="mt-3"
            @click="loadUnitData"
          >
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol
              cols="12"
              md="4"
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
                      Total Employees
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
              md="4"
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
              md="4"
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
                      icon="tabler-user-minus"
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
          </VRow>

          <!-- Staff Table -->
          <VCard>
            <VCardItem class="pb-4">
              <VCardTitle>Staff Members</VCardTitle>
              <template #append>
                <VBtn
                  color="primary"
                  prepend-icon="tabler-plus"
                  @click="isAddStaffModalVisible = true"
                >
                  Add Staff
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable
              :items="staffData"
              :headers="[...staffHeaders, { title: 'Actions', key: 'actions', sortable: false }]"
              class="text-no-wrap"
              item-value="id"
            >
              <!-- Name -->
              <template #item.name="{ item }">
                <div class="d-flex align-center gap-x-3">
                  <VAvatar
                    size="34"
                    color="primary"
                    variant="tonal"
                  >
                    <span>{{ item.name.split(' ').map((n: string) => n[0]).join('') }}</span>
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
        <div
          v-if="loading"
          class="text-center py-8"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
          <div class="mt-3 text-body-1">
            Loading inventory data...
          </div>
        </div>
        <div
          v-else-if="error"
          class="text-center py-8"
        >
          <VIcon
            icon="tabler-alert-circle"
            color="error"
            size="48"
            class="mb-3"
          />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn
            color="primary"
            variant="tonal"
            class="mt-3"
            @click="loadUnitData"
          >
            Retry
          </VBtn>
        </div>
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
              <VCardSubtitle>Manage stock levels for franchise products</VCardSubtitle>
              <template #append>
                <VBtn
                  color="primary"
                  prepend-icon="tabler-plus"
                  @click="openAddProductModal"
                >
                  Add to Inventory
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable
              :items="productsData"
              :headers="[...productHeaders, { title: 'Actions', key: 'actions', sortable: false }]"
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
        <div
          v-if="loading"
          class="text-center py-8"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
          <div class="mt-3 text-body-1">
            Loading reviews data...
          </div>
        </div>
        <div
          v-else-if="error"
          class="text-center py-8"
        >
          <VIcon
            icon="tabler-alert-circle"
            color="error"
            size="48"
            class="mb-3"
          />
          <div class="text-body-1 text-error">
            {{ error }}
          </div>
          <VBtn
            color="primary"
            variant="tonal"
            class="mt-3"
            @click="loadUnitData"
          >
            Retry
          </VBtn>
        </div>
        <template v-else>
          <!-- Stats Cards -->
          <VRow class="mb-6">
            <VCol
              cols="12"
              md="4"
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
                      Average Rating
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
              md="4"
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
                      icon="tabler-thumb-up"
                      size="26"
                    />
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
            <VCol
              cols="12"
              md="4"
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
                      icon="tabler-thumb-down"
                      size="26"
                    />
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
                <VBtn
                  color="primary"
                  prepend-icon="tabler-plus"
                  @click="isAddReviewModalVisible = true"
                >
                  Add Review
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VDataTable
              :items="reviewsData"
              :headers="[...reviewHeaders, { title: 'Actions', key: 'actions', sortable: false }]"
              class="text-no-wrap"
              item-value="id"
            >
              <!-- Rating -->
              <template #item.rating="{ item }">
                <div class="d-flex align-center gap-1">
                  <VRating
                    :model-value="item.rating"
                    readonly
                    size="small"
                    color="warning"
                  />
                  <span class="text-body-2">({{ item.rating }})</span>
                </div>
              </template>

              <!-- Comment -->
              <template #item.comment="{ item }">
                <div
                  class="text-body-2"
                  style="max-width: 300px;"
                >
                  {{ item.comment }}
                </div>
              </template>

              <!-- Sentiment -->
              <template #item.sentiment="{ item }">
                <VChip
                  :color="item.sentiment === 'positive' ? 'success' : 'error'"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ item.sentiment }}
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
    <CreateTaskModal
      v-model:is-dialog-visible="isAddTaskModalVisible"
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

    <!-- Edit Unit Details Modal -->
    <VDialog
      v-model="isEditUnitModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Unit Details
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editUnitForm.branchName"
                label="Branch Name"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editUnitForm.city"
                label="City"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editUnitForm.franchiseeName"
                label="Franchisee"
                disabled
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editUnitForm.contactNumber"
                label="Contact Number"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="editUnitForm.address"
                label="Address"
                rows="3"
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
            @click="isEditUnitModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveUnitDetails"
          >
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Staff Modal -->
    <VDialog
      v-model="isAddStaffModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Add New Staff Member
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.name"
                label="Full Name"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.email"
                label="Email"
                type="email"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.phone"
                label="Phone"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.jobTitle"
                label="Job Title"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.department"
                label="Department"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.salary"
                label="Salary"
                type="number"
                prefix="SAR"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.hireDate"
                label="Hire Date"
                type="date"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.shiftStart"
                label="Shift Start"
                type="time"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newStaffForm.shiftEnd"
                label="Shift End"
                type="time"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="newStaffForm.status"
                label="Status"
                :items="['Active', 'On Leave', 'Terminated', 'Inactive']"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="newStaffForm.employmentType"
                label="Employment Type"
                :items="[
                  { title: 'Full Time', value: 'full_time' },
                  { title: 'Part Time', value: 'part_time' },
                  { title: 'Contract', value: 'contract' },
                  { title: 'Temporary', value: 'temporary' },
                ]"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="newStaffForm.notes"
                label="Notes"
                rows="2"
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
            @click="isAddStaffModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="addStaff"
          >
            Add Staff
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Staff Modal -->
    <VDialog
      v-model="isViewStaffModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Staff Details
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedStaff"
          class="pa-6"
        >
          <VRow>
            <VCol
              cols="12"
              class="text-center pb-6"
            >
              <VAvatar
                size="80"
                color="primary"
                variant="tonal"
              >
                <span class="text-h4">{{ selectedStaff.name.split(' ').map((n: string) => n[0]).join('') }}</span>
              </VAvatar>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Full Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.name }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Email
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.email }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Phone
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.phone || 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Job Title
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.jobTitle }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Department
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.department || 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Salary
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.salary ? `SAR ${selectedStaff.salary}`
                  : 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Hire Date
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.hireDate || 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Shift Time
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedStaff.shiftTime }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Employment Type
              </div>
              <div class="text-body-1 font-weight-medium text-capitalize">
                {{ selectedStaff.employmentType?.replace('_',
                                                         ' ') || 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Status
              </div>
              <VChip
                :color="resolveStatusVariant(selectedStaff.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedStaff.status }}
              </VChip>
            </VCol>
            <VCol
              v-if="selectedStaff.notes"
              cols="12"
            >
              <div class="text-body-2 text-disabled mb-1">
                Notes
              </div>
              <div class="text-body-1">
                {{ selectedStaff.notes }}
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewStaffModalVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Staff Modal -->
    <VDialog
      v-model="isEditStaffModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Staff Member
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedStaff"
          class="pa-6"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.name"
                label="Full Name"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.email"
                label="Email"
                type="email"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.phone"
                label="Phone"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.jobTitle"
                label="Job Title"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.department"
                label="Department"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.salary"
                label="Salary"
                type="number"
                prefix="SAR"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.hireDate"
                label="Hire Date"
                type="date"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.shiftStart"
                label="Shift Start"
                type="time"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedStaff.shiftEnd"
                label="Shift End"
                type="time"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="selectedStaff.status"
                label="Status"
                :items="['working', 'leave', 'terminated', 'inactive']"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="selectedStaff.employmentType"
                label="Employment Type"
                :items="[
                  { title: 'Full Time', value: 'full_time' },
                  { title: 'Part Time', value: 'part_time' },
                  { title: 'Contract', value: 'contract' },
                  { title: 'Temporary', value: 'temporary' },
                ]"
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="selectedStaff.notes"
                label="Notes"
                rows="2"
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
            @click="isEditStaffModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveStaff"
          >
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Product to Inventory Modal -->
    <VDialog
      v-model="isAddProductModalVisible"
      max-width="600"
    >
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
              <VSelect
                v-model="selectedFranchiseProduct"
                label="Select Product"
                :items="availableFranchiseProducts"
                item-title="name"
                item-value="id"
                required
                placeholder="Choose a product from franchise catalog"
                :no-data-text="availableFranchiseProducts.length === 0 ? 'No available franchise products to add' : 'No products match your search'"
              >
                <template #item="{ item, props }">
                  <VListItem v-bind="props">
                    <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
                    <VListItemSubtitle>
                      {{ item.raw.category }} • SAR {{ item.raw.unitPrice.toFixed(2) }}
                    </VListItemSubtitle>
                  </VListItem>
                </template>
              </VSelect>

              <div
                v-if="availableFranchiseProducts.length === 0"
                class="text-center py-4"
              >
                <VIcon
                  icon="tabler-package-off"
                  size="48"
                  class="text-disabled mb-2"
                />
                <div class="text-body-2 text-disabled">
                  All franchise products are already in this unit's inventory
                </div>
              </div>
            </VCol>

            <!-- Product Preview (when product is selected) -->
            <VCol
              v-if="selectedProductPreview"
              cols="12"
            >
              <VCard
                variant="tonal"
                color="primary"
              >
                <VCardText>
                  <div class="d-flex align-center gap-3">
                    <VIcon
                      icon="tabler-package"
                      size="24"
                    />
                    <div>
                      <h6 class="text-h6">
                        {{ selectedProductPreview.name }}
                      </h6>
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

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newInventoryForm.quantity"
                label="Initial Stock Quantity"
                type="number"
                min="0"
                required
                placeholder="Enter quantity to add"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newInventoryForm.reorderLevel"
                label="Reorder Level"
                type="number"
                min="0"
                required
                placeholder="Minimum stock alert level"
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
            @click="closeAddProductModal"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :disabled="!selectedFranchiseProduct || !newInventoryForm.quantity"
            @click="addProductToInventory"
          >
            Add to Inventory
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add Review Modal -->
    <VDialog
      v-model="isAddReviewModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Add Customer Review
        </VCardTitle>

        <VDivider />

        <VCardText class="pa-6">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newReviewForm.customerName"
                label="Customer Name"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newReviewForm.customerEmail"
                label="Customer Email"
                type="email"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-2">
                Rating
              </div>
              <VRating
                v-model="newReviewForm.rating"
                size="large"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="newReviewForm.date"
                label="Date"
                type="date"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="newReviewForm.comment"
                label="Review Comment"
                rows="4"
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
            @click="isAddReviewModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="addReview"
          >
            Add Review
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Product Modal -->
    <VDialog
      v-model="isViewProductModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Product Details
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedProduct"
          class="pa-6"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Product Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedProduct.name }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Category
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedProduct.category }}
              </div>
            </VCol>
            <VCol cols="12">
              <div class="text-body-2 text-disabled mb-1">
                Description
              </div>
              <div class="text-body-1">
                {{ selectedProduct.description }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Unit Price
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ formatCurrency(selectedProduct.unitPrice) }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Stock
              </div>
              <VChip
                :color="selectedProduct.stock === 0 ? 'error' : selectedProduct.stock <= 10 ? 'warning' : 'success'"
                size="small"
                label
              >
                {{ selectedProduct.stock }} units
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
                :color="resolveStatusVariant(selectedProduct.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedProduct.status }}
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
            @click="isViewProductModalVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Product Modal -->
    <VDialog
      v-model="isEditProductModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Product Inventory
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedProduct"
          class="pa-6"
        >
          <VRow>
            <VCol cols="12">
              <div class="text-body-2 text-disabled mb-3">
                Product: <span class="font-weight-medium text-high-emphasis">{{ selectedProduct.name }}</span>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model.number="selectedProduct.stock"
                label="Stock Quantity"
                type="number"
                min="0"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Unit Price
              </div>
              <div class="text-h6">
                {{ formatCurrency(selectedProduct.unitPrice) }}
              </div>
            </VCol>
            <VCol cols="12">
              <div class="text-caption text-disabled">
                Note: You can only update the stock quantity. Product details like name, price, and category are managed
                at
                the franchise level.
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isEditProductModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveProduct"
          >
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Review Modal -->
    <VDialog
      v-model="isViewReviewModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Review Details
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedReview"
          class="pa-6"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Customer Name
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedReview.customerName }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Customer Email
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedReview.customerEmail || 'N/A' }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Rating
              </div>
              <div class="d-flex align-center gap-1">
                <VRating
                  :model-value="selectedReview.rating"
                  readonly
                  size="small"
                  density="compact"
                />
                <span class="text-body-1 font-weight-medium">{{ selectedReview.rating }}/5</span>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Date
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedReview.date }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-1">
                Sentiment
              </div>
              <VChip
                :color="selectedReview.sentiment === 'positive' ? 'success' : selectedReview.sentiment === 'neutral' ? 'warning' : 'error'"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedReview.sentiment }}
              </VChip>
            </VCol>
            <VCol cols="12">
              <div class="text-body-2 text-disabled mb-1">
                Comment
              </div>
              <div class="text-body-1">
                {{ selectedReview.comment }}
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-6">
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewReviewModalVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Review Modal -->
    <VDialog
      v-model="isEditReviewModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle class="text-h5 pa-6 pb-4">
          Edit Customer Review
        </VCardTitle>

        <VDivider />

        <VCardText
          v-if="selectedReview"
          class="pa-6"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedReview.customerName"
                label="Customer Name"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedReview.customerEmail"
                label="Customer Email"
                type="email"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-body-2 text-disabled mb-2">
                Rating
              </div>
              <VRating
                v-model="selectedReview.rating"
                size="large"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="selectedReview.date"
                label="Date"
                type="date"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="selectedReview.comment"
                label="Review Comment"
                rows="4"
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
            @click="isEditReviewModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveReview"
          >
            Save Changes
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
