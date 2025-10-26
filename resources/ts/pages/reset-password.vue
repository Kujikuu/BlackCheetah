<script setup lang="ts">
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import { useRoute, useRouter } from 'vue-router'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { authApi } from '@/services/api/auth'

const { smAndUp } = useDisplay()
const route = useRoute()
const router = useRouter()

// Extract token and email from URL query parameters
const token = ref(route.query.token as string || '')
const email = ref(route.query.email as string || '')

const form = ref({
  newPassword: '',
  confirmPassword: '',
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const loading = ref(false)
const validatingToken = ref(false)
const infoMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)
const errorMessages = ref<{ password?: string[]; password_confirmation?: string[] } | null>(null)
const tokenValid = ref(false)

// Validate token on component mount
onMounted(async () => {
  if (!token.value || !email.value) {
    errorMessage.value = 'Invalid reset link. Please request a new password reset.'
    return
  }

  validatingToken.value = true
  try {
    const response = await authApi.validateResetToken({
      token: token.value,
      email: email.value,
    })

    if (response.success) {
      tokenValid.value = true
    }
    else {
      errorMessage.value = 'Invalid or expired reset link. Please request a new password reset.'
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null
    errorMessage.value = data?.message || 'Invalid or expired reset link. Please request a new password reset.'
  }
  finally {
    validatingToken.value = false
  }
})

const onSubmit = async () => {
  errorMessage.value = null
  infoMessage.value = null
  errorMessages.value = null

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
    const response = await authApi.resetPassword({
      token: token.value,
      email: email.value,
      password: form.value.newPassword,
      password_confirmation: form.value.confirmPassword,
    })

    if (response.success) {
      infoMessage.value = response.message || 'Your password has been reset successfully. Redirecting to login...'

      // Redirect to login after 2 seconds
      setTimeout(() => {
        router.push({ name: 'login' })
      }, 2000)
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null

    if (data?.errors) {
      errorMessages.value = {
        password: data.errors.password,
        password_confirmation: data.errors.password_confirmation,
      }
      errorMessage.value = data.message
    }
    else {
      errorMessage.value = data?.message || 'Failed to reset password. Please try again.'
    }
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
                  v-if="validatingToken"
                  type="info"
                  variant="tonal"
                  class="mb-4"
                >
                  <div class="d-flex align-center">
                    <VProgressCircular
                      indeterminate
                      size="20"
                      class="me-3"
                    />
                    Validating reset link...
                  </div>
                </VAlert>
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
                  type="success"
                  variant="tonal"
                  class="mb-4"
                >
                  {{ infoMessage }}
                </VAlert>
              </VCol>

              <template v-if="tokenValid && !validatingToken">
                <!-- password -->
                <VCol cols="12">
                  <AppTextField
                    v-model="form.newPassword"
                    autofocus
                    label="New Password"
                    placeholder="············"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    autocomplete="new-password"
                    :error-messages="errorMessages?.password"
                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  />
                  <p class="text-caption text-disabled mt-1">
                    Password must be at least 8 characters and contain uppercase, lowercase, number, and special character.
                  </p>
                </VCol>

                <!-- Confirm Password -->
                <VCol cols="12">
                  <AppTextField
                    v-model="form.confirmPassword"
                    label="Confirm Password"
                    autocomplete="new-password"
                    placeholder="············"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :error-messages="errorMessages?.password_confirmation"
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
              </template>

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
