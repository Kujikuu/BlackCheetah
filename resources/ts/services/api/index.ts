/**
 * Centralized API Service Exports
 * Single import point for all API services
 */

export { accountSettingsApi, type AccountSettingsApi } from './account-settings'
export { adminApi, type AdminApi } from './admin'
export { authApi, type AuthApi } from './auth'
export { financialApi, type FinancialApi } from './financial'
export { franchiseApi, type FranchiseApi } from './franchise'
export { franchiseeDashboardApi, type FranchiseeDashboardApi } from './franchisee-dashboard'
export { leadApi, type LeadApi } from './lead'
export { notesApi, type NotesApi } from './notes'
export { notificationsApi, type NotificationsApi } from './notifications'
export { onboardingApi, type OnboardingApi } from './onboarding'
export { royaltyApi, type RoyaltyApi } from './royalty'
export { taskApi, type TaskApi } from './task'
export { technicalRequestApi, type TechnicalRequestApi } from './technical-request'
export { usersApi, type UsersApi } from './users'

// Re-export commonly used types from the API services
export type {
  // Account Settings
  UserProfile,
  UserPreferences,
  NotificationPreferences,
  NotificationChannels,
  UpdateProfilePayload,
  UpdatePasswordPayload,
  UpdateAvatarPayload,
} from './account-settings'

export type {
  // Lead Management
  Lead,
  LeadStatistic,
  LeadsResponse,
  StatisticsResponse,
  LeadFilters,
} from './lead'

export type {
  // Task Management
  Task,
  TaskStatistics,
  TaskListResponse,
  TaskStatisticsResponse,
  TaskResponse,
  TaskFilters,
} from './task'

export type {
  // Financial
  FinancialStatistics,
  SalesData,
  ExpenseData,
  ProfitData,
  UnitPerformance,
  ChartFilters,
  TableFilters,
} from './financial'

export type {
  // Royalty Management
  RoyaltyRecord,
  PaymentData,
  RoyaltyStatistics,
  RoyaltyFilters,
  ExportOptions,
} from './royalty'

export type {
  // Technical Requests
  TechnicalRequest,
  TechnicalRequestFilters,
  TechnicalRequestStatistics,
  CreateTechnicalRequestData,
  UpdateTechnicalRequestData,
} from './technical-request'

export type {
  // Franchisee Dashboard
  SalesWidgetData,
  ProductSalesItem,
  MonthlyPerformanceData,
  SalesStatistics,
  ProductSalesResponse,
  MonthlyPerformanceResponse,
  FinanceWidgetData,
  FinanceStatistics,
  FinancialSummaryData,
  OperationsWidgetData,
  StoreData,
  TopPerformer,
  StaffData,
  LowStockChartData,
  ShiftCoverageData,
  OperationsData,
  UnitDetails,
  UnitTask,
  UnitStaff,
  UnitProduct,
  UnitReview,
  UnitDocument,
  ProductPerformance,
  RoyaltyData,
  TasksOverview,
  CustomerSatisfaction,
  PerformanceManagementData,
  SalesRecord,
  ExpenseRecord,
  ProfitRecord,
  FinancialOverviewTotals,
  FinancialOverviewData,
  AddFinancialDataPayload,
  DeleteFinancialDataPayload,
} from './franchisee-dashboard'

export type {
  // Admin API
  AdminDashboardStats,
  AdminChartData,
  RecentUser,
  UserFilters,
  User,
  CreateUserPayload,
} from './admin'

export type {
  // Auth API
  LoginPayload,
  LoginResponse,
  RefreshTokenResponse,
} from './auth'

export type {
  // Franchise API
  FranchiseData,
  UpdateFranchisePayload,
  FranchiseWithUnit,
  FranchiseFilters,
  CreateFranchiseeWithUnitPayload,
  UploadLogoResponse,
  Unit,
  Franchisee,
  FranchisorDashboardStats,
  ProfileCompletionStatus,
  DashboardOperationsData,
  DashboardLeadsData,
  DashboardFinanceData,
  DashboardTimelineData,
  Document,
  Product,
  CreateDocumentPayload,
  UpdateDocumentPayload,
  CreateProductPayload,
  UpdateProductPayload,
} from './franchise'

export type {
  // Notes API
  Note,
  NoteAttachment,
  CreateNotePayload,
  UpdateNotePayload,
  NotesFilters,
} from './notes'

export type {
  // Notifications API
  BackendNotification,
  Notification,
  NotificationStats,
  NotificationFilters,
  MarkNotificationsPayload,
} from './notifications'

export type {
  // Onboarding API
  OnboardingStatus,
  OnboardingStep,
  OnboardingProgress,
  UpdateOnboardingPayload,
  CompleteOnboardingPayload,
} from './onboarding'

export type {
  // Users API
  Broker,
  UpdateUserPayload,
} from './users'
