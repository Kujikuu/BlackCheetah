<script setup lang="ts">
import { $api } from '@/utils/api'

// 👉 Router
const router = useRouter()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Loading states
const isLoading = ref(true)
const isUpdating = ref(false)
const isEditMode = ref(false)

// 👉 Snackbar for notifications
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
})

const showSnackbar = (message: string, color: string = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
  }
}

// 👉 Franchise data (will be populated from API)
const franchiseData = ref<FranchiseData>({
  id: 0,
  personalInfo: {
    contactNumber: '',
    country: '',
    state: '',
    city: '',
    address: '',
  },
  franchiseDetails: {
    franchiseName: '',
    website: '',
    logo: null,
  },
  legalDetails: {
    legalEntityName: '',
    businessStructure: '',
    taxId: '',
    industry: '',
    fundingAmount: '',
    fundingSource: '',
  },
  contactDetails: {
    contactNumber: '',
    email: '',
    address: '',
    country: '',
    state: '',
    city: '',
  },
})

// 👉 Type definitions
interface PersonalInfo {
  contactNumber: string
  country: string
  state: string
  city: string
  address: string
}

interface FranchiseDetails {
  franchiseName: string
  website: string
  logo: File | null
}

interface LegalDetails {
  legalEntityName: string
  businessStructure: string
  taxId: string
  industry: string
  fundingAmount: string
  fundingSource: string
}

interface ContactDetails {
  contactNumber: string
  email: string
  address: string
  country: string
  state: string
  city: string
}

interface FranchiseData {
  id: number
  personalInfo: PersonalInfo
  franchiseDetails: FranchiseDetails
  legalDetails: LegalDetails
  contactDetails: ContactDetails
}

interface DocumentData {
  id: number
  name: string
  description: string
  file_name: string
  file_size: number
  created_at: string
  type: string
  file_path?: string
  file_extension?: string
  mime_type?: string
  status?: string
  expiry_date?: string
  is_confidential?: boolean
  metadata?: any
}

interface ProductData {
  id: number
  name: string
  description: string
  unit_price: number
  category: string
  status: string
  stock: number
  sku?: string
  image?: string
  cost_price?: number
  weight?: number
  dimensions?: string
  minimum_stock?: number
  attributes?: any
}

// 👉 Documents data (will be populated from API)
const documentsData = ref<DocumentData[]>([])

// // 👉 Products data (will be populated from API)
const productsData = ref<ProductData[]>([])

// 👉 Logo upload handling
const logoFile = ref<File | null>(null)
const isUploadingLogo = ref(false)

// 👉 Load franchise data
const loadFranchiseData = async () => {
  try {
    isLoading.value = true
    const response = await $api('/v1/franchisor/franchise/data')

    if (response.success && response.data) {
      const apiData = response.data.franchise
      if (apiData) {
        // Map the API response structure to the frontend structure
        franchiseData.value = {
          id: apiData.id,
          personalInfo: {
            contactNumber: apiData.contactDetails?.contactNumber || '',
            address: apiData.contactDetails?.address || '',
            city: apiData.contactDetails?.city || '',
            state: apiData.contactDetails?.state || '',
            country: apiData.contactDetails?.country || '',
          },
          franchiseDetails: {
            franchiseName: apiData.franchiseDetails?.franchiseName || '',
            website: apiData.franchiseDetails?.website || '',
            logo: apiData.franchiseDetails?.logo || null,
          },
          legalDetails: {
            legalEntityName: apiData.legalDetails?.legalEntityName || '',
            businessStructure: apiData.legalDetails?.businessStructure || '',
            taxId: apiData.legalDetails?.taxId || '',
            industry: apiData.legalDetails?.industry || '',
            fundingAmount: apiData.legalDetails?.fundingAmount || '',
            fundingSource: apiData.legalDetails?.fundingSource || '',
          },
          contactDetails: {
            contactNumber: apiData.contactDetails?.contactNumber || '',
            email: apiData.contactDetails?.email || '',
            address: apiData.contactDetails?.address || '',
            city: apiData.contactDetails?.city || '',
            state: apiData.contactDetails?.state || '',
            country: apiData.contactDetails?.country || '',
          },
        }
      }
    }
  } catch (error: any) {
    console.error('Failed to load franchise data:', error)
    showSnackbar('Failed to load franchise data', 'error')
  } finally {
    isLoading.value = false
  }
}

