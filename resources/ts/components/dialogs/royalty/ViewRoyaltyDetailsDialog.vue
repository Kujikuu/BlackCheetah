<script setup lang="ts">
import { computed } from 'vue'
import { type RoyaltyRecord } from '@/services/api/royalty'

interface Props {
  isDialogVisible: boolean
  viewedRoyalty: RoyaltyRecord | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'markAsCompleted', royalty: RoyaltyRecord): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'paid': return 'success'
    case 'pending': return 'warning'
    case 'overdue': return 'error'
    default: return 'default'
  }
}

const handleMarkAsCompleted = () => {
  if (props.viewedRoyalty) {
    emit('markAsCompleted', props.viewedRoyalty)
    dialogValue.value = false
  }
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard v-if="props.viewedRoyalty" title="Royalty Details">

      <VCardText>
        <VRow>
          <!-- Basic Information -->
          <VCol cols="12">
            <h6 class="text-h6 mb-4 text-primary">
              Basic Information
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Billing Period
              </div>
              <div class="font-weight-medium">
                {{ props.viewedRoyalty.billing_period }}
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Due Date
              </div>
              <div class="font-weight-medium">
                {{ formatDate(props.viewedRoyalty.due_date) }}
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Franchisee Name
              </div>
              <div class="font-weight-medium text-primary">
                {{ props.viewedRoyalty.franchisee_name }}
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Store Location
              </div>
              <div class="font-weight-medium">
                {{ props.viewedRoyalty.store_location }}
              </div>
            </div>
          </VCol>

          <!-- Financial Information -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-4 text-primary">
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
                  icon="tabler-chart-line"
                  size="32"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Gross Sales
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ (props.viewedRoyalty.gross_sales || 0).toLocaleString() }} SAR
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
              color="primary"
              class="pa-4"
            >
              <div class="text-center">
                <VIcon
                  icon="tabler-percentage"
                  size="32"
                  class="mb-2"
                />
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Royalty Rate
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ props.viewedRoyalty.royalty_percentage }}%
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
                  Royalty Amount
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ (props.viewedRoyalty.amount || 0).toLocaleString() }} SAR
                </div>
              </div>
            </VCard>
          </VCol>

          <!-- Status Information -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-4 text-primary">
              Status Information
            </h6>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-2">
                Payment Status
              </div>
              <VChip
                :color="getStatusColor(props.viewedRoyalty.status)"
                size="large"
                variant="tonal"
                class="text-capitalize"
              >
                <VIcon
                  :icon="props.viewedRoyalty.status === 'paid' ? 'tabler-check'
                    : props.viewedRoyalty.status === 'pending' ? 'tabler-clock' : 'tabler-alert-triangle'"
                  class="me-2"
                />
                {{ props.viewedRoyalty.status }}
              </VChip>
            </div>
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-2">
                Days Until Due
              </div>
              <div class="font-weight-medium">
                {{ Math.ceil((new Date(props.viewedRoyalty.due_date).getTime() - new Date().getTime())
                  / (1000 * 60
                    * 60 * 24)) }} days
              </div>
            </div>
          </VCol>

          <!-- Calculation Breakdown -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <h6 class="text-h6 mb-4 text-primary">
              Calculation Breakdown
            </h6>
          </VCol>

          <VCol cols="12">
            <VTable density="compact">
              <tbody>
                <tr>
                  <td class="font-weight-medium">
                    Gross Sales:
                  </td>
                  <td class="text-end">
                    {{ (props.viewedRoyalty.gross_sales || 0).toLocaleString() }} SAR
                  </td>
                </tr>
                <tr>
                  <td class="font-weight-medium">
                    Royalty Rate:
                  </td>
                  <td class="text-end">
                    {{ props.viewedRoyalty.royalty_percentage }}%
                  </td>
                </tr>
                <tr>
                  <td class="font-weight-medium">
                    Calculation:
                  </td>
                  <td class="text-end">
                    {{ (props.viewedRoyalty.gross_sales || 0).toLocaleString() }} × {{
                      props.viewedRoyalty.royalty_percentage }}%
                  </td>
                </tr>
                <tr class="bg-primary-lighten-5">
                  <td class="font-weight-bold text-primary">
                    Total Royalty Amount:
                  </td>
                  <td class="text-end font-weight-bold text-primary">
                    {{
                      (props.viewedRoyalty.amount || 0).toLocaleString() }} SAR
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
        <VBtn
          v-if="props.viewedRoyalty.status !== 'paid'"
          color="primary"
          @click="handleMarkAsCompleted"
        >
          <VIcon
            icon="tabler-check"
            class="me-2"
          />
          Mark as Completed
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
