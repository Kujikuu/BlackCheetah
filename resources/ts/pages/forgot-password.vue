<script setup lang="ts">
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { authApi } from '@/services/api/auth'

const { smAndUp } = useDisplay()

const form = ref({
  email: '',
})

const loading = ref(false)
const infoMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)
const errorMessages = ref<{ email?: string[] } | null>(null)

const onSubmit = async () => {
  if (!form.value.email) {
    errorMessage.value = 'Please enter your email address.'
    return
  }

  loading.value = true
  infoMessage.value = null
  errorMessage.value = null
  errorMessages.value = null

  try {
    const response = await authApi.forgotPassword({ email: form.value.email })

    if (response.success) {
      infoMessage.value = response.message || 'If that email address exists in our system, we have sent a password reset link to it.'
      form.value.email = '' // Clear form on success
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null

    if (data?.errors) {
      errorMessages.value = {
        email: data.errors.email,
      }
      errorMessage.value = data.message
    }
    else {
      errorMessage.value = data?.message || 'Failed to request password reset. Please try again.'
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

      <!-- 👉 Auth card -->
      <VCard
        class="auth-card"
        max-width="600"
        :class="smAndUp ? 'pa-6' : 'pa-0'"
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
            Forgot Password? 🔒
          </h4>
          <p class="mb-0">
            Enter your email and we'll send you instructions to reset your password
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
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  autofocus
                  label="Email"
                  type="email"
                  placeholder="Email"
                  :error-messages="errorMessages?.email"
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
                  Send Reset Link
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
