import { $api } from '@/utils/api'

// Base API URL
const API_URL = '/v1/franchisor/financial'

// Type definitions
export interface ChartData {
  categories: string[]
  series: {
    name: string
    data: number[]
  }[]
}

export interface FinancialStatistics {
  totalSales: number
  totalExpenses: number
  totalProfit: number
  totalRoyalties: number
  salesGrowth: number
  profitMargin: number
}

export interface SalesData {
  id: number
  product: string
  unitPrice: number
  quantity: number
  sale: number
  date: string
}

export interface ExpenseData {
  id: number
  expenseCategory: string
  amount: number
  description: string
  date: string
}

export interface ProfitData {
  id: number
  date: string
  totalSales: number
  totalExpenses: number
  profit: number
}

export interface UnitPerformance {
  id: number
  name: string
  location: string
  sales: number
  expenses: number
  royalties: number
  netSales: number
  profit: number
  profitMargin: number
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface ChartFilters {
  period: 'daily' | 'monthly' | 'yearly'
  unit?: string
  startDate?: string
  endDate?: string
}

export interface TableFilters {
  search?: string
  page?: number
  perPage?: number
  sortBy?: string
  sortOrder?: 'asc' | 'desc'
}

// API response wrapper
interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
}

// API functions
export const financialApi = {
  // Chart data
  async getCharts(filters: ChartFilters): Promise<ApiResponse<ChartData>> {
    return await $api<ApiResponse<ChartData>>(`${API_URL}/charts`, {
      params: filters,
    })
  },

  // Financial statistics
  async getStatistics(filters: ChartFilters): Promise<ApiResponse<FinancialStatistics>> {
    return await $api<ApiResponse<FinancialStatistics>>(`${API_URL}/statistics`, {
      params: filters,
    })
  },

  // Sales data
  async getSales(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<SalesData>>> {
    return await $api<ApiResponse<PaginatedResponse<SalesData>>>(`${API_URL}/sales`, {
      params: filters,
    })
  },

  async createSale(data: Partial<SalesData>): Promise<ApiResponse<SalesData>> {
    return await $api<ApiResponse<SalesData>>(`${API_URL}/sales`, {
      method: 'POST',
      body: data,
    })
  },

  async updateSale(id: number, data: Partial<SalesData>): Promise<ApiResponse<SalesData>> {
    return await $api<ApiResponse<SalesData>>(`${API_URL}/sales/${id}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteSale(id: number): Promise<void> {
    await $api(`${API_URL}/sales/${id}`, {
      method: 'DELETE',
    })
  },

  // Expense data
  async getExpenses(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<ExpenseData>>> {
    return await $api<ApiResponse<PaginatedResponse<ExpenseData>>>(`${API_URL}/expenses`, {
      params: filters,
    })
  },

  async createExpense(data: Partial<ExpenseData>): Promise<ApiResponse<ExpenseData>> {
    return await $api<ApiResponse<ExpenseData>>(`${API_URL}/expenses`, {
      method: 'POST',
      body: data,
    })
  },

  async updateExpense(id: number, data: Partial<ExpenseData>): Promise<ApiResponse<ExpenseData>> {
    return await $api<ApiResponse<ExpenseData>>(`${API_URL}/expenses/${id}`, {
      method: 'PUT',
      body: data,
    })
  },

  async deleteExpense(id: number): Promise<void> {
    await $api(`${API_URL}/expenses/${id}`, {
      method: 'DELETE',
    })
  },

  // Profit data
  async getProfit(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<ProfitData>>> {
    return await $api<ApiResponse<PaginatedResponse<ProfitData>>>(`${API_URL}/profit`, {
      params: filters,
    })
  },

  // Unit performance
  async getUnitPerformance(filters: ChartFilters): Promise<ApiResponse<PaginatedResponse<UnitPerformance>>> {
    return await $api<ApiResponse<PaginatedResponse<UnitPerformance>>>(`${API_URL}/unit-performance`, {
      params: filters,
    })
  },

  // Import/Export
  async importData(file: File, category: 'sales' | 'expenses'): Promise<void> {
    const formData = new FormData()

    formData.append('file', file)
    formData.append('category', category)

    await $api(`${API_URL}/import`, {
      method: 'POST',
      body: formData,
    })
  },

  async exportData(category: 'sales' | 'expenses' | 'profit', filters: ChartFilters): Promise<Blob> {
    return await $api<Blob>(`${API_URL}/export`, {
      params: { ...filters, category },
      parseResponse: data => data,
    })
  },
}
