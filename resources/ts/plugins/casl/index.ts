import type { App } from 'vue'

import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'
import type { Rule } from './ability'

export default function (app: App) {
  const userAbilityRules = useCookie<Rule[]>('userAbilityRules')

  // Provide a fallback empty array if userAbilityRules is null/undefined
  // This prevents the CASL plugin from failing during initialization
  const abilityRules = userAbilityRules.value ?? []
  const initialAbility = createMongoAbility(abilityRules)

  app.use(abilitiesPlugin, initialAbility, {
    useGlobalProperties: true,
  })
}
