<script setup lang="ts">
import { ref, computed } from 'vue'
import type { UnitProduct } from '@/services/api/franchisee-dashboard'

interface InventoryForm {
  quantity: number
  reorderLevel: number
}

interface Props {
  isDialogVisible: boolean
  availableFranchiseProducts: UnitProduct[]
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'productAdded', data: { productId: number; quantity: number; reorderLevel: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const selectedFranchiseProduct = ref<number | null>(null)
const inventoryForm = ref<InventoryForm>({
  quantity: 0,
  reorderLevel: 5,
})

// Computed selected product preview
const selectedProductPreview = computed(() => {
  if (!selectedFranchiseProduct.value) return null
  
  return props.availableFranchiseProducts.find(p => p.id === selectedFranchiseProduct.value)
})

const handleAddProductToInventory = () => {
  if (!selectedFranchiseProduct.value) return
  
  emit('productAdded', {
    productId: selectedFranchiseProduct.value,
    quantity: Number.parseInt(inventoryForm.value.quantity.toString()),
    reorderLevel: Number.parseInt(inventoryForm.value.reorderLevel.toString()),
  })
  
  // Reset form
  selectedFranchiseProduct.value = null
  inventoryForm.value = {
    quantity: 0,
    reorderLevel: 5,
  }
  
  dialogValue.value = false
}

const handleClose = () => {
  dialogValue.value = false
  // Reset form on close
  selectedFranchiseProduct.value = null
  inventoryForm.value = {
    quantity: 0,
    reorderLevel: 5,
  }
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Add Product to Inventory">
      <VCardText class="pa-6">
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="selectedFranchiseProduct"
              label="Select Product"
              :items="props.availableFranchiseProducts"
              item-title="name"
              item-value="id"
              required
              placeholder="Choose a product from franchise catalog"
              :no-data-text="props.availableFranchiseProducts.length === 0 ? 'No available franchise products to add' : 'No products match your search'"
            >
              <template #item="{ item, props: itemProps }">
                <VListItem v-bind="itemProps">
                  <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
                  <VListItemSubtitle>
                    {{ item.raw.category }} • SAR {{ item.raw.unitPrice.toFixed(2) }}
                  </VListItemSubtitle>
                </VListItem>
              </template>
            </VSelect>

            <div
              v-if="props.availableFranchiseProducts.length === 0"
              class="text-center py-4"
            >
              <VIcon
                icon="tabler-package-off"
                size="48"
                class="text-disabled mb-2"
              />
              <div class="text-body-2 text-disabled">
                All franchise products are already in this unit's inventory
              </div>
            </div>
          </VCol>

          <!-- Product Preview (when product is selected) -->
          <VCol
            v-if="selectedProductPreview"
            cols="12"
          >
            <VCard
              variant="tonal"
              color="primary"
            >
              <VCardText>
                <div class="d-flex align-center gap-3">
                  <VIcon
                    icon="tabler-package"
                    size="24"
                  />
                  <div>
                    <h6 class="text-h6">
                      {{ selectedProductPreview.name }}
                    </h6>
                    <p class="text-body-2 mb-0">
                      {{ selectedProductPreview.description }}
                    </p>
                    <div class="text-body-2 text-medium-emphasis">
                      Category: {{ selectedProductPreview.category }} • Price: SAR {{
                        selectedProductPreview.unitPrice.toFixed(2) }}
                    </div>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="inventoryForm.quantity"
              label="Initial Stock Quantity"
              type="number"
              min="0"
              required
              placeholder="Enter quantity to add"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="inventoryForm.reorderLevel"
              label="Reorder Level"
              type="number"
              min="0"
              required
              placeholder="Minimum stock alert level"
            />
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
          :disabled="!selectedFranchiseProduct || !inventoryForm.quantity"
          @click="handleAddProductToInventory"
        >
          Add to Inventory
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
