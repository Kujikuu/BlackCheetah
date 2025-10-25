/**
 * Shared API Type Definitions
 * Common types used across all API services
 */

// Standard API response wrapper
export interface ApiResponse<T = any> {
  success: boolean
  data: T
  message: string
}

// Validation error types
export interface ValidationError {
  field: string
  message: string
}

export interface ValidationErrors {
  [field: string]: string[]
}

export interface ApiErrorResponse {
  success: false
  message: string
  errors?: ValidationErrors
}

// Paginated response for list endpoints
export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// Common filter interfaces
export interface PaginationFilters {
  page?: number
  per_page?: number
  itemsPerPage?: number
}

export interface SortFilters {
  sort_by?: string
  sortBy?: string
  sort_order?: 'asc' | 'desc'
  orderBy?: 'asc' | 'desc'
}

export interface SearchFilters {
  search?: string
  query?: string
}

// Combined common filters
export interface CommonFilters extends PaginationFilters, SortFilters, SearchFilters {}

// Common status enums
export enum TaskStatus {
  PENDING = 'pending',
  IN_PROGRESS = 'in_progress',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled',
  ON_HOLD = 'on_hold',
}

export enum Priority {
  LOW = 'low',
  MEDIUM = 'medium',
  HIGH = 'high',
  URGENT = 'urgent',
}

export enum LeadStatus {
  NEW = 'new',
  CONTACTED = 'contacted',
  QUALIFIED = 'qualified',
  UNQUALIFIED = 'unqualified',
  CONVERTED = 'converted',
  LOST = 'lost',
}

export enum RoyaltyStatus {
  PENDING = 'pending',
  PAID = 'paid',
  OVERDUE = 'overdue',
}

export enum TechnicalRequestStatus {
  OPEN = 'open',
  IN_PROGRESS = 'in_progress',
  PENDING_INFO = 'pending_info',
  RESOLVED = 'resolved',
  CLOSED = 'closed',
  CANCELLED = 'cancelled',
}

export enum TechnicalRequestCategory {
  HARDWARE = 'hardware',
  SOFTWARE = 'software',
  NETWORK = 'network',
  POS_SYSTEM = 'pos_system',
  WEBSITE = 'website',
  MOBILE_APP = 'mobile_app',
  TRAINING = 'training',
  OTHER = 'other',
}

// Period filter type
export type PeriodFilter = 'all' | 'daily' | 'monthly' | 'yearly'

// Common chart data structure
export interface ChartData {
  categories: string[]
  series: {
    name: string
    data: number[]
  }[]
}

