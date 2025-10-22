import admin from './admin'
import franchisee from './franchisee'
import franchisor from './franchisor'
import misc from './misc'
import brokers from './brokers'
import type { HorizontalNavItems } from '@layouts/types'

export default [...admin, ...franchisee, ...franchisor, ...brokers, ...misc] as HorizontalNavItems