// 👉 Load documents from API
const loadDocuments = async () => {
  try {
    if (!franchiseData.value?.id) {
      console.warn('No franchise ID available for loading documents')
      return
    }

    const response = await $api(`/v1/franchises/${franchiseData.value.id}/documents`)
    if (response.success && response.data) {
      documentsData.value = response.data.data || []
    }
  } catch (error: any) {
    console.error('Failed to load documents:', error)
    showSnackbar('Failed to load documents', 'error')
  }
}

// 👉 Load products from API
const loadProducts = async () => {
  try {
    if (!franchiseData.value?.id) {
      console.warn('No franchise ID available for loading products')
      return
    }

    const response = await $api(`/v1/franchises/${franchiseData.value.id}/products`)
    if (response.success && response.data) {
      productsData.value = response.data.data || []
    }
  } catch (error: any) {
    console.error('Failed to load products:', error)
    showSnackbar('Failed to load products', 'error')
  }
}

// 👉 Upload logo
const uploadLogo = async (): Promise<string | null> => {
  if (!logoFile.value) return null

  isUploadingLogo.value = true

  try {
    const formData = new FormData()
    formData.append('logo', logoFile.value)

    const response = await $api('/v1/franchisor/franchise/upload-logo', {
      method: 'POST',
      body: formData,
    })

    if (response.success) {
      return response.data.logo_url
    } else {
      showSnackbar(response.message || 'Failed to upload logo', 'error')
      return null
    }
  } catch (error: any) {
    console.error('Failed to upload logo:', error)
    showSnackbar('Failed to upload logo', 'error')
    return null
  } finally {
    isUploadingLogo.value = false
  }
}

// 👉 Update franchise data
const updateFranchiseData = async () => {
  try {
    isUpdating.value = true

    // Upload logo first if a new one is selected
    let logoUrl = franchiseData.value.franchiseDetails.logo
    if (logoFile.value) {
      const uploadedLogoUrl = await uploadLogo()
      if (uploadedLogoUrl) {
        logoUrl = uploadedLogoUrl
        franchiseData.value.franchiseDetails.logo = logoUrl
      } else {
        // If logo upload failed, don't proceed with the update
        return
      }
    }

    // Transform the data to match the backend's expected nested structure
    const updatePayload = {
      personalInfo: {
        contactNumber: franchiseData.value.personalInfo.contactNumber,
        address: franchiseData.value.personalInfo.address,
        city: franchiseData.value.personalInfo.city,
        state: franchiseData.value.personalInfo.state,
        country: franchiseData.value.personalInfo.country,
      },
      franchiseDetails: {
        franchiseDetails: {
          franchiseName: franchiseData.value.franchiseDetails.franchiseName,
          website: franchiseData.value.franchiseDetails.website,
          logo: franchiseData.value.franchiseDetails.logo,
        },
        legalDetails: {
          legalEntityName: franchiseData.value.legalDetails.legalEntityName,
          businessStructure: franchiseData.value.legalDetails.businessStructure,
          taxId: franchiseData.value.legalDetails.taxId,
          industry: franchiseData.value.legalDetails.industry,
          fundingAmount: franchiseData.value.legalDetails.fundingAmount,
          fundingSource: franchiseData.value.legalDetails.fundingSource,
        },
        contactDetails: {
          contactNumber: franchiseData.value.contactDetails.contactNumber,
          email: franchiseData.value.contactDetails.email,
          address: franchiseData.value.contactDetails.address,
          city: franchiseData.value.contactDetails.city,
          state: franchiseData.value.contactDetails.state,
          country: franchiseData.value.contactDetails.country,
        },
      },
    }

    const response = await $api('/v1/franchisor/franchise/update', {
      method: 'PUT',
      body: updatePayload,
    })

    if (response.success) {
      showSnackbar('Franchise information updated successfully', 'success')
      // Clear the logo file after successful update
      logoFile.value = null
      // Reload franchise data to get the updated information
      await loadFranchiseData()
    } else {
      showSnackbar(response.message || 'Failed to update franchise information', 'error')
    }
  } catch (error: any) {
    console.error('Failed to update franchise data:', error)

    if (error.status === 422 && error.data?.errors) {
      const errorMessages = Object.values(error.data.errors).flat()
      showSnackbar(`Validation failed: ${errorMessages.join(', ')}`, 'error')
    } else {
      showSnackbar('Failed to update franchise information', 'error')
    }
  } finally {
    isUpdating.value = false
  }
}

