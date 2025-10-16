/**
 * useFranchisorDashboard Composable Tests
 * Tests for the franchisor dashboard composable functionality
 */

import { useFranchisorDashboard } from '@/composables/useFranchisorDashboard'
import { isReadonly } from 'vue'

// Mock the API utility
jest.mock('@/utils/api', () => ({
  $api: jest.fn()
}))

describe('useFranchisorDashboard', () => {
  beforeEach(() => {
    // Reset all mocks before each test
    jest.clearAllMocks()
  })

  it('should handle undefined leads array in generateRecentActivities', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Call generateRecentActivities without initializing leads
    // This should not throw an error
    expect(() => {
      dashboard.generateRecentActivities()
    }).not.toThrow()
    
    // Should result in empty activities
    expect(dashboard.recentActivities.value).toEqual([])
  })

  it('should handle undefined salesAssociates array in generateRecentActivities', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Call generateRecentActivities without initializing salesAssociates
    // This should not throw an error
    expect(() => {
      dashboard.generateRecentActivities()
    }).not.toThrow()
    
    // Should result in empty activities
    expect(dashboard.recentActivities.value).toEqual([])
  })

  it('should handle empty leads array', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Set leads to empty array
    dashboard.leads.value = []
    
    // Call generateRecentActivities
    expect(() => {
      dashboard.generateRecentActivities()
    }).not.toThrow()
    
    // Should result in empty activities
    expect(dashboard.recentActivities.value).toEqual([])
  })

  it('should handle empty salesAssociates array', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Set salesAssociates to empty array
    dashboard.salesAssociates.value = []
    
    // Call generateRecentActivities
    expect(() => {
      dashboard.generateRecentActivities()
    }).not.toThrow()
    
    // Should result in empty activities
    expect(dashboard.recentActivities.value).toEqual([])
  })

  it('should generate activities with valid leads data', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Set valid leads data
    dashboard.leads.value = [
      {
        id: 1,
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        source: 'website',
        status: 'new',
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'jane@example.com',
        source: 'referral',
        status: 'contacted',
        created_at: new Date().toISOString()
      }
    ]
    
    // Call generateRecentActivities
    dashboard.generateRecentActivities()
    
    // Should generate activities for leads
    expect(dashboard.recentActivities.value.length).toBeGreaterThan(0)
    expect(dashboard.recentActivities.value.some(activity => activity.type === 'lead')).toBe(true)
  })

  it('should generate activities with valid salesAssociates data', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Set valid salesAssociates data
    dashboard.salesAssociates.value = [
      {
        id: 1,
        name: 'Sales Person 1',
        email: 'sp1@example.com',
        status: 'active',
        leads_count: 5,
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        name: 'Sales Person 2',
        email: 'sp2@example.com',
        status: 'active',
        leads_count: 3,
        created_at: new Date().toISOString()
      }
    ]
    
    // Call generateRecentActivities
    dashboard.generateRecentActivities()
    
    // Should generate activities for active sales associates
    expect(dashboard.recentActivities.value.length).toBeGreaterThan(0)
    expect(dashboard.recentActivities.value.some(activity => activity.type === 'team')).toBe(true)
  })

  it('should filter out inactive sales associates', async () => {
    const dashboard = useFranchisorDashboard()
    
    // Set salesAssociates with mixed status
    dashboard.salesAssociates.value = [
      {
        id: 1,
        name: 'Active Person',
        email: 'active@example.com',
        status: 'active',
        leads_count: 5,
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        name: 'Inactive Person',
        email: 'inactive@example.com',
        status: 'inactive',
        leads_count: 0,
        created_at: new Date().toISOString()
      }
    ]
    
    // Call generateRecentActivities
    dashboard.generateRecentActivities()
    
    // Should only generate activity for active associate
    const teamActivities = dashboard.recentActivities.value.filter(activity => activity.type === 'team')
    expect(teamActivities).toHaveLength(1)
    expect(teamActivities[0].title).toContain('Active Person')
  })
})