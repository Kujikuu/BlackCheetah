<script setup lang="ts">
import { computed } from 'vue'

interface SaleData {
  id: string | number
  product: string
  date: string
  unitPrice: number
  quantity: number
  sale: number
  referenceNumber?: string
}

interface Props {
  isDialogVisible: boolean
  saleData: SaleData | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const formatCurrency = (amount: number) => {
  return Number(amount).toFixed(2) + ' SAR'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard
      v-if="props.saleData"
      title="Sale Details"
    >
      <VCardText>
        <VChip
          color="success"
          size="small"
          variant="tonal"
        >
          {{ props.saleData.referenceNumber || `REV-${String(props.saleData.id).padStart(6, '0')}` }}
        </VChip>
      </VCardText>

      <VDivider class="mb-4" />

      <VCardText>
        <VRow>
          <!-- Product Information -->
          <VCol cols="12">
            <h6 class="text-h6 mb-4 text-primary">
              <VIcon
                icon="tabler-shopping-cart"
                class="me-2"
              />
              Product Information
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Product Name
              </div>
              <div class="font-weight-medium text-h6">
                {{ props.saleData.product }}
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Sale Date
              </div>
              <div class="font-weight-medium">
                {{ formatDate(props.saleData.date) }}
              </div>
            </div>
          </VCol>

          <!-- Financial Details -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-4 text-primary">
              <VIcon
                icon="tabler-report-money"
                class="me-2"
              />
              Financial Details
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VCard
              variant="tonal"
              color="info"
              class="pa-4"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-tag"
                  size="32"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Unit Price
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ formatCurrency(props.saleData.unitPrice) }}
                </div>
              </div>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VCard
              variant="tonal"
              color="warning"
              class="pa-4"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-stack"
                  size="32"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Quantity
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ props.saleData.quantity }}
                </div>
              </div>
            </VCard>
          </VCol>

          <VCol
            cols="12"
            md="4"
          >
            <VCard
              variant="tonal"
              color="success"
              class="pa-4"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-coins"
                  size="32"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Total Sale
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ formatCurrency(props.saleData.sale) }}
                </div>
              </div>
            </VCard>
          </VCol>

          <!-- Calculation Breakdown -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-4 text-primary">
              <VIcon
                icon="tabler-calculator"
                class="me-2"
              />
              Calculation
            </h6>
          </VCol>

          <VCol cols="12">
            <VTable density="compact">
              <tbody>
                <tr>
                  <td class="font-weight-medium">
                    Unit Price:
                  </td>
                  <td class="text-end">
                    {{ formatCurrency(props.saleData.unitPrice) }}
                  </td>
                </tr>
                <tr>
                  <td class="font-weight-medium">
                    Quantity:
                  </td>
                  <td class="text-end">
                    × {{ props.saleData.quantity }}
                  </td>
                </tr>
                <tr class="bg-success-lighten-5">
                  <td class="font-weight-bold text-success">
                    Total Amount:
                  </td>
                  <td class="text-end font-weight-bold text-success">
                    {{ formatCurrency(props.saleData.sale) }}
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="dialogValue = false"
        >
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
