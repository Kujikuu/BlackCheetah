<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitProduct } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  selectedProduct: UnitProduct | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'productUpdated', product: UnitProduct): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const editableProduct = ref<UnitProduct | null>(null)

// Watch for changes in selectedProduct and create a copy for editing
watch(() => props.selectedProduct, (newProduct) => {
  if (newProduct) {
    editableProduct.value = { ...newProduct }
  }
}, { immediate: true })

const handleSaveProduct = () => {
  if (editableProduct.value) {
    emit('productUpdated', editableProduct.value)
    dialogValue.value = false
  }
}

const handleClose = () => {
  dialogValue.value = false
}

// Format currency helper
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'SAR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Edit Product Inventory">
      <VCardText
        v-if="editableProduct"
        class="pa-6"
      >
        <VRow>
          <VCol cols="12">
            <div class="text-body-2 text-disabled mb-3">
              Product: <span class="font-weight-medium text-high-emphasis">{{ editableProduct.name }}</span>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model.number="editableProduct.stock"
              label="Stock Quantity"
              type="number"
              min="0"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Unit Price
            </div>
            <div class="text-h6">
              {{ formatCurrency(editableProduct.unitPrice) }}
            </div>
          </VCol>
          <VCol cols="12">
            <div class="text-caption text-disabled">
              Note: You can only update the stock quantity. Product details like name, price, and category are managed
              at
              the franchise level.
            </div>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="editableProduct?.stock === undefined || editableProduct?.stock < 0"
          @click="handleSaveProduct"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
