import { $api } from '@/utils/api'

// Base API URL
const API_URL = '/v1/unit-manager'

// Type definitions
export interface SalesWidgetData {
  title: string
  value: string
  change: number
  desc: string
  icon: string
  iconColor: string
}

export interface ProductSalesItem {
  name: string
  quantity: number
  price?: string // For backward compatibility
  avgPrice?: number // New field from backend
}

export interface MonthlyPerformanceData {
  name: string
  data: number[]
}

export interface SalesStatistics {
  totalSales: number
  totalProfit: number
  salesChange: number
  profitChange: number
}

export interface ProductSalesResponse {
  mostSelling: ProductSalesItem[]
  lowSelling: ProductSalesItem[]
}

export interface MonthlyPerformanceResponse {
  topPerforming: number[]
  lowPerforming: number[]
  averagePerformance: number[]
  categories: string[]
}

// Finance types
export interface FinanceWidgetData {
  icon: string
  color: string
  title: string
  value: string
  change: number
  isHover: boolean
}

export interface FinanceStatistics {
  totalSales: number
  totalExpenses: number
  totalProfit: number
  salesChange: number
  expensesChange: number
  profitChange: number
}

export interface FinancialSummaryData {
  sales: number[]
  expenses: number[]
  profit: number[]
  categories: string[]
}

// Operations types
export interface OperationsWidgetData {
  title: string
  value: string
  change: number
  desc: string
  icon: string
  iconColor: string
}

export interface StoreData {
  totalItems: number
  totalStocks: number
  lowStockItems: number
  outOfStockItems: number
}

export interface TopPerformer {
  id?: string
  name: string
  performance?: number
  score?: number
  department: string
}

export interface StaffData {
  totalStaffs: number
  newHires: number
  monthlyAbsenteeismRate: number
  topPerformers: TopPerformer[]
}

export interface LowStockChartData {
  name: string
  data: number[]
}

export interface ShiftCoverageData {
  name: string
  data: number[]
}

export interface OperationsData {
  storeData: StoreData
  staffData: StaffData
  lowStockChart: LowStockChartData[]
  shiftCoverageChart: ShiftCoverageData[]
}

// Unit Operations types
export interface UnitDetails {
  id: number
  branchName: string
  franchiseeName: string
  email: string
  contactNumber: string
  address: string
  city: string
  state: string
  country: string
  royaltyPercentage: number
  contractStartDate: string
  renewalDate: string
  status: string
}

export interface UnitTask {
  id: number
  title: string
  description: string
  category: string
  assignedTo: string
  startDate: string
  dueDate: string
  priority: 'low' | 'medium' | 'high'
  status: 'pending' | 'in_progress' | 'completed'
}

export interface UnitStaff {
  id: number
  name: string
  jobTitle: string
  email: string
  shiftTime: string
  status: 'working' | 'leave'
}

export interface UnitProduct {
  id: number
  name: string
  description: string
  unitPrice: number
  category: string
  status: 'active' | 'inactive'
  stock: number
}

export interface UnitReview {
  id: number
  customerName: string
  rating: number
  comment: string
  date: string
  sentiment: 'positive' | 'neutral' | 'negative'
}

export interface UnitDocument {
  id: number
  title: string
  description: string
  fileName: string
  fileSize: string
  uploadDate: string
  type: string
  status: 'approved' | 'pending' | 'rejected'
  comment: string
}

// Performance Management types
export interface ProductPerformance {
  topPerformingProductData: number[]
  lowPerformingProductData: number[]
}

export interface RoyaltyData {
  amount: number
  phaseData: number[]
}

export interface TasksOverview {
  total: number
  completed: number
  inProgress: number
  due: number
}

export interface CustomerSatisfaction {
  score: number
  users: number
  positive: number
  neutral: number
  negative: number
}

export interface PerformanceManagementData {
  productPerformance: ProductPerformance
  royalty: RoyaltyData
  tasksOverview: TasksOverview
  customerSatisfaction: CustomerSatisfaction
}

