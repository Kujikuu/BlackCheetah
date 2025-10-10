import admin from './admin'
import franchisee from './franchisee'
import franchisor from './franchisor'
import misc from './misc'
import sales from './sales'
import type { HorizontalNavItems } from '@layouts/types'

export default [...admin, ...franchisee, ...franchisor, ...sales, ...misc] as HorizontalNavItems
