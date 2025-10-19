<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
  title?: string
  message?: string
  entityName?: string
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'confirm': []
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Confirm Delete',
  message: 'Are you sure you want to delete this item? This action cannot be undone.',
  entityName: 'item'
})

const emit = defineEmits<Emits>()

const handleConfirm = () => {
  emit('confirm')
  emit('update:isDialogVisible', false)
}

const handleCancel = () => {
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard :title="title">
      <VCardText>
        {{ message }}
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="error"
          @click="handleConfirm"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
