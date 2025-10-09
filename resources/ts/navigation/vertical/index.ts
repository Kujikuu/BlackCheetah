import admin from './admin'
import franchisor from './franchisor'
import sales from './sales'

// import appsAndPages from './apps-and-pages'
// import charts from './charts'
// import dashboard from './dashboard'
// import forms from './forms'
// import others from './others'
// import uiElements from './ui-elements'
import type { VerticalNavItems } from '@layouts/types'

export default [...admin, ...franchisor, ...sales] as VerticalNavItems
