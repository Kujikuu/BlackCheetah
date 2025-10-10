<script setup lang="ts">
import { computed, ref } from 'vue'

// Type definitions
interface RoyaltyRecord {
    id: string
    billingPeriod: string
    franchiseeName: string
    storeLocation: string
    dueDate: string
    grossSales: number
    royaltyPercentage: number
    amount: number
    status: 'pending' | 'paid' | 'overdue'
}

interface PaymentData {
    amountPaid: number
    paymentDate: string
    paymentType: string
    attachment: File | null
}

// Reactive data
const selectedPeriod = ref('monthly')
const isExportDialogVisible = ref(false)
const isMarkCompletedModalVisible = ref(false)
const isViewRoyaltyDialogVisible = ref(false)
const selectedRoyalty = ref<RoyaltyRecord | null>(null)
const viewedRoyalty = ref<RoyaltyRecord | null>(null)
const exportFormat = ref('csv')
const exportDataType = ref('all')

// Payment form data
const paymentData = ref<PaymentData>({
    amountPaid: 0,
    paymentDate: '',
    paymentType: '',
    attachment: null,
})

// Mock royalty data
const royaltyRecords = ref<RoyaltyRecord[]>([
    {
        id: '1',
        billingPeriod: 'January 2024',
        franchiseeName: 'Downtown Branch',
        storeLocation: 'New York, NY',
        dueDate: '2024-02-15',
        grossSales: 125000,
        royaltyPercentage: 8,
        amount: 10000,
        status: 'paid',
    },
    {
        id: '2',
        billingPeriod: 'February 2024',
        franchiseeName: 'Mall Location',
        storeLocation: 'Los Angeles, CA',
        dueDate: '2024-03-15',
        grossSales: 110000,
        royaltyPercentage: 8,
        amount: 8800,
        status: 'paid',
    },
    {
        id: '3',
        billingPeriod: 'March 2024',
        franchiseeName: 'Airport Store',
        storeLocation: 'Chicago, IL',
        dueDate: '2024-04-15',
        grossSales: 135000,
        royaltyPercentage: 8,
        amount: 10800,
        status: 'pending',
    },
    {
        id: '4',
        billingPeriod: 'March 2024',
        franchiseeName: 'Suburban Center',
        storeLocation: 'Houston, TX',
        dueDate: '2024-04-15',
        grossSales: 98000,
        royaltyPercentage: 8,
        amount: 7840,
        status: 'pending',
    },
    {
        id: '5',
        billingPeriod: 'February 2024',
        franchiseeName: 'City Plaza',
        storeLocation: 'Phoenix, AZ',
        dueDate: '2024-03-15',
        grossSales: 105000,
        royaltyPercentage: 8,
        amount: 8400,
        status: 'overdue',
    },
    {
        id: '6',
        billingPeriod: 'April 2024',
        franchiseeName: 'Downtown Branch',
        storeLocation: 'New York, NY',
        dueDate: '2024-05-15',
        grossSales: 142000,
        royaltyPercentage: 8,
        amount: 11360,
        status: 'pending',
    },
    {
        id: '7',
        billingPeriod: 'April 2024',
        franchiseeName: 'Mall Location',
        storeLocation: 'Los Angeles, CA',
        dueDate: '2024-05-15',
        grossSales: 118000,
        royaltyPercentage: 8,
        amount: 9440,
        status: 'pending',
    },
    {
        id: '8',
        billingPeriod: 'January 2024',
        franchiseeName: 'City Plaza',
        storeLocation: 'Phoenix, AZ',
        dueDate: '2024-02-15',
        grossSales: 89000,
        royaltyPercentage: 8,
        amount: 7120,
        status: 'overdue',
    },
])

// Computed values for stat cards
const royaltyCollectedTillDate = computed(() => {
    return royaltyRecords.value
        .filter(record => record.status === 'paid')
        .reduce((sum, record) => sum + record.amount, 0)
})

