<script setup lang="ts">
interface Props {
  activeTab: string
  selectedIndustry: string
  selectedPropertyType: string
  selectedCountry: string
  selectedCity: string
  minFranchiseFee?: number
  maxFranchiseFee?: number
  minRent?: number
  maxRent?: number
}

interface Emit {
  (e: 'update:selectedIndustry', value: string): void
  (e: 'update:selectedPropertyType', value: string): void
  (e: 'update:selectedCountry', value: string): void
  (e: 'update:selectedCity', value: string): void
  (e: 'update:minFranchiseFee', value: number | undefined): void
  (e: 'update:maxFranchiseFee', value: number | undefined): void
  (e: 'update:minRent', value: number | undefined): void
  (e: 'update:maxRent', value: number | undefined): void
}

defineProps<Props>()
const emit = defineEmits<Emit>()
</script>

<template>
  <VCard>
    <VCardTitle>Filters</VCardTitle>
    <VCardText>
      <VSelect
        v-if="activeTab === 'franchises'"
        :model-value="selectedIndustry"
        label="Industry"
        :items="[
          { title: 'Food & Beverage', value: 'food_beverage' },
          { title: 'Retail', value: 'retail' },
          { title: 'Services', value: 'services' },
          { title: 'Healthcare', value: 'healthcare' },
          { title: 'Education', value: 'education' },
        ]"
        clearable
        class="mb-4"
        @update:model-value="emit('update:selectedIndustry', $event)"
      />

      <VSelect
        v-if="activeTab === 'properties'"
        :model-value="selectedPropertyType"
        label="Property Type"
        :items="[
          { title: 'Retail', value: 'retail' },
          { title: 'Office', value: 'office' },
          { title: 'Kiosk', value: 'kiosk' },
          { title: 'Food Court', value: 'food_court' },
          { title: 'Standalone', value: 'standalone' },
        ]"
        clearable
        class="mb-4"
        @update:model-value="emit('update:selectedPropertyType', $event)"
      />

      <template v-if="activeTab === 'franchises'">
        <VTextField
          :model-value="minFranchiseFee"
          label="Min Franchise Fee"
          type="number"
          clearable
          class="mb-4"
          @update:model-value="emit('update:minFranchiseFee', $event ? Number($event) : undefined)"
        />

        <VTextField
          :model-value="maxFranchiseFee"
          label="Max Franchise Fee"
          type="number"
          clearable
          class="mb-4"
          @update:model-value="emit('update:maxFranchiseFee', $event ? Number($event) : undefined)"
        />
      </template>

      <template v-if="activeTab === 'properties'">
        <VTextField
          :model-value="minRent"
          label="Min Monthly Rent"
          type="number"
          clearable
          class="mb-4"
          @update:model-value="emit('update:minRent', $event ? Number($event) : undefined)"
        />

        <VTextField
          :model-value="maxRent"
          label="Max Monthly Rent"
          type="number"
          clearable
          class="mb-4"
          @update:model-value="emit('update:maxRent', $event ? Number($event) : undefined)"
        />
      </template>
    </VCardText>
  </VCard>
</template>

