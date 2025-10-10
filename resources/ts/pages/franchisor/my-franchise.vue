<script setup lang="ts">
// 👉 Router
const router = useRouter()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Mock franchise data
const franchiseData = ref({
  // Overview data
  franchiseDetails: {
    franchiseName: 'Premium Coffee Co.',
    website: 'https://premiumcoffee.com',
    logo: null,
  },
  legalDetails: {
    legalEntityName: 'Premium Coffee Corporation',
    businessStructure: 'Corporation',
    taxId: 'EIN-12-3456789',
    industry: 'Food & Beverage',
    fundingAmount: 'SAR 937,500', // Approximately $250,000 in SAR
    fundingSource: 'Bank Loan',
  },
  contactDetails: {
    contactNumber: '+1 555-123-4567',
    email: 'info@premiumcoffee.com',
    address: '123 Business Ave, Suite 100',
    country: 'United States',
    state: 'California',
    city: 'Los Angeles',
  },
})

// 👉 Documents data
const documentsData = ref([
  {
    id: 1,
    title: 'Franchise Disclosure Document',
    description: 'Official FDD for Premium Coffee Co.',
    fileName: 'FDD_PremiumCoffee_2024.pdf',
    fileSize: '2.4 MB',
    uploadDate: '2024-01-15',
    type: 'FDD',
  },
  {
    id: 2,
    title: 'Franchise Agreement',
    description: 'Legal franchise agreement document',
    fileName: 'Franchise_Agreement_2024.pdf',
    fileSize: '1.8 MB',
    uploadDate: '2024-01-15',
    type: 'Agreement',
  },
  {
    id: 3,
    title: 'Operations Manual',
    description: 'Complete operations and procedures manual',
    fileName: 'Operations_Manual_v2.pdf',
    fileSize: '5.2 MB',
    uploadDate: '2024-01-20',
    type: 'Manual',
  },
])

// 👉 Products data
const productsData = ref([
  {
    id: 1,
    name: 'Premium Espresso',
    description: 'High-quality espresso blend',
    unitPrice: 93.75, // Approximately $24.99 in SAR
    category: 'Coffee',
    status: 'active',
    stock: 150,
  },
  {
    id: 2,
    name: 'Organic House Blend',
    description: 'Organic coffee house blend',
    unitPrice: 75.00, // Approximately $19.99 in SAR
    category: 'Coffee',
    status: 'active',
    stock: 200,
  },
  {
    id: 3,
    name: 'Coffee Mug - Premium',
    description: 'Branded coffee mug with logo',
    unitPrice: 48.75, // Approximately $12.99 in SAR
    category: 'Merchandise',
    status: 'active',
    stock: 75,
  },
])

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
const selectedDocument = ref({
  title: '',
  description: '',
  file: null,
})

const selectedProduct = ref({
  id: null,
  name: '',
  description: '',
  unitPrice: 0,
  category: '',
  status: 'active',
  stock: 0,
})

const productToDelete = ref(null)
const documentToDelete = ref(null)

// 👉 Options
const businessStructures = [
  { title: 'Corporation', value: 'Corporation' },
  { title: 'LLC', value: 'LLC' },
  { title: 'Partnership', value: 'Partnership' },
  { title: 'Sole Proprietorship', value: 'Sole Proprietorship' },
]

const industries = [
  { title: 'Food & Beverage', value: 'Food & Beverage' },
  { title: 'Retail', value: 'Retail' },
  { title: 'Services', value: 'Services' },
  { title: 'Health & Fitness', value: 'Health & Fitness' },
  { title: 'Education', value: 'Education' },
  { title: 'Technology', value: 'Technology' },
]

const fundingSources = [
  { title: 'Personal Savings', value: 'Personal Savings' },
  { title: 'Bank Loan', value: 'Bank Loan' },
  { title: 'Investors', value: 'Investors' },
  { title: 'SBA Loan', value: 'SBA Loan' },
  { title: 'Other', value: 'Other' },
]

const countries = [
  { title: 'United States', value: 'United States' },
  { title: 'Canada', value: 'Canada' },
  { title: 'United Kingdom', value: 'United Kingdom' },
  { title: 'Australia', value: 'Australia' },
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
          <VBtn color="primary" prepend-icon="tabler-edit"
            @click="router.push('/franchisor/franchise-registration-wizard')">
            Edit Franchise Details
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
              <!-- Franchise Details -->
              <VCol cols="12">
                <h4 class="text-h6 mb-4">Franchise Information</h4>
                <VCard variant="outlined">
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Franchise Name</div>
                          <div class="text-body-1 font-weight-medium">{{ franchiseData.franchiseDetails.franchiseName }}
                          </div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Website</div>
                          <div class="text-body-1">
                            <a :href="franchiseData.franchiseDetails.website" target="_blank" class="text-primary">
                              {{ franchiseData.franchiseDetails.website }}
                            </a>
                          </div>
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
                          <div class="text-body-1">{{ franchiseData.legalDetails.legalEntityName }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Business Structure</div>
                          <div class="text-body-1">{{ franchiseData.legalDetails.businessStructure }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Tax ID</div>
                          <div class="text-body-1">{{ franchiseData.legalDetails.taxId }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Industry</div>
                          <div class="text-body-1">{{ franchiseData.legalDetails.industry }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Funding Amount</div>
                          <div class="text-body-1 font-weight-medium text-success">{{
                            franchiseData.legalDetails.fundingAmount }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Funding Source</div>
                          <div class="text-body-1">{{ franchiseData.legalDetails.fundingSource }}</div>
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
                          <div class="text-body-1">{{ franchiseData.contactDetails.contactNumber }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="6">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Email</div>
                          <div class="text-body-1">
                            <a :href="`mailto:${franchiseData.contactDetails.email}`" class="text-primary">
                              {{ franchiseData.contactDetails.email }}
                            </a>
                          </div>
                        </div>
                      </VCol>
                      <VCol cols="12">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Address</div>
                          <div class="text-body-1">{{ franchiseData.contactDetails.address }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">Country</div>
                          <div class="text-body-1">{{ franchiseData.contactDetails.country }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">State</div>
                          <div class="text-body-1">{{ franchiseData.contactDetails.state }}</div>
                        </div>
                      </VCol>
                      <VCol cols="12" md="4">
                        <div class="mb-4">
                          <div class="text-sm text-disabled mb-1">City</div>
                          <div class="text-body-1">{{ franchiseData.contactDetails.city }}</div>
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
  </section>
</template>
