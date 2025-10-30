<script setup lang="ts">
import { useTheme, useDisplay } from 'vuetify'
import ctaDashboardDark from '@images/front-pages/landing-page/cta-dashboard.png'
import ctaDashboardLight from '@images/front-pages/landing-page/cta-dashboard.png'

const theme = useTheme()
const display = useDisplay()

const ctaDashborad = computed(() => 
  theme.current.value.dark 
    ? ctaDashboardDark
    : ctaDashboardLight
)

// Check if user is authenticated
const userData = useCookie<Record<string, unknown> | null | undefined>('userData')
const isAuthenticated = computed(() => !!userData.value?.role)

// Get dashboard route based on user role
const getDashboardRoute = () => {
  const userRole = userData.value?.role

  if (userRole === 'admin')
    return { name: 'admin-dashboard' }
  if (userRole === 'franchisor')
    return { name: 'franchisor' }
  if (userRole === 'franchisee')
    return { name: 'franchisee-dashboard-sales' }
  if (userRole === 'broker')
    return { name: 'broker-lead-management' }

  return { name: 'login' }
}
</script>

<template>
  <div class="landing-cta bg-surface" :class="theme.current.value.dark ? 'banner-bg-dark' : 'banner-bg-light'">
    <VContainer>
      <div class="d-flex justify-center justify-md-space-between flex-wrap gap-6 gap-x-10 position-relative pt-12">
        <div class="align-self-center">
          <div class="banner-title text-primary mb-1">
            Ready to get started?
          </div>
          <h5 class="text-h5 text-medium-emphasis mb-8">
            Create your account and start managing your franchise.
          </h5>
          <VBtn color="primary" :to="isAuthenticated ? getDashboardRoute() : { name: 'register' }"
            :size="display.smAndUp ? 'large' : 'default'">
            {{ isAuthenticated ? 'Go to Dashboard' : 'Get Started' }}
          </VBtn>
        </div>

        <div class="banner-img">
          <img :src="ctaDashborad" class="w-100">
        </div>
      </div>
    </VContainer>
  </div>
</template>

<style lang="scss">
.landing-cta {
  background-size: cover;
  margin-block: auto;
}

.banner-img {
  margin-block-end: -22px;
}

.banner-title {
  font-size: 34px;
  font-weight: 700;
  line-height: 44px;
}

.banner-bg-light {
  background-image: url("@images/front-pages/backgrounds/cta-bg.png");
}

.banner-bg-dark {
  background-image: url("@images/front-pages/backgrounds/cta-bg-dark.png");
}
</style>
