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
const franchiseData = ref({
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
interface DocumentData {
  id: number
  title: string
  description: string
  fileName: string
  fileSize: string
  uploadDate: string
  type: string
}

interface ProductData {
  id: number
  name: string
  description: string
  unitPrice: number
  category: string
  status: string
  stock: number
}

// 👉 Documents data (will be populated from API)
const documentsData = ref<DocumentData[]>([])

// 👉 Products data (will be populated from API)
const productsData = ref<ProductData[]>([])

// 👉 Load franchise data from API
const loadFranchiseData = async () => {
  try {
    isLoading.value = true
    const response = await $api('/v1/franchisor/franchise/data')

    if (response.success && response.data) {
      franchiseData.value = response.data.franchise || franchiseData.value
      documentsData.value = response.data.documents || []
      productsData.value = response.data.products || []
    }
  } catch (error: any) {
    console.error('Failed to load franchise data:', error)
    showSnackbar('Failed to load franchise data', 'error')
  } finally {
    isLoading.value = false
  }
}

// 👉 Update franchise data
const updateFranchiseData = async () => {
  try {
    isUpdating.value = true
    const response = await $api('/v1/franchisor/franchise/update', {
      method: 'PUT',
      body: franchiseData.value,
    })

    if (response.success) {
      showSnackbar('Franchise information updated successfully', 'success')
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
onMounted(() => {
  loadFranchiseData()
})

// 👉 Modals state
const isAddDocumentModalVisible = ref(false)
const isAddProductModalVisible = ref(false)
const isEditProductModalVisible = ref(false)
const isDeleteProductModalVisible = ref(false)
const isDeleteDocumentDialogVisible = ref(false)

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
  unitPrice: number
  category: string
  status: string
  stock: number
}>({
  id: null,
  name: '',
  description: '',
  unitPrice: 0,
  category: '',
  status: 'active',
  stock: 0,
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
    title: '',
    description: '',
    file: null,
  }
  isAddDocumentModalVisible.value = true
}

const saveDocument = () => {
  const newDocument = {
    id: documentsData.value.length + 1,
    title: selectedDocument.value.title,
    description: selectedDocument.value.description,
    fileName: selectedDocument.value.file?.name || 'document.pdf',
    fileSize: '1.2 MB',
    uploadDate: new Date().toISOString().split('T')[0],
    type: 'Custom',
  }

  documentsData.value.push(newDocument)
  isAddDocumentModalVisible.value = false
}

const downloadDocument = (document: any) => {
  console.log('Downloading document:', document.fileName)
  // TODO: Implement download functionality
}

const confirmDeleteDocument = (document: any) => {
  documentToDelete.value = document
  isDeleteDocumentDialogVisible.value = true
}

const deleteDocument = () => {
  if (documentToDelete.value) {
    const index = documentsData.value.findIndex(doc => doc.id === documentToDelete.value.id)
    if (index !== -1) {
      documentsData.value.splice(index, 1)
    }
  }
  isDeleteDocumentDialogVisible.value = false
  documentToDelete.value = null
}

// 👉 Product functions
const addProduct = () => {
  selectedProduct.value = {
    id: null,
    name: '',
    description: '',
    unitPrice: 0,
    category: '',
    status: 'active',
    stock: 0,
  }
  isAddProductModalVisible.value = true
}

const editProduct = (product: any) => {
  selectedProduct.value = { ...product }
  isEditProductModalVisible.value = true
}

const saveProduct = () => {
  if (selectedProduct.value.id === null) {
    // Add new product
    const newProduct = {
      ...selectedProduct.value,
      id: productsData.value.length + 1,
    }
    productsData.value.push(newProduct)
  } else {
    // Update existing product
    const index = productsData.value.findIndex(p => p.id === selectedProduct.value.id)
    if (index !== -1) {
      productsData.value[index] = { ...selectedProduct.value }
    }
  }

  isProductModalVisible.value = false
}

const confirmDeleteProduct = (product: any) => {
  productToDelete.value = product
  isDeleteProductModalVisible.value = true
}

const deleteProduct = () => {
  if (productToDelete.value) {
    const index = productsData.value.findIndex(p => p.id === productToDelete.value.id)
    if (index !== -1) {
      productsData.value.splice(index, 1)
    }
  }
  isDeleteProductModalVisible.value = false
  productToDelete.value = null
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
  { title: 'Unit Price', key: 'unitPrice' },
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
                          <VFileInput v-else v-model="franchiseData.franchiseDetails.logo" label="Franchise Logo"
                            accept="image/*" prepend-icon="tabler-upload" />
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
                          <h6 class="text-h6">{{ document.title }}</h6>
                          <p class="text-body-2 text-disabled mb-0">{{ document.type }}</p>
                        </div>
                      </div>

                      <p class="text-body-2 mb-3">{{ document.description }}</p>

                      <div class="d-flex align-center justify-space-between mb-3">
                        <span class="text-body-2 text-disabled">{{ document.fileName }}</span>
                        <VChip size="small" color="secondary">{{ document.fileSize }}</VChip>
                      </div>

                      <div class="text-body-2 text-disabled mb-3">
                        Uploaded: {{ document.uploadDate }}
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
              <template #item.unitPrice="{ item }">
                <div class="text-body-1 text-high-emphasis">
                  {{ item.unitPrice.toFixed(2) }} SAR
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
                <AppTextField v-model="selectedDocument.title" label="Document Title" placeholder="Enter document title"
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
                <AppSelect v-model="selectedProduct.category" label="Category" :items="productCategories"
                  placeholder="Select category" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="selectedProduct.description" label="Description"
                  placeholder="Enter product description" rows="3" />
              </VCol>
              <VCol cols="12" md="4">
                <AppTextField v-model.number="selectedProduct.unitPrice" label="Unit Price" type="number"
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
