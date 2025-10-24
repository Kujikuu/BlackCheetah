<script setup lang="ts">
import { brokerApi, type AssignedFranchise } from '@/services/api'
import { formatCurrency } from '@/@core/utils/formatters'

// 👉 Page state
const isLoading = ref(true)
const franchises = ref<AssignedFranchise[]>([])
const totalFranchises = ref(0)

// 👉 Snackbar for notifications
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
})

const showSnackbar = (message: string, color: string = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
  }
}

// 👉 Load assigned franchises
const loadAssignedFranchises = async () => {
  try {
    isLoading.value = true
    const response = await brokerApi.getAssignedFranchises()

    if (response.success && response.data) {
      franchises.value = response.data.data || []
      totalFranchises.value = response.data.total || 0
    }
  }
  catch (error: any) {
    console.error('Failed to load assigned franchises:', error)
    showSnackbar('Failed to load assigned franchises', 'error')
  }
  finally {
    isLoading.value = false
  }
}

// 👉 Toggle marketplace listing
const toggleMarketplaceListing = async (franchise: AssignedFranchise) => {
  try {
    const response = await brokerApi.toggleMarketplaceListing(franchise.id)

    if (response.success) {
      showSnackbar(response.message || 'Marketplace listing updated', 'success')
      await loadAssignedFranchises() // Reload to get updated status
    }
    else {
      showSnackbar(response.message || 'Failed to update marketplace listing', 'error')
    }
  }
  catch (error: any) {
    console.error('Failed to toggle marketplace listing:', error)
    showSnackbar('Failed to update marketplace listing', 'error')
  }
}

// 👉 Computed
const listedCount = computed(() => franchises.value.filter(f => f.is_marketplace_listed).length)
const activeCount = computed(() => franchises.value.filter(f => f.status === 'active').length)

// 👉 Load data on component mount
onMounted(() => {
  loadAssignedFranchises()
})

// 👉 Status color resolver
const resolveStatusColor = (status: string) => {
  if (status === 'active')
    return 'success'
  if (status === 'inactive')
    return 'warning'
  if (status === 'pending')
    return 'info'

  return 'secondary'
}
</script>

<template>
  <section>
    <!-- Loading Overlay -->
    <VOverlay
      v-model="isLoading"
      class="align-center justify-center"
    >
      <VProgressCircular
        color="primary"
        indeterminate
        size="64"
      />
    </VOverlay>

    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div>
          <h2 class="text-h2 mb-1">
            Assigned Franchises
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            Manage marketplace listings for franchises assigned to you
          </p>
        </div>
      </VCol>
    </VRow>

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="primary"
              variant="tonal"
            >
              <VIcon
                icon="tabler-building-store"
                size="26"
              />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Total Franchises
              </div>
              <h4 class="text-h4">
                {{ totalFranchises }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="success"
              variant="tonal"
            >
              <VIcon
                icon="tabler-circle-check"
                size="26"
              />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Listed on Marketplace
              </div>
              <h4 class="text-h4">
                {{ listedCount }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="info"
              variant="tonal"
            >
              <VIcon
                icon="tabler-chart-line"
                size="26"
              />
            </VAvatar>
            <div class="ms-4">
              <div class="text-body-2 text-disabled">
                Active Franchises
              </div>
              <h4 class="text-h4">
                {{ activeCount }}
              </h4>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Franchises Grid -->
    <VRow v-if="franchises.length > 0">
      <VCol
        v-for="franchise in franchises"
        :key="franchise.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard>
          <VCardText>
            <!-- Franchise Header -->
            <div class="d-flex align-center mb-4">
              <VAvatar
                size="48"
                :image="franchise.logo || undefined"
                color="primary"
                variant="tonal"
                class="me-3"
              >
                <VIcon
                  v-if="!franchise.logo"
                  icon="tabler-building-store"
                  size="28"
                />
              </VAvatar>
              <div class="flex-grow-1">
                <h6 class="text-h6 mb-1">
                  {{ franchise.brand_name }}
                </h6>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  {{ franchise.business_name }}
                </p>
              </div>
              <VChip
                :color="resolveStatusColor(franchise.status)"
                size="small"
                variant="tonal"
              >
                {{ franchise.status }}
              </VChip>
            </div>

            <VDivider class="mb-4" />

            <!-- Franchise Details -->
            <div class="mb-4">
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2 text-disabled">Industry</span>
                <span class="text-body-2 font-weight-medium">{{ franchise.industry }}</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2 text-disabled">Franchise Fee</span>
                <span class="text-body-2 font-weight-medium text-success">
                  {{ formatCurrency(franchise.franchise_fee) }}
                </span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2 text-disabled">Royalty</span>
                <span class="text-body-2 font-weight-medium">{{ franchise.royalty_percentage }}%</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span class="text-body-2 text-disabled">Total Units</span>
                <span class="text-body-2 font-weight-medium">{{ franchise.total_units }} ({{ franchise.active_units }} active)</span>
              </div>
            </div>

            <VDivider class="mb-4" />

            <!-- Franchisor Info -->
            <div v-if="franchise.franchisor" class="mb-4">
              <div class="text-body-2 text-disabled mb-2">
                Franchisor
              </div>
              <div class="d-flex align-center">
                <VAvatar
                  size="32"
                  color="secondary"
                  variant="tonal"
                  class="me-2"
                >
                  <VIcon
                    icon="tabler-user"
                    size="18"
                  />
                </VAvatar>
                <div>
                  <div class="text-body-2 font-weight-medium">
                    {{ franchise.franchisor.name }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ franchise.franchisor.email }}
                  </div>
                </div>
              </div>
            </div>

            <VDivider class="mb-4" />

            <!-- Marketplace Status & Toggle -->
            <div>
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <VIcon
                    :icon="franchise.is_marketplace_listed ? 'tabler-eye' : 'tabler-eye-off'"
                    :color="franchise.is_marketplace_listed ? 'success' : 'secondary'"
                    size="20"
                    class="me-2"
                  />
                  <span class="text-body-2">
                    {{ franchise.is_marketplace_listed ? 'Listed on Marketplace' : 'Not Listed' }}
                  </span>
                </div>
                <VSwitch
                  :model-value="franchise.is_marketplace_listed"
                  color="success"
                  hide-details
                  inset
                  @update:model-value="toggleMarketplaceListing(franchise)"
                />
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VCard v-else-if="!isLoading">
      <VCardText class="text-center py-16">
        <VAvatar
          size="80"
          color="secondary"
          variant="tonal"
          class="mb-4"
        >
          <VIcon
            icon="tabler-building-off"
            size="48"
          />
        </VAvatar>
        <h5 class="text-h5 mb-2">
          No Franchises Assigned
        </h5>
        <p class="text-body-1 text-medium-emphasis mb-0">
          You don't have any franchises assigned to you yet. Contact your administrator to get franchises assigned.
        </p>
      </VCardText>
    </VCard>

    <!-- Snackbar for notifications -->
    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="top end"
      timeout="4000"
    >
      {{ snackbar.message }}

      <template #actions>
        <VBtn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </VBtn>
      </template>
    </VSnackbar>
  </section>
</template>

