<script setup lang="ts">
import { useWindowScroll } from '@vueuse/core'
import type { RouteLocationRaw } from 'vue-router'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'
import navImg from '@images/front-pages/misc/nav-item-col-img.png'

import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const props = defineProps({
  activeId: String,
})

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

const display = useDisplay()

interface navItem {
  name: string
  to: RouteLocationRaw
}

interface MenuItem {
  listTitle: string
  listIcon: string
  navItems: navItem[]
}
const { y } = useWindowScroll()

const route = useRoute()
const router = useRouter()

const sidebar = ref(false)

watch(() => display, () => {
  return display.mdAndUp ? sidebar.value = false : sidebar.value
}, { deep: true })

const isMenuOpen = ref(false)
const isMegaMenuOpen = ref(false)

const menuItems: MenuItem[] = [
  {
    listTitle: 'Authentication',
    listIcon: 'tabler-lock-open',
    navItems: [
      { name: 'Login', to: { name: 'login' } },
      { name: 'Register', to: { name: 'register' } },
      { name: 'Forgot Password', to: { name: 'forgot-password' } },
      { name: 'Reset Password', to: { name: 'reset-password' } },
    ],
  },
  {
    listTitle: 'Other Pages',
    listIcon: 'tabler-photo',
    navItems: [
      { name: 'Not Authorized', to: { path: '/not-authorized' } },
      { name: 'Verify Email', to: { name: 'verify-email' } },
      { name: 'Two Steps', to: { name: 'two-steps' } },
    ],
  },
]

const isCurrentRoute = (to: RouteLocationRaw) => {
  return route.matched.some(_route => _route.path.startsWith(router.resolve(to).path))

  // ℹ️ Below is much accurate approach if you don't have any nested routes
  // return route.matched.some(_route => _route.path === router.resolve(to).path)
}

const isPageActive = computed(() => menuItems.some(item => item.navItems.some(listItem => isCurrentRoute(listItem.to))))
</script>

