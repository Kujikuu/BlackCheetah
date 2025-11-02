<script setup lang="ts">
import type { Property } from '@/services/api'
import { formatCurrency } from '@/@core/utils/formatters'
import placeholderImage from '@images/placholder.png'

interface Props {
  property: Property
}

const props = defineProps<Props>()

// Format property type for display
const formattedPropertyType = computed(() => {
  const type = props.property.property_type
  if (!type) return ''
  
  // Map property types to display names
  const typeMap: Record<string, string> = {
    retail: 'Retail',
    office: 'Office',
    kiosk: 'Kiosk',
    food_court: 'Food Court',
    standalone: 'Standalone',
  }
  
  return typeMap[type.toLowerCase()] || type.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
  ).join(' ')
})

// Compute image source with proper fallback
const imageSrc = computed(() => {
  // Handle case where images might be null, undefined, or not an array
  if (!props.property.images) {
    return placeholderImage
  }

  // Handle case where images might be a string (JSON)
  let imagesArray = props.property.images
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
</script>

<template>
  <VCard class="property-card h-100">
    <VImg :src="imageSrc" :height="200" cover />

    <VCardTitle>{{ property.title }}</VCardTitle>

    <VCardText>
      <VChip size="small" color="success" class="mb-2">
        {{ formattedPropertyType }}
      </VChip>

      <p class="text-sm mb-2">
        {{ property.description.substring(0, 100) }}...
      </p>

      <div class="d-flex justify-space-between text-sm mb-2">
        <span><strong>Rent:</strong> {{ formatCurrency(property.monthly_rent) }}/mo</span>
        <span><strong>Size:</strong> {{ property.size_sqm }} sqm</span>
      </div>

      <div class="text-sm">
        <strong>Location:</strong> {{ property.city }}, {{ property.state_province }}
      </div>
    </VCardText>

    <VCardActions>
      <VBtn color="primary" variant="tonal" block
        :to="{ name: 'marketplace-property-details', params: { id: property.id } }">
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
