import { $api } from '@/utils/api'

// Base API URL
const API_URL = '/v1/leads'

// Type definitions
export interface Lead {
    id: number
    firstName: string
    lastName: string
    email: string
    phone: string
    company: string
    country: string
    state: string
    city: string
    source: string
    status: 'qualified' | 'unqualified' | 'new' | 'contacted' | 'converted' | 'lost'
    owner: string
    lastContacted: string | null
    scheduledMeeting: string | null
    note?: string
    priority?: string
    estimatedInvestment?: number
    franchiseFeeQuoted?: number
    expectedDecisionDate?: string
    contactAttempts?: number
    interests?: string[]
    documents?: any[]
}

export interface LeadStatistic {
    title: string
    value: string
    change: number
    icon: string
    iconColor: string
}

export interface LeadsResponse {
    success: boolean
    leads: Lead[]
    total: number
    currentPage: number
    perPage: number
    lastPage: number
    message: string
}

export interface StatisticsResponse {
    success: boolean
    data: LeadStatistic[]
    message: string
}

export interface ApiResponse<T = any> {
    success: boolean
    data?: T
    message: string
}

export interface LeadFilters {
    status?: string
    source?: string
    owner?: string
    search?: string
    itemsPerPage?: number
    page?: number
    sortBy?: string
    orderBy?: 'asc' | 'desc'
}

class LeadApi {
    /**
     * Get lead statistics
     */
    async getStatistics(): Promise<StatisticsResponse> {
        return await $api<StatisticsResponse>(`${API_URL}/statistics`)
    }

    /**
     * Get paginated leads list with filters
     */
    async getLeads(filters: LeadFilters = {}): Promise<LeadsResponse> {
        const params = new URLSearchParams()

        if (filters.status) params.append('status', filters.status)
        if (filters.source) params.append('source', filters.source)
        if (filters.owner) params.append('owner', filters.owner)
        if (filters.search) params.append('search', filters.search)
        if (filters.itemsPerPage) params.append('itemsPerPage', filters.itemsPerPage.toString())
        if (filters.page) params.append('page', filters.page.toString())
        if (filters.sortBy) params.append('sortBy', filters.sortBy)
        if (filters.orderBy) params.append('orderBy', filters.orderBy)

        const queryString = params.toString()
        const url = queryString ? `${API_URL}?${queryString}` : API_URL

        return await $api<LeadsResponse>(url)
    }

    /**
     * Get single lead by ID
     */
    async getLead(id: number): Promise<ApiResponse<Lead>> {
        const response = await $api<any>(`${API_URL}/${id}`)

        // Transform snake_case to camelCase
        if (response.success && response.data) {
            response.data = {
                id: response.data.id,
                firstName: response.data.first_name,
                lastName: response.data.last_name,
                email: response.data.email,
                phone: response.data.phone,
                company: response.data.company_name || response.data.company,
                country: response.data.country,
                state: response.data.state || response.data.address,
                city: response.data.city,
                source: response.data.lead_source,
                status: response.data.status,
                owner: response.data.assignedUser?.name || response.data.assigned_to || 'Unassigned',
                lastContacted: response.data.last_contact_date,
                scheduledMeeting: response.data.next_follow_up_date,
                note: response.data.notes,
                priority: response.data.priority,
                estimatedInvestment: response.data.estimated_investment,
                franchiseFeeQuoted: response.data.franchise_fee_quoted,
                expectedDecisionDate: response.data.expected_decision_date,
                contactAttempts: response.data.contact_attempts,
                interests: response.data.interests,
                documents: response.data.documents,
            }
        }

        return response as ApiResponse<Lead>
    }

    /**
     * Create new lead
     */
    async createLead(data: Partial<Lead>): Promise<ApiResponse<Lead>> {
        return await $api<ApiResponse<Lead>>(API_URL, {
            method: 'POST',
            body: data,
        })
    }

    /**
     * Update existing lead
     */
    async updateLead(id: number, data: Partial<Lead>): Promise<ApiResponse<Lead>> {
        // Transform camelCase to snake_case for backend
        const backendData: any = {}

        if (data.firstName !== undefined) backendData.first_name = data.firstName
        if (data.lastName !== undefined) backendData.last_name = data.lastName
        if (data.email !== undefined) backendData.email = data.email
        if (data.phone !== undefined) backendData.phone = data.phone
        if (data.company !== undefined) backendData.company = data.company
        if (data.country !== undefined) backendData.country = data.country
        if (data.state !== undefined) backendData.state = data.state
        if (data.city !== undefined) backendData.city = data.city
        if (data.source !== undefined) backendData.source = data.source
        if (data.status !== undefined) backendData.status = data.status
        if (data.note !== undefined) backendData.notes = data.note
        if (data.priority !== undefined) backendData.priority = data.priority
        if (data.estimatedInvestment !== undefined) backendData.estimated_investment = data.estimatedInvestment
        if (data.franchiseFeeQuoted !== undefined) backendData.franchise_fee_quoted = data.franchiseFeeQuoted
        if (data.expectedDecisionDate !== undefined) backendData.expected_decision_date = data.expectedDecisionDate

        return await $api<ApiResponse<Lead>>(`${API_URL}/${id}`, {
            method: 'PUT',
            body: backendData,
        })
    }

    /**
     * Delete lead
     */
    async deleteLead(id: number): Promise<ApiResponse> {
        return await $api<ApiResponse>(`${API_URL}/${id}`, {
            method: 'DELETE',
        })
    }

    /**
     * Bulk delete leads
     */
    async bulkDelete(ids: number[]): Promise<ApiResponse> {
        return await $api<ApiResponse>(`${API_URL}/bulk-delete`, {
            method: 'POST',
            body: { ids },
        })
    }

    /**
     * Import leads from CSV
     */
    async importCsv(file: File): Promise<ApiResponse> {
        const formData = new FormData()
        formData.append('file', file)

        return await $api<ApiResponse>(`${API_URL}/import-csv`, {
            method: 'POST',
            body: formData,
        })
    }

    /**
     * Export leads to CSV
     */
    exportCsv(ids?: number[]): string {
        const params = new URLSearchParams()
        if (ids && ids.length > 0) {
            ids.forEach(id => params.append('ids[]', id.toString()))
        }

        const queryString = params.toString()
        return queryString
            ? `${API_URL}/export-csv?${queryString}`
            : `${API_URL}/export-csv`
    }

    /**
     * Add note to lead
     */
    async addNote(leadId: number, note: { content: string }): Promise<ApiResponse> {
        return await $api<ApiResponse>(`${API_URL}/${leadId}/notes`, {
            method: 'POST',
            body: note,
        })
    }
}

export const leadApi = new LeadApi()