// Financial Overview types
export interface SalesRecord {
  id: string
  product: string
  dateOfSale: string
  unitPrice: number
  quantitySold: number
  sale: number
}

export interface ExpenseRecord {
  id: string
  expenseCategory: string
  dateOfExpense: string
  amount: number
  description: string
}

export interface ProfitRecord {
  id: string
  date: string
  totalSales: number
  totalExpenses: number
  profit: number
}

export interface FinancialOverviewTotals {
  sales: number
  expenses: number
  profit: number
}

export interface FinancialOverviewData {
  sales: SalesRecord[]
  expenses: ExpenseRecord[]
  profit: ProfitRecord[]
  totals: FinancialOverviewTotals
}

export interface AddFinancialDataPayload {
  category: 'sales' | 'expense'
  product?: string
  date: string
  quantitySold?: number
  expenseCategory?: string
  amount?: number
  description?: string
}

export interface DeleteFinancialDataPayload {
  category: 'sales' | 'expense' | 'profit'
  ids: string[]
}

// API response wrapper
interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
}

// API functions
export const franchiseeDashboardApi = {
  // Get sales statistics for widgets
  async getSalesStatistics(): Promise<ApiResponse<SalesStatistics>> {
    return await $api<ApiResponse<SalesStatistics>>(`${API_URL}/dashboard/sales-statistics`)
  },

  // Get product sales data (most selling and low selling items)
  async getProductSales(): Promise<ApiResponse<ProductSalesResponse>> {
    return await $api<ApiResponse<ProductSalesResponse>>(`${API_URL}/dashboard/product-sales`)
  },

  // Get monthly performance data for charts
  async getMonthlyPerformance(): Promise<ApiResponse<MonthlyPerformanceResponse>> {
    return await $api<ApiResponse<MonthlyPerformanceResponse>>(`${API_URL}/dashboard/monthly-performance`)
  },

  // Finance API methods
  // Get finance statistics for widgets
  async getFinanceStatistics(): Promise<ApiResponse<FinanceStatistics>> {
    return await $api<ApiResponse<FinanceStatistics>>(`${API_URL}/dashboard/finance-statistics`)
  },

  // Get financial summary data for charts
  async getFinancialSummary(): Promise<ApiResponse<FinancialSummaryData>> {
    return await $api<ApiResponse<FinancialSummaryData>>(`${API_URL}/dashboard/financial-summary`)
  },

  // Operations API methods
  // Get store data statistics
  async getStoreData(): Promise<ApiResponse<StoreData>> {
    return await $api<ApiResponse<StoreData>>(`${API_URL}/dashboard/store-data`)
  },

  // Get staff data statistics
  async getStaffData(): Promise<ApiResponse<StaffData>> {
    return await $api<ApiResponse<StaffData>>(`${API_URL}/dashboard/staff-data`)
  },

  // Get low stock chart data
  async getLowStockChart(): Promise<ApiResponse<LowStockChartData[]>> {
    return await $api<ApiResponse<LowStockChartData[]>>(`${API_URL}/dashboard/low-stock-chart`)
  },

  // Get shift coverage chart data
  async getShiftCoverageChart(): Promise<ApiResponse<ShiftCoverageData[]>> {
    return await $api<ApiResponse<ShiftCoverageData[]>>(`${API_URL}/dashboard/shift-coverage-chart`)
  },

  // Get all operations data in one call
  async getOperationsData(): Promise<ApiResponse<OperationsData>> {
    return await $api<ApiResponse<OperationsData>>(`${API_URL}/dashboard/operations-data`)
  },

  // Performance Management API method
  async getPerformanceManagement(): Promise<ApiResponse<PerformanceManagementData>> {
    return await $api<ApiResponse<PerformanceManagementData>>(`${API_URL}/dashboard/performance-management`)
  },

  // Financial Overview API methods
  // Get financial overview data (sales, expenses, profit)
  async getFinancialOverview(): Promise<ApiResponse<FinancialOverviewData>> {
    return await $api<ApiResponse<FinancialOverviewData>>(`${API_URL}/dashboard/financial-overview`)
  },

  // Add new financial data (sale or expense)
  async addFinancialData(payload: AddFinancialDataPayload): Promise<ApiResponse<SalesRecord | ExpenseRecord>> {
    return await $api<ApiResponse<SalesRecord | ExpenseRecord>>(`${API_URL}/dashboard/financial-data`, {
      method: 'POST',
      body: payload,
    })
  },

  // Update financial data (sale or expense)
  async updateFinancialData(id: string, payload: AddFinancialDataPayload): Promise<ApiResponse<SalesRecord | ExpenseRecord>> {
    return await $api<ApiResponse<SalesRecord | ExpenseRecord>>(`${API_URL}/dashboard/financial-data/${id}`, {
      method: 'PUT',
      body: payload,
    })
  },

  // Delete financial data
  async deleteFinancialData(payload: DeleteFinancialDataPayload): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/dashboard/financial-data`, {
      method: 'DELETE',
      body: payload,
    })
  },

  // Unit Operations API methods
  // Get unit details and overview
  async getUnitDetails(unitId?: number): Promise<ApiResponse<UnitDetails>> {
    const url = unitId ? `${API_URL}/units/details/${unitId}` : `${API_URL}/units/details`

    return await $api<ApiResponse<UnitDetails>>(url)
  },

  // Get unit tasks
  async getUnitTasks(unitId?: number): Promise<ApiResponse<UnitTask[]>> {
    const url = unitId ? `${API_URL}/units/tasks/${unitId}` : `${API_URL}/units/tasks`

    return await $api<ApiResponse<UnitTask[]>>(url)
  },

  // Get my tasks (all tasks assigned to or created by current user)
  async getMyTasks(): Promise<ApiResponse<UnitTask[]>> {
    return await $api<ApiResponse<UnitTask[]>>(`${API_URL}/my-tasks`)
  },

  // Update my task status
  async updateMyTaskStatus(taskId: number, status: string): Promise<ApiResponse<UnitTask>> {
    return await $api<ApiResponse<UnitTask>>(`${API_URL}/my-tasks/${taskId}/status`, {
      method: 'PATCH',
      body: { status },
    })
  },

  // Get unit staff members
  async getUnitStaff(unitId?: number): Promise<ApiResponse<UnitStaff[]>> {
    const url = unitId ? `${API_URL}/units/staff/${unitId}` : `${API_URL}/units/staff`

    return await $api<ApiResponse<UnitStaff[]>>(url)
  },

  // Get unit products/inventory
  async getUnitProducts(unitId?: number): Promise<ApiResponse<UnitProduct[]>> {
    const url = unitId ? `${API_URL}/units/products/${unitId}` : `${API_URL}/units/products`

    return await $api<ApiResponse<UnitProduct[]>>(url)
  },

  // Get unit reviews
  async getUnitReviews(unitId?: number): Promise<ApiResponse<UnitReview[]>> {
    const url = unitId ? `${API_URL}/units/reviews/${unitId}` : `${API_URL}/units/reviews`

    return await $api<ApiResponse<UnitReview[]>>(url)
  },

  // Get unit documents
  async getUnitDocuments(unitId?: number): Promise<ApiResponse<UnitDocument[]>> {
    const url = unitId ? `${API_URL}/units/documents/${unitId}` : `${API_URL}/units/documents`

    return await $api<ApiResponse<UnitDocument[]>>(url)
  },

  // CRUD Operations for Unit Management

  // Unit Details Operations
  async updateUnitDetails(unitId: number, data: Partial<UnitDetails>): Promise<ApiResponse<UnitDetails>> {
    return await $api<ApiResponse<UnitDetails>>(`${API_URL}/units/details/${unitId}`, {
      method: 'PUT',
      body: data,
    })
  },

  // Task Operations
  async createTask(unitId: number, data: Omit<UnitTask, 'id'>): Promise<ApiResponse<UnitTask>> {
    return await $api<ApiResponse<UnitTask>>(`${API_URL}/units/tasks/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateTask(unitId: number, taskId: number, data: Partial<UnitTask>): Promise<ApiResponse<UnitTask>> {
    return await $api<ApiResponse<UnitTask>>(`${API_URL}/units/tasks/${unitId}/${taskId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteTask(unitId: number, taskId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/tasks/${unitId}/${taskId}`, {
      method: 'DELETE',
    })
  },

  // Staff Operations
  async createStaff(unitId: number, data: Omit<UnitStaff, 'id'>): Promise<ApiResponse<UnitStaff>> {
    return await $api<ApiResponse<UnitStaff>>(`${API_URL}/units/staff/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateStaff(unitId: number, staffId: number, data: Partial<UnitStaff>): Promise<ApiResponse<UnitStaff>> {
    return await $api<ApiResponse<UnitStaff>>(`${API_URL}/units/staff/${unitId}/${staffId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteStaff(unitId: number, staffId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/staff/${unitId}/${staffId}`, {
      method: 'DELETE',
    })
  },

  // Product Operations
  async createProduct(unitId: number, data: Omit<UnitProduct, 'id'>): Promise<ApiResponse<UnitProduct>> {
    return await $api<ApiResponse<UnitProduct>>(`${API_URL}/units/products/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateProduct(unitId: number, productId: number, data: Partial<UnitProduct>): Promise<ApiResponse<UnitProduct>> {
    return await $api<ApiResponse<UnitProduct>>(`${API_URL}/units/products/${unitId}/${productId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteProduct(unitId: number, productId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/products/${unitId}/${productId}`, {
      method: 'DELETE',
    })
  },

  // Review Operations
  async createReview(unitId: number, data: Omit<UnitReview, 'id'>): Promise<ApiResponse<UnitReview>> {
    return await $api<ApiResponse<UnitReview>>(`${API_URL}/units/reviews/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateReview(unitId: number, reviewId: number, data: Partial<UnitReview>): Promise<ApiResponse<UnitReview>> {
    return await $api<ApiResponse<UnitReview>>(`${API_URL}/units/reviews/${unitId}/${reviewId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteReview(unitId: number, reviewId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/reviews/${unitId}/${reviewId}`, {
      method: 'DELETE',
    })
  },

  // Document Operations
  async createDocument(unitId: number, data: Omit<UnitDocument, 'id'> | FormData): Promise<ApiResponse<UnitDocument>> {
    return await $api<ApiResponse<UnitDocument>>(`${API_URL}/units/documents/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateDocument(unitId: number, documentId: number, data: Partial<UnitDocument>): Promise<ApiResponse<UnitDocument>> {
    return await $api<ApiResponse<UnitDocument>>(`${API_URL}/units/documents/${unitId}/${documentId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteDocument(unitId: number, documentId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/documents/${unitId}/${documentId}`, {
      method: 'DELETE',
    })
  },

  async downloadDocument(unitId: number, documentId: number): Promise<ApiResponse<{ url: string }>> {
    return await $api<ApiResponse<{ url: string }>>(`${API_URL}/units/documents/${unitId}/${documentId}/download`, {
      method: 'GET',
    })
  },

  // Inventory Operations (Many-to-Many Product-Unit Relationship)
  async getAvailableFranchiseProducts(unitId: number): Promise<ApiResponse<UnitProduct[]>> {
    return await $api<ApiResponse<UnitProduct[]>>(`${API_URL}/units/available-products/${unitId}`, {
      method: 'GET',
    })
  },

  async addProductToInventory(unitId: number, data: { productId: number; quantity: number; reorderLevel: number }): Promise<ApiResponse<UnitProduct>> {
    return await $api<ApiResponse<UnitProduct>>(`${API_URL}/units/inventory/${unitId}`, {
      method: 'POST',
      body: data,
    })
  },

  async updateInventoryStock(unitId: number, productId: number, data: { quantity: number; reorderLevel?: number }): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/inventory/${unitId}/${productId}`, {
      method: 'PUT',
      body: data,
    })
  },

  async removeProductFromInventory(unitId: number, productId: number): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${API_URL}/units/inventory/${unitId}/${productId}`, {
      method: 'DELETE',
    })
  },
}
