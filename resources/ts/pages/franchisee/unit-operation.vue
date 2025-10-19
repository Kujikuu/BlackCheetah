<script setup lang="ts">

// 👉 Imports
import { computed, onMounted, ref } from 'vue'
import AddDocumentDialog from '@/components/dialogs/franchise/AddDocumentDialog.vue'
import CreateTaskModal from '@/components/dialogs/CreateTaskModal.vue'
import DocumentActionModal from '@/components/dialogs/DocumentActionModal.vue'
import ViewTaskDialog from '@/components/dialogs/tasks/ViewTaskDialog.vue'
import EditTaskDialog from '@/components/dialogs/tasks/EditTaskDialog.vue'
import DeleteTaskDialog from '@/components/dialogs/tasks/DeleteTaskDialog.vue'
import ViewReviewDialog from '@/components/dialogs/reviews/ViewReviewDialog.vue'
import ViewProductDialog from '@/components/dialogs/products/ViewProductDialog.vue'
import ViewStaffDialog from '@/components/dialogs/staff/ViewStaffDialog.vue'
import AddReviewDialog from '@/components/dialogs/reviews/AddReviewDialog.vue'
import EditReviewDialog from '@/components/dialogs/reviews/EditReviewDialog.vue'
import EditProductDialog from '@/components/dialogs/products/EditProductDialog.vue'
import EditStaffDialog from '@/components/dialogs/staff/EditStaffDialog.vue'
import AddStaffDialog from '@/components/dialogs/staff/AddStaffDialog.vue'
import EditUnitDialog from '@/components/dialogs/units/EditUnitDialog.vue'
import AddProductToInventoryDialog from '@/components/dialogs/products/AddProductToInventoryDialog.vue'
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

