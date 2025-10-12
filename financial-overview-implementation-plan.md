# Financial Overview Implementation Plan

## Overview
This document outlines the implementation plan for creating backend API endpoints and updating the frontend for the financial overview page.

## Frontend Requirements Analysis

Based on the `resources/ts/pages/franchisor/financial-overview.vue` file, the following features need to be implemented:

### 1. Chart Data
- Financial charts showing sales, expenses, royalties, and profit
- Period selection: daily, monthly, yearly
- Unit filtering: all units or specific units
- Data needs to be formatted for ApexCharts

### 2. Statistics Cards
- Total Sales for selected period
- Total Expenses for selected period  
- Total Profit for selected period
- Dynamic updates based on period selection

### 3. Data Tables
- Three tabs: Sales, Expenses, Profit
- Pagination and sorting
- Search functionality
- Row selection for bulk operations
- CRUD operations (Add, Edit, Delete)

### 4. Unit Performance Table
- List of all franchise units
- Sales, expenses, royalties, net sales, profit, profit margin
- Sorting and pagination

### 5. Data Management
- Add new financial records (Sales/Expenses)
- Import data from CSV/Excel
- Export data functionality
- Bulk delete operations

## Backend Implementation Plan

### 1. Create FinancialController

**File**: `app/Http/Controllers/Api/FinancialController.php`

**Methods to implement**:

#### Chart Data Endpoints
```php
// GET /api/v1/franchisor/financial/charts
public function charts(Request $request)
{
    // Parameters: period (daily/monthly/yearly), unit_id (optional)
    // Returns: Chart data for sales, expenses, royalties, profit
}
```

#### Statistics Endpoints
```php
// GET /api/v1/franchisor/financial/statistics
public function statistics(Request $request)
{
    // Parameters: period (daily/monthly/yearly), unit_id (optional)
    // Returns: Total sales, expenses, profit for the period
}
```

#### Sales Data Endpoints
```php
// GET /api/v1/franchisor/financial/sales
public function sales(Request $request)
{
    // Parameters: period, unit_id, search, pagination
    // Returns: Paginated sales data
}

// POST /api/v1/franchisor/financial/sales
public function storeSale(Request $request)
{
    // Create new sales record
}

// PUT /api/v1/franchisor/financial/sales/{id}
public function updateSale(Request $request, $id)
{
    // Update sales record
}

// DELETE /api/v1/franchisor/financial/sales/{id}
public function destroySale($id)
{
    // Delete sales record
}
```

#### Expenses Data Endpoints
```php
// GET /api/v1/franchisor/financial/expenses
public function expenses(Request $request)
{
    // Parameters: period, unit_id, search, pagination
    // Returns: Paginated expenses data
}

// POST /api/v1/franchisor/financial/expenses
public function storeExpense(Request $request)
{
    // Create new expense record
}

// PUT /api/v1/franchisor/financial/expenses/{id}
public function updateExpense(Request $request, $id)
{
    // Update expense record
}

// DELETE /api/v1/franchisor/financial/expenses/{id}
public function destroyExpense($id)
{
    // Delete expense record
}
```

#### Profit Data Endpoints
```php
// GET /api/v1/franchisor/financial/profit
public function profit(Request $request)
{
    // Parameters: period, unit_id, search, pagination
    // Returns: Calculated profit data
}
```

#### Unit Performance Endpoints
```php
// GET /api/v1/franchisor/financial/unit-performance
public function unitPerformance(Request $request)
{
    // Parameters: period, search, pagination
    // Returns: Performance metrics for each unit
}
```

#### Import/Export Endpoints
```php
// POST /api/v1/franchisor/financial/import
public function import(Request $request)
{
    // Import financial data from CSV/Excel
}

// GET /api/v1/franchisor/financial/export
public function export(Request $request)
{
    // Export financial data to CSV/Excel
}
```

### 2. Database Models Needed

We already have the necessary models:
- `Transaction` (for sales and expenses)
- `Revenue` (for sales data)
- `Royalty` (for royalty calculations)
- `Unit` (for unit information)

We might need to create additional tables/methods for:
- Daily profit calculations
- Financial data aggregation
- Import/Export tracking

### 3. API Routes

Add to `routes/api.php` inside the franchisor route group:

```php
// Financial Overview Routes
Route::prefix('financial')->group(function () {
    Route::get('charts', [FinancialController::class, 'charts']);
    Route::get('statistics', [FinancialController::class, 'statistics']);
    Route::get('unit-performance', [FinancialController::class, 'unitPerformance']);
    
    // Sales routes
    Route::get('sales', [FinancialController::class, 'sales']);
    Route::post('sales', [FinancialController::class, 'storeSale']);
    Route::put('sales/{id}', [FinancialController::class, 'updateSale']);
    Route::delete('sales/{id}', [FinancialController::class, 'destroySale']);
    
    // Expenses routes
    Route::get('expenses', [FinancialController::class, 'expenses']);
    Route::post('expenses', [FinancialController::class, 'storeExpense']);
    Route::put('expenses/{id}', [FinancialController::class, 'updateExpense']);
    Route::delete('expenses/{id}', [FinancialController::class, 'destroyExpense']);
    
    // Profit routes
    Route::get('profit', [FinancialController::class, 'profit']);
    
    // Import/Export routes
    Route::post('import', [FinancialController::class, 'import']);
    Route::get('export', [FinancialController::class, 'export']);
});
```

## Frontend Implementation Plan

### 1. Create API Service

**File**: `resources/ts/services/api/financial.ts`

