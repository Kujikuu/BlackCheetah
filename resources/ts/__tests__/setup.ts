import { config } from '@vue/test-utils'

// Configure Vue Test Utils
config.global.stubs = {
  'v-icon': true,
  'v-btn': true,
  'v-card': true,
  'v-card-text': true,
  'v-card-title': true,
  'v-card-subtitle': true,
  'v-card-item': true,
  'v-card-actions': true,
  'v-row': true,
  'v-col': true,
  'v-chip': true,
  'v-avatar': true,
  'v-progress-circular': true,
  'v-alert': true,
  'v-divider': true,
  'v-data-table': true,
  'v-tabs': true,
  'v-tab': true,
  'v-window': true,
  'v-window-item': true,
  'v-form': true,
  'v-text-field': true,
  'v-select': true,
  'v-textarea': true,
  'v-dialog': true,
  'v-list': true,
  'v-list-item': true,
  'v-list-item-title': true,
  'v-menu': true,
  'v-tooltip': true,
  'v-spacer': true,
  'transition': true,
}

config.global.mocks = {
  $api: jest.fn(),
  $router: {
    push: jest.fn(),
  },
  $route: {
    params: {
      id: '1',
    },
  },
}

// Global test utilities
global.ResizeObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
  disconnect: jest.fn(),
}))