// 👉 Edit mode methods
const originalData = ref(null)

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value
  if (isEditMode.value) {
    // Store original data for cancel functionality
    originalData.value = JSON.parse(JSON.stringify(franchiseData.value))
  }
}

const cancelEdit = () => {
  // Restore original data
  if (originalData.value) {
    franchiseData.value = JSON.parse(JSON.stringify(originalData.value))
  }
  isEditMode.value = false
  originalData.value = null
}

const saveChanges = async () => {
  await updateFranchiseData()
  if (!isUpdating.value) {
    isEditMode.value = false
    originalData.value = null
  }
}

// 👉 Load data on component mount
onMounted(async () => {
  await loadFranchiseData()
  // Only load documents and products after franchise data is loaded
  if (franchiseData.value?.id) {
    await Promise.all([
      loadDocuments(),
      loadProducts()
    ])
  }
})

// 👉 Modals state
const isAddDocumentModalVisible = ref(false)
const isEditDocumentModalVisible = ref(false)
const isAddProductModalVisible = ref(false)
const isEditProductModalVisible = ref(false)
const isDeleteProductModalVisible = ref(false)
const isDeleteDocumentDialogVisible = ref(false)

// 👉 Computed for document modal visibility
const isDocumentModalVisible = computed({
  get: () => isAddDocumentModalVisible.value || isEditDocumentModalVisible.value,
  set: (value) => {
    if (!value) {
      isAddDocumentModalVisible.value = false
      isEditDocumentModalVisible.value = false
    }
  }
})

// 👉 Computed for product modal visibility
const isProductModalVisible = computed({
  get: () => isAddProductModalVisible.value || isEditProductModalVisible.value,
  set: (value) => {
    if (!value) {
      isAddProductModalVisible.value = false
      isEditProductModalVisible.value = false
    }
  }
})

// 👉 Selected items
const selectedDocument = ref<{
  title: string
  description: string
  file: File | null
}>({
  title: '',
  description: '',
  file: null,
})

const selectedProduct = ref<{
  id: number | null
  name: string
  description: string
  unit_price: number
  category: string
  status: string
  stock: number
  sku: string
}>({
  id: null,
  name: '',
  description: '',
  unit_price: 0,
  category: '',
  status: 'active',
  stock: 0,
  sku: '',
})

const productToDelete = ref<ProductData | null>(null)
const documentToDelete = ref<DocumentData | null>(null)

// 👉 Options
const businessStructures = [
  { title: 'Corporation', value: 'corporation' },
  { title: 'LLC', value: 'llc' },
  { title: 'Partnership', value: 'partnership' },
  { title: 'Sole Proprietorship', value: 'sole_proprietorship' },
]

const industries = [
  { title: 'Food & Beverage', value: 'food_beverage' },
  { title: 'Retail', value: 'retail' },
  { title: 'Services', value: 'services' },
  { title: 'Health & Fitness', value: 'health_fitness' },
  { title: 'Education', value: 'education' },
  { title: 'Technology', value: 'technology' },
  { title: 'Real Estate', value: 'real_estate' },
  { title: 'Automotive', value: 'automotive' },
]

const fundingSources = [
  { title: 'Personal Savings', value: 'personal_savings' },
  { title: 'Bank Loan', value: 'bank_loan' },
  { title: 'Investors', value: 'investors' },
  { title: 'SBA Loan', value: 'sba_loan' },
  { title: 'Other', value: 'other' },
]

const countries = [
  { title: 'United States', value: 'United States' },
  { title: 'Canada', value: 'Canada' },
  { title: 'United Kingdom', value: 'United Kingdom' },
  { title: 'Australia', value: 'Australia' },
  { title: 'Germany', value: 'Germany' },
  { title: 'France', value: 'France' },
  { title: 'Japan', value: 'Japan' },
  { title: 'China', value: 'China' },
  { title: 'India', value: 'India' },
  { title: 'Brazil', value: 'Brazil' },
]

const productCategories = [
  { title: 'Coffee', value: 'Coffee' },
  { title: 'Tea', value: 'Tea' },
  { title: 'Pastries', value: 'Pastries' },
  { title: 'Merchandise', value: 'Merchandise' },
  { title: 'Equipment', value: 'Equipment' },
]

const productStatuses = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
  { title: 'Discontinued', value: 'discontinued' },
]