const upcomingRoyalties = computed(() => {
    return royaltyRecords.value
        .filter(record => record.status === 'pending')
        .reduce((sum, record) => sum + record.amount, 0)
})

// Period options
const periodOptions = [
    { title: 'Daily', value: 'daily' },
    { title: 'Monthly', value: 'monthly' },
    { title: 'Yearly', value: 'yearly' },
]

// Export options
const exportFormatOptions = [
    { title: 'CSV', value: 'csv' },
    { title: 'Excel', value: 'excel' },
]

const exportDataTypeOptions = [
    { title: 'All Royalties', value: 'all' },
    { title: 'Paid Only', value: 'paid' },
    { title: 'Pending Only', value: 'pending' },
    { title: 'Overdue Only', value: 'overdue' },
]

// Payment type options
const paymentTypeOptions = [
    { title: 'Bank Transfer', value: 'bank_transfer' },
    { title: 'Credit Card', value: 'credit_card' },
    { title: 'Check', value: 'check' },
    { title: 'Cash', value: 'cash' },
    { title: 'Online Payment', value: 'online_payment' },
]

// Table headers
const tableHeaders = [
    { title: 'Billing Period', key: 'billingPeriod', sortable: true },
    { title: 'Franchisee Name', key: 'franchiseeName', sortable: true },
    { title: 'Store Location', key: 'storeLocation', sortable: true },
    { title: 'Due Date', key: 'dueDate', sortable: true },
    { title: 'Gross Sales (SAR)', key: 'grossSales', sortable: true },
    { title: 'Royalty %', key: 'royaltyPercentage', sortable: true },
    { title: 'Amount (SAR)', key: 'amount', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false },
]

// Functions
const openExportDialog = () => {
    isExportDialogVisible.value = true
}

const performExport = () => {
    let dataToExport = royaltyRecords.value

    // Filter data based on export type
    if (exportDataType.value !== 'all') {
        dataToExport = royaltyRecords.value.filter(record => record.status === exportDataType.value)
    }

    const exportData = {
        period: selectedPeriod.value,
        format: exportFormat.value,
        dataType: exportDataType.value,
        records: dataToExport.length,
        currency: 'SAR',
        exportedAt: new Date().toISOString(),
        data: dataToExport,
    }

    console.log('Exporting royalty data:', exportData)

    // Here you would implement actual export logic
    if (exportFormat.value === 'csv') {
        exportToCSV(dataToExport)
    } else {
        exportToExcel(dataToExport)
    }

    isExportDialogVisible.value = false
}

const exportToCSV = (data: RoyaltyRecord[]) => {
    const headers = ['Billing Period', 'Franchisee Name', 'Store Location', 'Due Date', 'Gross Sales (SAR)', 'Royalty %', 'Amount (SAR)', 'Status']
    const csvContent = [
        headers.join(','),
        ...data.map(record => [
            record.billingPeriod,
            record.franchiseeName,
            record.storeLocation,
            record.dueDate,
            record.grossSales,
            record.royaltyPercentage,
            record.amount,
            record.status,
        ].join(','))
    ].join('\n')

    downloadFile(csvContent, `royalty-data-${selectedPeriod.value}.csv`, 'text/csv')
}

const exportToExcel = (data: RoyaltyRecord[]) => {
    // Placeholder for Excel export
    console.log('Excel export would be implemented here', data)
}