<template>
  <!-- 👉 Navigation drawer for mobile devices  -->
  <VNavigationDrawer
    v-model="sidebar"
    width="275"
    data-allow-mismatch
    disable-resize-watcher
  >
    <PerfectScrollbar
      :options="{ wheelPropagation: false }"
      class="h-100"
    >
      <!-- Nav items -->
      <div>
        <div class="d-flex flex-column gap-y-4 pa-4">
          <RouterLink
            v-for="(item, index) in ['Home', 'Features', 'Pricing', 'FAQ', 'Contact']"
            :key="index"
            :to="{ name: 'index', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
            class="nav-link font-weight-medium"
            :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link' : '']"
          >
            {{ item }}
          </RouterLink>

          <RouterLink
            v-if="isAuthenticated"
            :to="getDashboardRoute()"
            class="font-weight-medium nav-link"
          >
            Go to Dashboard
          </RouterLink>
          <RouterLink
            v-else
            :to="{ name: 'login' }"
            class="font-weight-medium nav-link"
          >
            Login
          </RouterLink>
        </div>
      </div>

      <!-- Navigation drawer close icon -->
      <VIcon
        id="navigation-drawer-close-btn"
        icon="tabler-x"
        size="20"
        @click="sidebar = !sidebar"
      />
    </PerfectScrollbar>
  </VNavigationDrawer>

  <!-- 👉 Navbar for desktop devices  -->
  <div class="front-page-navbar">
    <div class="front-page-navbar">
      <VAppBar
        color="rgba(var(--v-theme-surface), 0.38)"
        :class="y > 10 ? 'app-bar-scrolled' : ['app-bar-light', 'elevation-0']"
        class="navbar-blur"
      >
        <!-- toggle icon for mobile device -->
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 me-2 d-inline-block d-md-none"
          @click="sidebar = !sidebar"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
            color="rgba(var(--v-theme-on-surface))"
          />
        </IconBtn>
        <!-- Title and Landing page sections -->
        <div class="d-flex align-center">
          <VAppBarTitle class="me-6">
            <RouterLink
              to="/"
              class="d-flex gap-x-4"
              :class="display.mdAndUp ? 'd-none' : 'd-block'"
            >
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VAppBarTitle>

          <!-- landing page sections -->
          <div class="text-base align-center d-none d-md-flex">
            <RouterLink
              v-for="(item, index) in ['Home', 'Features', 'Pricing', 'FAQ', 'Contact']"
              :key="index"
              :to="{ name: 'index', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
              class="nav-link font-weight-medium py-2 px-2 px-lg-4"
              :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link' : '']"
            >
              {{ item }}
            </RouterLink>

            <RouterLink
              :to="{ name: 'marketplace' }"
              class="nav-link font-weight-medium py-2 px-2 px-lg-4"
            >
              Marketplace
            </RouterLink>

            <!-- Pages Menu -->
            <!-- <span
              class="font-weight-medium cursor-pointer px-2 px-lg-4 py-2"
              :class="isPageActive || isMegaMenuOpen ? 'active-link' : ''"
              style="color: rgba(var(--v-theme-on-surface));"
            >
              Pages
              <VIcon
                icon="tabler-chevron-down"
                size="16"
                class="ms-2"
              />
              <VMenu
                v-model="isMegaMenuOpen"
                open-on-hover
                activator="parent"
                transition="slide-y-transition"
                location="bottom center"
                offset="16"
                content-class="mega-menu"
                location-strategy="static"
                close-on-content-click
              >
                <VCard max-width="1000">
                  <VCardText class="pa-8">
                    <div class="nav-menu">
                      <div
                        v-for="(item, index) in menuItems"
                        :key="index"
                      >
                        <div class="d-flex align-center gap-x-3 mb-6">
                          <VAvatar
                            variant="tonal"
                            color="primary"
                            rounded
                            :icon="item.listIcon"
                          />
                          <div class="text-body-1 text-high-emphasis font-weight-medium">
                            {{ item.listTitle }}
                          </div>
                        </div>
                        <ul>
                          <li
                            v-for="listItem in item.navItems"
                            :key="listItem.name"
                            style="list-style: none;"
                            class="text-body-1 mb-4 text-no-wrap"
                          >
                            <RouterLink
                              class="mega-menu-item"
                              :to="listItem.to"
                              :target="item.listTitle === 'Page' ? '_self' : '_blank'"
                              :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                            >
                              <div class="d-flex align-center">
                                <VIcon
                                  icon="tabler-circle"
                                  color="primary"
                                  :size="10"
                                  class="me-2"
                                />
                                <span>{{ listItem.name }}</span>
                              </div>
                            </RouterLink>
                          </li>
                        </ul>
                      </div>
                      <img
                        :src="navImg"
                        alt="Navigation Image"
                        class="d-inline-block rounded-lg"
                        style="border: 10px solid rgb(var(--v-theme-background));"
                        :width="display.lgAndUp ? '330' : '250'"
                        :height="display.lgAndUp ? '330' : '250'"
                      >
                    </div>
                  </VCardText>
                </VCard>
              </VMenu>
            </span> -->

          </div>
        </div>

        <VSpacer />

        <div class="d-flex align-center navbar-actions" :class="{ 'navbar-actions-mobile': !display.mdAndUp }">
          <NavbarThemeSwitcher />

          <!-- Desktop buttons -->
          <VBtn
            v-if="!isAuthenticated && display.lgAndUp"
            prepend-icon="tabler-rocket"
            variant="elevated"
            color="primary"
            :to="{ name: 'register' }"
            class="d-none d-lg-flex"
          >
            Create Account
          </VBtn>
          <VBtn
            v-if="isAuthenticated && display.lgAndUp"
            prepend-icon="tabler-dashboard"
            variant="elevated"
            color="primary"
            :to="getDashboardRoute()"
            class="d-none d-lg-flex"
          >
            Go to Dashboard
          </VBtn>

          <!-- Mobile/Tablet buttons - Icon only -->
          <VBtn
            v-if="!isAuthenticated && display.mdAndUp && !display.lgAndUp"
            icon="tabler-rocket"
            variant="elevated"
            color="primary"
            :to="{ name: 'register' }"
            size="small"
            class="d-flex d-lg-none"
          />
          <VBtn
            v-if="isAuthenticated && display.mdAndUp && !display.lgAndUp"
            icon="tabler-dashboard"
            variant="elevated"
            color="primary"
            :to="getDashboardRoute()"
            size="small"
            class="d-flex d-lg-none"
          />

          <!-- Login button - Hidden on mobile (available in drawer) -->
          <VBtn
            v-if="!isAuthenticated && display.mdAndUp"
            prepend-icon="tabler-login"
            variant="outlined"
            :to="{ name: 'login' }"
            :size="display.lgAndUp ? 'default' : 'small'"
            class="d-none d-md-flex"
          >
            <span v-if="display.lgAndUp">Login</span>
          </VBtn>
        </div>
      </VAppBar>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.nav-menu {
  display: flex;
  gap: 2rem;
}

