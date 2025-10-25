<script setup lang="ts">
import { useSnackbar } from '@/composables/useSnackbar'

const { state, hide } = useSnackbar()

// Map variants to Vuetify colors
const colorMap = {
  success: 'success',
  error: 'error',
  info: 'info',
  warning: 'warning',
}
</script>

<template>
  <VSnackbar
    v-model="state.visible"
    :color="colorMap[state.variant]"
    :timeout="state.timeout"
    location="top right"
    :elevation="6"
  >
    {{ state.message }}

    <template #actions>
      <VBtn
        color="white"
        variant="text"
        @click="hide"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>

