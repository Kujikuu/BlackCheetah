<script setup lang="ts">
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { authApi } from '@/services/api'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const { smAndUp } = useDisplay()

const userEmail = ref<string | null>(null)
const loading = ref(false)
const infoMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)

onMounted(async () => {
  // Try to get authenticated user email if token exists
  const token = useCookie('accessToken').value
  if (!token)
    return

  loading.value = true
  try {
    const resp = await authApi.me()

    if (resp.success && resp.data) {
      userEmail.value = resp.data.email
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null

    errorMessage.value = data?.message || 'Failed to load user info.'
  }
  finally {
    loading.value = false
  }
})

const resend = async () => {
  // No backend endpoint available yet; show informational message
  infoMessage.value = 'If your email is registered, a verification link will be resent shortly.'
  setTimeout(() => { infoMessage.value = null }, 3000)
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
            Verify your email ✉️
          </h4>
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
          <p class="text-body-1 mb-0">
            Account activation link sent to your email address:
            <span class="font-weight-medium text-high-emphasis">{{ userEmail || 'hello@example.com' }}</span>
            Please follow the link inside to continue.
          </p>

          <VBtn
            block
            to="/"
            class="my-5"
            :loading="loading"
          >
            Skip for now
          </VBtn>

          <div class="d-flex align-center justify-center">
            <span class="me-1">Didn't get the mail? </span><a
              href="#"
              @click.prevent="resend"
            >Resend</a>
          </div>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
