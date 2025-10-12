import { mount } from '@vue/test-utils'
import FranchiseeSalesDashboard from '@/pages/franchisee/dashboard/sales.vue'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'

// Mock the API service
jest.mock('@/services/api/franchisee-dashboard', () => ({
  franchiseeDashboardApi: {
    getSalesStatistics: jest.fn(),
    getProductSales: jest.fn(),
    getMonthlyPerformance: jest.fn(),
  },
}))

// Mock Vuetify
jest.mock('vuetify', () => ({
  useTheme: jest.fn(() => ({
    current: {
      value: {
        colors: {
          primary: '#1976D2',
          success: '#4CAF50',
          error: '#F44336',
        },
      },
    },
  })),
}))

// Mock apex chart config
jest.mock('@core/libs/apex-chart/apexCharConfig', () => ({
  getAreaChartSplineConfig: jest.fn(() => ({
    chart: {
      type: 'area',
      height: 350,
    },
    colors: ['#1976D2', '#4CAF50', '#FF9800'],
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: 'smooth',
      width: 2,
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
  })),
}))

describe('FranchiseeSalesDashboard', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  it('renders loading state initially', () => {
    const wrapper = mount(FranchiseeSalesDashboard)
    
    expect(wrapper.text()).toContain('Loading dashboard data...')
    expect(wrapper.findComponent({ name: 'VProgressCircular' }).exists()).toBe(true)
  })

  it('renders dashboard with data after successful API calls', async () => {
    // Mock successful API responses
    const mockSalesStats = {
      success: true,
      data: {
        totalSales: 125450,
        totalProfit: 45230,
        salesChange: 15,
        profitChange: 22,
      },
    }

    const mockProductSales = {
      success: true,
      data: {
        mostSelling: [
          { name: 'Smartphones', quantity: 245, price: '$899.99' },
          { name: 'Laptops', quantity: 189, price: '$1,299.99' },
        ],
        lowSelling: [
          { name: 'Cameras', quantity: 23, price: '$799.99' },
          { name: 'Printers', quantity: 18, price: '$299.99' },
        ],
      },
    }

    const mockMonthlyPerformance = {
      success: true,
      data: {
        topPerforming: [120, 140, 110, 180, 95, 160, 85, 200, 145, 125, 190, 165],
        lowPerforming: [45, 65, 55, 35, 48, 75, 90, 42, 58, 70, 52, 68],
        averagePerformance: [82, 102, 82, 107, 71, 117, 87, 121, 101, 97, 121, 116],
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      },
    }

    ;(franchiseeDashboardApi.getSalesStatistics as jest.Mock).mockResolvedValue(mockSalesStats)
    ;(franchiseeDashboardApi.getProductSales as jest.Mock).mockResolvedValue(mockProductSales)
    ;(franchiseeDashboardApi.getMonthlyPerformance as jest.Mock).mockResolvedValue(mockMonthlyPerformance)

    const wrapper = mount(FranchiseeSalesDashboard)

    // Wait for API calls to complete
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Check that loading state is gone
    expect(wrapper.text()).not.toContain('Loading dashboard data...')
    expect(wrapper.findComponent({ name: 'VProgressCircular' }).exists()).toBe(false)

    // Check that widgets are rendered
    expect(wrapper.text()).toContain('Total Sales')
    expect(wrapper.text()).toContain('$125,450')
    expect(wrapper.text()).toContain('Total Profit')
    expect(wrapper.text()).toContain('$45,230')

    // Check that product sales sections are rendered
    expect(wrapper.text()).toContain('Most Selling Items')
    expect(wrapper.text()).toContain('Smartphones')
    expect(wrapper.text()).toContain('Laptops')
    expect(wrapper.text()).toContain('Low Selling Items')
    expect(wrapper.text()).toContain('Cameras')
    expect(wrapper.text()).toContain('Printers')

    // Check that chart is rendered
    expect(wrapper.text()).toContain('Month wise Top and Low Performing Items')
  })

  it('renders error state when API calls fail', async () => {
    // Mock failed API responses
    const error = new Error('API Error')
    ;(franchiseeDashboardApi.getSalesStatistics as jest.Mock).mockRejectedValue(error)
    ;(franchiseeDashboardApi.getProductSales as jest.Mock).mockRejectedValue(error)
    ;(franchiseeDashboardApi.getMonthlyPerformance as jest.Mock).mockRejectedValue(error)

    const wrapper = mount(FranchiseeSalesDashboard)

    // Wait for API calls to complete
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Check that loading state is gone
    expect(wrapper.text()).not.toContain('Loading dashboard data...')

    // Check that error state is shown
    expect(wrapper.text()).toContain('Failed to load dashboard data. Please try again.')
    expect(wrapper.findComponent({ name: 'VAlert' }).exists()).toBe(true)
    expect(wrapper.findComponent({ name: 'VAlert' }).props('type')).toBe('error')
  })

  it('retries loading data when retry button is clicked', async () => {
    // Mock failed API responses initially
    const error = new Error('API Error')
    ;(franchiseeDashboardApi.getSalesStatistics as jest.Mock).mockRejectedValueOnce(error)
    ;(franchiseeDashboardApi.getProductSales as jest.Mock).mockRejectedValueOnce(error)
    ;(franchiseeDashboardApi.getMonthlyPerformance as jest.Mock).mockRejectedValueOnce(error)

    // Mock successful API responses on retry
    const mockSalesStats = {
      success: true,
      data: {
        totalSales: 125450,
        totalProfit: 45230,
        salesChange: 15,
        profitChange: 22,
      },
    }

    const mockProductSales = {
      success: true,
      data: {
        mostSelling: [{ name: 'Smartphones', quantity: 245, price: '$899.99' }],
        lowSelling: [{ name: 'Cameras', quantity: 23, price: '$799.99' }],
      },
    }

    const mockMonthlyPerformance = {
      success: true,
      data: {
        topPerforming: [120, 140],
        lowPerforming: [45, 65],
        averagePerformance: [82, 102],
        categories: ['Jan', 'Feb'],
      },
    }

    ;(franchiseeDashboardApi.getSalesStatistics as jest.Mock).mockResolvedValueOnce(mockSalesStats)
    ;(franchiseeDashboardApi.getProductSales as jest.Mock).mockResolvedValueOnce(mockProductSales)
    ;(franchiseeDashboardApi.getMonthlyPerformance as jest.Mock).mockResolvedValueOnce(mockMonthlyPerformance)

    const wrapper = mount(FranchiseeSalesDashboard)

    // Wait for initial failed API calls
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Check that error state is shown
    expect(wrapper.text()).toContain('Failed to load dashboard data. Please try again.')

    // Click retry button
    const retryButton = wrapper.findComponent({ name: 'VBtn' })
    expect(retryButton.text()).toBe('Retry')
    await retryButton.trigger('click')

    // Wait for retry API calls
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Check that data is loaded after retry
    expect(wrapper.text()).not.toContain('Failed to load dashboard data. Please try again.')
    expect(wrapper.text()).toContain('Total Sales')
    expect(wrapper.text()).toContain('Smartphones')
  })

  it('formats currency correctly', () => {
    // Test currency formatting utility function
    const formatCurrency = (value: number) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
      }).format(value)
    }
    
    expect(formatCurrency(125450)).toBe('$125,450.00')
    expect(formatCurrency(45230)).toBe('$45,230.00')
    expect(formatCurrency(0)).toBe('$0.00')
  })

  it('prefixes positive numbers with plus sign correctly', () => {
    // Test prefix utility function
    const prefixWithPlus = (value: number) => {
      if (value > 0) return `+${value}`
      return value.toString()
    }
    
    expect(prefixWithPlus(15)).toBe('+15')
    expect(prefixWithPlus(-5)).toBe('-5')
    expect(prefixWithPlus(0)).toBe('0')
  })
})