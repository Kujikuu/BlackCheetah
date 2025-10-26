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

const loading = ref(false)
const resendingEmail = ref(false)
const infoMessage = ref<string | null>(null)
const errorMessage = ref<string | null>(null)
const isVerified = ref(false)
const verificationAttempted = ref(false)
const userEmail = ref('')

// Check if this is a verification callback
onMounted(async () => {
  // Get user info to display email
  try {
    const user = useCookie('userData').value
    if (user && typeof user === 'object' && 'email' in user) {
      userEmail.value = user.email as string
    }
  }
  catch (e) {
    // User not logged in or cookie not available
  }

  // Check if user is trying to verify via signed URL
  const { id, hash, expires, signature } = route.query
  if (id && hash && expires && signature) {
    await verifyEmail(
      Number(id),
      String(hash),
      String(expires),
      String(signature),
    )
  }
  else {
    // Check verification status if user is logged in
    await checkStatus()
  }
})

const checkStatus = async () => {
  try {
    const response = await authApi.checkVerificationStatus()
    if (response.success && response.data) {
      isVerified.value = response.data.is_verified
      if (isVerified.value) {
        infoMessage.value = 'Your email address is already verified!'
      }
    }
  }
  catch (e: any) {
    // User not authenticated, ignore
  }
}

const verifyEmail = async (id: number, hash: string, expires: string, signature: string) => {
  verificationAttempted.value = true
  loading.value = true
  errorMessage.value = null
  infoMessage.value = null

  try {
    const response = await authApi.verifyEmail(id, hash, expires, signature)

    if (response.success) {
      isVerified.value = true
      infoMessage.value = response.message || 'Your email has been verified successfully!'

      // Redirect to appropriate dashboard after 2 seconds
      setTimeout(() => {
        const userData = useCookie('userData').value
        if (userData && typeof userData === 'object' && 'role' in userData) {
          const role = userData.role as string
          switch (role) {
            case 'admin':
              router.push('/admin/dashboard')
              break
            case 'franchisor':
              router.push('/franchisor')
              break
            case 'franchisee':
              router.push('/franchisee/dashboard/sales')
              break
            case 'broker':
              router.push('/brokers/lead-management')
              break
            default:
              router.push('/')
          }
        }
        else {
          router.push('/login')
        }
      }, 2000)
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null
    errorMessage.value = data?.message || 'Failed to verify email. The link may be invalid or expired.'
  }
  finally {
    loading.value = false
  }
}

const resendVerificationEmail = async () => {
  resendingEmail.value = true
  errorMessage.value = null
  infoMessage.value = null

  try {
    const response = await authApi.sendVerificationEmail()

    if (response.success) {
      infoMessage.value = response.message || 'Verification email has been sent! Please check your inbox.'
    }
  }
  catch (e: any) {
    const data = e?.data || e?.response?._data || null
    errorMessage.value = data?.message || 'Failed to send verification email. Please try again.'
  }
  finally {
    resendingEmail.value = false
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
            {{ isVerified ? 'Email Verified! ✅' : 'Verify Your Email 📧' }}
          </h4>
          <p class="mb-0">
            {{
              isVerified
                ? 'Your email address has been successfully verified.'
                : 'Please verify your email address to continue.'
            }}
          </p>
        </VCardText>

        <VCardText>
          <VRow>
            <VCol cols="12">
              <VAlert
                v-if="loading"
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
                  Verifying your email...
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
                :type="isVerified ? 'success' : 'info'"
                variant="tonal"
                class="mb-4"
              >
                {{ infoMessage }}
              </VAlert>
            </VCol>

            <template v-if="!isVerified && !loading">
              <VCol
                v-if="userEmail"
                cols="12"
              >
                <p class="text-body-1">
                  We've sent a verification email to:
                </p>
                <p class="text-h6 mb-4">
                  {{ userEmail }}
                </p>
                <p class="text-body-2 text-disabled">
                  Please check your inbox and click the verification link. Don't forget to check your spam folder.
                </p>
              </VCol>

              <VCol cols="12">
                <VBtn
                  block
                  :loading="resendingEmail"
                  :disabled="resendingEmail"
                  @click="resendVerificationEmail"
                >
                  <VIcon
                    icon="tabler-mail"
                    class="me-2"
                  />
                  Resend Verification Email
                </VBtn>
              </VCol>

              <VCol cols="12">
                <VDivider />
              </VCol>

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
            </template>

            <template v-if="isVerified">
              <VCol cols="12">
                <p class="text-center">
                  <VIcon
                    icon="tabler-circle-check"
                    color="success"
                    size="64"
                  />
                </p>
                <p class="text-body-1 text-center">
                  Redirecting you to the dashboard...
                </p>
              </VCol>
            </template>
          </VRow>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