// 👉 Document functions
const addDocument = () => {
  selectedDocument.value = {
    id: null,
    name: '',
    description: '',
    type: 'other',
    file: null,
    expiry_date: null,
    is_confidential: false,
    status: 'active',
  }
  isAddDocumentModalVisible.value = true
}

const editDocument = (document: any) => {
  selectedDocument.value = { ...document }
  isEditDocumentModalVisible.value = true
}

const saveDocument = async () => {
  try {
    if (!franchiseData.value?.id) {
      showSnackbar('No franchise ID available', 'error')
      return
    }

    const formData = new FormData()
    formData.append('name', selectedDocument.value.name)
    formData.append('description', selectedDocument.value.description || '')
    formData.append('type', selectedDocument.value.type)
    formData.append('status', selectedDocument.value.status)
    formData.append('is_confidential', selectedDocument.value.is_confidential ? '1' : '0')

    if (selectedDocument.value.expiry_date) {
      formData.append('expiry_date', selectedDocument.value.expiry_date)
    }

    if (selectedDocument.value.file) {
      formData.append('file', selectedDocument.value.file)
    }

    let response
    if (selectedDocument.value.id !== null) {
      // Update existing document (metadata only)
      response = await $api(`/v1/franchises/${franchiseData.value.id}/documents/${selectedDocument.value.id}`, {
        method: 'PUT',
        body: formData,
      })
    } else {
      // Add new document
      response = await $api(`/v1/franchises/${franchiseData.value.id}/documents`, {
        method: 'POST',
        body: formData,
      })
    }

    if (response.success) {
      showSnackbar(
        selectedDocument.value.id !== null ? 'Document updated successfully' : 'Document uploaded successfully',
        'success'
      )
      isDocumentModalVisible.value = false
      await loadDocuments() // Reload documents list
    } else {
      showSnackbar(response.message || 'Failed to save document', 'error')
    }
  } catch (error: any) {
    console.error('Failed to save document:', error)
    if (error.status === 422 && error.data?.errors) {
      const errorMessages = Object.values(error.data.errors).flat()
      showSnackbar(`Validation failed: ${errorMessages.join(', ')}`, 'error')
    } else {
      showSnackbar('Failed to save document', 'error')
    }
  }
}

