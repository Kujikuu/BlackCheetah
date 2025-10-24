import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse } from '@/types/api'

// Franchise types
export interface FranchiseData {
  id: number
  personalInfo: {
    contactNumber: string
    nationality: string
    state: string
    city: string
    address: string
  }
  franchiseDetails: {
    franchiseName: string
    website: string
    logo: string | null
  }
  legalDetails: {
    legalEntityName: string
    businessStructure: string
    taxId: string
  }
  bankDetails: {
    bankName: string
    accountNumber: string
    routingNumber: string
  }
}

export interface UpdateFranchisePayload {
  personalInfo?: Partial<FranchiseData['personalInfo']>
  franchiseDetails?: Partial<FranchiseData['franchiseDetails']>
  legalDetails?: Partial<FranchiseData['legalDetails']>
  bankDetails?: Partial<FranchiseData['bankDetails']>
}

export interface FranchiseWithUnit {
  id: number
  name: string
  email: string
  unit?: {
    id: number
    name: string
    location: string
  }
}

export interface FranchiseFilters {
  search?: string
  page?: number
  per_page?: number
  has_unit?: boolean
}

export interface CreateFranchiseeWithUnitPayload {
  name: string
  email: string
  phone: string
  unit_name: string
  franchise_id: number
  unit_type: string
  address: string
  city: string
  state_province: string
  postal_code: string
  nationality: string
  size_sqft?: string
  monthly_rent?: string
  opening_date?: string
  status?: string
}

export interface UploadLogoResponse {
  logo_url: string
}

export interface Unit {
  id: number
  name: string
  location: string
  type: string
  status: string
  created_at: string
  updated_at: string
}

export interface Franchisee {
  id: number
  name: string
  email: string
  phone?: string
  status: string
  created_at: string
  updated_at: string
}

export interface FranchisorDashboardStats {
  totalFranchisees: number
  totalUnits: number
  totalLeads: number
  activeTasks: number
  currentMonthRevenue: number
  revenueChange: number
  pendingRoyalties: number
}

export interface ProfileCompletionStatus {
  is_complete: boolean
  completion_percentage: number
  completed_fields: number
  total_fields: number
  missing_fields: string[]
}

export interface DashboardOperationsData {
  stats: {
    franchisee: {
      total: number
      total_change: number
      completed: number
      completed_change: number
      in_progress: number
      in_progress_change: number
      due: number
      due_change: number
    }
    sales: {
      total: number
      total_change: number
      completed: number
      completed_change: number
      in_progress: number
      in_progress_change: number
      due: number
      due_change: number
    }
    staff: {
      total: number
      total_change: number
      completed: number
      completed_change: number
      in_progress: number
      in_progress_change: number
      due: number
      due_change: number
    }
  }
  tasks: {
    franchisee: Array<{
      id: number
      task: string
      assigned_to: string
      priority: string
      status: string
      due_date: string
    }>
    sales: Array<{
      id: number
      task: string
      assigned_to: string
      priority: string
      status: string
      due_date: string
    }>
    staff: Array<{
      id: number
      task: string
      assigned_to: string
      priority: string
      status: string
      due_date: string
    }>
  }
}

export interface DashboardLeadsData {
  leads: Array<{
    id: number
    name: string
    email: string
    phone: string
    source: string
    status: string
    createdDate: string
  }>
  stats: {
    total: number
    new: number
    contacted: number
    qualified: number
    converted: number
  }
}

export interface DashboardFinanceData {
  revenue: Array<{
    month: string
    amount: number
    change: number
  }>
  expenses: Array<{
    month: string
    amount: number
    change: number
  }>
  profit: Array<{
    month: string
    amount: number
    change: number
  }>
}

export interface DashboardTimelineData {
  events: Array<{
    id: number
    title: string
    description: string
    date: string
    type: string
    user: string
  }>
}

export interface Document {
  id: number
  name: string
  type: string
  size: number
  url: string
  created_at: string
  updated_at: string
}

export interface Product {
  id: number
  name: string
  description: string
  price: number
  category: string
  image_url?: string
  status: string
  created_at: string
  updated_at: string
}

export interface CreateDocumentPayload {
  name: string
  description?: string
  type: string
  file?: File
  status?: string
  is_confidential?: boolean
  expiry_date?: string
}

export interface UpdateDocumentPayload {
  name?: string
  description?: string
  type?: string
  file?: File
  status?: string
  is_confidential?: boolean
  expiry_date?: string
}

export interface CreateProductPayload {
  name: string
  description: string
  price?: number
  unit_price?: number
  category: string
  image?: File
  status?: string
  stock?: number
  sku?: string
}

export interface UpdateProductPayload {
  name?: string
  description?: string
  price?: number
  unit_price?: number
  category?: string
  image?: File
  status?: string
  stock?: number
  sku?: string
}

