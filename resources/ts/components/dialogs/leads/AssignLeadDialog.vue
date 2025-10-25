<script setup lang="ts">
import { leadApi, usersApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useAssignLeadValidation } from '@/validation/leadsValidation'

interface Broker {
  id: number
  name: string
  email: string
}

interface Props {
  isDialogVisible: boolean
  leadId: number | null
  currentAssigneeId?: number | null
  brokers?: any[]
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'leadAssigned'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useAssignLeadValidation()

const selectedAssociateId = ref<number | null>(props.currentAssigneeId || null)
const brokers = ref<Broker[]>([])
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
    const response = await usersApi.getBrokers()

    if (response.success) {
      // API returns paginated data, handle both array and paginated response
      const associatesArray = Array.isArray(response.data) ? response.data : response.data.data || []
      
      // Transform the data to match the expected format
      brokers.value = associatesArray.map((associate: any) => ({
        id: associate.id,
        name: associate.name,
        email: associate.email,
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
    isLoadingAssociates.value = false
  }
}

const assignLead = async () => {
  if (!props.leadId)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true

  try {
    await leadApi.assignLead(props.leadId, selectedAssociateId.value!)

    emit('leadAssigned')
    updateModelValue(false)
  }
  catch (error: any) {
    console.error('Error assigning lead:', error)
    setBackendErrors(error)
  }
  finally {
    isLoading.value = false
  }
}

// Fetch brokers when dialog opens
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
        <VForm ref="formRef">
          <VSelect
            v-model="selectedAssociateId"
            :items="brokers"
            item-title="name"
            item-value="id"
            label="Broker"
            placeholder="Select a broker"
            variant="outlined"
            :loading="isLoadingAssociates"
            :disabled="isLoading"
            :rules="validationRules.assignedTo"
            :error-messages="backendErrors.assignedTo"
            @update:model-value="clearError('assignedTo')"
          >
            <template #item="{ props: itemProps, item }">
              <VListItem v-bind="itemProps">
                <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
                <VListItemSubtitle>{{ item.raw.email }}</VListItemSubtitle>
              </VListItem>
            </template>
          </VSelect>
        </VForm>
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
