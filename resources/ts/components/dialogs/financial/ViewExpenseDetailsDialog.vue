<script setup lang="ts">
import { computed } from 'vue'

interface ExpenseData {
  id: string | number
  expenseCategory: string
  date: string
  amount: number
  description?: string
  vendorName?: string
  referenceNumber?: string
}

interface Props {
  isDialogVisible: boolean
  expenseData: ExpenseData | null
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
      v-if="props.expenseData"
      title="Expense Details"
    >
      <VCardText>
        <VChip
          color="error"
          size="small"
          variant="tonal"
        >
          {{ props.expenseData.referenceNumber || `EXP-${String(props.expenseData.id).padStart(6, '0')}` }}
        </VChip>
      </VCardText>

      <VDivider class="mb-4" />

      <VCardText>
        <VRow>
          <!-- Expense Information -->
          <VCol cols="12">
            <h6 class="text-h6 mb-4 text-primary">
              <VIcon
                icon="tabler-receipt"
                class="me-2"
              />
              Expense Information
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Category
              </div>
              <VChip
                color="error"
                size="small"
                variant="tonal"
                class="text-capitalize"
              >
                {{ props.expenseData.expenseCategory }}
              </VChip>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Expense Date
              </div>
              <div class="font-weight-medium">
                {{ formatDate(props.expenseData.date) }}
              </div>
            </div>
          </VCol>

          <VCol
            v-if="props.expenseData.vendorName"
            cols="12"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Vendor
              </div>
              <div class="font-weight-medium">
                {{ props.expenseData.vendorName }}
              </div>
            </div>
          </VCol>

          <VCol cols="12">
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Description
              </div>
              <div class="font-weight-medium">
                {{ props.expenseData.description || 'No description provided' }}
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
              Amount
            </h6>
          </VCol>

          <VCol cols="12">
            <VCard
              variant="tonal"
              color="error"
              class="pa-6"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-currency-dollar"
                  size="48"
                  class="mb-3"
                />
                <div class="text-body-1 text-medium-emphasis mb-2">
                  Expense Amount
                </div>
                <div class="text-h4 font-weight-bold">
                  {{ formatCurrency(props.expenseData.amount) }}
                </div>
              </div>
            </VCard>
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