export class FranchiseApi {
  private getBaseUrl(): string {
    return getEndpoint('franchise')
  }

  /**
   * Get franchise data for current franchisor
   */
  async getFranchiseData(): Promise<ApiResponse<FranchiseData>> {
    return await $api(`${this.getBaseUrl()}/data`)
  }

  /**
   * Update franchise information
   */
  async updateFranchise(payload: UpdateFranchisePayload): Promise<ApiResponse<FranchiseData>> {
    return await $api(`${this.getBaseUrl()}/update`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Upload franchise logo
   */
  async uploadLogo(file: File): Promise<ApiResponse<UploadLogoResponse>> {
    const formData = new FormData()
    formData.append('logo', file)

    return await $api(`${this.getBaseUrl()}/upload-logo`, {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Get all franchises (for franchisor dashboard)
   */
  async getFranchises(filters?: FranchiseFilters): Promise<ApiResponse<FranchiseData[]>> {
    return await $api(this.getBaseUrl(), {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get franchisees with unit information
   */
  async getFranchiseesWithUnit(filters?: FranchiseFilters): Promise<ApiResponse<FranchiseWithUnit[]>> {
    return await $api(`${this.getBaseUrl()}/franchisees-with-unit`, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Create new franchise registration
   */
  async createFranchise(payload: Partial<FranchiseData>): Promise<ApiResponse<FranchiseData>> {
    return await $api(this.getBaseUrl(), {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Get franchise by ID
   */
  async getFranchiseById(id: number): Promise<ApiResponse<FranchiseData>> {
    return await $api(`${this.getBaseUrl()}/${id}`)
  }

  /**
   * Create franchisee with unit
   */
  async createFranchiseeWithUnit(payload: CreateFranchiseeWithUnitPayload): Promise<ApiResponse<any>> {
    return await $api(`${this.getBaseUrl()}/franchisees-with-unit`, {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Get franchise units
   */
  async getUnits(): Promise<ApiResponse<{ data: Unit[] }>> {
    return await $api('/v1/franchisor/units')
  }

  /**
   * Get units statistics
   */
  async getUnitsStatistics(): Promise<ApiResponse<any>> {
    return await $api('/v1/franchisor/units/statistics')
  }

  /**
   * Get franchisees
   */
  async getFranchisees(): Promise<ApiResponse<{ data: Franchisee[] }>> {
    return await $api('/v1/franchisor/franchisees')
  }

  /**
   * Register new franchise
   */
  async registerFranchise(payload: {
    personalInfo: any
    franchiseDetails: any
    reviewComplete: any
  }): Promise<ApiResponse<{ franchise_id: number }>> {
    return await $api('/v1/franchisor/franchise/register', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Get franchisor dashboard statistics
   */
  async getDashboardStats(): Promise<ApiResponse<FranchisorDashboardStats>> {
    return await $api('/v1/franchisor/dashboard/stats')
  }

  /**
   * Get profile completion status
   */
  async getProfileCompletionStatus(): Promise<ApiResponse<ProfileCompletionStatus>> {
    return await $api('/v1/franchisor/profile/completion-status')
  }

  /**
   * Get dashboard operations data
   */
  async getDashboardOperations(): Promise<ApiResponse<DashboardOperationsData>> {
    return await $api('/v1/franchisor/dashboard/operations')
  }

  /**
   * Get dashboard leads data
   */
  async getDashboardLeads(): Promise<ApiResponse<DashboardLeadsData>> {
    return await $api('/v1/franchisor/dashboard/leads')
  }

  /**
   * Get dashboard finance data
   */
  async getDashboardFinance(): Promise<ApiResponse<DashboardFinanceData>> {
    return await $api('/v1/franchisor/dashboard/finance')
  }

  /**
   * Get dashboard timeline data
   */
  async getDashboardTimeline(): Promise<ApiResponse<DashboardTimelineData>> {
    return await $api('/v1/franchisor/dashboard/timeline')
  }

  /**
   * Get franchise documents
   */
  async getDocuments(franchiseId: number): Promise<ApiResponse<{ data: Document[] }>> {
    return await $api(`/v1/franchises/${franchiseId}/documents`)
  }

  /**
   * Create franchise document
   */
  async createDocument(franchiseId: number, payload: CreateDocumentPayload): Promise<ApiResponse<Document>> {
    const formData = new FormData()
    formData.append('name', payload.name)
    formData.append('type', payload.type)
    
    if (payload.description) formData.append('description', payload.description)
    if (payload.status) formData.append('status', payload.status)
    formData.append('is_confidential', (payload.is_confidential || false) ? '1' : '0')
    if (payload.expiry_date) formData.append('expiry_date', payload.expiry_date)
    if (payload.file) formData.append('file', payload.file)

    return await $api(`/v1/franchises/${franchiseId}/documents`, {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Update franchise document
   */
  async updateDocument(franchiseId: number, documentId: number, payload: UpdateDocumentPayload): Promise<ApiResponse<Document>> {
    const formData = new FormData()
    if (payload.name) formData.append('name', payload.name)
    if (payload.type) formData.append('type', payload.type)
    if (payload.description) formData.append('description', payload.description)
    if (payload.status) formData.append('status', payload.status)
    if (payload.is_confidential !== undefined) formData.append('is_confidential', payload.is_confidential ? '1' : '0')
    if (payload.expiry_date) formData.append('expiry_date', payload.expiry_date)
    if (payload.file) formData.append('file', payload.file)

    return await $api(`/v1/franchises/${franchiseId}/documents/${documentId}`, {
      method: 'PUT',
      body: formData,
    })
  }

  /**
   * Delete franchise document
   */
  async deleteDocument(franchiseId: number, documentId: number): Promise<ApiResponse<void>> {
    return await $api(`/v1/franchises/${franchiseId}/documents/${documentId}`, {
      method: 'DELETE',
    })
  }

  /**
   * Download franchise document
   */
  async downloadDocument(franchiseId: number, documentId: number): Promise<ApiResponse<Blob>> {
    return await $api(`/v1/franchises/${franchiseId}/documents/${documentId}/download`, {
      method: 'GET',
    })
  }

  /**
   * Get franchise products
   */
  async getProducts(franchiseId: number): Promise<ApiResponse<{ data: Product[] }>> {
    return await $api(`/v1/franchises/${franchiseId}/products`)
  }

  /**
   * Create franchise product
   */
  async createProduct(franchiseId: number, payload: CreateProductPayload): Promise<ApiResponse<Product>> {
    const formData = new FormData()
    formData.append('name', payload.name)
    formData.append('description', payload.description)
    formData.append('category', payload.category)
    formData.append('status', payload.status || 'active')
    
    const price = payload.price || payload.unit_price || 0
    formData.append('unit_price', price.toString())
    if (payload.stock !== undefined) formData.append('stock', payload.stock.toString())
    if (payload.sku) formData.append('sku', payload.sku)
    if (payload.image) formData.append('image', payload.image)

    return await $api(`/v1/franchises/${franchiseId}/products`, {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Update franchise product
   */
  async updateProduct(franchiseId: number, productId: number, payload: UpdateProductPayload): Promise<ApiResponse<Product>> {
    const body = payload.image ? (() => {
      const formData = new FormData()
      if (payload.name) formData.append('name', payload.name)
      if (payload.description) formData.append('description', payload.description)
      if (payload.category) formData.append('category', payload.category)
      if (payload.status) formData.append('status', payload.status)
      
      const price = payload.price || payload.unit_price
      if (price !== undefined) formData.append('unit_price', price.toString())
      if (payload.stock !== undefined) formData.append('stock', payload.stock.toString())
      if (payload.sku) formData.append('sku', payload.sku)
      if (payload.image) formData.append('image', payload.image)
      return formData
    })() : payload

    return await $api(`/v1/franchises/${franchiseId}/products/${productId}`, {
      method: 'PUT',
      body,
    })
  }

  /**
   * Delete franchise product
   */
  async deleteProduct(franchiseId: number, productId: number): Promise<ApiResponse<void>> {
    return await $api(`/v1/franchises/${franchiseId}/products/${productId}`, {
      method: 'DELETE',
    })
  }

  /**
   * Upload document during franchise registration
   */
  async uploadRegistrationDocument(franchiseId: number, file: File, name: string, type: string): Promise<ApiResponse> {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('name', name)
    formData.append('description', `${name} uploaded during franchise registration`)
    formData.append('type', type)

    return await $api(`/v1/documents/${franchiseId}`, {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Update unit status
   */
  async updateUnitStatus(unitId: number, status: string): Promise<ApiResponse> {
    return await $api(`/v1/units/${unitId}/status`, {
      method: 'PATCH',
      body: { status },
    })
  }

  /**
   * Assign broker to franchise for marketplace listing
   */
  async assignBroker(franchiseId: number, brokerId: number): Promise<ApiResponse<any>> {
    return await $api(`/v1/franchisor/franchises/${franchiseId}/assign-broker`, {
      method: 'PATCH',
      body: { broker_id: brokerId },
    })
  }

  /**
   * Toggle franchise marketplace listing status
   */
  async toggleMarketplaceListing(franchiseId: number): Promise<ApiResponse<any>> {
    return await $api(`/v1/franchisor/franchises/${franchiseId}/marketplace-toggle`, {
      method: 'PATCH',
    })
  }

  /**
   * Get available brokers for marketplace
   */
  async getBrokers(): Promise<ApiResponse<{ data: any[] }>> {
    return await $api('/v1/franchisor/brokers')
  }
}

export const franchiseApi = new FranchiseApi()
