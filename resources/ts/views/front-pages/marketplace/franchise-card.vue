<script setup lang="ts">
import type { Franchise } from '@/services/api'
import { formatCurrency } from '@/@core/utils/formatters'

interface Props {
  franchise: Franchise
}

defineProps<Props>()
</script>

<template>
  <VCard class="franchise-card h-100">
    <VImg
      :src="franchise.logo || '/images/placeholder.png'"
      height="200"
      cover
    />

    <VCardTitle>{{ franchise.brand_name }}</VCardTitle>

    <VCardText>
      <VChip size="small" color="primary" class="mb-2">
        {{ franchise.industry }}
      </VChip>

      <p class="text-sm mb-2">
        {{ franchise.description?.substring(0, 100) }}...
      </p>

      <div class="d-flex justify-space-between text-sm">
        <span><strong>Fee:</strong> {{ formatCurrency(franchise.franchise_fee || 0) }}</span>
        <span><strong>Units:</strong> {{ franchise.total_units }}</span>
      </div>

      <div class="text-sm mt-2">
        <strong>Location:</strong> {{ franchise.headquarters_city }}, {{ franchise.headquarters_country }}
      </div>
    </VCardText>

    <VCardActions>
      <VBtn
        color="primary"
        variant="tonal"
        block
        :to="{ name: 'marketplace-franchise-details', params: { id: franchise.id } }"
      >
        Learn More
      </VBtn>
    </VCardActions>
  </VCard>
</template>

<style lang="scss" scoped>
.franchise-card {
  display: flex;
  flex-direction: column;

  .v-card-text {
    flex: 1;
  }
}
</style>

