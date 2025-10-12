<script setup lang="ts">
import { useClipboard } from '@vueuse/core'

interface Props {
  isDialogOpen: boolean
  userName?: string
}

interface Emit {
  (e: 'update:isDialogOpen', value: boolean): void
  (e: 'confirm', password: string): void
}

const props = withDefaults(defineProps<Props>(), {
  userName: 'User',
})

const emit = defineEmits<Emit>()

const newPassword = ref('')
const isPasswordGenerated = ref(false)
const { copy, copied } = useClipboard()

// Generate random password
const generatePassword = () => {
  const length = 12
  const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'
  let password = ''

  for (let i = 0; i < length; i++)
    password += charset.charAt(Math.floor(Math.random() * charset.length))

  newPassword.value = password
  isPasswordGenerated.value = true
}

// Auto-generate password when dialog opens
watch(() => props.isDialogOpen, newVal => {
  if (newVal && !isPasswordGenerated.value)
    generatePassword()
})

const handleConfirm = () => {
  emit('confirm', newPassword.value)
  emit('update:isDialogOpen', false)
  resetDialog()
}

const handleCancel = () => {
  emit('update:isDialogOpen', false)
  resetDialog()
}

const resetDialog = () => {
  newPassword.value = ''
  isPasswordGenerated.value = false
}

const copyPassword = () => {
  copy(newPassword.value)
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogOpen"
    max-width="500"
    @update:model-value="(val: boolean) => emit('update:isDialogOpen', val)"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>Reset Password</VCardTitle>
        <VCardSubtitle>Generate new password for {{ props.userName }}</VCardSubtitle>

        <template #append>
          <IconBtn @click="handleCancel">
            <VIcon icon="tabler-x" />
          </IconBtn>
        </template>
      </VCardItem>

      <VDivider />

      <VCardText class="pa-6">
        <VAlert
          type="info"
          variant="tonal"
          class="mb-4"
        >
          A new password will be generated and sent to the user's email.
        </VAlert>

        <div class="mb-4">
          <label class="text-body-2 text-medium-emphasis mb-2 d-block">
            New Password
          </label>
          <div class="d-flex gap-2">
            <AppTextField
              v-model="newPassword"
              readonly
              placeholder="Generated password will appear here"
              density="compact"
            >
              <template #append-inner>
                <VBtn
                  v-if="newPassword"
                  icon
                  variant="text"
                  size="small"
                  @click="copyPassword"
                >
                  <VIcon
                    :icon="copied ? 'tabler-check' : 'tabler-copy'"
                    :color="copied ? 'success' : 'default'"
                  />
                  <VTooltip
                    activator="parent"
                    location="top"
                  >
                    {{ copied ? 'Copied!' : 'Copy to clipboard' }}
                  </VTooltip>
                </VBtn>
              </template>
            </AppTextField>
            <VBtn
              icon
              variant="tonal"
              color="primary"
              @click="generatePassword"
            >
              <VIcon icon="tabler-refresh" />
              <VTooltip
                activator="parent"
                location="top"
              >
                Generate New Password
              </VTooltip>
            </VBtn>
          </div>
        </div>

        <VAlert
          v-if="newPassword"
          type="success"
          variant="tonal"
          density="compact"
        >
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-info-circle" />
            <span class="text-sm">Password generated successfully. Click copy to save it.</span>
          </div>
        </VAlert>
      </VCardText>

      <VDivider />

      <VCardActions>
        <VSpacer />
        <VBtn
          variant="outlined"
          color="secondary"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!newPassword"
          @click="handleConfirm"
        >
          Reset Password
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