const downloadFile = (content: string, fileName: string, contentType: string) => {
    const blob = new Blob([content], { type: contentType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = fileName
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
}

const viewRoyalty = (royalty: RoyaltyRecord) => {
    viewedRoyalty.value = royalty
    isViewRoyaltyDialogVisible.value = true
}

const markAsCompleted = (royalty: RoyaltyRecord) => {
    selectedRoyalty.value = royalty
    paymentData.value = {
        amountPaid: royalty.amount,
        paymentDate: new Date().toISOString().split('T')[0],
        paymentType: '',
        attachment: null,
    }
    isMarkCompletedModalVisible.value = true
}

const submitPayment = () => {
    if (!selectedRoyalty.value) return

    // Update the royalty status
    const index = royaltyRecords.value.findIndex(r => r.id === selectedRoyalty.value?.id)
    if (index !== -1) {
        royaltyRecords.value[index].status = 'paid'
    }

    console.log('Payment submitted:', {
        royaltyId: selectedRoyalty.value.id,
        paymentData: paymentData.value,
    })

    // Reset form and close modal
    isMarkCompletedModalVisible.value = false
    selectedRoyalty.value = null
    paymentData.value = {
        amountPaid: 0,
        paymentDate: '',
        paymentType: '',
        attachment: null,
    }
}

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        paymentData.value.attachment = target.files[0]
    }
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'paid': return 'success'
        case 'pending': return 'warning'
        case 'overdue': return 'error'
        default: return 'default'
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<template>
    <section>
        <!-- Page Header -->
        <VRow class="mb-6">
            <VCol cols="12">
                <div class="d-flex justify-space-between align-center flex-wrap gap-4">
                    <div>
                        <h4 class="text-h4 mb-1">Royalty Management</h4>
                        <p class="text-body-1 text-medium-emphasis">
                            Track and manage royalty payments from all franchise units
                        </p>
                    </div>

                    <!-- Header Actions -->
                    <div class="d-flex gap-3 align-center flex-wrap">
                        <!-- Period Selector -->
                        <VSelect v-model="selectedPeriod" :items="periodOptions" item-title="title" item-value="value"
                            density="compact" style="min-width: 120px;" variant="outlined" />

                        <!-- Export Button -->
                        <VBtn color="primary" variant="elevated" @click="openExportDialog">
                            <VIcon icon="tabler-download" class="me-2" />
                            Export
                        </VBtn>
                    </div>
                </div>
            </VCol>
        </VRow>

        <!-- Stat Cards -->
        <VRow class="mb-6">
            <!-- Royalty Collected Till Date -->
            <VCol cols="12" md="6">
                <VCard>
                    <VCardText>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <h6 class="text-h6 mb-1">Royalty Collected Till Date</h6>
                                <div class="text-body-2 text-medium-emphasis mb-3">
                                    Total payments received
                                </div>
                                <h4 class="text-h4 text-success">
                                    {{ royaltyCollectedTillDate.toLocaleString() }} SAR
                                </h4>
                            </div>
                            <VAvatar color="success" variant="tonal" size="56">
                                <VIcon icon="tabler-currency-dollar" size="28" />
                            </VAvatar>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Upcoming Royalties -->
            <VCol cols="12" md="6">
                <VCard>
                    <VCardText>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <h6 class="text-h6 mb-1">Upcoming Royalties</h6>
                                <div class="text-body-2 text-medium-emphasis mb-3">
                                    Pending payments due
                                </div>
                                <h4 class="text-h4 text-warning">
                                    {{ upcomingRoyalties.toLocaleString() }} SAR
                                </h4>
                            </div>
                            <VAvatar color="warning" variant="tonal" size="56">
                                <VIcon icon="tabler-clock" size="28" />
                            </VAvatar>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <!-- Royalties Table -->
        <VRow>
            <VCol cols="12">
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle class="text-h6">Royalty Records</VCardTitle>
                        <VCardSubtitle class="text-body-2">
                            Manage royalty payments and track collection status
                        </VCardSubtitle>
                    </VCardItem>

                    <VDivider />

                    <VDataTable :headers="tableHeaders" :items="royaltyRecords" :items-per-page="10"
                        class="text-no-wrap">
                        <!-- Billing Period Column -->
                        <template #item.billingPeriod="{ item }">
                            <div class="font-weight-medium">
                                {{ item.billingPeriod }}
                            </div>
                        </template>

                        <!-- Franchisee Name Column -->
                        <template #item.franchiseeName="{ item }">
                            <div class="font-weight-medium text-primary">
                                {{ item.franchiseeName }}
                            </div>
                        </template>

                        <!-- Store Location Column -->
                        <template #item.storeLocation="{ item }">
                            <div class="text-body-2 text-medium-emphasis">
                                {{ item.storeLocation }}
                            </div>
                        </template>

                        <!-- Due Date Column -->
                        <template #item.dueDate="{ item }">
                            <div class="text-body-2">
                                {{ formatDate(item.dueDate) }}
                            </div>
                        </template>

                        <!-- Gross Sales Column -->
                        <template #item.grossSales="{ item }">
                            <div class="font-weight-medium text-info">
                                {{ item.grossSales.toLocaleString() }}
                            </div>
                        </template>

                        <!-- Royalty Percentage Column -->
                        <template #item.royaltyPercentage="{ item }">
                            <VChip size="small" variant="tonal" color="primary">
                                {{ item.royaltyPercentage }}%
                            </VChip>
                        </template>

                        <!-- Amount Column -->
                        <template #item.amount="{ item }">
                            <div class="font-weight-medium text-success">
                                {{ item.amount.toLocaleString() }}
                            </div>
                        </template>

                        <!-- Status Column -->
                        <template #item.status="{ item }">
                            <VChip :color="getStatusColor(item.status)" size="small" variant="tonal"
                                class="text-capitalize">
                                {{ item.status }}
                            </VChip>
                        </template>

                        <!-- Actions Column -->
                        <template #item.actions="{ item }">
                            <div class="d-flex gap-2">
                                <VBtn icon size="small" color="info" variant="text" @click="viewRoyalty(item)">
                                    <VIcon icon="tabler-eye" size="20" />
                                    <VTooltip activator="parent" location="top">
                                        View Details
                                    </VTooltip>
                                </VBtn>

                                <VBtn v-if="item.status !== 'paid'" icon size="small" color="success" variant="text"
                                    @click="markAsCompleted(item)">
                                    <VIcon icon="tabler-check" size="20" />
                                    <VTooltip activator="parent" location="top">
                                        Mark as Completed
                                    </VTooltip>
                                </VBtn>
                            </div>
                        </template>
                    </VDataTable>
                </VCard>
            </VCol>
        </VRow>

        <!-- Export Dialog -->
        <VDialog v-model="isExportDialogVisible" max-width="500">
            <VCard class="text-center px-6 py-6">
                <VCardItem class="pb-4">
                    <VCardTitle class="text-h6">Export Royalty Data</VCardTitle>
                    <VCardSubtitle>Choose export format and data type</VCardSubtitle>
                </VCardItem>

                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <VSelect v-model="exportDataType" :items="exportDataTypeOptions" item-title="title"
                                item-value="value" label="Data Type" variant="outlined" density="compact" />
                        </VCol>
                        <VCol cols="12">
                            <VSelect v-model="exportFormat" :items="exportFormatOptions" item-title="title"
                                item-value="value" label="Export Format" variant="outlined" density="compact" />
                        </VCol>
                    </VRow>

                    <VAlert type="info" variant="tonal" class="mt-4">
                        <div class="text-body-2">
                            <strong>Current Selection:</strong><br>
                            Period: {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}<br>
                            Data Type: {{exportDataTypeOptions.find(opt => opt.value === exportDataType)?.title}}<br>
                            Format: {{ exportFormat.toUpperCase() }}
                        </div>
                    </VAlert>
                </VCardText>

                <VCardText class="d-flex align-center justify-center gap-4">
                    <VBtn color="error" variant="outlined" @click="isExportDialogVisible = false">
                        Cancel
                    </VBtn>
                    <VBtn color="primary" variant="elevated" @click="performExport">
                        Export Data
                    </VBtn>
                </VCardText>
            </VCard>
        </VDialog>

        <!-- Mark as Completed Modal -->
        <VDialog v-model="isMarkCompletedModalVisible" max-width="600">
            <VCard class="px-6 py-6">
                <VCardItem class="pb-4">
                    <VCardTitle class="text-h6">Mark Royalty as Completed</VCardTitle>
                    <VCardSubtitle v-if="selectedRoyalty">
                        {{ selectedRoyalty.franchiseeName }} - {{ selectedRoyalty.billingPeriod }}
                    </VCardSubtitle>
                </VCardItem>

                <VCardText>
                    <VForm @submit.prevent="submitPayment">
                        <VRow>
                            <VCol cols="12" md="6">
                                <VTextField v-model.number="paymentData.amountPaid" label="Amount Paid (SAR)"
                                    type="number" variant="outlined" density="compact"
                                    :rules="[v => !!v || 'Amount is required']" />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField v-model="paymentData.paymentDate" label="Payment Date" type="date"
                                    variant="outlined" density="compact"
                                    :rules="[v => !!v || 'Payment date is required']" />
                            </VCol>
                            <VCol cols="12">
                                <VSelect v-model="paymentData.paymentType" :items="paymentTypeOptions"
                                    item-title="title" item-value="value" label="Payment Type" variant="outlined"
                                    density="compact" :rules="[v => !!v || 'Payment type is required']" />
                            </VCol>
                            <VCol cols="12">
                                <VFileInput label="Attachment (Optional)" variant="outlined" density="compact"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" @change="handleFileUpload" />
                                <div class="text-caption text-medium-emphasis mt-1">
                                    Supported formats: PDF, JPG, PNG, DOC, DOCX
                                </div>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>

                <VCardText class="d-flex align-center justify-end gap-4">
                    <VBtn color="error" variant="outlined" @click="isMarkCompletedModalVisible = false">
                        Cancel
                    </VBtn>
                    <VBtn color="success" variant="elevated" @click="submitPayment">
                        Mark as Completed
                    </VBtn>
                </VCardText>
            </VCard>
        </VDialog>

        <!-- View Royalty Details Dialog -->
        <VDialog v-model="isViewRoyaltyDialogVisible" max-width="700">
            <VCard v-if="viewedRoyalty" class="px-6 py-6">
                <VCardItem class="pb-4">
                    <VCardTitle class="text-h6 d-flex align-center gap-3">
                        <VIcon icon="tabler-eye" color="primary" />
                        Royalty Details
                    </VCardTitle>
                    <VCardSubtitle>
                        {{ viewedRoyalty.franchiseeName }} - {{ viewedRoyalty.billingPeriod }}
                    </VCardSubtitle>
                </VCardItem>

                <VDivider class="mb-4" />

                <VCardText>
                    <VRow>
                        <!-- Basic Information -->
                        <VCol cols="12">
                            <h6 class="text-h6 mb-4 text-primary">Basic Information</h6>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-1">Billing Period</div>
                                <div class="font-weight-medium">{{ viewedRoyalty.billingPeriod }}</div>
                            </div>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-1">Due Date</div>
                                <div class="font-weight-medium">{{ formatDate(viewedRoyalty.dueDate) }}</div>
                            </div>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-1">Franchisee Name</div>
                                <div class="font-weight-medium text-primary">{{ viewedRoyalty.franchiseeName }}</div>
                            </div>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-1">Store Location</div>
                                <div class="font-weight-medium">{{ viewedRoyalty.storeLocation }}</div>
                            </div>
                        </VCol>

                        <!-- Financial Information -->
                        <VCol cols="12">
                            <VDivider class="my-4" />
                            <h6 class="text-h6 mb-4 text-primary">Financial Details</h6>
                        </VCol>

                        <VCol cols="12" md="4">
                            <VCard variant="tonal" color="info" class="pa-4">
                                <div class="text-center">
                                    <VIcon icon="tabler-chart-line" size="32" class="mb-2" />
                                    <div class="text-body-2 text-medium-emphasis mb-1">Gross Sales</div>
                                    <div class="text-h6 font-weight-bold">
                                        {{ viewedRoyalty.grossSales.toLocaleString() }} SAR
                                    </div>
                                </div>
                            </VCard>
                        </VCol>

                        <VCol cols="12" md="4">
                            <VCard variant="tonal" color="primary" class="pa-4">
                                <div class="text-center">
                                    <VIcon icon="tabler-percentage" size="32" class="mb-2" />
                                    <div class="text-body-2 text-medium-emphasis mb-1">Royalty Rate</div>
                                    <div class="text-h6 font-weight-bold">
                                        {{ viewedRoyalty.royaltyPercentage }}%
                                    </div>
                                </div>
                            </VCard>
                        </VCol>

                        <VCol cols="12" md="4">
                            <VCard variant="tonal" color="success" class="pa-4">
                                <div class="text-center">
                                    <VIcon icon="tabler-coins" size="32" class="mb-2" />
                                    <div class="text-body-2 text-medium-emphasis mb-1">Royalty Amount</div>
                                    <div class="text-h6 font-weight-bold">
                                        {{ viewedRoyalty.amount.toLocaleString() }} SAR
                                    </div>
                                </div>
                            </VCard>
                        </VCol>

                        <!-- Status Information -->
                        <VCol cols="12">
                            <VDivider class="my-4" />
                            <h6 class="text-h6 mb-4 text-primary">Status Information</h6>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-2">Payment Status</div>
                                <VChip :color="getStatusColor(viewedRoyalty.status)" size="large" variant="tonal"
                                    class="text-capitalize">
                                    <VIcon
                                        :icon="viewedRoyalty.status === 'paid' ? 'tabler-check' :
                                            viewedRoyalty.status === 'pending' ? 'tabler-clock' : 'tabler-alert-triangle'"
                                        class="me-2" />
                                    {{ viewedRoyalty.status }}
                                </VChip>
                            </div>
                        </VCol>

                        <VCol cols="12" md="6">
                            <div class="mb-4">
                                <div class="text-body-2 text-medium-emphasis mb-2">Days Until Due</div>
                                <div class="font-weight-medium">
                                    {{ Math.ceil((new Date(viewedRoyalty.dueDate).getTime() - new Date().getTime()) /
                                        (1000 * 60
                                            * 60 * 24)) }} days
                                </div>
                            </div>
                        </VCol>

                        <!-- Calculation Breakdown -->
                        <VCol cols="12">
                            <VDivider class="my-4" />
                            <h6 class="text-h6 mb-4 text-primary">Calculation Breakdown</h6>
                        </VCol>

                        <VCol cols="12">
                            <VTable density="compact">
                                <tbody>
                                    <tr>
                                        <td class="font-weight-medium">Gross Sales:</td>
                                        <td class="text-end">{{ viewedRoyalty.grossSales.toLocaleString() }} SAR</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-medium">Royalty Rate:</td>
                                        <td class="text-end">{{ viewedRoyalty.royaltyPercentage }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-medium">Calculation:</td>
                                        <td class="text-end">{{ viewedRoyalty.grossSales.toLocaleString() }} × {{
                                            viewedRoyalty.royaltyPercentage }}%</td>
                                    </tr>
                                    <tr class="bg-primary-lighten-5">
                                        <td class="font-weight-bold text-primary">Total Royalty Amount:</td>
                                        <td class="text-end font-weight-bold text-primary">{{
                                            viewedRoyalty.amount.toLocaleString() }} SAR</td>
                                    </tr>
                                </tbody>
                            </VTable>
                        </VCol>
                    </VRow>
                </VCardText>

                <VCardText class="d-flex align-center justify-end gap-4 pt-4">
                    <VBtn color="primary" variant="outlined" @click="isViewRoyaltyDialogVisible = false">
                        Close
                    </VBtn>
                    <VBtn v-if="viewedRoyalty.status !== 'paid'" color="success" variant="elevated"
                        @click="markAsCompleted(viewedRoyalty); isViewRoyaltyDialogVisible = false">
                        <VIcon icon="tabler-check" class="me-2" />
                        Mark as Completed
                    </VBtn>
                </VCardText>
            </VCard>
        </VDialog>
    </section>
</template>
