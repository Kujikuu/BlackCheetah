<script setup lang="ts">
import { marketplaceApi, type Franchise } from '@/services/api'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import Footer from '@/views/front-pages/front-page-footer.vue'
import InquiryForm from '@/views/front-pages/marketplace/inquiry-form.vue'
import { useConfigStore } from '@core/stores/config'
import { formatCurrency } from '@/@core/utils/formatters'

const store = useConfigStore()
store.skin = 'default'

const route = useRoute()
const router = useRouter()

const franchiseId = computed(() => Number(route.params.id))

// Data
const franchise = ref<Franchise | null>(null)
const isLoading = ref(false)

// Fetch franchise details
const fetchFranchiseDetails = async () => {
  try {
    isLoading.value = true
    const response = await marketplaceApi.getFranchiseDetails(franchiseId.value)

    if (response.success && response.data) {
      franchise.value = response.data
    }
    else {
      router.push({ name: 'marketplace' })
    }
  }
  catch (error) {
    console.error('Error fetching franchise details:', error)
    router.push({ name: 'marketplace' })
  }
  finally {
    isLoading.value = false
  }
}

// Format percentage
const formatPercentage = (value: number | undefined) => {
  if (!value)
    return 'N/A'

  return `${value}%`
}

// Lifecycle
onMounted(() => {
  fetchFranchiseDetails()
})
</script>