const downloadDocument = async (document: any) => {
  try {
    if (!franchiseData.value?.id) {
      showSnackbar('No franchise ID available', 'error')
      return
    }

    const response = await $api(`/v1/franchises/${franchiseData.value.id}/documents/${document.id}/download`, {
      method: 'GET',
    })

    // Create a blob from the response and trigger download
    const blob = new Blob([response], { type: 'application/octet-stream' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = document.file_name || document.name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    showSnackbar('Document downloaded successfully', 'success')
  } catch (error: any) {
    console.error('Failed to download document:', error)
    showSnackbar('Failed to download document', 'error')
  }
}

const confirmDeleteDocument = (document: any) => {
  documentToDelete.value = document
  isDeleteDocumentDialogVisible.value = true
}

const deleteDocument = async () => {
  try {
    if (!franchiseData.value?.id || !documentToDelete.value) {
      showSnackbar('Invalid request', 'error')
      return
    }

    const response = await $api(`/v1/franchises/${franchiseData.value.id}/documents/${documentToDelete.value.id}`, {
      method: 'DELETE',
    })

    if (response.success) {
      showSnackbar('Document deleted successfully', 'success')
      await loadDocuments() // Reload documents list
    } else {
      showSnackbar(response.message || 'Failed to delete document', 'error')
    }
  } catch (error: any) {
    console.error('Failed to delete document:', error)
    showSnackbar('Failed to delete document', 'error')
  } finally {
    isDeleteDocumentDialogVisible.value = false
    documentToDelete.value = null
  }
}

// 👉 Product functions
const addProduct = () => {
  selectedProduct.value = {
    id: null,
    name: '',
    description: '',
    unit_price: 0,
    category: '',
    status: 'active',
    stock: 0,
    sku: '',
  }
  isAddProductModalVisible.value = true
}

const editProduct = (product: any) => {
  selectedProduct.value = { ...product }
  isEditProductModalVisible.value = true
}

const saveProduct = async () => {
  try {
    if (!franchiseData.value?.id) {
      showSnackbar('No franchise ID available', 'error')
      return
    }

    const formData = new FormData()
    formData.append('name', selectedProduct.value.name)
    formData.append('description', selectedProduct.value.description)
    formData.append('category', selectedProduct.value.category)
    formData.append('unit_price', selectedProduct.value.unit_price.toString())
    formData.append('stock', selectedProduct.value.stock.toString())
    formData.append('status', selectedProduct.value.status)
    formData.append('sku', selectedProduct.value.sku)

    let response
    if (selectedProduct.value.id !== null) {
      // Update existing product
      response = await $api(`/v1/franchises/${franchiseData.value.id}/products/${selectedProduct.value.id}`, {
        method: 'PUT',
        body: formData,
      })
    } else {
      // Add new product
      response = await $api(`/v1/franchises/${franchiseData.value.id}/products`, {
        method: 'POST',
        body: formData,
      })
    }

    if (response.success) {
      showSnackbar(
        selectedProduct.value.id !== null ? 'Product updated successfully' : 'Product created successfully',
        'success'
      )
      isProductModalVisible.value = false
      await loadProducts() // Reload products list
    } else {
      showSnackbar(response.message || 'Failed to save product', 'error')
    }
  } catch (error: any) {
    console.error('Failed to save product:', error)
    if (error.status === 422 && error.data?.errors) {
      const errorMessages = Object.values(error.data.errors).flat()
      showSnackbar(`Validation failed: ${errorMessages.join(', ')}`, 'error')
    } else {
      showSnackbar('Failed to save product', 'error')
    }
  }
}

const confirmDeleteProduct = (product: any) => {
  productToDelete.value = product
  isDeleteProductModalVisible.value = true
}

const deleteProduct = async () => {
  try {
    if (!franchiseData.value?.id || !productToDelete.value) {
      showSnackbar('Invalid request', 'error')
      return
    }

    const response = await $api(`/v1/franchises/${franchiseData.value.id}/products/${productToDelete.value.id}`, {
      method: 'DELETE',
    })

    if (response.success) {
      showSnackbar('Product deleted successfully', 'success')
      await loadProducts() // Reload products list
    } else {
      showSnackbar(response.message || 'Failed to delete product', 'error')
    }
  } catch (error: any) {
    console.error('Failed to delete product:', error)
    showSnackbar('Failed to delete product', 'error')
  } finally {
    isDeleteProductModalVisible.value = false
    productToDelete.value = null
  }
}

// 👉 Computed
const totalProducts = computed(() => productsData.value.length)
const activeProducts = computed(() => productsData.value.filter(p => p.status === 'active').length)
const totalDocuments = computed(() => documentsData.value.length)

// 👉 Headers for products table
const productHeaders = [
  { title: 'Product Name', key: 'name' },
  { title: 'Description', key: 'description' },
  { title: 'Category', key: 'category' },
  { title: 'Unit Price', key: 'unit_price' },
  { title: 'Stock', key: 'stock' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// 👉 Status variant resolver
const resolveStatusVariant = (status: string) => {
  if (status === 'active') return 'success'
  if (status === 'inactive') return 'warning'
  if (status === 'discontinued') return 'error'
  return 'secondary'
}
</script>

<template>
  <section>
    <!-- Loading Overlay -->
    <VOverlay v-model="isLoading" class="align-center justify-center">
      <VProgressCircular color="primary" indeterminate size="64" />
    </VOverlay>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div>
            <h2 class="text-h2 mb-1">
              My Franchise
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage your franchise details, documents, and products
            </p>
          </div>
          <VBtn :color="isEditMode ? 'secondary' : 'primary'" :prepend-icon="isEditMode ? 'tabler-x' : 'tabler-edit'"
            @click="isEditMode = !isEditMode">
            {{ isEditMode ? 'Cancel Edit' : 'Edit Franchise Details' }}
          </VBtn>
        </div>
      </VCol>
    </VRow>

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="primary" variant="tonal">
              <VIcon icon="tabler-building-store" size="26" />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Franchise Status
              </div>
              <h4 class="text-h4">
                Active
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="success" variant="tonal">
              <VIcon icon="tabler-files" size="26" />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Total Documents
              </div>
              <h4 class="text-h4">
                {{ totalDocuments }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="info" variant="tonal">
              <VIcon icon="tabler-package" size="26" />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Active Products
              </div>
              <h4 class="text-h4">
                {{ activeProducts }} / {{ totalProducts }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Tabs -->
    <VTabs v-model="currentTab" class="mb-6">
      <VTab value="overview">
        <VIcon icon="tabler-info-circle" start />
        Overview
      </VTab>
      <VTab value="documents">
        <VIcon icon="tabler-files" start />
        Documents
      </VTab>
      <VTab value="products">
        <VIcon icon="tabler-package" start />
        Products
      </VTab>
    </VTabs>

    <VWindow v-model="currentTab" class="disable-tab-transition">
      <VWindowItem value="overview">
        <VCard>
          <VCardText>
            <VRow>
              <!-- Personal Information -->
              <!-- <VCol cols="12">
                <h4 class="text-h6 mb-4">Personal Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Personal Contact Number</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.personalInfo.contactNumber }}</div>
                          <AppTextField v-else v-model="franchiseData.personalInfo.contactNumber"
                            label="Personal Contact Number" placeholder="Enter your contact number" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Personal Country</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.personalInfo.country }}</div>
                          <AppSelect v-else v-model="franchiseData.personalInfo.country" label="Personal Country"
                            placeholder="Select country" :items="countries" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Personal State</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.personalInfo.state }}</div>
                          <AppTextField v-else v-model="franchiseData.personalInfo.state" label="Personal State"
                            placeholder="Enter state" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Personal City</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.personalInfo.city }}</div>
                          <AppTextField v-else v-model="franchiseData.personalInfo.city" label="Personal City"
                            placeholder="Enter city" />
                        </div>
                      </VCol>
                      <VCol cols="12">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Personal Address</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.personalInfo.address }}</div>
                          <AppTextarea v-else v-model="franchiseData.personalInfo.address" label="Personal Address"
                            placeholder="Enter your full address" rows="2" />
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol> -->

              <!-- Franchise Details -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Franchise Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Franchise Name</div>
                          <div v-if="!isEditMode" class="text-body-1 font-weight-medium">{{
                            franchiseData.franchiseDetails.franchiseName }}</div>
                          <AppTextField v-else v-model="franchiseData.franchiseDetails.franchiseName"
                            label="Franchise Name" placeholder="Enter franchise name" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Website</div>
                          <div v-if="!isEditMode" class="text-body-1">
                            <a :href="franchiseData.franchiseDetails.website" target="_blank" class="text-primary">
                              {{ franchiseData.franchiseDetails.website }}
                            </a>
                          </div>
                          <AppTextField v-else v-model="franchiseData.franchiseDetails.website" label="Website"
                            placeholder="https://example.com" />
                        </div>
                      </VCol>
                      <VCol cols="12">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Franchise Logo</div>
                          <div v-if="!isEditMode" class="text-body-1">
                            <div v-if="franchiseData.franchiseDetails.logo" class="d-flex align-center">
                              <VAvatar size="40" class="me-3">
                                <VImg :src="franchiseData.franchiseDetails.logo" alt="Franchise Logo" />
                              </VAvatar>
                              <span>Logo uploaded</span>
                            </div>
                            <span v-else class="text-disabled">No logo uploaded</span>
                          </div>
                          <VFileInput v-else v-model="logoFile" label="Franchise Logo" accept="image/*"
                            prepend-icon="tabler-upload" :loading="isUploadingLogo" />
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Legal Details -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Legal Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Legal Entity Name</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.legalDetails.legalEntityName }}
                          </div>
                          <AppTextField v-else v-model="franchiseData.legalDetails.legalEntityName"
                            label="Legal Entity Name" placeholder="Enter legal entity name" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Business Structure</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.legalDetails.businessStructure }}
                          </div>
                          <AppSelect v-else v-model="franchiseData.legalDetails.businessStructure"
                            label="Business Structure" placeholder="Select business structure"
                            :items="businessStructures" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Tax ID</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.legalDetails.taxId }}</div>
                          <AppTextField v-else v-model="franchiseData.legalDetails.taxId" label="Tax ID"
                            placeholder="Enter tax ID" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Industry</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.legalDetails.industry }}</div>
                          <AppSelect v-else v-model="franchiseData.legalDetails.industry" label="Industry"
                            placeholder="Select industry" :items="industries" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Funding Amount</div>
                          <div v-if="!isEditMode" class="text-body-1 font-weight-medium text-success">{{
                            franchiseData.legalDetails.fundingAmount }}</div>
                          <AppTextField v-else v-model="franchiseData.legalDetails.fundingAmount" label="Funding Amount"
                            placeholder="Enter funding amount" type="number" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Funding Source</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.legalDetails.fundingSource }}
                          </div>
                          <AppSelect v-else v-model="franchiseData.legalDetails.fundingSource" label="Funding Source"
                            placeholder="Select funding source" :items="fundingSources" />
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Contact Details -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Contact Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Contact Number</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.contactDetails.contactNumber }}
                          </div>
                          <AppTextField v-else v-model="franchiseData.contactDetails.contactNumber"
                            label="Contact Number" placeholder="Enter contact number" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Email</div>
                          <div v-if="!isEditMode" class="text-body-1">
                            <a :href="`mailto:${franchiseData.contactDetails.email}`" class="text-primary">
                              {{ franchiseData.contactDetails.email }}
                            </a>
                          </div>
                          <AppTextField v-else v-model="franchiseData.contactDetails.email" label="Email"
                            placeholder="Enter email address" type="email" />
                        </div>
                      </VCol>
                      <VCol cols="12">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Address</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.contactDetails.address }}</div>
                          <AppTextarea v-else v-model="franchiseData.contactDetails.address" label="Address"
                            placeholder="Enter full address" rows="2" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Country</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.contactDetails.country }}</div>
                          <AppSelect v-else v-model="franchiseData.contactDetails.country" label="Country"
                            placeholder="Select country" :items="countries" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">State</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.contactDetails.state }}</div>
                          <AppTextField v-else v-model="franchiseData.contactDetails.state" label="State"
                            placeholder="Enter state" />
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">City</div>
                          <div v-if="!isEditMode" class="text-body-1">{{ franchiseData.contactDetails.city }}</div>
                          <AppTextField v-else v-model="franchiseData.contactDetails.city" label="City"
                            placeholder="Enter city" />
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>

          <!-- Action Buttons -->
          <VCardActions class="justify-end">
            <template v-if="isEditMode">
              <VBtn variant="outlined" prepend-icon="tabler-x" @click="cancelEdit" class="me-2">
                Cancel
              </VBtn>
              <VBtn color="primary" :loading="isUpdating" prepend-icon="tabler-device-floppy" @click="saveChanges">
                Save Changes
              </VBtn>
            </template>
          </VCardActions>
        </VCard>
      </VWindowItem>

      <!-- Documents Tab -->
      <VWindowItem value="documents">
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-4">
              <h4 class="text-h6">Franchise Documents</h4>
              <VBtn color="primary" prepend-icon="tabler-plus" @click="addDocument">
                Add Document
              </VBtn>
            </div>

            <VRow>
              <template v-for="document in documentsData" :key="document.id">
                <VCol cols="12" md="6" lg="4">
                  <VCard>
                    <VCardText>
                      <div class="d-flex align-center mb-3">
                        <VIcon icon="tabler-file-text" size="24" color="primary" class="me-3" />
                        <div>
                          <h6 class="text-h6">{{ document.name }}</h6>
                          <p class="text-body-2 text-disabled mb-0">{{ document.type }}</p>
                        </div>
                      </div>

                      <p class="text-body-2 mb-3">{{ document.description }}</p>

                      <div class="d-flex align-center justify-space-between mb-3">
                        <span class="text-body-2 text-disabled">{{ document.file_name }}</span>
                        <VChip size="small" color="secondary">{{ document.file_size }}</VChip>
                      </div>

                      <div class="text-body-2 text-disabled mb-3">
                        Uploaded: {{ document.created_at }}
                      </div>
                    </VCardText>

                    <VCardActions>
                      <VBtn size="small" variant="text" color="primary" prepend-icon="tabler-download"
                        @click="downloadDocument(document)">
                        Download
                      </VBtn>
                      <VSpacer />
                      <VBtn size="small" variant="text" color="error" icon="tabler-trash"
                        @click="confirmDeleteDocument(document)" />
                    </VCardActions>
                  </VCard>
                </VCol>
              </template>
            </VRow>
          </VCardText>
        </VCard>
      </VWindowItem>

      <!-- Products Tab -->
      <VWindowItem value="products">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Franchise Products</VCardTitle>
            <template #append>
              <VBtn color="primary" prepend-icon="tabler-plus" @click="addProduct">
                Add Product
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <VDataTable :headers="productHeaders" :items="productsData" item-value="id" class="text-no-wrap"
              show-select>
              <!-- Product Name -->
              <template #item.name="{ item }">
                <div class="d-flex align-center gap-x-4">
                  <VAvatar size="34" color="primary" variant="tonal" class="text-primary">
                    <VIcon icon="tabler-package" />
                  </VAvatar>
                  <div class="d-flex flex-column">
                    <h6 class="text-base font-weight-medium">
                      {{ item.name }}
                    </h6>
                    <div class="text-sm text-medium-emphasis">
                      {{ item.category }}
                    </div>
                  </div>
                </div>
              </template>

              <!-- Description -->
              <template #item.description="{ item }">
                <div class="text-body-1 text-high-emphasis">
                  {{ item.description }}
                </div>
              </template>

              <!-- Unit Price -->
              <template #item.unit_price="{ item }">
                <div class="text-body-1 text-high-emphasis">
                  {{ Number(item.unit_price).toFixed(2) }} SAR
                </div>
              </template>

              <!-- Stock -->
              <template #item.stock="{ item }">
                <div class="text-body-1 text-high-emphasis">
                  {{ item.stock }}
                </div>
              </template>

              <!-- Status -->
              <template #item.status="{ item }">
                <VChip :color="resolveStatusVariant(item.status)" size="small" class="text-capitalize" label>
                  {{ item.status }}
                </VChip>
              </template>

              <!-- Actions -->
              <template #item.actions="{ item }">
                <IconBtn @click="editProduct(item)">
                  <VIcon icon="tabler-edit" />
                </IconBtn>

                <IconBtn @click="confirmDeleteProduct(item)">
                  <VIcon icon="tabler-trash" />
                </IconBtn>
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VWindowItem>
    </VWindow>

    <!-- Add Document Modal -->
    <VDialog v-model="isAddDocumentModalVisible" max-width="600">
      <VCard>
        <VCardTitle>Add New Document</VCardTitle>

        <VCardText>
          <VForm>
            <VRow>
              <VCol cols="12">
                <AppTextField v-model="selectedDocument.name" label="Document Name" placeholder="Enter document name"
                  required />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="selectedDocument.description" label="Description"
                  placeholder="Enter document description" rows="3" />
              </VCol>
              <VCol cols="12">
                <VFileInput v-model="selectedDocument.file" label="Attach File" accept=".pdf,.doc,.docx"
                  prepend-icon="tabler-paperclip" required />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="outlined" @click="isAddDocumentModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="saveDocument">
            Add Document
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Add/Edit Product Modal -->
    <VDialog v-model="isProductModalVisible" max-width="600">
      <VCard>
        <VCardTitle>{{ selectedProduct.id ? 'Edit Product' : 'Add New Product' }}</VCardTitle>

        <VCardText>
          <VForm>
            <VRow>
              <VCol cols="12" md="6">
                <AppTextField v-model="selectedProduct.name" label="Product Name" placeholder="Enter product name"
                  required />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="selectedProduct.sku" label="SKU" placeholder="Enter product SKU"
                  required />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="selectedProduct.category" label="Category" :items="productCategories"
                  placeholder="Select category" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="selectedProduct.description" label="Description"
                  placeholder="Enter product description" rows="3" />
              </VCol>
              <VCol cols="12" md="4">
                <AppTextField v-model.number="selectedProduct.unit_price" label="Unit Price" type="number"
                  placeholder="0.00" prefix="$" step="0.01" />
              </VCol>
              <VCol cols="12" md="4">
                <AppTextField v-model.number="selectedProduct.stock" label="Stock Quantity" type="number"
                  placeholder="0" />
              </VCol>
              <VCol cols="12" md="4">
                <AppSelect v-model="selectedProduct.status" label="Status" :items="productStatuses"
                  placeholder="Select status" />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="outlined" @click="isProductModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="saveProduct">
            {{ selectedProduct.id ? 'Update Product' : 'Add Product' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Product Confirmation -->
    <VDialog v-model="isDeleteProductModalVisible" max-width="400">
      <VCard>
        <VCardTitle>Delete Product</VCardTitle>

        <VCardText>
          Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="outlined" @click="isDeleteProductModalVisible = false">
            Cancel
          </VBtn>
          <VBtn color="error" @click="deleteProduct">
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Document Confirmation -->
    <VDialog v-model="isDeleteDocumentDialogVisible" max-width="500">
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete "{{ documentToDelete?.title }}"? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isDeleteDocumentDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="error" @click="deleteDocument">
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar for notifications -->
    <VSnackbar v-model="snackbar.show" :color="snackbar.color" location="top end" timeout="4000">
      {{ snackbar.message }}

      <template #actions>
        <VBtn color="white" variant="text" @click="snackbar.show = false">
          Close
        </VBtn>
      </template>
    </VSnackbar>
  </section>
</template>
