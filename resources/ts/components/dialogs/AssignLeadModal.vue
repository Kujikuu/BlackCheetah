<script setup lang="ts">
import { leadApi, usersApi } from '@/services/api'

interface SalesAssociate {
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

const selectedAssociateId = ref<number | null>(props.currentAssigneeId || null)
const salesAssociates = ref<SalesAssociate[]>([])
const isLoading = ref(false)
const isLoadingAssociates = ref(false)

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    selectedAssociateId.value = props.currentAssigneeId || null
}

const fetchSalesAssociates = async () => {
  isLoadingAssociates.value = true

  try {
    const response = await usersApi.getSalesAssociates()

    if (response.success) {
      // API returns paginated data, handle both array and paginated response
      const associatesArray = Array.isArray(response.data) ? response.data : response.data.data || []
      
      // Transform the data to match the expected format
      salesAssociates.value = associatesArray.map((associate: any) => ({
        id: associate.id,
        name: associate.name,
        email: associate.email,
      }))
    }
    else {
      salesAssociates.value = []
    }
  }
  catch (error) {
    console.error('Error fetching sales associates:', error)
    salesAssociates.value = []
  }
  finally {
    isLoadingAssociates.value = false
  }
}

const assignLead = async () => {
  if (!props.leadId || selectedAssociateId.value === null)
    return

  isLoading.value = true

  try {
    await leadApi.assignLead(props.leadId, selectedAssociateId.value!)

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

// Fetch sales associates when dialog opens
watch(() => props.isDialogVisible, newVal => {
  if (newVal) {
    fetchSalesAssociates()
    selectedAssociateId.value = props.currentAssigneeId || null
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
          v-model="selectedAssociateId"
          :items="salesAssociates"
          item-title="name"
          item-value="id"
          label="Sales Associate"
          placeholder="Select a sales associate"
          variant="outlined"
          :loading="isLoadingAssociates"
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
          :disabled="selectedAssociateId === null"
          @click="assignLead"
        >
          Assign Lead
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
