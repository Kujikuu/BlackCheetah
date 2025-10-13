<script setup lang="ts">
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import { useAbility } from '@/plugins/casl/useAbility'

definePage({
  meta: {
    layout: 'blank',
    requiresAuth: true,
  },
})

const form = ref({
  name: '',
  phone: '',
  country: '',
  state: '',
  city: '',
  address: '',
})

const loading = ref(false)
const errorMessages = ref<{ [key: string]: string[] } | null>(null)
const router = useRouter()
const { smAndUp } = useDisplay()
const userData = useCookie('userData')
const { updateAbility } = useAbility()

// Check onboarding status on mount
onMounted(async () => {
  try {
    const response = await $api('/v1/onboarding/status')
    if (response.profile_completed) {
      // If profile is already completed, redirect to dashboard
      const userRole = userData.value?.role
      switch (userRole) {
        case 'admin':
          router.push('/admin/dashboard')
          break
        case 'franchisor':
          router.push('/franchisor')
          break
        case 'franchisee':
          router.push('/franchisee/dashboard/sales')
          break
        case 'sales':
          router.push('/sales/lead-management')
          break
        default:
          router.push('/')
      }
    }
  } catch (error) {
    console.error('Failed to check onboarding status:', error)
  }
})

const completeProfile = async () => {
  loading.value = true
  errorMessages.value = null

  try {
    const response = await $api('/v1/onboarding/complete', {
      method: 'POST',
      body: form.value,
    })

    // Update user data in cookie to reflect profile completion
    const userDataCookie = useCookie<any>('userData')
    if (userDataCookie.value) {
      userDataCookie.value = {
        ...userDataCookie.value,
        profile_completed: true,
        ...response.user,
      }
    }

    // Refresh user abilities
    const abilityResponse = await $api('/v1/abilities')
    useCookie<any>('userAbilityRules').value = abilityResponse.rules
    updateAbility(abilityResponse.rules)

    // Redirect directly to role-specific dashboard
    const userRole = userDataCookie.value?.role
    switch (userRole) {
      case 'admin':
        router.push('/admin/dashboard')
        break
      case 'franchisor':
        router.push('/franchisor')
        break
      case 'franchisee':
        router.push('/franchisee/dashboard/sales')
        break
      case 'sales':
        router.push('/sales/lead-management')
        break
      default:
        router.push('/')
    }
  } catch (e: any) {
    const data = e?.data || e?.response?._data || null
    if (data?.errors) {
      errorMessages.value = data.errors
    } else {
      errorMessages.value = {
        general: [data?.message || 'Failed to complete profile. Please try again.']
      }
    }
  } finally {
    loading.value = false
  }
}

const isFormValid = computed(() => {
  return form.value.name &&
    form.value.phone &&
    form.value.country &&
    form.value.state &&
    form.value.city &&
    form.value.address
})
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block" />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block" />

      <!-- 👉 Onboarding Card -->
      <VCard class="auth-card" max-width="600" :class="smAndUp ? 'pa-6' : 'pa-0'">
        <VCardItem class="justify-center">
          <VCardTitle>
            <div class="app-logo">
              <VNodeRenderer :nodes="themeConfig.app.logo" />
              <h1 class="app-logo-title">
                {{ themeConfig.app.title }}
              </h1>
            </div>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Complete Your Profile
          </h4>
          <p class="mb-0">
            Please provide your personal information to complete your account setup
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="completeProfile">
            <VRow>
              <VCol cols="12">
                <VAlert v-if="errorMessages?.general" type="error" variant="tonal" class="mb-4">
                  {{ errorMessages.general[0] }}
                </VAlert>
              </VCol>

              <!-- Full Name -->
              <VCol cols="12">
                <AppTextField v-model="form.name" autofocus label="Full Name" placeholder="John Doe"
                  :error-messages="errorMessages?.name" required />
              </VCol>

              <!-- Phone -->
              <VCol cols="12">
                <AppTextField v-model="form.phone" label="Phone Number" placeholder="+1 (555) 123-4567"
                  :error-messages="errorMessages?.phone" required />
              </VCol>

              <!-- Country and State -->
              <VCol cols="12" md="6">
                <AppTextField v-model="form.country" label="Country" placeholder="United States"
                  :error-messages="errorMessages?.country" required />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField v-model="form.state" label="State/Province" placeholder="California"
                  :error-messages="errorMessages?.state" required />
              </VCol>

              <!-- City -->
              <VCol cols="12">
                <AppTextField v-model="form.city" label="City" placeholder="Los Angeles"
                  :error-messages="errorMessages?.city" required />
              </VCol>

              <!-- Address -->
              <VCol cols="12">
                <AppTextarea v-model="form.address" label="Address" placeholder="123 Main Street, Apt 4B"
                  :error-messages="errorMessages?.address" rows="3" required />
              </VCol>

              <!-- Complete Profile Button -->
              <VCol cols="12">
                <VBtn block type="submit" :loading="loading" :disabled="loading || !isFormValid" color="primary"
                  size="large">
                  Complete Profile
                </VBtn>
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
