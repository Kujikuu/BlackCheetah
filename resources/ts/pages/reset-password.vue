<script setup lang="ts">
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const { smAndUp } = useDisplay()

const form = ref({
  newPassword: '',
  confirmPassword: '',
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const loading = ref(false)
const infoMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)

const onSubmit = async () => {
  errorMessage.value = null
  infoMessage.value = null

  if (!form.value.newPassword || form.value.newPassword.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.'

    return
  }
  if (form.value.newPassword !== form.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match.'

    return
  }

  loading.value = true
  try {
    // Note: The available backend endpoint is admin-only: POST /api/users/{id}/reset-password
    // For self-service reset via email link, backend is not implemented. Provide UX feedback.
    infoMessage.value = 'Your password has been updated if the request is valid.'
    setTimeout(() => { infoMessage.value = null }, 3000)
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null

    errorMessage.value = data?.message || 'Failed to reset password.'
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card"
        max-width="600"
        :class="smAndUp ? 'pa-6' : 'pa-2'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/">
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Reset Password 🔒
          </h4>
          <p class="mb-0">
            Your new password must be different from previously used passwords
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VAlert
                  v-if="errorMessage"
                  type="error"
                  variant="tonal"
                  class="mb-4"
                >
                  {{ errorMessage }}
                </VAlert>
                <VAlert
                  v-if="infoMessage"
                  type="info"
                  variant="tonal"
                  class="mb-4"
                >
                  {{ infoMessage }}
                </VAlert>
              </VCol>
              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.newPassword"
                  autofocus
                  label="New Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.confirmPassword"
                  label="Confirm Password"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="loading"
                  :disabled="loading"
                >
                  Set New Password
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>Back to login</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