```typescript
// API service methods for financial data
export const financialApi = {
  // Chart data
  getCharts: (params: ChartParams) => axios.get('/api/v1/franchisor/financial/charts', { params }),
  
  // Statistics
  getStatistics: (params: StatisticsParams) => axios.get('/api/v1/franchisor/financial/statistics', { params }),
  
  // Sales data
  getSales: (params: SalesParams) => axios.get('/api/v1/franchisor/financial/sales', { params }),
  createSale: (data: SaleData) => axios.post('/api/v1/franchisor/financial/sales', data),
  updateSale: (id: string, data: SaleData) => axios.put(`/api/v1/franchisor/financial/sales/${id}`, data),
  deleteSale: (id: string) => axios.delete(`/api/v1/franchisor/financial/sales/${id}`),
  
  // Expenses data
  getExpenses: (params: ExpensesParams) => axios.get('/api/v1/franchisor/financial/expenses', { params }),
  createExpense: (data: ExpenseData) => axios.post('/api/v1/franchisor/financial/expenses', data),
  updateExpense: (id: string, data: ExpenseData) => axios.put(`/api/v1/franchisor/financial/expenses/${id}`, data),
  deleteExpense: (id: string) => axios.delete(`/api/v1/franchisor/financial/expenses/${id}`),
  
  // Profit data
  getProfit: (params: ProfitParams) => axios.get('/api/v1/franchisor/financial/profit', { params }),
  
  // Unit performance
  getUnitPerformance: (params: UnitPerformanceParams) => axios.get('/api/v1/franchisor/financial/unit-performance', { params }),
  
  // Import/Export
  importData: (formData: FormData) => axios.post('/api/v1/franchisor/financial/import', formData),
  exportData: (params: ExportParams) => axios.get('/api/v1/franchisor/financial/export', { 
    params, 
    responseType: 'blob' 
  }),
}
```

### 2. Update Vue Component

**File**: `resources/ts/pages/franchisor/financial-overview.vue`

Key changes needed:

1. Replace mock data with API calls
2. Add loading states
3. Add error handling
4. Implement real CRUD operations
5. Add pagination for data tables
6. Implement search and filtering

### 3. Component Structure Updates

```typescript
// Replace mock data with reactive refs
const loading = ref(false)
const error = ref(null)

// Chart data
const chartData = ref([])
const statistics = ref({
  totalSales: 0,
  totalExpenses: 0,
  totalProfit: 0
})

// Table data with pagination
const salesData = ref([])
const expensesData = ref([])
const profitData = ref([])
const unitPerformance = ref([])

// Pagination
const pagination = ref({
  page: 1,
  perPage: 10,
  total: 0
})

// API methods
const fetchChartData = async () => {
  loading.value = true
  try {
    const response = await financialApi.getCharts({
      period: selectedPeriod.value,
      unit_id: selectedUnit.value
    })
    chartData.value = response.data.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch chart data'
  } finally {
    loading.value = false
  }
}

// Similar methods for other data fetches
```

## Implementation Steps

1. **Backend Setup**
   - Create FinancialController with all required methods
   - Add API routes
   - Implement data aggregation logic
   - Add validation rules
   - Test endpoints with Postman

2. **Frontend Setup**
   - Create API service file
   - Update Vue component with real API calls
   - Add loading and error states
   - Implement pagination
   - Add search and filtering

3. **Integration Testing**
   - Test all API endpoints
   - Verify data flow from backend to frontend
   - Test CRUD operations
   - Test import/export functionality
   - Test error handling

4. **Performance Optimization**
   - Add caching for expensive calculations
   - Optimize database queries
   - Implement lazy loading for large datasets
   - Add request debouncing for search

## Data Structure Examples

### Chart Data Response
```json
{
  "success": true,
  "data": {
    "categories": ["Jan", "Feb", "Mar", ...],
    "series": [
      { "name": "Sales", "data": [450000, 520000, ...] },
      { "name": "Expenses", "data": [250000, 280000, ...] },
      { "name": "Royalties", "data": [45000, 52000, ...] },
      { "name": "Profit", "data": [155000, 188000, ...] }
    ]
  }
}
```

### Statistics Response
```json
{
  "success": true,
  "data": {
    "totalSales": 6700000,
    "totalExpenses": 3500000,
    "totalProfit": 3200000,
    "period": "monthly",
    "change": {
      "sales": 12.5,
      "expenses": -5.2,
      "profit": 28.7
    }
  }
}
```

### Sales Data Response
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "product": "Product A",
        "unitPrice": 150,
        "quantity": 100,
        "sale": 15000,
        "date": "2024-01-15",
        "unit": {
          "id": 1,
          "name": "Downtown Branch"
        }
      }
    ],
    "pagination": {
      "total": 150,
      "per_page": 10,
      "current_page": 1,
      "last_page": 15
    }
  }
}
```

## Testing Plan

1. **Unit Tests**
   - Test FinancialController methods
   - Test data aggregation logic
   - Test validation rules

2. **Integration Tests**
   - Test API endpoints
   - Test data flow
   - Test error responses

3. **Frontend Tests**
   - Test component rendering
   - Test API integration
   - Test user interactions

4. **End-to-End Tests**
   - Test complete workflows
   - Test CRUD operations
   - Test import/export

## Deployment Considerations

1. **Database Optimization**
   - Add indexes for frequently queried columns
   - Optimize aggregation queries
   - Consider caching for historical data

2. **API Performance**
   - Implement pagination for all list endpoints
   - Add request validation
   - Rate limiting for expensive operations

3. **Security**
   - Validate all input data
   - Sanitize output data
   - Implement proper authorization
   - Log all financial operations

## Future Enhancements

1. **Advanced Analytics**
   - Year-over-year comparisons
   - Trend analysis
   - Forecasting capabilities

2. **Reporting**
   - PDF report generation
   - Automated email reports
   - Custom report builder

3. **Dashboard Customization**
   - User-configurable widgets
   - Custom date ranges
   - Personalized metrics
