<script setup lang="ts">
import { marketplaceApi, type Property } from '@/services/api'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import Footer from '@/views/front-pages/front-page-footer.vue'
import InquiryForm from '@/views/front-pages/marketplace/inquiry-form.vue'
import { useConfigStore } from '@core/stores/config'
import { formatCurrency } from '@/@core/utils/formatters'
import { SaudiRiyal } from 'lucide-vue-next'
import placeholderImage from '@images/placholder.png'

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

// Format property type for display
const formattedPropertyType = computed(() => {
  if (!property.value?.property_type)
    return ''

  const type = property.value.property_type
  const typeMap: Record<string, string> = {
    retail: 'Retail',
    office: 'Office',
    kiosk: 'Kiosk',
    food_court: 'Food Court',
    standalone: 'Standalone',
  }

  return typeMap[type.toLowerCase()] || type.split('_').map(word =>
    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(),
  ).join(' ')
})

// Compute image source with proper fallback
// Compute image source with proper fallback
const imageSrc = computed(() => {
  // Handle case where images might be null, undefined, or not an array
  if (!property.value?.images) {
    return placeholderImage
  }

  // Handle case where images might be a string (JSON)
  let imagesArray = property.value?.images
  if (typeof imagesArray === 'string') {
    try {
      imagesArray = JSON.parse(imagesArray)
    } catch {
      return placeholderImage
    }
  }

  // Ensure it's an array
  if (!Array.isArray(imagesArray) || imagesArray.length === 0) {
    return placeholderImage
  }

  // Get first image and validate it
  const firstImage = imagesArray[0]
  if (firstImage && typeof firstImage === 'string' && firstImage.trim() !== '' && !firstImage.startsWith('[')) {
    return firstImage
  }

  return placeholderImage
})

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
      <!-- Breadcrumbs -->
      <!-- <VContainer class="pt-6 pb-0">
        <VBreadcrumbs
          class="px-0"
          :items="[
            { title: 'Home', to: { name: 'index' }, class: 'text-primary' },
            { title: 'Marketplace', to: { name: 'marketplace' }, class: 'text-primary' },
            { title: property.title },
          ]"
        />
      </VContainer> -->

      <!-- Hero Section with Full-Width Image -->
      <section class="property-hero">
        <div class="hero-image-wrapper">
          <VImg :src="imageSrc" cover class="hero-image" />
          <div class="hero-overlay" />
          <VContainer class="hero-content">
            <VRow>
              <VCol cols="12" md="8" lg="7">
                <!-- Property Type Badge -->
                <VChip color="success" size="small" class="mb-4">
                  <VIcon icon="tabler-building" size="16" class="me-1" />
                  {{ formattedPropertyType }}
                </VChip>

                <!-- Title -->
                <h1 class="hero-title text-white font-weight-bold">
                  {{ property.title }}
                </h1>
              </VCol>
            </VRow>
          </VContainer>
        </div>
      </section>

      <!-- Main Content -->
      <VContainer class="py-8">
        <VRow>
          <!-- Left Column - Details -->
          <VCol cols="12" md="8">
            <!-- Description -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Property Description</VCardTitle>
              </VCardItem>
              <VCardText>
                <p class="text-body-1">
                  {{ property.description }}
                </p>
              </VCardText>
            </VCard>

            <!-- Property Details -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Property Details</VCardTitle>
              </VCardItem>
              <VCardText>
                <VRow>
                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="primary" variant="tonal" class="me-3">
                        <VIcon icon="tabler-ruler" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Size
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ (property.size_sqm || 0).toLocaleString() }} sqm
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="success" variant="tonal" class="me-3">
                        <VIcon :icon="SaudiRiyal" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Monthly Rent
                        </div>
                        <div class="text-h6 font-weight-medium text-success">
                          {{ formatCurrency(property.monthly_rent) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="info" variant="tonal" class="me-3">
                        <VIcon icon="tabler-coin" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Deposit Amount
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ formatCurrency(property.deposit_amount || 0) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="warning" variant="tonal" class="me-3">
                        <VIcon icon="tabler-calendar" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Lease Term
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ property.lease_term_months }} months
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="secondary" variant="tonal" class="me-3">
                        <VIcon icon="tabler-calendar-check" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Available From
                        </div>
                        <div class="text-h6 font-weight-medium">
                          {{ formatDate(property.available_from) }}
                        </div>
                      </div>
                    </div>
                  </VCol>

                  <VCol cols="12" sm="6">
                    <div class="detail-item">
                      <VAvatar size="40" color="primary" variant="tonal" class="me-3">
                        <VIcon icon="tabler-building" size="20" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="text-sm text-medium-emphasis mb-1">
                          Property Type
                        </div>
                        <div class="text-h6 font-weight-medium text-capitalize">
                          {{ formattedPropertyType }}
                        </div>
                      </div>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Location -->
            <VCard elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Location</VCardTitle>
              </VCardItem>
              <VCardText>
                <div class="d-flex align-start gap-4">
                  <VAvatar size="48" color="error" variant="tonal">
                    <VIcon icon="tabler-map-pin" size="24" />
                  </VAvatar>
                  <div class="flex-grow-1">
                    <div class="text-body-1 font-weight-medium mb-1">
                      {{ property.address }}
                    </div>
                    <div class="text-sm text-medium-emphasis">
                      {{ property.city }}, {{ property.state_province }} {{ property.postal_code }}
                    </div>
                  </div>
                </div>
              </VCardText>
            </VCard>

            <!-- Amenities -->
            <!-- <VCard v-if="property.amenities && property.amenities.length > 0" class="mb-6">
              <VCardTitle>Amenities</VCardTitle>
              <VCardText>
                <div class="d-flex flex-wrap gap-2">
                  <VChip v-for="amenity in property.amenities" :key="amenity" color="primary" variant="tonal">
                    <VIcon icon="tabler-check" class="me-1" size="16" />
                    {{ amenity }}
                  </VChip>
                </div>
              </VCardText>
            </VCard> -->

            <!-- Broker Contact -->
            <VCard v-if="property.broker" elevation="2" class="mb-6 rounded-lg">
              <VCardItem>
                <VCardTitle class="text-h5">Listed By</VCardTitle>
              </VCardItem>
              <VCardText>
                <div class="d-flex align-start gap-4">
                  <VAvatar size="64" color="primary" variant="tonal">
                    <VIcon icon="tabler-user" size="32" />
                  </VAvatar>
                  <div class="flex-grow-1">
                    <div class="text-h6 font-weight-medium mb-2">
                      {{ property.broker.name }}
                    </div>
                    <div class="d-flex align-center gap-2 mb-1">
                      <VIcon icon="tabler-mail" size="18" class="text-medium-emphasis" />
                      <span class="text-body-2 text-medium-emphasis">
                        {{ property.broker.email }}
                      </span>
                    </div>
                    <div v-if="property.broker.phone" class="d-flex align-center gap-2">
                      <VIcon icon="tabler-phone" size="18" class="text-medium-emphasis" />
                      <span class="text-body-2 text-medium-emphasis">
                        {{ property.broker.phone }}
                      </span>
                    </div>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Right Column - Inquiry Form -->
          <VCol cols="12" md="4">
            <div class="sticky-form-wrapper">
              <InquiryForm inquiry-type="property" :item-id="propertyId" title="Interested in This Property?" />
            </div>
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
  position: relative;
  margin-bottom: 2rem;
  overflow: hidden;
  padding-top: 8rem;
}

.hero-image-wrapper {
  position: relative;
  min-height: 500px;
  display: flex;
  align-items: flex-end;
  padding-bottom: 3rem;

  @media (max-width: 959px) {
    min-height: 400px;
    padding-bottom: 2rem;
  }

  @media (max-width: 599px) {
    min-height: 350px;
    padding-bottom: 1.5rem;
  }
}

.hero-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  object-fit: cover;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 2;
}

.hero-content {
  position: relative;
  z-index: 3;
  padding: 0;
  width: 100%;
}

.hero-title {
  font-size: 2.5rem;
  line-height: 1.2;
  color: white;

  @media (max-width: 959px) {
    font-size: 2rem;
  }

  @media (max-width: 599px) {
    font-size: 1.75rem;
  }
}

.sticky-form-wrapper {
  position: sticky;
  top: 100px;

  @media (max-width: 959px) {
    position: relative;
    top: 0;
  }
}

.detail-item {
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
</style>

