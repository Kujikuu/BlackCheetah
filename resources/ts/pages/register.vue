<script setup lang="ts">
import type { Rule } from '@/plugins/casl/ability'
import { useAbility } from '@/plugins/casl/useAbility'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { h } from 'vue'
import { useDisplay } from 'vuetify'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'franchisor',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const loading = ref(false)
const errorMessages = ref<{ name?: string[]; email?: string[]; password?: string[]; password_confirmation?: string[]; role?: string[]; general?: string } | null>(null)
const router = useRouter()
const { smAndUp } = useDisplay()
const { updateAbility } = useAbility()

const register = async () => {
  if (!form.value.privacyPolicies) {
    errorMessages.value = { general: 'Please agree to the privacy policy & terms to continue.' }

    return
  }

  loading.value = true
  errorMessages.value = null
  try {
    const resp = await $api<{ success: boolean; message: string; data: { user: { id: number; name: string; email: string; role: string; status: string }; token: string } }>('/auth/register', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        role: form.value.role,
      },
    })

    // Persist token and basic user data
    useCookie<string>('accessToken').value = resp.data.token
    useCookie<any>('userData').value = {
      id: resp.data.user.id,
      fullName: resp.data.user.name,
      username: resp.data.user.email,
      avatar: undefined,
      email: resp.data.user.email,
      role: resp.data.user.role,
      status: resp.data.user.status,
    }

    // Attempt login to fetch ability rules from backend and ensure consistent cookies
    let loginResp
    try {
      loginResp = await $api<{ accessToken: string; userData: any; userAbilityRules: Rule[] }>('/auth/login', {
        method: 'POST',
        body: {
          email: form.value.email,
          password: form.value.password,
        },
      })

      useCookie<string>('accessToken').value = loginResp.accessToken
      useCookie<any>('userData').value = loginResp.userData
      useCookie<Rule[]>('userAbilityRules').value = loginResp.userAbilityRules
      updateAbility(loginResp.userAbilityRules)
    }
    catch (e) {
      // If auto-login fails, proceed with registration token and empty abilities
      useCookie<Rule[]>('userAbilityRules').value = []
      updateAbility([])
    }

    // Redirect directly to role-specific dashboard
    const userRole = loginResp?.userData?.role || 'franchisor' // Default to franchisor for new registrations
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
  catch (e: any) {
    const data = e?.data || e?.response?._data || null
    if (data?.errors) {
      errorMessages.value = {
        name: data.errors.name,
        email: data.errors.email,
        password: data.errors.password,
        password_confirmation: data.errors.password_confirmation,
        role: data.errors.role,
        general: data.message,
      }
    }
    else {
      errorMessages.value = { general: data?.message || 'Registration failed. Please check your details and try again.' }
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
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block" />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block" />

      <!-- 👉 Auth card -->
      <VCard class="auth-card" max-width="460" :class="smAndUp ? 'pa-6' : 'pa-0'">
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
            Adventure starts here 🚀
          </h4>
          <p class="mb-0">
            Make your app management easy and fun!
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="register">
            <VRow>
              <VCol cols="12">
                <VAlert v-if="errorMessages?.general" type="error" variant="tonal" class="mb-4">
                  {{ errorMessages?.general }}
                </VAlert>
              </VCol>
              <!-- Name -->
              <VCol cols="12">
                <AppTextField v-model="form.name" autofocus label="Full Name" placeholder="Name"
                  :error-messages="errorMessages?.name" />
              </VCol>
              <!-- Email -->
              <VCol cols="12">
                <AppTextField v-model="form.email" label="Email" type="email" placeholder="Email"
                  :error-messages="errorMessages?.email" />
              </VCol>

              <!-- Role -->
              <!-- <VCol cols="12">
                <AppSelect
                  v-model="form.role"
                  :items="['franchisor', 'franchisee', 'sales']"
                  label="Role"
                  :error-messages="errorMessages?.role"
                />
              </VCol> -->

              <!-- Password -->
              <VCol cols="12">
                <AppTextField v-model="form.password" label="Password" placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'" autocomplete="new-password"
                  :error-messages="errorMessages?.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <AppTextField v-model="form.password_confirmation" label="Confirm Password" autocomplete="new-password"
                  placeholder="············" :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :error-messages="errorMessages?.password_confirmation"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible" />
              </VCol>

              <VCol cols="12">
                <div class="d-flex align-center my-6">
                  <VCheckbox id="privacy-policy" v-model="form.privacyPolicies" inline />
                  <VLabel for="privacy-policy" style="opacity: 1;">
                    <span class="me-1 text-high-emphasis">I agree to</span>
                    <a href="javascript:void(0)" class="text-primary">privacy policy & terms</a>
                  </VLabel>
                </div>

                <VBtn block type="submit" :loading="loading" :disabled="loading">
                  Sign up
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol cols="12" class="text-center text-base">
                <span>Already have an account?</span>
                <RouterLink class="text-primary ms-1" :to="{ name: 'login' }">
                  Sign in instead
                </RouterLink>
              </VCol>

              <VCol cols="12" class="d-flex align-center">
                <VDivider />
                <span class="mx-4">or</span>
                <VDivider />
              </VCol>

              <!-- auth providers -->
              <VCol cols="12" class="text-center">
                <AuthProvider />
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
