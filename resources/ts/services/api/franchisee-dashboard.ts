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
  price: string
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
}