<script setup lang="ts">
import { propertyApi, type Property } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  property?: Property | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'propertyDeleted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isDeleting = ref(false)

// Computed for v-model dialog
const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const onConfirm = async () => {
  if (!props.property)
    return

  try {
    isDeleting.value = true

    const response = await propertyApi.deleteProperty(props.property.id)

    if (response.success) {
      emit('propertyDeleted')
      dialogValue.value = false
    }
  }
  catch (error) {
    console.error('Error deleting property:', error)
  }
  finally {
    isDeleting.value = false
  }
}

const onCancel = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="500"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="tabler-alert-circle" color="error" size="24" />
        Confirm Deletion
      </VCardTitle>

      <VCardText>
        <p class="mb-2">
          Are you sure you want to delete this property?
        </p>
        <p class="font-weight-medium">
          {{ property?.title }}
        </p>
        <p class="text-sm text-error mt-2">
          This action cannot be undone.
        </p>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isDeleting"
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="error"
          :loading="isDeleting"
          @click="onConfirm"
        >
          Delete Property
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

