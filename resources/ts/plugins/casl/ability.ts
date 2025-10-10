import { createMongoAbility } from '@casl/ability'

export type Actions = 'create' | 'read' | 'update' | 'delete' | 'manage'

// Application entities and authentication subjects
export type Subjects = 
  // Core entities
  | 'User'
  | 'Lead'
  | 'Franchise'
  | 'Task'
  | 'TechnicalRequest'
  | 'Transaction'
  | 'Royalty'
  | 'Revenue'
  | 'Unit'
  | 'Note'
  
  // Dashboard access by role
  | 'Dashboard'
  | 'AdminDashboard'
  | 'FranchisorDashboard'
  | 'FranchiseeDashboard'
  | 'SalesDashboard'
  
  // Feature-specific subjects
  | 'Sales'
  | 'Finance'
  | 'Operations'
  | 'Timeline'
  | 'Statistics'
  | 'UserManagement'
  | 'FranchiseManagement'
  | 'LeadManagement'
  | 'TaskManagement'
  | 'TechnicalSupport'
  | 'FinancialReports'
  | 'RoyaltyManagement'
  
  // Special subjects
  | 'all'

export interface Rule { action: Actions; subject: Subjects }

// Helper function to define role-based abilities
export function defineAbilitiesFor(role: string): Rule[] {
  switch (role) {
    case 'admin':
      return [
        { action: 'manage', subject: 'all' }
      ]
    
    case 'franchisor':
      return [
        { action: 'read', subject: 'Dashboard' },
        { action: 'read', subject: 'FranchisorDashboard' },
        { action: 'manage', subject: 'Franchise' },
        { action: 'manage', subject: 'FranchiseManagement' },
        { action: 'manage', subject: 'Lead' },
        { action: 'manage', subject: 'LeadManagement' },
        { action: 'read', subject: 'User' },
        { action: 'read', subject: 'Task' },
        { action: 'read', subject: 'TechnicalRequest' },
        { action: 'read', subject: 'Revenue' },
        { action: 'read', subject: 'Royalty' },
        { action: 'read', subject: 'Transaction' },
        { action: 'read', subject: 'Unit' },
        { action: 'read', subject: 'Statistics' },
        { action: 'read', subject: 'Finance' },
        { action: 'read', subject: 'Operations' },
        { action: 'read', subject: 'Timeline' },
        { action: 'manage', subject: 'Note' }
      ]
    
    case 'franchisee':
      return [
        { action: 'read', subject: 'Dashboard' },
        { action: 'read', subject: 'FranchiseeDashboard' },
        { action: 'read', subject: 'Franchise' },
        { action: 'read', subject: 'Unit' },
        { action: 'read', subject: 'Task' },
        { action: 'update', subject: 'Task' },
        { action: 'read', subject: 'TechnicalRequest' },
        { action: 'create', subject: 'TechnicalRequest' },
        { action: 'read', subject: 'Revenue' },
        { action: 'read', subject: 'Royalty' },
        { action: 'read', subject: 'Transaction' },
        { action: 'read', subject: 'Statistics' },
        { action: 'read', subject: 'Finance' },
        { action: 'read', subject: 'Operations' },
        { action: 'read', subject: 'FinancialReports' },
        { action: 'read', subject: 'RoyaltyManagement' },
        { action: 'manage', subject: 'Note' }
      ]
    
    case 'sales':
      return [
        { action: 'read', subject: 'Dashboard' },
        { action: 'read', subject: 'SalesDashboard' },
        { action: 'manage', subject: 'Sales' },
        { action: 'manage', subject: 'Lead' },
        { action: 'manage', subject: 'LeadManagement' },
        { action: 'read', subject: 'Task' },
        { action: 'update', subject: 'Task' },
        { action: 'read', subject: 'TechnicalRequest' },
        { action: 'create', subject: 'TechnicalRequest' },
        { action: 'update', subject: 'TechnicalRequest' },
        { action: 'read', subject: 'Statistics' },
        { action: 'manage', subject: 'Note' }
      ]
    
    default:
      return []
  }
}

export const ability = createMongoAbility<[Actions, Subjects]>()