<template>
  <div class="franchise-details-page">
    <Navbar />

    <!-- Loading State -->
    <VContainer v-if="isLoading" class="py-16">
      <div class="text-center">
        <VProgressCircular indeterminate color="primary" size="64" />
        <p class="text-h6 mt-4">
          Loading franchise details...
        </p>
      </div>
    </VContainer>

    <!-- Franchise Details -->
    <template v-else-if="franchise">
      <!-- Breadcrumbs -->
      <!-- <VContainer class="pt-6 pb-0">
        <VBreadcrumbs
          class="px-0"
          :items="[
            { title: 'Home', to: { name: 'index' }, class: 'text-primary' },
            { title: 'Marketplace', to: { name: 'marketplace' }, class: 'text-primary' },
            { title: franchise.brand_name },
          ]"
        />
      </VContainer> -->

      <!-- Hero Section -->
      <section class="franchise-hero">
        <VContainer>
          <VRow align="center" class="py-8">
            <VCol cols="12" md="8" lg="7">
              <VChip color="primary" size="small" class="mb-4">
                {{ franchise.industry }}
              </VChip>
              <h1 class="text-h3 text-md-h2 mb-3 font-weight-bold">
                {{ franchise.brand_name }}
              </h1>
              <p class="text-h6 text-md-h5 text-medium-emphasis mb-6">
                {{ franchise.business_name }}
              </p>
              <div class="d-flex gap-3 gap-md-4 flex-wrap mb-4">
                <VChip color="primary" variant="flat" size="default">
                  <VIcon icon="tabler-building" class="me-2" size="18" />
                  <span class="font-weight-medium">{{ franchise.total_units }} Units</span>
                </VChip>
                <VChip color="success" variant="flat" size="default">
                  <VIcon icon="tabler-map-pin" class="me-2" size="18" />
                  <span class="font-weight-medium">{{ franchise.headquarters_city }}, {{ franchise.headquarters_country }}</span>
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="4" lg="5" class="text-center">
              <VCard
                v-if="franchise.logo"
                elevation="4"
                class="logo-card mx-auto"
              >
                <VCardText class="pa-6">
                  <VImg
                    :src="franchise.logo"
                    max-width="200"
                    max-height="200"
                    contain
                    class="mx-auto"
                  />
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VContainer>
      </section>

      <!-- Main Content -->
      <VContainer class="py-8">
        <VRow>
          <!-- Left Column - Details -->
          <VCol cols="12" md="8">
            <!-- About Section -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">About This Franchise</VCardTitle>
              </VCardItem>
              <VCardText>
                <p class="text-body-1 mb-4">
                  {{ franchise.description || 'No description available.' }}
                </p>

                <div v-if="franchise.website" class="d-flex align-center gap-2">
                  <VIcon icon="tabler-world" size="20" class="text-primary" />
                  <span class="text-body-2 text-medium-emphasis me-2">Website:</span>
                  <a :href="franchise.website" target="_blank" class="text-primary font-weight-medium">
                    {{ franchise.website }}
                  </a>
                </div>
              </VCardText>
            </VCard>

            <!-- Investment Details -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Investment Details</VCardTitle>
              </VCardItem>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="investment-item">
                      <VAvatar size="40" color="primary" variant="tonal" class="me-3">
                        <VIcon icon="tabler-currency-dollar" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Franchise Fee
                        </div>
                        <div class="text-h6 font-weight-medium text-primary">
                          {{ formatCurrency(franchise.franchise_fee || 0) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="investment-item">
                      <VAvatar size="40" color="success" variant="tonal" class="me-3">
                        <VIcon icon="tabler-percentage" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Royalty Fee
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ formatPercentage(franchise.royalty_percentage) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="investment-item">
                      <VAvatar size="40" color="info" variant="tonal" class="me-3">
                        <VIcon icon="tabler-brand-google-ads" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Marketing Fee
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ formatPercentage(franchise.marketing_fee_percentage) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="investment-item">
                      <VAvatar size="40" color="warning" variant="tonal" class="me-3">
                        <VIcon icon="tabler-briefcase" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Business Type
                        </div>
                        <div class="text-h6 font-weight-medium text-capitalize">
                          {{ franchise.business_type || 'N/A' }}
                        </div>
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Locations -->
            <VCard v-if="franchise.units && franchise.units.length > 0" elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Existing Locations</VCardTitle>
              </VCardItem>
              <VCardText>
                <VRow>
                  <VCol
                    v-for="unit in franchise.units"
                    :key="unit.id"
                    cols="12"
                    sm="6"
                  >
                    <VCard elevation="1" class="unit-card rounded-lg h-100">
                      <VCardText>
                        <div class="d-flex align-center gap-3 mb-3">
                          <VAvatar size="40" color="primary" variant="tonal">
                            <VIcon icon="tabler-building" size="20" />
                          </VAvatar>
                          <div class="flex-grow-1">
                            <div class="text-h6 font-weight-medium mb-1">
                              {{ unit.unit_name }}
                            </div>
                            <div class="text-sm text-medium-emphasis">
                              {{ unit.city }}, {{ unit.state_province }}, {{ unit.nationality }}
                            </div>
                          </div>
                        </div>
                        <VChip size="small" :color="unit.status === 'active' ? 'success' : 'default'" variant="tonal">
                          {{ unit.status }}
                        </VChip>
                      </VCardText>
                    </VCard>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Contact Information -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Contact Information</VCardTitle>
              </VCardItem>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="d-flex align-start gap-3 mb-4">
                      <VAvatar size="40" color="success" variant="tonal">
                        <VIcon icon="tabler-phone" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Phone
                        </div>
                        <div class="text-body-1 font-weight-medium">
                          {{ franchise.contact_phone }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="d-flex align-start gap-3 mb-4">
                      <VAvatar size="40" color="primary" variant="tonal">
                        <VIcon icon="tabler-mail" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Email
                        </div>
                        <div class="text-body-1 font-weight-medium">
                          {{ franchise.contact_email }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12">
                    <div class="d-flex align-start gap-3">
                      <VAvatar size="40" color="error" variant="tonal">
                        <VIcon icon="tabler-map-pin" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Headquarters
                        </div>
                        <div class="text-body-1 font-weight-medium">
                          {{ franchise.headquarters_address }}, {{ franchise.headquarters_city }}, {{ franchise.headquarters_country }}
                        </div>
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Right Column - Inquiry Form -->
          <VCol cols="12" md="4">
            <div class="sticky-form-wrapper">
              <InquiryForm
                inquiry-type="franchise"
                :item-id="franchiseId"
                title="Interested in This Franchise?"
              />
            </div>
          </VCol>
        </VRow>
      </VContainer>
    </template>

    <Footer />
  </div>
</template>

<style lang="scss" scoped>
.franchise-details-page {
  background-color: rgb(var(--v-theme-background));
}

.franchise-hero {
  background-color: rgb(var(--v-theme-surface));
  border-radius: 0 0 24px 24px;
  margin-bottom: 2rem;
  padding-top: 8rem;

  @media (max-width: 599px) {
    border-radius: 0 0 16px 16px;
    margin-bottom: 1rem;
  }
}

.logo-card {
  max-width: 280px;
  background: rgb(var(--v-theme-surface)) !important;
}

.sticky-form-wrapper {
  position: sticky;
  top: 100px;

  @media (max-width: 959px) {
    position: relative;
    top: 0;
  }
}

.investment-item {
  display: flex;
  align-items: flex-start;
  padding: 1.25rem;
  background-color: rgb(var(--v-theme-surface));
  border-radius: 12px;
  transition: all 0.2s ease;
  height: 100%;

  @media (max-width: 599px) {
    padding: 1rem;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}

.unit-card {
  transition: all 0.2s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
}
</style>

