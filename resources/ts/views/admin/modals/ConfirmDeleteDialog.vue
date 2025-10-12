<script setup lang="ts">
interface Props {
  isDialogOpen: boolean
  userName?: string
  userType?: string
}

interface Emit {
  (e: 'update:isDialogOpen', value: boolean): void
  (e: 'confirm'): void
}

const props = withDefaults(defineProps<Props>(), {
  userName: 'this user',
  userType: 'user',
})

const emit = defineEmits<Emit>()

const handleConfirm = () => {
  emit('confirm')
  emit('update:isDialogOpen', false)
}

const handleCancel = () => {
  emit('update:isDialogOpen', false)
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogOpen"
    max-width="500"
    @update:model-value="(val: boolean) => emit('update:isDialogOpen', val)"
  >
    <VCard>
      <VCardText class="text-center pa-8">
        <VIcon
          icon="tabler-alert-circle"
          size="64"
          color="error"
          class="mb-4"
        />

        <h3 class="text-h5 mb-2">
          Confirm Deletion
        </h3>

        <p class="text-body-1 mb-6">
          Are you sure you want to delete <strong>{{ props.userName }}</strong>?
          <br>
          This action cannot be undone.
        </p>

        <div class="d-flex gap-3 justify-center">
          <VBtn
            variant="outlined"
            color="secondary"
            @click="handleCancel"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            @click="handleConfirm"
          >
            Delete {{ props.userType }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
