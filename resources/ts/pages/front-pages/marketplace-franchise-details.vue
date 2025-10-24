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
      <!-- Hero Section -->
      <section class="franchise-hero">
        <VContainer>
          <VRow align="center">
            <VCol cols="12" md="8">
              <VChip color="primary" class="mb-4">
                {{ franchise.industry }}
              </VChip>
              <h1 class="text-h2 mb-4">
                {{ franchise.brand_name }}
              </h1>
              <p class="text-h6 mb-4">
                {{ franchise.business_name }}
              </p>
              <div class="d-flex gap-4 flex-wrap">
                <VChip color="primary" variant="flat">
                  <VIcon icon="tabler-building" class="me-2" />
                  {{ franchise.total_units }} Units
                </VChip>
                <VChip color="primary" variant="flat">
                  <VIcon icon="tabler-map-pin" class="me-2" />
                  {{ franchise.headquarters_city }}, {{ franchise.headquarters_country }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="4" class="text-center">
              <VImg
                v-if="franchise.logo"
                :src="franchise.logo"
                max-width="200"
                class="mx-auto"
                style="background: white; border-radius: 12px; padding: 20px;"
              />
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
            <VCard class="mb-6">
              <VCardTitle>About This Franchise</VCardTitle>
              <VCardText>
                <p class="text-body-1">
                  {{ franchise.description || 'No description available.' }}
                </p>

                <div v-if="franchise.website" class="mt-4">
                  <strong>Website:</strong>
                  <a :href="franchise.website" target="_blank" class="text-primary ms-2">
                    {{ franchise.website }}
                  </a>
                </div>
              </VCardText>
            </VCard>

            <!-- Investment Details -->
            <VCard class="mb-6">
              <VCardTitle>Investment Details</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="mb-4">
                      <div class="text-sm text-medium-emphasis mb-1">
                        Franchise Fee
                      </div>
                      <div class="text-h6">
                        {{ formatCurrency(franchise.franchise_fee || 0) }}
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="mb-4">
                      <div class="text-sm text-medium-emphasis mb-1">
                        Royalty Fee
                      </div>
                      <div class="text-h6">
                        {{ formatPercentage(franchise.royalty_percentage) }}
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="mb-4">
                      <div class="text-sm text-medium-emphasis mb-1">
                        Marketing Fee
                      </div>
                      <div class="text-h6">
                        {{ formatPercentage(franchise.marketing_fee_percentage) }}
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="mb-4">
                      <div class="text-sm text-medium-emphasis mb-1">
                        Business Type
                      </div>
                      <div class="text-h6 text-capitalize">
                        {{ franchise.business_type || 'N/A' }}
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Locations -->
            <VCard v-if="franchise.units && franchise.units.length > 0" class="mb-6">
              <VCardTitle>Existing Locations</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol
                    v-for="unit in franchise.units"
                    :key="unit.id"
                    cols="12"
                    sm="6"
                  >
                    <VCard variant="tonal">
                      <VCardText>
                        <div class="d-flex align-center gap-2 mb-2">
                          <VIcon icon="tabler-building" size="20" />
                          <strong>{{ unit.unit_name }}</strong>
                        </div>
                        <div class="text-sm">
                          {{ unit.city }}, {{ unit.state_province }}, {{ unit.nationality }}
                        </div>
                        <VChip size="small" :color="unit.status === 'active' ? 'success' : 'default'" class="mt-2">
                          {{ unit.status }}
                        </VChip>
                      </VCardText>
                    </VCard>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Contact Information -->
            <VCard class="mb-6">
              <VCardTitle>Contact Information</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="d-flex align-center gap-2 mb-3">
                      <VIcon icon="tabler-phone" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Phone
                        </div>
                        <div class="text-body-1">
                          {{ franchise.contact_phone }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="d-flex align-center gap-2 mb-3">
                      <VIcon icon="tabler-mail" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Email
                        </div>
                        <div class="text-body-1">
                          {{ franchise.contact_email }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12">
                    <div class="d-flex align-center gap-2">
                      <VIcon icon="tabler-map-pin" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Headquarters
                        </div>
                        <div class="text-body-1">
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
            <InquiryForm
              inquiry-type="franchise"
              :item-id="franchiseId"
              title="Interested in This Franchise?"
            />
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
  padding: 80px 0;
}

.sticky-card {
  position: sticky;
  top: 100px;
}
</style>

