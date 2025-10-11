<script setup lang="ts">
import { formatCurrency } from '@/@core/utils/formatters'
import AddFranchiseeModal from '@/components/dialogs/AddFranchiseeModal.vue'

// 👉 Router
const router = useRouter()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Units data and loading state
const unitsData = ref([])
const loading = ref(false)
const error = ref<string | null>(null)

// 👉 Statistics data
const statisticsData = ref({
    totalUnits: 0,
    activeUnits: 0,
    pendingUnits: 0,
    inactiveUnits: 0,
    totalRevenue: 0,
    monthlyRoyalty: 0,
    avgRevenuePerUnit: 0,
    revenueChange: 0,
    royaltyRate: 8.5,
    taskStats: {
        total: 0,
        completed: 0,
        pending: 0,
        completionRate: 0,
    },
})

// 👉 Modal state
const isAddFranchiseeModalVisible = ref(false)

// 👉 Computed stats
const totalUnits = computed(() => statisticsData.value.totalUnits || unitsData.value.length)
const activeUnits = computed(() => statisticsData.value.activeUnits || unitsData.value.filter(unit => unit.status === 'active').length)
const pendingUnits = computed(() => statisticsData.value.pendingUnits || unitsData.value.filter(unit => unit.status === 'pending').length)
const totalRevenue = computed(() => statisticsData.value.monthlyRoyalty)

// 👉 Headers for units table
const unitHeaders = [
    { title: 'Branch Info', key: 'branchInfo' },
    { title: 'Franchisee', key: 'franchisee' },
    { title: 'Location', key: 'location' },
    { title: 'Royalty %', key: 'royaltyPercentage' },
    { title: 'Contract Period', key: 'contractPeriod' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false },
]

// 👉 Status variant resolver
const resolveStatusVariant = (status: string) => {
    if (status === 'active') return 'success'
    if (status === 'pending') return 'warning'
    if (status === 'inactive') return 'error'
    return 'secondary'
}

// 👉 Functions
const loadUnitsData = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/units')

        if (response.success && response.data.data) {
            // Transform API data to match frontend structure
            unitsData.value = response.data.data.map((unit: any) => ({
                id: unit.id,
                branchName: unit.unit_name || 'Unnamed Unit',
                franchiseeName: unit.franchisee?.name || 'Unassigned',
                email: unit.franchisee?.email || unit.email || 'unassigned@example.com',
                contactNumber: unit.franchisee?.phone || unit.phone || 'Not available',
                address: unit.address || 'Address not available',
                city: unit.city || 'Unknown',
                state: unit.state_province || 'Unknown',
                country: unit.country || 'Unknown',
                royaltyPercentage: 8.5, // Default royalty percentage
                contractStartDate: unit.lease_start_date || '2024-01-01',
                renewalDate: unit.lease_end_date || '2027-01-01',
                status: unit.status || 'inactive',
                totalTasks: 0, // Would need to be calculated from tasks relationship
                completedTasks: 0, // Would need to be calculated from tasks relationship
                totalStaff: unit.employee_count || 0,
                totalProducts: 0, // Would need to be calculated from products relationship
                avgRating: 0, // Would need to be calculated from ratings relationship
            }))
        } else {
            unitsData.value = []
        }
    } catch (err: any) {
        console.error('Failed to load units data:', err)
        error.value = err?.data?.message || 'Failed to load units data'
        unitsData.value = []
    } finally {
        loading.value = false
    }
}

const loadStatisticsData = async () => {
    try {
        const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/units/statistics')

        if (response.success && response.data) {
            statisticsData.value = {
                totalUnits: response.data.totalUnits || 0,
                activeUnits: response.data.activeUnits || 0,
                pendingUnits: response.data.pendingUnits || 0,
                inactiveUnits: response.data.inactiveUnits || 0,
                totalRevenue: response.data.totalRevenue || 0,
                monthlyRoyalty: response.data.monthlyRoyalty || 0,
                avgRevenuePerUnit: response.data.avgRevenuePerUnit || 0,
                revenueChange: response.data.revenueChange || 0,
                royaltyRate: response.data.royaltyRate || 8.5,
                taskStats: {
                    total: response.data.taskStats?.total || 0,
                    completed: response.data.taskStats?.completed || 0,
                    pending: response.data.taskStats?.pending || 0,
                    completionRate: response.data.taskStats?.completionRate || 0,
                },
            }
        }
    } catch (err: any) {
        console.error('Failed to load statistics data:', err)
        // Don't set error state for statistics, just use defaults
    }
}

const loadData = async () => {
    await Promise.all([
        loadUnitsData(),
        loadStatisticsData(),
    ])
}

const viewUnit = (unitId: number) => {
    router.push(`/franchisor/units/${unitId}`)
}

const addFranchisee = () => {
    isAddFranchiseeModalVisible.value = true
}



const onFranchiseeAdded = (franchiseeData: any) => {
    // Reload both units and statistics data from API to get the latest
    loadData()
}

