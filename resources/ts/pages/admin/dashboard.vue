<script setup lang="ts">
import CardStatisticsVertical from '@/@core/components/cards/CardStatisticsVertical.vue'
import AdminRequestsChart from '@/views/admin/charts/AdminRequestsChart.vue'
import AdminRevenueGrowth from '@/views/admin/charts/AdminRevenueGrowth.vue'
import AdminUsersChart from '@/views/admin/charts/AdminUsersChart.vue'
import AddEditFranchiseeDrawer from '@/views/admin/modals/AddEditFranchiseeDrawer.vue'
import AddEditFranchisorDrawer from '@/views/admin/modals/AddEditFranchisorDrawer.vue'
import AddEditSalesDrawer from '@/views/admin/modals/AddEditSalesDrawer.vue'
import ViewUserDialog from '@/views/admin/modals/ViewUserDialog.vue'

interface StatData {
  title: string
  color?: string
  icon: string
  stats: string
  height: number
  series: unknown[]
  chartOptions: unknown
}

interface RecentUser {
  id: number
  fullName: string
  email: string
  role: string
  status: string
  avatar: string
  joinedDate: string
  lastLogin?: string
  franchiseName?: string
  plan?: string
  location?: string
  phone?: string
  city?: string
}

// Reactive data
const statsData = ref<StatData[]>([])
const recentUsers = ref<RecentUser[]>([])
const isLoading = ref(true)
const error = ref('')

// Fetch dashboard statistics
const fetchStats = async () => {
  try {
    const response = await $api('/v1/admin/dashboard/stats')
    if (response.success) {
      // Use the actual API response structure
      statsData.value = response.data
    }
  }
  catch (err) {
    console.error('Error fetching stats:', err)
    error.value = 'Failed to load dashboard statistics'
  }
}

// Fetch recent users
const fetchRecentUsers = async () => {
  try {
    const response = await $api('/v1/admin/dashboard/recent-users')
    if (response.success)
      recentUsers.value = response.data
  }
  catch (err) {
    console.error('Error fetching recent users:', err)
    error.value = 'Failed to load recent users'
  }
}

// Load dashboard data
const loadDashboardData = async () => {
  isLoading.value = true
  error.value = ''

  try {
    await Promise.all([
      fetchStats(),
      fetchRecentUsers(),
    ])
  }
  catch (err) {
    console.error('Error loading dashboard data:', err)
    error.value = 'Failed to load dashboard data'
  }
  finally {
    isLoading.value = false
  }
}

// Load data on component mount
onMounted(() => {
  loadDashboardData()
})

const resolveUserRoleVariant = (role: string) => {
  const roleLowerCase = role.toLowerCase()

  if (roleLowerCase === 'franchisor')
    return { color: 'success', icon: 'tabler-building-store' }
  if (roleLowerCase === 'franchisee')
    return { color: 'info', icon: 'tabler-user-check' }
  if (roleLowerCase === 'sales')
    return { color: 'warning', icon: 'tabler-chart-line' }

  return { color: 'primary', icon: 'tabler-user' }
}

const resolveUserStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'

  return 'primary'
}

// Modal/Drawer states
const isViewDialogVisible = ref(false)
const isEditFranchisorDrawerVisible = ref(false)
const isEditFranchiseeDrawerVisible = ref(false)
const isEditSalesDrawerVisible = ref(false)
const selectedUser = ref<any>(null)

// View user
const viewUser = (user: any) => {
  selectedUser.value = user
  isViewDialogVisible.value = true
}

// Edit user
const editUser = (user: any) => {
  selectedUser.value = { ...user }

  // Open appropriate drawer based on role
  if (user.role === 'franchisor')
    isEditFranchisorDrawerVisible.value = true

  else if (user.role === 'franchisee')
    isEditFranchiseeDrawerVisible.value = true

  else if (user.role === 'sales')
    isEditSalesDrawerVisible.value = true
}

// Handle edit from view dialog
const handleEditFromView = () => {
  if (selectedUser.value)
    editUser(selectedUser.value)
}

// Update user data
const updateUser = (userData: any) => {
  const index = recentUsers.value.findIndex(u => u.id === userData.id)
  if (index !== -1)
    recentUsers.value[index] = { ...recentUsers.value[index], ...userData }

  selectedUser.value = null
}

// Get user type label
const getUserTypeLabel = (role: string) => {
  if (role === 'franchisor')
    return 'Franchisor'
  if (role === 'franchisee')
    return 'Franchisee'
  if (role === 'sales')
    return 'Sales User'

  return 'User'
}

