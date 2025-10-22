<script setup lang="ts">
import { leadApi, usersApi } from '@/services/api'

interface Broker {
  id: number
  name: string
  email: string
}

interface Props {
  isDialogVisible: boolean
  leadId: number | null
  currentAssigneeId?: number | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'leadAssigned'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const selectedBrokerId = ref<number | null>(props.currentAssigneeId || null)
const brokers = ref<Broker[]>([])
const isLoading = ref(false)
const isLoadingBrokers = ref(false)

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    selectedBrokerId.value = props.currentAssigneeId || null
}

const fetchBrokers = async () => {
  isLoadingBrokers.value = true

  try {
    const response = await usersApi.getBrokers()

    if (response.success) {
      // API returns paginated data, handle both array and paginated response
      const brokersArray = Array.isArray(response.data) ? response.data : (response.data as any).data || []
      
      // Transform the data to match the expected format
      brokers.value = brokersArray.map((broker: any) => ({
        id: broker.id,
        name: broker.name,
        email: broker.email,
      }))
    }
    else {
      brokers.value = []
    }
  }
  catch (error) {
    console.error('Error fetching brokers:', error)
    brokers.value = []
  }
  finally {
    isLoadingBrokers.value = false
  }
}

const assignLead = async () => {
  if (!props.leadId || selectedBrokerId.value === null)
    return

  isLoading.value = true

  try {
      await leadApi.assignLead(props.leadId, selectedBrokerId.value!)

    emit('leadAssigned')
    updateModelValue(false)
  }
  catch (error) {
    console.error('Error assigning lead:', error)
  }
  finally {
    isLoading.value = false
  }
}

  // Fetch brokers when dialog opens
watch(() => props.isDialogVisible, newVal => {
  if (newVal) {
    fetchBrokers()
    selectedBrokerId.value = props.currentAssigneeId || null
  }
})
</script>

<template>
  <VDialog
    max-width="500"
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>Assign Lead</VCardTitle>
      </VCardItem>

      <VCardText>
        <VSelect
          v-model="selectedBrokerId"
          :items="brokers"
          item-title="name"
          item-value="id"
          label="Broker"
          placeholder="Select a broker"
          variant="outlined"
          :loading="isLoadingBrokers"
          :disabled="isLoading"
        >
          <template #item="{ props: itemProps, item }">
            <VListItem v-bind="itemProps">
              <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
              <VListItemSubtitle>{{ item.raw.email }}</VListItemSubtitle>
            </VListItem>
          </template>
        </VSelect>
      </VCardText>

      <VCardText class="d-flex align-center justify-end gap-2">
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isLoading"
          @click="updateModelValue(false)"
        >
          Cancel
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          :loading="isLoading"
          :disabled="selectedBrokerId === null"
          @click="assignLead"
        >
          Assign Lead
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
