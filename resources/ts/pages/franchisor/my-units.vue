<script setup lang="ts">
import AddFranchiseeModal from '@/components/dialogs/AddFranchiseeModal.vue'

// 👉 Router
const router = useRouter()

// 👉 Current tab
const currentTab = ref('overview')

// 👉 Mock units data
const unitsData = ref([
    {
        id: 1,
        branchName: 'Downtown Coffee Hub',
        franchiseeName: 'John Smith',
        email: 'john.smith@email.com',
        contactNumber: '+1 555-123-4567',
        address: '123 Main St, Downtown',
        city: 'Los Angeles',
        state: 'California',
        country: 'United States',
        royaltyPercentage: 8.5,
        contractStartDate: '2024-01-15',
        renewalDate: '2027-01-15',
        status: 'active',
        totalTasks: 25,
        completedTasks: 18,
        totalStaff: 12,
        totalProducts: 45,
        avgRating: 4.5,
    },
    {
        id: 2,
        branchName: 'Westside Cafe',
        franchiseeName: 'Sarah Johnson',
        email: 'sarah.johnson@email.com',
        contactNumber: '+1 555-987-6543',
        address: '456 West Ave, Westside',
        city: 'Los Angeles',
        state: 'California',
        country: 'United States',
        royaltyPercentage: 7.5,
        contractStartDate: '2024-02-01',
        renewalDate: '2027-02-01',
        status: 'active',
        totalTasks: 30,
        completedTasks: 22,
        totalStaff: 15,
        totalProducts: 42,
        avgRating: 4.2,
    },
    {
        id: 3,
        branchName: 'Eastside Express',
        franchiseeName: 'Mike Davis',
        email: 'mike.davis@email.com',
        contactNumber: '+1 555-456-7890',
        address: '789 East Blvd, Eastside',
        city: 'Los Angeles',
        state: 'California',
        country: 'United States',
        royaltyPercentage: 9.0,
        contractStartDate: '2023-12-01',
        renewalDate: '2026-12-01',
        status: 'pending',
        totalTasks: 20,
        completedTasks: 12,
        totalStaff: 8,
        totalProducts: 38,
        avgRating: 4.0,
    },
])

// 👉 Modal state
const isAddFranchiseeModalVisible = ref(false)

// 👉 Computed stats
const totalUnits = computed(() => unitsData.value.length)
const activeUnits = computed(() => unitsData.value.filter(unit => unit.status === 'active').length)
const pendingUnits = computed(() => unitsData.value.filter(unit => unit.status === 'pending').length)
const totalRevenue = computed(() => {
    return unitsData.value.reduce((total, unit) => {
        // Mock calculation: assume average monthly revenue per unit
        const monthlyRevenue = 50000 // SAR
        return total + (monthlyRevenue * unit.royaltyPercentage / 100)
    }, 0)
})

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
const viewUnit = (unitId: number) => {
    router.push(`/franchisor/units/${unitId}`)
}

const addFranchisee = () => {
    isAddFranchiseeModalVisible.value = true
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-SA', {
        style: 'currency',
        currency: 'SAR',
        minimumFractionDigits: 0,
    }).format(amount)
}

const onFranchiseeAdded = (franchiseeData: any) => {
    // Add the new franchisee to the units data
    const newUnit = {
        id: unitsData.value.length + 1,
        branchName: franchiseeData.branchName,
        franchiseeName: franchiseeData.franchiseeName,
        email: franchiseeData.email,
        contactNumber: franchiseeData.contactNumber,
        address: franchiseeData.address,
        city: franchiseeData.city,
        state: franchiseeData.state,
        country: franchiseeData.country,
        royaltyPercentage: franchiseeData.royaltyPercentage,
        contractStartDate: franchiseeData.contractStartDate,
        renewalDate: franchiseeData.renewalDate,
        status: 'active',
        totalTasks: 0,
        completedTasks: 0,
        totalStaff: 0,
        totalProducts: 0,
        avgRating: 0,
    }

    unitsData.value.push(newUnit)
}

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
                    <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee">
                        Add Franchisee
                    </VBtn>
                </div>
            </VCol>
        </VRow>

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
                                        {{ totalUnits }}
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
                                        {{ activeUnits }}
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
                                        {{ pendingUnits }}
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
                                        {{ formatCurrency(totalRevenue) }}
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
                        <VRow>
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
                            <VBtn color="primary" prepend-icon="tabler-plus" @click="addFranchisee">
                                Add Franchisee
                            </VBtn>
                        </template>
                    </VCardItem>

                    <VDivider />

                    <!-- Units Table -->
                    <VDataTable :items="unitsData" :headers="unitHeaders" class="text-no-wrap" item-value="id"
                        @click:row="(_event: any, { item }: { item: any }) => viewUnitDetails(item)"
                        style="cursor: pointer;">
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
