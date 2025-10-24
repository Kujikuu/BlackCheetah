<script setup lang="ts">
import type { Property } from '@/services/api'
import { formatCurrency } from '@/@core/utils/formatters'

interface Props {
  property: Property
}

defineProps<Props>()
</script>

<template>
  <VCard class="property-card h-100">
    <VImg
      :src="property.images?.[0] || '/images/placeholder.png'"
      height="200"
      cover
    />

    <VCardTitle>{{ property.title }}</VCardTitle>

    <VCardText>
      <VChip size="small" color="success" class="mb-2">
        {{ property.property_type }}
      </VChip>

      <p class="text-sm mb-2">
        {{ property.description.substring(0, 100) }}...
      </p>

      <div class="d-flex justify-space-between text-sm mb-2">
        <span><strong>Rent:</strong> {{ formatCurrency(property.monthly_rent) }}/mo</span>
        <span><strong>Size:</strong> {{ property.size_sqm }} m²</span>
      </div>

      <div class="text-sm">
        <strong>Location:</strong> {{ property.city }}, Saudi Arabia
      </div>
    </VCardText>

    <VCardActions>
      <VBtn
        color="primary"
        variant="tonal"
        block
        :to="{ name: 'marketplace-property-details', params: { id: property.id } }"
      >
        View Details
      </VBtn>
    </VCardActions>
  </VCard>
</template>

<style lang="scss" scoped>
.property-card {
  display: flex;
  flex-direction: column;

  .v-card-text {
    flex: 1;
  }
}
</style>

