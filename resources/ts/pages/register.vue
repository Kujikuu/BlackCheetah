<script setup lang="ts">
import { h } from 'vue'
import { useDisplay } from 'vuetify'
import type { Rule } from '@/plugins/casl/ability'
import { useAbility } from '@/plugins/casl/useAbility'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { authApi } from '@/services/api'

// Step management
const currentStep = ref(1)
const selectedRole = ref<'broker' | 'franchisor' | ''>('')

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const loading = ref(false)
const errorMessages = ref<{ name?: string[]; email?: string[]; password?: string[]; password_confirmation?: string[]; role?: string[]; general?: string } | null>(null)
const router = useRouter()
const { smAndUp } = useDisplay()
const { updateAbility } = useAbility()

// Step 1: Role selection
const selectRole = (role: 'broker' | 'franchisor') => {
  selectedRole.value = role
}

const proceedToStep2 = () => {
  if (selectedRole.value) {
    form.value.role = selectedRole.value
    currentStep.value = 2
  }
}

const register = async () => {
  if (!form.value.privacyPolicies) {
    errorMessages.value = { general: 'Please agree to the privacy policy & terms to continue.' }

    return
  }

  loading.value = true
  errorMessages.value = null
  try {
    const resp = await authApi.register({
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: form.value.role,
    })

    // Registration now returns the same format as login
    if (resp.success && resp.data) {
      // Persist auth data in cookies (same as login)
      useCookie<string>('accessToken').value = resp.data.accessToken
      useCookie<any>('userData').value = resp.data.userData
      useCookie<Rule[]>('userAbilityRules').value = resp.data.userAbilityRules

      // Update CASL ability rules
      updateAbility(resp.data.userAbilityRules)
    }

    // Redirect to email verification page
    router.push({ name: 'verify-email' })
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

        <!-- Step 1: Role Selection -->
        <VCardText v-if="currentStep === 1">
          <h4 class="text-h4 mb-1 text-center">
            Join as a Broker or Franchisor
          </h4>
          <p class="mb-6 text-center">
            Select your role to get started
          </p>

          <VRow class="role-selection-cards mb-6">
            <!-- Franchisor Card -->
            <VCol cols="12" sm="6">
              <VCard
                :class="[
                  'role-card cursor-pointer',
                  { 'role-card-selected': selectedRole === 'franchisor' }
                ]"
                variant="outlined"
                @click="selectRole('franchisor')"
              >
                <VCardText class="pa-6">
                  <div class="d-flex justify-space-between align-start mb-4">
                    <VIcon
                      icon="tabler-briefcase"
                      size="48"
                      class="text-primary"
                    />
                    <div class="role-radio">
                      <div
                        :class="[
                          'radio-outer',
                          { 'radio-selected': selectedRole === 'franchisor' }
                        ]"
                      >
                        <div v-if="selectedRole === 'franchisor'" class="radio-inner" />
                      </div>
                    </div>
                  </div>
                  <div class="text-body-1 font-weight-medium">
                    I'm a franchisor, managing franchises
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <!-- Broker Card -->
            <VCol cols="12" sm="6">
              <VCard
                :class="[
                  'role-card cursor-pointer',
                  { 'role-card-selected': selectedRole === 'broker' }
                ]"
                variant="outlined"
                @click="selectRole('broker')"
              >
                <VCardText class="pa-6">
                  <div class="d-flex justify-space-between align-start mb-4">
                    <VIcon
                      icon="tabler-users"
                      size="48"
                      class="text-primary"
                    />
                    <div class="role-radio">
                      <div
                        :class="[
                          'radio-outer',
                          { 'radio-selected': selectedRole === 'broker' }
                        ]"
                      >
                        <div v-if="selectedRole === 'broker'" class="radio-inner" />
                      </div>
                    </div>
                  </div>
                  <div class="text-body-1 font-weight-medium">
                    I'm a broker, connecting opportunities
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>

          <VBtn
            block
            size="large"
            :disabled="!selectedRole"
            @click="proceedToStep2"
          >
            Create Account
          </VBtn>

          <div class="text-center text-base mt-6">
            <span>Already have an account?</span>
            <RouterLink
              class="text-primary ms-1"
              :to="{ name: 'login' }"
            >
              Log In
            </RouterLink>
          </div>
        </VCardText>

        <!-- Step 2: Registration Form -->
        <VCardText v-if="currentStep === 2">
          <h4 class="text-h4 mb-1">
            Complete Your Registration as {{ selectedRole === 'franchisor' ? 'Franchisor' : 'Broker' }}
          </h4>
          <p class="mb-0">
            Fill in your details to create your account
          </p>
        </VCardText>

        <VCardText v-if="currentStep === 2">
          <VForm @submit.prevent="register">
            <VRow>
              <VCol cols="12">
                <VAlert
                  v-if="errorMessages?.general"
                  type="error"
                  variant="tonal"
                  class="mb-4"
                >
                  {{ errorMessages?.general }}
                </VAlert>
              </VCol>
              <!-- Name -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.name"
                  autofocus
                  label="Full Name"
                  placeholder="Name"
                  :error-messages="errorMessages?.name"
                />
              </VCol>
              <!-- Email -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  placeholder="Email"
                  :error-messages="errorMessages?.email"
                />
              </VCol>

              <!-- Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  label="Password"
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
                  v-model="form.password_confirmation"
                  label="Confirm Password"
                  autocomplete="new-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :error-messages="errorMessages?.password_confirmation"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    inline
                  />
                  <VLabel
                    for="privacy-policy"
                    style="opacity: 1;"
                  >
                    <span class="me-1 text-high-emphasis">I agree to</span>
                    <a
                      href="javascript:void(0)"
                      class="text-primary"
                    >privacy policy & terms</a>
                  </VLabel>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="loading"
                  :disabled="loading"
                >
                  Sign up
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol
                cols="12"
                class="text-center text-base"
              >
                <span>Already have an account?</span>
                <RouterLink
                  class="text-primary ms-1"
                  :to="{ name: 'login' }"
                >
                  Sign in instead
                </RouterLink>
              </VCol>

              <VCol
                cols="12"
                class="d-flex align-center"
              >
                <VDivider />
                <span class="mx-4">or</span>
                <VDivider />
              </VCol>

              <!-- auth providers -->
              <VCol
                cols="12"
                class="text-center"
              >
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

// Role selection cards styling
.role-selection-cards {
  .role-card {
    transition: all 0.2s ease-in-out;
    border: 2px solid rgba(var(--v-border-color), var(--v-border-opacity));
    
    &:hover {
      border-color: rgb(var(--v-theme-primary));
      box-shadow: 0 2px 8px rgba(var(--v-theme-primary), 0.15);
    }
    
    &.role-card-selected {
      border-color: rgb(var(--v-theme-primary));
      background-color: rgba(var(--v-theme-primary), 0.04);
      box-shadow: 0 2px 12px rgba(var(--v-theme-primary), 0.2);
    }
  }

  // Custom radio button
  .role-radio {
    .radio-outer {
      width: 24px;
      height: 24px;
      border: 2px solid rgba(var(--v-border-color), var(--v-border-opacity));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease-in-out;
      
      &.radio-selected {
        border-color: rgb(var(--v-theme-primary));
      }
      
      .radio-inner {
        width: 12px;
        height: 12px;
        background-color: rgb(var(--v-theme-primary));
        border-radius: 50%;
      }
    }
  }
}

// Responsive adjustments
@media (max-width: 600px) {
  .role-selection-cards {
    .role-card {
      margin-bottom: 1rem;
    }
  }
}
</style>
