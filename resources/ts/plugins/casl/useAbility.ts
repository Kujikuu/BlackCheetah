import { createMongoAbility } from '@casl/ability'
import type { Rule } from './ability'

const ability = createMongoAbility<[string, string]>()

export const useAbility = () => {
  const updateAbility = (rules: Rule[]) => {
    ability.update(rules)
  }

  return {
    ability,
    updateAbility,
  }
}