// Load data on component mount
onMounted(() => {
    loadData()
})

const viewUnitDetails = (unit: any) => {
    router.push(`/franchisor/units/${unit.id}`)
}
</script>

<template>
    <section>
        <!-- Page Header -->
        <VRow class="mb-6">
            <VCol cols="12">
                <div class="d-flex align-center justify-space-between">
                    <div>
                        <h2 class="text-h2 mb-1">
                            My Units
                        </h2>
                        <p class="text-body-1 text-medium-emphasis">
                            Manage your franchise units and franchisees
                        </p>
                    </div>
                    <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee" :loading="loading">
                        Add Franchisee
                    </VBtn>
                </div>
            </VCol>
        </VRow>

        <!-- Error Alert -->
        <VAlert v-if="error" type="error" variant="tonal" class="mb-6" closable @click:close="error = null">
            {{ error }}
        </VAlert>

        <!-- Tabs -->
        <VTabs v-model="currentTab" class="mb-6">
            <VTab value="overview">
                <VIcon icon="tabler-dashboard" start />
                Overview
            </VTab>
            <VTab value="units">
                <VIcon icon="tabler-building-store" start />
                All Units
            </VTab>
        </VTabs>

        <VWindow v-model="currentTab" class="disable-tab-transition">
            <!-- Overview Tab -->
            <VWindowItem value="overview">
                <!-- Stats Cards -->
                <VRow class="mb-6">
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="primary" variant="tonal">
                                    <VIcon icon="tabler-building-store" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">
                                        Total Units
                                    </div>
                                    <h4 class="text-h4">
                                        <VProgressCircular v-if="loading" indeterminate size="20" />
                                        <span v-else>{{ totalUnits }}</span>
                                    </h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="success" variant="tonal">
                                    <VIcon icon="tabler-check" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">
                                        Active Units
                                    </div>
                                    <h4 class="text-h4">
                                        <VProgressCircular v-if="loading" indeterminate size="20" />
                                        <span v-else>{{ activeUnits }}</span>
                                    </h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="warning" variant="tonal">
                                    <VIcon icon="tabler-clock" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">
                                        Pending Units
                                    </div>
                                    <h4 class="text-h4">
                                        <VProgressCircular v-if="loading" indeterminate size="20" />
                                        <span v-else>{{ pendingUnits }}</span>
                                    </h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="3">
                        <VCard>
                            <VCardText class="d-flex align-center">
                                <VAvatar size="44" rounded color="info" variant="tonal">
                                    <VIcon icon="tabler-currency-dollar" size="26" />
                                </VAvatar>
                                <div class="ms-4">
                                    <div class="text-body-2 text-disabled">
                                        Monthly Royalty
                                    </div>
                                    <h4 class="text-h4">
                                        <VProgressCircular v-if="loading" indeterminate size="20" />
                                        <span v-else>{{ formatCurrency(totalRevenue) }}</span>
                                    </h4>
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>

                <!-- Recent Units -->
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle>Recent Units</VCardTitle>
                        <template #append>
                            <VBtn variant="text" color="primary" @click="currentTab = 'units'">
                                View All
                            </VBtn>
                        </template>
                    </VCardItem>
                    <VCardText>
                        <VRow v-if="loading">
                            <VCol cols="12" md="4" v-for="i in 3" :key="i">
                                <VCard variant="outlined">
                                    <VCardText>
                                        <VSkeletonLoader type="text" class="mb-3" />
                                        <VSkeletonLoader type="text" class="mb-2" />
                                        <VSkeletonLoader type="text" class="mb-2" />
                                        <VSkeletonLoader type="text" class="mb-3" />
                                        <VSkeletonLoader type="button" />
                                    </VCardText>
                                </VCard>
                            </VCol>
                        </VRow>
                        <VRow v-else-if="unitsData.length === 0">
                            <VCol cols="12">
                                <div class="text-center py-8">
                                    <VIcon icon="tabler-building-store" size="48" class="text-disabled mb-4" />
                                    <h4 class="text-h4 mb-2">No Units Found</h4>
                                    <p class="text-body-1 text-medium-emphasis mb-4">
                                        You haven't added any franchise units yet. Click "Add Franchisee" to get
                                        started.
                                    </p>
                                    <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee">
                                        Add Your First Unit
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                        <VRow v-else>
                            <template v-for="unit in unitsData.slice(0, 3)" :key="unit.id">
                                <VCol cols="12" md="4">
                                    <VCard variant="outlined">
                                        <VCardText>
                                            <div class="d-flex align-center justify-space-between mb-3">
                                                <h6 class="text-h6">{{ unit.branchName }}</h6>
                                                <VChip :color="resolveStatusVariant(unit.status)" size="small" label
                                                    class="text-capitalize">
                                                    {{ unit.status }}
                                                </VChip>
                                            </div>

                                            <div class="mb-2">
                                                <div class="text-body-2 text-disabled">Franchisee</div>
                                                <div class="text-body-1">{{ unit.franchiseeName }}</div>
                                            </div>

                                            <div class="mb-2">
                                                <div class="text-body-2 text-disabled">Location</div>
                                                <div class="text-body-2">{{ unit.city }}, {{ unit.state }}</div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="text-body-2 text-disabled">Royalty</div>
                                                <div class="text-body-1 font-weight-medium">{{ unit.royaltyPercentage
                                                    }}%</div>
                                            </div>
                                        </VCardText>

                                        <VCardActions>
                                            <VBtn variant="text" color="primary" @click="viewUnit(unit.id)">
                                                View Details
                                            </VBtn>
                                        </VCardActions>
                                    </VCard>
                                </VCol>
                            </template>
                        </VRow>
                    </VCardText>
                </VCard>
            </VWindowItem>

            <!-- All Units Tab -->
            <VWindowItem value="units">
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle>All Franchise Units</VCardTitle>
                        <template #append>
                            <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee" :loading="loading">
                                Add Franchisee
                            </VBtn>
                        </template>
                    </VCardItem>

                    <VDivider />

                    <!-- Loading State -->
                    <VCardText v-if="loading" class="py-8">
                        <div class="text-center">
                            <VProgressCircular indeterminate size="48" class="mb-4" />
                            <h4 class="text-h4 mb-2">Loading Units...</h4>
                            <p class="text-body-1 text-medium-emphasis">
                                Please wait while we fetch your franchise units.
                            </p>
                        </div>
                    </VCardText>

                    <!-- Empty State -->
                    <VCardText v-else-if="unitsData.length === 0" class="py-8">
                        <div class="text-center">
                            <VIcon icon="tabler-building-store" size="64" class="text-disabled mb-4" />
                            <h4 class="text-h4 mb-2">No Units Found</h4>
                            <p class="text-body-1 text-medium-emphasis mb-6">
                                You haven't added any franchise units yet. Start by adding your first franchise unit.
                            </p>
                            <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee" size="large">
                                Add Your First Unit
                            </VBtn>
                        </div>
                    </VCardText>

                    <!-- Units Table -->
                    <VDataTable v-else :items="unitsData" :headers="unitHeaders" class="text-no-wrap" item-value="id"
                        @click:row="(event: any, { item }: any) => viewUnitDetails(item)" style="cursor: pointer;">
                        <!-- Branch Info -->
                        <template #item.branchInfo="{ item }">
                            <div class="d-flex align-center gap-x-3">
                                <VAvatar size="34" color="primary" variant="tonal">
                                    <VIcon icon="tabler-building-store" size="20" />
                                </VAvatar>
                                <div>
                                    <h6 class="text-base font-weight-medium">
                                        {{ item.branchName }}
                                    </h6>
                                    <div class="text-body-2 text-disabled">
                                        {{ item.email }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Franchisee -->
                        <template #item.franchisee="{ item }">
                            <div>
                                <div class="text-body-1 font-weight-medium">{{ item.franchiseeName }}</div>
                                <div class="text-body-2 text-disabled">{{ item.contactNumber }}</div>
                            </div>
                        </template>

                        <!-- Location -->
                        <template #item.location="{ item }">
                            <div>
                                <div class="text-body-1">{{ item.city }}, {{ item.state }}</div>
                                <div class="text-body-2 text-disabled">{{ item.country }}</div>
                            </div>
                        </template>

                        <!-- Royalty Percentage -->
                        <template #item.royaltyPercentage="{ item }">
                            <div class="text-body-1 font-weight-medium">
                                {{ item.royaltyPercentage }}%
                            </div>
                        </template>

                        <!-- Contract Period -->
                        <template #item.contractPeriod="{ item }">
                            <div>
                                <div class="text-body-2 text-disabled">Start: {{ item.contractStartDate }}</div>
                                <div class="text-body-2 text-disabled">Renewal: {{ item.renewalDate }}</div>
                            </div>
                        </template>

                        <!-- Status -->
                        <template #item.status="{ item }">
                            <VChip :color="resolveStatusVariant(item.status)" size="small" label
                                class="text-capitalize">
                                {{ item.status }}
                            </VChip>
                        </template>

                        <!-- Actions -->
                        <template #item.actions="{ item }">
                            <VBtn icon variant="text" color="medium-emphasis" size="small" @click="viewUnit(item.id)">
                                <VIcon icon="tabler-eye" />
                                <VTooltip activator="parent">
                                    View Unit Details
                                </VTooltip>
                            </VBtn>

                        </template>
                    </VDataTable>
                </VCard>
            </VWindowItem>
        </VWindow>

        <!-- Add Franchisee Modal -->
        <AddFranchiseeModal v-model:is-dialog-visible="isAddFranchiseeModalVisible"
            @franchisee-added="onFranchiseeAdded" />
    </section>
</template>
