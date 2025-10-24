<script setup lang="ts">
import { marketplaceApi, type Franchise, type Property, type MarketplaceFilters as MarketplaceFiltersType } from '@/services/api'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import Footer from '@/views/front-pages/front-page-footer.vue'
import FranchiseCard from '@/views/front-pages/marketplace/franchise-card.vue'
import PropertyCard from '@/views/front-pages/marketplace/property-card.vue'
import MarketplaceFilters from '@/views/front-pages/marketplace/marketplace-filters.vue'
import { useConfigStore } from '@core/stores/config'

const store = useConfigStore()

store.skin = 'default'

// Tab state
const activeTab = ref('franchises')

// Filters
const searchQuery = ref('')
const selectedIndustry = ref('')
const selectedCountry = ref('')
const selectedCity = ref('')
const selectedPropertyType = ref('')
const minFranchiseFee = ref<number>()
const maxFranchiseFee = ref<number>()
const minRent = ref<number>()
const maxRent = ref<number>()

// Data
const franchises = ref<Franchise[]>([])
const properties = ref<Property[]>([])
const isLoading = ref(false)

// Pagination
const currentPage = ref(1)
const perPage = ref(12)
const total = ref(0)

// Fetch franchises
const fetchFranchises = async () => {
  try {
    isLoading.value = true

    const filters: MarketplaceFiltersType = {
      page: currentPage.value,
      per_page: perPage.value,
    }

    if (searchQuery.value)
      filters.search = searchQuery.value
    if (selectedIndustry.value)
      filters.industry = selectedIndustry.value
    if (selectedCountry.value)
      filters.country = selectedCountry.value
    if (selectedCity.value)
      filters.city = selectedCity.value
    if (minFranchiseFee.value)
      filters.min_franchise_fee = minFranchiseFee.value
    if (maxFranchiseFee.value)
      filters.max_franchise_fee = maxFranchiseFee.value

    const response = await marketplaceApi.getFranchises(filters)

    if (response.success && response.data) {
      franchises.value = response.data.data
      total.value = response.data.total
    }
  }
  catch (error) {
    console.error('Error fetching franchises:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Fetch properties
const fetchProperties = async () => {
  try {
    isLoading.value = true

    const filters: MarketplaceFiltersType = {
      page: currentPage.value,
      per_page: perPage.value,
    }

    if (searchQuery.value)
      filters.search = searchQuery.value
    if (selectedPropertyType.value)
      filters.property_type = selectedPropertyType.value
    if (selectedCountry.value)
      filters.country = selectedCountry.value
    if (selectedCity.value)
      filters.city = selectedCity.value
    if (minRent.value)
      filters.min_rent = minRent.value
    if (maxRent.value)
      filters.max_rent = maxRent.value

    const response = await marketplaceApi.getProperties(filters)

    if (response.success && response.data) {
      properties.value = response.data.data
      total.value = response.data.total
    }
  }
  catch (error) {
    console.error('Error fetching properties:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Watch tab changes
watch(activeTab, (newTab) => {
  currentPage.value = 1
  if (newTab === 'franchises')
    fetchFranchises()
  else
    fetchProperties()
})

// Watch filters
watch([searchQuery, selectedIndustry, selectedCountry, selectedCity, selectedPropertyType, minFranchiseFee, maxFranchiseFee, minRent, maxRent], () => {
  currentPage.value = 1
  if (activeTab.value === 'franchises')
    fetchFranchises()
  else
    fetchProperties()
})

// Initial load
onMounted(() => {
  fetchFranchises()
})

</script>

<template>
  <div class="marketplace-page">
    <Navbar />

    <!-- Hero Section -->
    <section class="marketplace-hero">
      <VContainer>
        <div class="text-center py-16">
          <h1 class="text-h2 mb-4">
            Explore Franchise Opportunities
          </h1>
          <p class="text-h6 mb-8">
            Find the perfect franchise opportunity or commercial property for your business
          </p>

          <VTextField v-model="searchQuery" variant="outlined" placeholder="Search franchises or properties..."
            prepend-inner-icon="tabler-search" class="marketplace-search mx-auto" density="comfortable"
            style="max-width: 600px;" />
        </div>
      </VContainer>
    </section>

    <!-- Main Content -->
    <VContainer class="py-8">
      <VRow>
        <!-- Filters Sidebar -->
        <VCol cols="12" md="3">
          <MarketplaceFilters
            :active-tab="activeTab"
            v-model:selected-industry="selectedIndustry"
            v-model:selected-property-type="selectedPropertyType"
            v-model:selected-country="selectedCountry"
            v-model:selected-city="selectedCity"
            v-model:min-franchise-fee="minFranchiseFee"
            v-model:max-franchise-fee="maxFranchiseFee"
            v-model:min-rent="minRent"
            v-model:max-rent="maxRent"
          />
        </VCol>

        <!-- Main Content Area -->
        <VCol cols="12" md="9">
          <VTabs v-model="activeTab" class="mb-6">
            <VTab value="franchises">
              Franchise Opportunities
            </VTab>
            <VTab value="properties">
              Available Properties
            </VTab>
          </VTabs>

          <VProgressLinear v-if="isLoading" indeterminate color="primary" class="mb-4" />

          <!-- Franchises Grid -->
          <VWindow v-model="activeTab">
            <VWindowItem value="franchises">
              <VRow v-if="franchises.length > 0">
                <VCol v-for="franchise in franchises" :key="franchise.id" cols="12" sm="6" md="4">
                  <FranchiseCard :franchise="franchise" />
                </VCol>
              </VRow>

              <div v-else class="text-center py-8">
                <p class="text-h6">
                  No franchises found
                </p>
              </div>
            </VWindowItem>

            <!-- Properties Grid -->
            <VWindowItem value="properties">
              <VRow v-if="properties.length > 0">
                <VCol v-for="property in properties" :key="property.id" cols="12" sm="6" md="4">
                  <PropertyCard :property="property" />
                </VCol>
              </VRow>

              <div v-else class="text-center py-8">
                <p class="text-h6">
                  No properties found
                </p>
              </div>
            </VWindowItem>
          </VWindow>

          <!-- Pagination -->
          <VPagination v-if="total > perPage" v-model="currentPage" :length="Math.ceil(total / perPage)" class="mt-6"
            @update:model-value="activeTab === 'franchises' ? fetchFranchises() : fetchProperties()" />
        </VCol>
      </VRow>
    </VContainer>

    <Footer />
  </div>
</template>

<style lang="scss" scoped>
.marketplace-page {
  background-color: rgb(var(--v-theme-background));
}

.marketplace-hero {
  background-color: rgb(var(--v-theme-surface));
  min-height: 300px;
  display: flex;
  align-items: center;
  padding: 80px 0;
}

.marketplace-search {
  :deep(.v-field) {
    border-radius: 50px;
  }
}
</style>
