<script setup lang="ts">
import { marketplaceApi, type Property } from '@/services/api'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import Footer from '@/views/front-pages/front-page-footer.vue'
import InquiryForm from '@/views/front-pages/marketplace/inquiry-form.vue'
import { useConfigStore } from '@core/stores/config'
import { formatCurrency } from '@/@core/utils/formatters'

const store = useConfigStore()
store.skin = 'default'

const route = useRoute()
const router = useRouter()

const propertyId = computed(() => Number(route.params.id))

// Data
const property = ref<Property | null>(null)
const isLoading = ref(false)

// Fetch property details
const fetchPropertyDetails = async () => {
  try {
    isLoading.value = true
    const response = await marketplaceApi.getPropertyDetails(propertyId.value)

    if (response.success && response.data) {
      property.value = response.data
    }
    else {
      router.push({ name: 'marketplace' })
    }
  }
  catch (error) {
    console.error('Error fetching property details:', error)
    router.push({ name: 'marketplace' })
  }
  finally {
    isLoading.value = false
  }
}

// Format date
const formatDate = (date: string | undefined) => {
  if (!date)
    return 'Available now'

  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Lifecycle
onMounted(() => {
  fetchPropertyDetails()
})
</script>

<template>
  <div class="property-details-page">
    <Navbar />

    <!-- Loading State -->
    <VContainer v-if="isLoading" class="py-16">
      <div class="text-center">
        <VProgressCircular indeterminate color="primary" size="64" />
        <p class="text-h6 mt-4">
          Loading property details...
        </p>
      </div>
    </VContainer>

    <!-- Property Details -->
    <template v-else-if="property">
      <!-- Hero Section with Image -->
      <section class="property-hero">
        <VContainer>
          <VRow>
            <VCol cols="12" md="8">
              <VImg
                :src="property.images?.[0] || '/images/placeholder.png'"
                height="400"
                cover
                class="rounded-lg"
              />
            </VCol>
            <VCol cols="12" md="4">
              <VCard class="fill-height">
                <VCardText class="pa-6">
                  <VChip color="success" class="mb-4">
                    {{ property.property_type }}
                  </VChip>
                  <h1 class="text-h3 mb-4">
                    {{ property.title }}
                  </h1>
                  <div class="d-flex align-center gap-2 mb-3">
                    <VIcon icon="tabler-map-pin" size="20" />
                    <span class="text-body-1">
                      {{ property.city }}, {{ property.state_province }}, {{ property.country }}
                    </span>
                  </div>
                  <div class="text-h4 text-primary mt-4">
                    {{ formatCurrency(property.monthly_rent) }}/month
                  </div>
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
            <!-- Description -->
            <VCard class="mb-6">
              <VCardTitle>Property Description</VCardTitle>
              <VCardText>
                <p class="text-body-1">
                  {{ property.description }}
                </p>
              </VCardText>
            </VCard>

            <!-- Property Details -->
            <VCard class="mb-6">
              <VCardTitle>Property Details</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-ruler" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Size
                        </div>
                        <div class="text-h6">
                          {{ property.size_sqft.toLocaleString() }} sqft
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-currency-dollar" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Monthly Rent
                        </div>
                        <div class="text-h6">
                          {{ formatCurrency(property.monthly_rent) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-coin" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Deposit Amount
                        </div>
                        <div class="text-h6">
                          {{ formatCurrency(property.deposit_amount || 0) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-calendar" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Lease Term
                        </div>
                        <div class="text-h6">
                          {{ property.lease_term_months }} months
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-calendar-check" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Available From
                        </div>
                        <div class="text-h6">
                          {{ formatDate(property.available_from) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VIcon icon="tabler-building" class="me-2" />
                      <div>
                        <div class="text-sm text-medium-emphasis">
                          Property Type
                        </div>
                        <div class="text-h6 text-capitalize">
                          {{ property.property_type.replace('_', ' ') }}
                        </div>
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Location -->
            <VCard class="mb-6">
              <VCardTitle>Location</VCardTitle>
              <VCardText>
                <div class="d-flex align-center gap-2 mb-3">
                  <VIcon icon="tabler-map-pin" size="24" />
                  <div>
                    <div class="text-body-1 font-weight-medium">
                      {{ property.address }}
                    </div>
                    <div class="text-sm text-medium-emphasis">
                      {{ property.city }}, {{ property.state_province }} {{ property.postal_code }}
                    </div>
                    <div class="text-sm text-medium-emphasis">
                      {{ property.country }}
                    </div>
                  </div>
                </div>
              </VCardText>
            </VCard>

            <!-- Amenities -->
            <VCard v-if="property.amenities && property.amenities.length > 0" class="mb-6">
              <VCardTitle>Amenities</VCardTitle>
              <VCardText>
                <div class="d-flex flex-wrap gap-2">
                  <VChip
                    v-for="amenity in property.amenities"
                    :key="amenity"
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon icon="tabler-check" class="me-1" size="16" />
                    {{ amenity }}
                  </VChip>
                </div>
              </VCardText>
            </VCard>

            <!-- Broker Contact -->
            <VCard v-if="property.broker" class="mb-6">
              <VCardTitle>Listed By</VCardTitle>
              <VCardText>
                <div class="d-flex align-center gap-4">
                  <VAvatar size="64" color="primary" variant="tonal">
                    <VIcon icon="tabler-user" size="32" />
                  </VAvatar>
                  <div>
                    <div class="text-h6">
                      {{ property.broker.name }}
                    </div>
                    <div class="text-sm text-medium-emphasis">
                      <VIcon icon="tabler-mail" size="16" class="me-1" />
                      {{ property.broker.email }}
                    </div>
                    <div v-if="property.broker.phone" class="text-sm text-medium-emphasis">
                      <VIcon icon="tabler-phone" size="16" class="me-1" />
                      {{ property.broker.phone }}
                    </div>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Right Column - Inquiry Form -->
          <VCol cols="12" md="4">
            <InquiryForm
              inquiry-type="property"
              :item-id="propertyId"
              title="Interested in This Property?"
            />
          </VCol>
        </VRow>
      </VContainer>
    </template>

    <Footer />
  </div>
</template>

<style lang="scss" scoped>
.property-details-page {
  background-color: rgb(var(--v-theme-background));
}

.property-hero {
  padding: 40px 0;
  background-color: rgb(var(--v-theme-surface));
}

.sticky-card {
  position: sticky;
  top: 100px;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 12px;
  background-color: rgb(var(--v-theme-surface));
  border-radius: 8px;
}
</style>

