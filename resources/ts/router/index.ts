import type { App } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'
import routes from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to) {
    if (to.hash)
      return { el: to.hash, behavior: 'smooth', top: 60 }

    return { top: 0 }
  },
})

setupGuards(router)

export { router }

export default function (app: App) {
  app.use(router)
}

