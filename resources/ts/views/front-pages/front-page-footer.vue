<script setup lang="ts">
import { useDisplay, useTheme } from 'vuetify'
import footerDarkBg from '@images/front-pages/backgrounds/footer-bg-dark.png'
import footerLightBg from '@images/front-pages/backgrounds/footer-bg-light.png'

import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

interface Menu {
  name: string
  to: string
}

const display = useDisplay()
const theme = useTheme()
const footerBg = useGenerateImageVariant(footerLightBg, footerDarkBg)

const pagesList: Menu[] = [
  { name: 'Contact', to: '#contact' },
  { name: 'Help', to: '#help' },
  { name: 'About', to: '#about' },
  { name: 'Privacy Policy', to: '#privacy' },
  { name: 'Terms of Service', to: '#terms' },
]
</script>

<template>
  <div class="footer">
    <div class="footer-top pt-11" :style="{ 'background-image': `url(${footerBg})` }">
      <VContainer>
        <VRow>
          <!-- 👉 Footer About -->
          <VCol cols="12" md="5">
            <div class="mb-4" :class="display.smAndDown ? 'w-100' : 'w-75'">
              <div class="app-logo mb-6">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title text-white">
                  {{ themeConfig.app.title }}
                </h1>
              </div>

              <div class="mb-6" :class="theme.current.value.dark ? 'text-body-1' : 'text-white-variant'">
                Comprehensive franchise management platform for franchisors, franchisees, and brokers. Streamline
                operations and grow your franchise network.
              </div>
            </div>
          </VCol>

          <!-- 👉 Pages  -->
          <VCol cols="12" md="4">
            <div class="footer-links">
              <h6 class="footer-title text-h6 mb-6">
                Quick Links
              </h6>
              <ul style="list-style: none;">
                <li v-for="(item, index) in pagesList" :key="index" class="mb-4">
                  <a :href="item.to" :class="theme.current.value.dark ? 'text-body-1' : 'text-white-variant'"
                    class="me-2">
                    {{ item.name }}
                  </a>
                </li>
              </ul>
            </div>
          </VCol>

          <!-- 👉 Contact Info -->
          <VCol cols="12" md="3">
            <div>
              <h6 class="footer-title text-h6 mb-6">
                Contact Us
              </h6>
              <ul style="list-style: none;">
                <li class="mb-4">
                  <div :class="theme.current.value.dark ? 'text-body-1' : 'text-white-variant'">
                    <VIcon icon="tabler-mail" size="20" class="me-2" />
                    support@cheetah.com.sa
                  </div>
                </li>
                <li class="mb-4">
                  <div :class="theme.current.value.dark ? 'text-body-1' : 'text-white-variant'">
                    <VIcon icon="tabler-phone" size="20" class="me-2" />
                    +966 XX XXX XXXX
                  </div>
                </li>
                <li class="mb-4">
                  <div :class="theme.current.value.dark ? 'text-body-1' : 'text-white-variant'">
                    <VIcon icon="tabler-map-pin" size="20" class="me-2" />
                    Riyadh, Saudi Arabia
                  </div>
                </li>
              </ul>
            </div>
          </VCol>
        </VRow>
      </VContainer>
    </div>

    <!-- 👉 Footer Line -->
    <div class="footer-line w-100">
      <VContainer>
        <div class="d-flex justify-space-between flex-wrap gap-y-5 align-center">
          <div class="text-body-1 text-white-variant text-wrap me-4">
            &copy; {{ new Date().getFullYear() }} Cheetah. All rights reserved.
          </div>

          <div class="d-flex gap-x-6">
            <template v-for="(item, index) in [
              { title: 'linkedin', icon: 'tabler-brand-linkedin', href: '#' },
              { title: 'twitter', icon: 'tabler-brand-twitter', href: '#' },
              { title: 'facebook', icon: 'tabler-brand-facebook', href: '#' },
              { title: 'instagram', icon: 'tabler-brand-instagram', href: '#' },
            ]" :key="index">
              <a :href="item.href" target="_blank" rel="noopener noreferrer">
                <VIcon :icon="item.icon" size="20" color="white" />
              </a>
            </template>
          </div>
        </div>
      </VContainer>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.footer-title {
  color: rgba(255, 255, 255, 92%);
}

.footer-top {
  border-radius: 60px 60px 0 0;
  background-size: cover;
  color: #fff;
}

.footer-links {

  .text-white-variant,
  .text-body-1 {
    &:hover {
      color: #fff;
    }
  }
}

.footer-line {
  background: #282c3e;
}
</style>

<style lang="scss">
.subscribe-form {
  .v-label {
    color: rgba(225, 222, 245, 90%) !important;
  }

  .v-field {
    border-end-end-radius: 0;
    border-end-start-radius: 10px;
    border-start-end-radius: 0;
    border-start-start-radius: 10px;

    input.v-field__input::placeholder {
      color: rgba(225, 222, 245, 40%) !important;
    }

    input.v-field__input {
      color: rgba(255, 255, 255, 78%);
    }
  }
}

.footer {
  border-radius: 50%;

  @media (min-width: 600px) and (max-width: 960px) {
    .v-container {
      padding-inline: 2rem !important;
    }

    .footer-logo-buttons {
      gap: 0.75rem;
    }
  }
}
</style>