const avatarText = (name: string | null | undefined) => {
  if (!name || typeof name !== 'string')
    return 'U'

  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase()
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
              Admin Dashboard
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Welcome back! Here's what's happening with your platform today.
            </p>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Error Alert -->
    <VRow
      v-if="error"
      class="mb-6"
    >
      <VCol cols="12">
        <VAlert
          type="error"
          variant="tonal"
          closable
          @click:close="error = ''"
        >
          {{ error }}
        </VAlert>
      </VCol>
    </VRow>

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <template v-if="isLoading">
        <VCol
          v-for="i in 4"
          :key="i"
          cols="12"
          md="3"
          sm="6"
        >
          <VCard>
            <VCardText>
              <div class="d-flex justify-space-between">
                <div class="d-flex flex-column gap-y-1">
                  <VSkeletonLoader
                    type="text"
                    width="80px"
                  />
                  <VSkeletonLoader
                    type="text"
                    width="60px"
                    height="32px"
                  />
                  <VSkeletonLoader
                    type="text"
                    width="120px"
                  />
                </div>
                <VSkeletonLoader
                  type="avatar"
                  size="42"
                />
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </template>
      <VCol
        v-for="stats in statsData"
        :key="stats.title"
        cols="12"
        md="3"
        sm="6"
      >
        <CardStatisticsVertical v-bind="stats" />
      </VCol>
    </VRow>

    <!-- Charts Row -->
    <VRow class="mb-6">
      <VCol
        cols="12"
        md="4"
      >
        <AdminUsersChart />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <AdminRevenueGrowth />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <AdminRequestsChart />
      </VCol>
    </VRow>

    <!-- Recent Users Table -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Recent Users</VCardTitle>
            <VCardSubtitle>Latest registered users</VCardSubtitle>

            <template #append>
              <VBtn
                variant="outlined"
                color="primary"
                size="small"
                :to="{ name: 'admin-users-franchisors' }"
              >
                View All
              </VBtn>
            </template>
          </VCardItem>

          <VDivider />

          <VTable class="text-no-wrap">
            <thead>
              <tr>
                <th scope="col">
                  USER
                </th>
                <th scope="col">
                  ROLE
                </th>
                <th scope="col">
                  STATUS
                </th>
                <th scope="col">
                  JOINED DATE
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="user in recentUsers"
                :key="user.id"
              >
                <td>
                  <div class="d-flex align-center gap-x-4">
                    <VAvatar
                      size="34"
                      :variant="!user.avatar ? 'tonal' : undefined"
                      :color="!user.avatar ? resolveUserRoleVariant(user.role).color : undefined"
                    >
                      <VImg
                        v-if="user.avatar"
                        :src="user.avatar"
                      />
                      <span v-else>{{ avatarText(user.fullName) }}</span>
                    </VAvatar>
                    <div class="d-flex flex-column">
                      <h6 class="text-base font-weight-medium">
                        {{ user.fullName }}
                      </h6>
                      <div class="text-sm text-medium-emphasis">
                        {{ user.email }}
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-center gap-x-2">
                    <VIcon
                      :size="22"
                      :icon="resolveUserRoleVariant(user.role).icon"
                      :color="resolveUserRoleVariant(user.role).color"
                    />
                    <div class="text-capitalize text-high-emphasis text-body-1">
                      {{ user.role }}
                    </div>
                  </div>
                </td>
                <td>
                  <VChip
                    :color="resolveUserStatusVariant(user.status)"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ user.status }}
                  </VChip>
                </td>
                <td>
                  <div class="text-body-1">
                    {{ user.joinedDate }}
                  </div>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <IconBtn
                      size="small"
                      @click="viewUser(user)"
                    >
                      <VIcon icon="tabler-eye" />
                      <VTooltip
                        activator="parent"
                        location="top"
                      >
                        View
                      </VTooltip>
                    </IconBtn>

                    <IconBtn
                      size="small"
                      @click="editUser(user)"
                    >
                      <VIcon icon="tabler-edit" />
                      <VTooltip
                        activator="parent"
                        location="top"
                      >
                        Edit
                      </VTooltip>
                    </IconBtn>
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VCol>
    </VRow>

    <!-- View User Dialog -->
    <ViewUserDialog
      v-model:is-dialog-open="isViewDialogVisible"
      :user="selectedUser"
      :user-type="selectedUser ? getUserTypeLabel(selectedUser.role) : 'User'"
      @edit="handleEditFromView"
    />

    <!-- Edit Franchisor Drawer -->
    <AddEditFranchisorDrawer
      v-model:is-drawer-open="isEditFranchisorDrawerVisible"
      :franchisor="selectedUser"
      @franchisor-data="updateUser"
    />

    <!-- Edit Franchisee Drawer -->
    <AddEditFranchiseeDrawer
      v-model:is-drawer-open="isEditFranchiseeDrawerVisible"
      :franchisee="selectedUser"
      @franchisee-data="updateUser"
    />

    <!-- Edit Sales Drawer -->
    <AddEditSalesDrawer
      v-model:is-drawer-open="isEditSalesDrawerVisible"
      :sales-user="selectedUser"
      @sales-user-data="updateUser"
    />
  </section>
</template>
