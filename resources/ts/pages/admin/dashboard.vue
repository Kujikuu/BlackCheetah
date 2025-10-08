<script setup lang="ts">
import AdminUsersChart from '@/views/admin/charts/AdminUsersChart.vue'
import AdminRevenueGrowth from '@/views/admin/charts/AdminRevenueGrowth.vue'
import AdminRequestsChart from '@/views/admin/charts/AdminRequestsChart.vue'
import ViewUserDialog from '@/views/admin/modals/ViewUserDialog.vue'
import AddEditFranchisorDrawer from '@/views/admin/modals/AddEditFranchisorDrawer.vue'
import AddEditFranchiseeDrawer from '@/views/admin/modals/AddEditFranchiseeDrawer.vue'
import AddEditSalesDrawer from '@/views/admin/modals/AddEditSalesDrawer.vue'

definePage({
  meta: {
    subject: 'Admin',
    action: 'read',
  },
})

// Stats data
const statsData = ref([
  { title: 'Total Users', value: '1,245', change: 18.2, desc: 'All registered users', icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Franchisors', value: '156', change: 12.5, desc: 'Active franchisors', icon: 'tabler-building-store', iconColor: 'success' },
  { title: 'Franchisees', value: '892', change: 22.8, desc: 'Active franchisees', icon: 'tabler-user-check', iconColor: 'info' },
  { title: 'Sales Users', value: '197', change: 8.4, desc: 'Sales team members', icon: 'tabler-chart-line', iconColor: 'warning' },
])

// Recent users data
const recentUsers = ref([
  {
    id: 1,
    fullName: 'John Doe',
    email: 'john.doe@example.com',
    role: 'franchisor',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-15',
    franchiseName: 'Acme Corporation',
    plan: 'Enterprise',
    lastLogin: '2024-01-15',
  },
  {
    id: 2,
    fullName: 'Jane Smith',
    email: 'jane.smith@example.com',
    role: 'franchisee',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-14',
    location: 'Riyadh',
    phone: '+966 50 567 8905',
  },
  {
    id: 3,
    fullName: 'Mike Johnson',
    email: 'mike.j@example.com',
    role: 'sales',
    status: 'pending',
    avatar: '',
    joinedDate: '2024-01-13',
    city: 'Riyadh',
    phone: '+966 50 567 8910',
  },
  {
    id: 4,
    fullName: 'Sarah Williams',
    email: 'sarah.w@example.com',
    role: 'franchisee',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-12',
    location: 'Jeddah',
    phone: '+966 55 567 8906',
  },
  {
    id: 5,
    fullName: 'David Brown',
    email: 'david.b@example.com',
    role: 'franchisor',
    status: 'inactive',
    avatar: '',
    joinedDate: '2024-01-11',
    franchiseName: 'Global Ventures',
    plan: 'Pro',
    lastLogin: '2024-01-11',
  },
])

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
  if (user.role === 'franchisor') {
    isEditFranchisorDrawerVisible.value = true
  }
  else if (user.role === 'franchisee') {
    isEditFranchiseeDrawerVisible.value = true
  }
  else if (user.role === 'sales') {
    isEditSalesDrawerVisible.value = true
  }
}

// Handle edit from view dialog
const handleEditFromView = () => {
  if (selectedUser.value) {
    editUser(selectedUser.value)
  }
}

// Update user data
const updateUser = (userData: any) => {
  const index = recentUsers.value.findIndex(u => u.id === userData.id)
  if (index !== -1) {
    recentUsers.value[index] = { ...recentUsers.value[index], ...userData }
  }
  selectedUser.value = null
}

// Get user type label
const getUserTypeLabel = (role: string) => {
  if (role === 'franchisor') return 'Franchisor'
  if (role === 'franchisee') return 'Franchisee'
  if (role === 'sales') return 'Sales User'
  return 'User'
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

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <template
        v-for="(data, id) in statsData"
        :key="id"
      >
        <VCol
          cols="12"
          md="3"
          sm="6"
        >
          <VCard>
            <VCardText>
              <div class="d-flex justify-space-between">
                <div class="d-flex flex-column gap-y-1">
                  <div class="text-body-1 text-high-emphasis">
                    {{ data.title }}
                  </div>
                  <div class="d-flex gap-x-2 align-center">
                    <h4 class="text-h4">
                      {{ data.value }}
                    </h4>
                    <div
                      class="text-base"
                      :class="data.change > 0 ? 'text-success' : 'text-error'"
                    >
                      ({{ prefixWithPlus(data.change) }}%)
                    </div>
                  </div>
                  <div class="text-sm">
                    {{ data.desc }}
                  </div>
                </div>
                <VAvatar
                  :color="data.iconColor"
                  variant="tonal"
                  rounded
                  size="42"
                >
                  <VIcon
                    :icon="data.icon"
                    size="26"
                  />
                </VAvatar>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </template>
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
