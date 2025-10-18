import { abilitiesPlugin } from '@casl/vue'
import type { App } from 'vue'
import type { Rule } from './ability'
import { useAbility } from './useAbility'

export default function (app: App) {
  const { ability, updateAbility } = useAbility()
  const userAbilityRules = useCookie<Rule[]>('userAbilityRules')

  // Initialize ability with rules from cookie
  if (userAbilityRules.value)
    updateAbility(userAbilityRules.value)

  app.use(abilitiesPlugin, ability, {
    useGlobalProperties: true,
  })

  // Watch for changes and update ability
  watch(userAbilityRules, newRules => {
    if (newRules)
      updateAbility(newRules)
  })
}

export { useAbility }
