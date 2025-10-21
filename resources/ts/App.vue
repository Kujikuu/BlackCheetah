<script setup lang="ts">
import { useTheme } from 'vuetify'
import { useRoute } from 'vue-router'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import { initConfigStore, useConfigStore } from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import BlankLayout from '@/layouts/blank.vue'
import DefaultLayout from '@/layouts/default.vue'

const { global } = useTheme()
const route = useRoute()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()

// Dynamically select layout based on route meta
const layout = computed(() => {
  const layoutName = route.meta?.layout || 'default'
  
  return layoutName === 'blank' ? BlankLayout : DefaultLayout
})
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <Component :is="layout">
        <RouterView />
      </Component>
      <ScrollToTop />
    </VApp>
  </VLocaleProvider>
</template>
