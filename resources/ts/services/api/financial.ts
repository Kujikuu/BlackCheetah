import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse, PaginatedResponse, ChartData, PeriodFilter } from '@/types/api'

// Type definitions (keeping domain-specific types here)

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

export interface ChartFilters {
  period: PeriodFilter
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

// API Service Class
export class FinancialApi {
  private getBaseUrl(): string {
    return getEndpoint('financial')
  }

  // Chart data
  async getCharts(filters: ChartFilters): Promise<ApiResponse<ChartData>> {
    return await $api<ApiResponse<ChartData>>(`${this.getBaseUrl()}/charts`, {
      params: filters,
    })
  }

  // Financial statistics
  async getStatistics(filters: ChartFilters): Promise<ApiResponse<FinancialStatistics>> {
    return await $api<ApiResponse<FinancialStatistics>>(`${this.getBaseUrl()}/statistics`, {
      params: filters,
    })
  }

  // Sales data
  async getSales(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<SalesData>>> {
    return await $api<ApiResponse<PaginatedResponse<SalesData>>>(`${this.getBaseUrl()}/sales`, {
      params: filters,
    })
  }

  async createSale(data: Partial<SalesData>): Promise<ApiResponse<SalesData>> {
    return await $api<ApiResponse<SalesData>>(`${this.getBaseUrl()}/sales`, {
      method: 'POST',
      body: data,
    })
  }

  async updateSale(id: number, data: Partial<SalesData>): Promise<ApiResponse<SalesData>> {
    return await $api<ApiResponse<SalesData>>(`${this.getBaseUrl()}/sales/${id}`, {
      method: 'PUT',
      body: data,
    })
  }

  async deleteSale(id: number): Promise<void> {
    await $api(`${this.getBaseUrl()}/sales/${id}`, {
      method: 'DELETE',
    })
  }

  // Expense data
  async getExpenses(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<ExpenseData>>> {
    return await $api<ApiResponse<PaginatedResponse<ExpenseData>>>(`${this.getBaseUrl()}/expenses`, {
      params: filters,
    })
  }

  async createExpense(data: Partial<ExpenseData>): Promise<ApiResponse<ExpenseData>> {
    return await $api<ApiResponse<ExpenseData>>(`${this.getBaseUrl()}/expenses`, {
      method: 'POST',
      body: data,
    })
  }

  async updateExpense(id: number, data: Partial<ExpenseData>): Promise<ApiResponse<ExpenseData>> {
    return await $api<ApiResponse<ExpenseData>>(`${this.getBaseUrl()}/expenses/${id}`, {
      method: 'PUT',
      body: data,
    })
  }

  async deleteExpense(id: number): Promise<void> {
    await $api(`${this.getBaseUrl()}/expenses/${id}`, {
      method: 'DELETE',
    })
  }

  // Profit data
  async getProfit(filters: TableFilters & ChartFilters): Promise<ApiResponse<PaginatedResponse<ProfitData>>> {
    return await $api<ApiResponse<PaginatedResponse<ProfitData>>>(`${this.getBaseUrl()}/profit`, {
      params: filters,
    })
  }

  // Unit performance
  async getUnitPerformance(filters: ChartFilters): Promise<ApiResponse<PaginatedResponse<UnitPerformance>>> {
    return await $api<ApiResponse<PaginatedResponse<UnitPerformance>>>(`${this.getBaseUrl()}/unit-performance`, {
      params: filters,
    })
  }

  // Import/Export
  async importData(file: File, category: 'sales' | 'expenses'): Promise<void> {
    const formData = new FormData()

    formData.append('file', file)
    formData.append('category', category)

    await $api(`${this.getBaseUrl()}/import`, {
      method: 'POST',
      body: formData,
    })
  }

  async exportData(category: 'sales' | 'expenses' | 'profit', filters: ChartFilters): Promise<Blob> {
    return await $api<Blob>(`${this.getBaseUrl()}/export`, {
      params: { ...filters, category },
      parseResponse: data => data,
    })
  }
}

// Export class instance
export const financialApi = new FinancialApi()
