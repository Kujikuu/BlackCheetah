<script setup lang="ts">
import { computed } from 'vue'

interface Product {
  id: number
  name: string
  category: string
  description: string
  unitPrice: number
  stock: number
  status: string
}

interface Props {
  isDialogVisible: boolean
  selectedProduct: Product | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'SAR',
  }).format(amount)
}

const resolveStatusVariant = (status: string) => {
  if (status === 'active' || status === 'completed' || status === 'approved' || status === 'working')
    return 'success'
  if (status === 'pending' || status === 'in_progress')
    return 'warning'
  if (status === 'inactive' || status === 'cancelled' || status === 'rejected')
    return 'error'

  return 'default'
}

const handleClose = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Product Details">
      <VCardText
        v-if="selectedProduct"
        class="pa-6"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Product Name
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedProduct.name }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Category
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedProduct.category }}
            </div>
          </VCol>
          <VCol cols="12">
            <div class="text-body-2 text-disabled mb-1">
              Description
            </div>
            <div class="text-body-1">
              {{ selectedProduct.description }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Unit Price
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ formatCurrency(selectedProduct.unitPrice) }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Stock
            </div>
            <VChip
              :color="selectedProduct.stock === 0 ? 'error' : selectedProduct.stock <= 10 ? 'warning' : 'success'"
              size="small"
              label
            >
              {{ selectedProduct.stock }} units
            </VChip>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Status
            </div>
            <VChip
              :color="resolveStatusVariant(selectedProduct.status)"
              size="small"
              label
              class="text-capitalize"
            >
              {{ selectedProduct.status }}
            </VChip>
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
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