// Computed unit ID for dialogs (prioritizes unitData.id, falls back to currentUnitId)
const dialogUnitId = computed<number | null>(() => {
  return unitData.value?.id || currentUnitId.value
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
const documentAction = ref<'approve' | 'reject' | null>(null)

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

const saveUnitDetails = async (updatedUnitData?: any) => {
  const unitToUpdate = updatedUnitData || editUnitForm.value
  
  try {
    const response = await franchiseeDashboardApi.updateUnitDetails(getUnitId(), unitToUpdate)
    if (response.success) {
      unitData.value = response.data
      isEditUnitModalVisible.value = false
    }
  }
  catch (err) {
    console.error('Error updating unit details:', err)
  }
}

// 👉 Handle unit updated event from dialog
const onUnitUpdated = async (updatedUnitData: any) => {
  await saveUnitDetails(updatedUnitData)
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

const onDocumentActionConfirmed = async (data: { document: any; action: string; comment: string }) => {
  try {
    if (data.action === 'approve') {
      // Handle document approval
      console.log('Approving document:', data.document, 'with comment:', data.comment)
      // Add approval logic here
    }
    else if (data.action === 'reject') {
      // Handle document rejection
      console.log('Rejecting document:', data.document, 'with comment:', data.comment)
      // Add rejection logic here
    }
  }
  catch (error) {
    console.error('Error performing document action:', error)
  }
  finally {
    isDocumentActionModalVisible.value = false
    selectedDocument.value = null
    documentAction.value = null
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

const saveStaff = async (updatedStaff?: any) => {
  const staffToSave = updatedStaff || selectedStaff.value
  if (!staffToSave)
    return

  try {
    const response = await franchiseeDashboardApi.updateStaff(getUnitId(), staffToSave.id, staffToSave)
    if (response.success) {
      const index = staffData.value.findIndex(staff => staff.id === staffToSave.id)
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

// 👉 Handle staff updated event from dialog
const onStaffUpdated = async (staff: any) => {
  await saveStaff(staff)
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

const addStaff = async (staffToAdd?: any) => {
  const staffToSubmit = staffToAdd || newStaffForm.value
  
  try {
    const response = await franchiseeDashboardApi.createStaff(getUnitId(), staffToSubmit)
    if (response.success) {
      staffData.value.push(response.data)
      isAddStaffModalVisible.value = false

      // Reset form
      newStaffForm.value = {
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
      }
    }
  }
  catch (error) {
    console.error('Error adding staff:', error)
  }
}

// 👉 Handle staff added event from dialog
const onStaffAdded = async (staff: any) => {
  await addStaff(staff)
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

const saveProduct = async (updatedProduct?: any) => {
  const productToSave = updatedProduct || selectedProduct.value
  if (!productToSave)
    return

  try {
    const response = await franchiseeDashboardApi.updateProduct(getUnitId(), productToSave.id, productToSave)
    if (response.success) {
      const index = productsData.value.findIndex(product => product.id === productToSave.id)
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

// 👉 Handle product updated event from dialog
const onProductUpdated = async (product: any) => {
  await saveProduct(product)
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
const addProductToInventory = async (inventoryData?: { productId: number; quantity: number; reorderLevel: number }) => {
  const dataToUse = inventoryData || {
    productId: selectedFranchiseProduct.value!,
    quantity: Number.parseInt(newInventoryForm.value.quantity.toString()),
    reorderLevel: Number.parseInt(newInventoryForm.value.reorderLevel.toString()),
  }

  try {
    // Get unit ID from loaded data or route params
    const unitIdToUse = unitData.value?.id || getUnitId()

    if (!unitIdToUse) {
      console.error('Unit ID not available for adding product')
      return
    }

    const response = await franchiseeDashboardApi.addProductToInventory(unitIdToUse, dataToUse)
    if (response.success) {
      // Add the product with inventory data to our products list
      const selectedProduct = availableFranchiseProducts.value.find(p => p.id === dataToUse.productId)
      if (selectedProduct) {
        productsData.value.push({
          ...selectedProduct,
          stock: dataToUse.quantity,
        })
      }
      closeAddProductModal()
    }
  }
  catch (error) {
    console.error('Error adding product to inventory:', error)
  }
}

// 👉 Handle product added event from dialog
const onProductAdded = async (inventoryData: { productId: number; quantity: number; reorderLevel: number }) => {
  await addProductToInventory(inventoryData)
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

const saveReview = async (updatedReview?: any) => {
  const reviewToSave = updatedReview || selectedReview.value
  if (!reviewToSave)
    return

  try {
    const response = await franchiseeDashboardApi.updateReview(getUnitId(), reviewToSave.id, reviewToSave)
    if (response.success) {
      const index = reviewsData.value.findIndex(review => review.id === reviewToSave.id)
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

// 👉 Handle review updated event from dialog
const onReviewUpdated = async (review: any) => {
  await saveReview(review)
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

const addReview = async (reviewData?: any) => {
  const reviewToSubmit = reviewData || newReviewForm.value
  
  try {
    const response = await franchiseeDashboardApi.createReview(getUnitId(), reviewToSubmit)
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

// 👉 Handle review added event from dialog
const onReviewAdded = async (review: any) => {
  await addReview(review)
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

// Event handlers for dialog components
const onTaskUpdated = (updatedTask: any) => {
  const index = tasksData.value.findIndex(task => task.id === updatedTask.id)
  if (index !== -1)
    tasksData.value[index] = updatedTask

  isEditTaskModalVisible.value = false
  selectedTask.value = null
}

const onTaskDeleted = (taskId: number) => {
  const index = tasksData.value.findIndex(task => task.id === taskId)
  if (index !== -1)
    tasksData.value.splice(index, 1)

  isDeleteDialogVisible.value = false
  taskToDelete.value = null
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
          <VChip v-if="unitData" :color="resolveStatusVariant(unitData.status)" size="large" label
            class="text-capitalize">
            {{ unitData.status }}
          </VChip>
          <VSkeletonLoader v-else type="chip" width="80" height="32" />
        </div>
      </VCol>
    </VRow>

    <!-- Tabs -->
    <VTabs v-model="currentTab" class="mb-6">
      <VTab value="overview">
        <VIcon icon="tabler-info-circle" start />
        Overview
      </VTab>
      <VTab value="tasks" hidden>
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
                      <VBtn size="small" variant="text" color="primary" prepend-icon="tabler-download"
                        @click="downloadDocument(document)">
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

    <AddDocumentDialog 
      v-model:is-dialog-visible="isAddDocumentModalVisible" 
      :unit-id="dialogUnitId"
      mode="unit"
      @document-added="onDocumentAdded" 
    />

    <DocumentActionModal 
      v-if="documentAction" 
      v-model:is-dialog-visible="isDocumentActionModalVisible" 
      :document="selectedDocument"
      :action="documentAction" 
      @document-action-confirmed="onDocumentActionConfirmed" 
    />

    <!-- View Task Dialog -->
    <ViewTaskDialog
      v-model:is-dialog-visible="isViewTaskModalVisible"
      :task="selectedTask"
    />

    <!-- Edit Task Dialog -->
    <EditTaskDialog
      v-model:is-dialog-visible="isEditTaskModalVisible"
      :task="selectedTask"
      :user-options="[]"
      :users-loading="false"
      @task-updated="onTaskUpdated"
    />

    <!-- Delete Task Dialog -->
    <DeleteTaskDialog
      v-model:is-dialog-visible="isDeleteDialogVisible"
      :task-id="taskToDelete"
      @task-deleted="onTaskDeleted"
    />

    <!-- Edit Unit Details Dialog -->
    <EditUnitDialog
      v-model:is-dialog-visible="isEditUnitModalVisible"
      :unit-data="unitData"
      @unit-updated="onUnitUpdated"
    />

    <!-- Add Staff Dialog -->
    <AddStaffDialog
      v-model:is-dialog-visible="isAddStaffModalVisible"
      @staff-added="onStaffAdded"
    />

    <!-- View Staff Dialog -->
    <ViewStaffDialog
      v-model:is-dialog-visible="isViewStaffModalVisible"
      :selected-staff="selectedStaff"
    />

    <!-- Edit Staff Dialog -->
    <EditStaffDialog
      v-model:is-dialog-visible="isEditStaffModalVisible"
      :selected-staff="selectedStaff"
      @staff-updated="onStaffUpdated"
    />

    <!-- Add Product to Inventory Dialog -->
    <AddProductToInventoryDialog
      v-model:is-dialog-visible="isAddProductModalVisible"
      :available-franchise-products="availableFranchiseProducts"
      @product-added="onProductAdded"
    />

    <!-- Add Review Dialog -->
    <AddReviewDialog
      v-model:is-dialog-visible="isAddReviewModalVisible"
      @review-added="onReviewAdded"
    />

    <!-- View Product Dialog -->
    <ViewProductDialog
      v-model:is-dialog-visible="isViewProductModalVisible"
      :selected-product="selectedProduct"
    />

    <!-- Edit Product Dialog -->
    <EditProductDialog
      v-model:is-dialog-visible="isEditProductModalVisible"
      :selected-product="selectedProduct"
      @product-updated="onProductUpdated"
    />

    <!-- View Review Dialog -->
    <ViewReviewDialog
      v-model:is-dialog-visible="isViewReviewModalVisible"
      :selected-review="selectedReview"
    />

    <!-- Edit Review Dialog -->
    <EditReviewDialog
      v-model:is-dialog-visible="isEditReviewModalVisible"
      :selected-review="selectedReview"
      @review-updated="onReviewUpdated"
    />
  </section>
</template>