.nav-link {
  &:not(:hover) {
    color: rgb(var(--v-theme-on-surface));
  }
}

.page-link {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

@media (max-width: 1280px) {
  .nav-menu {
    gap: 2.25rem;
  }
}

@media (min-width: 1920px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1440px - 32px);
    }
  }
}

@media (min-width: 1280px) and (max-width: 1919px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1200px - 32px);
    }
  }
}

@media (min-width: 960px) and (max-width: 1279px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(900px - 32px);
    }
  }
}

@media (min-width: 600px) and (max-width: 959px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 64px);
    }
  }
}

@media (max-width: 600px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 32px);
    }
  }
}

.nav-item-img {
  border: 10px solid rgb(var(--v-theme-background));
  border-radius: 10px;
}

.active-link {
  color: rgb(var(--v-theme-primary)) !important;
}

.app-bar-light {
  border: 2px solid rgba(var(--v-theme-surface), 68%);
  border-radius: 0.5rem;
  background-color: rgba(var(--v-theme-surface), 38%);
  transition: all 0.1s ease-in-out;
}

.app-bar-dark {
  border: 2px solid rgba(var(--v-theme-surface), 68%);
  border-radius: 0.5rem;
  background-color: rgba(255, 255, 255, 4%);
  transition: all 0.1s ease-in-out;
}

.app-bar-scrolled {
  border: 2px solid rgb(var(--v-theme-surface));
  border-radius: 0.5rem;
  background-color: rgb(var(--v-theme-surface)) !important;
  transition: all 0.1s ease-in-out;
}

.front-page-navbar::after {
  position: fixed;
  z-index: 2;
  // backdrop-filter: saturate(100%) blur(6px);
  block-size: 5rem;
  content: "";
  inline-size: 100%;
}

.navbar-actions {
  gap: 1rem;
}

.navbar-actions-mobile {
  gap: 0.5rem;
}

@media (max-width: 959px) {
  .navbar-actions {
    gap: 0.5rem;
  }
}
</style>

<style lang="scss">
@use "@layouts/styles/mixins" as layoutMixins;

.mega-menu {
  position: fixed !important;
  inset-block-start: 5.4rem;
  inset-inline-start: 50%;
  transform: translateX(-50%);

  @include layoutMixins.rtl {
    transform: translateX(50%);
  }
}

.front-page-navbar {
  .v-toolbar__content {
    padding-inline: 30px !important;

    @media (max-width: 959px) {
      padding-inline: 16px !important;
    }

    @media (max-width: 599px) {
      padding-inline: 12px !important;
    }
  }

  .v-toolbar {
    inset-inline: 0 !important;
    margin-block-start: 1rem !important;
    margin-inline: auto !important;
  }
}

.mega-menu-item {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

#navigation-drawer-close-btn {
  position: absolute;
  cursor: pointer;
  inset-block-start: 0.5rem;
  inset-inline-end: 1rem;
}
</style>
