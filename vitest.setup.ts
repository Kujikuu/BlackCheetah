import { config } from '@vue/test-utils'
import { vi } from 'vitest'

// Mock all CSS/style imports
vi.mock('*.css', () => ({ default: {} }))
vi.mock('*.scss', () => ({ default: {} }))
vi.mock('*.sass', () => ({ default: {} }))

// Mock Vuetify CSS imports specifically
vi.mock('vuetify/lib/**/*.css', () => ({ default: {} }))
vi.mock('vuetify/styles', () => ({ default: {} }))

// Mock global objects
global.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

global.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})

// Mock window.scrollTo
Object.defineProperty(window, 'scrollTo', {
  writable: true,
  value: vi.fn(),
})

// Mock window.URL.createObjectURL
Object.defineProperty(window.URL, 'createObjectURL', {
  writable: true,
  value: vi.fn(() => 'mock-url'),
})

Object.defineProperty(window.URL, 'revokeObjectURL', {
  writable: true,
  value: vi.fn(),
})

// Mock SVG imports
vi.mock('*.svg', () => ({
  default: 'mocked-svg',
}))

// Mock image imports
vi.mock('*.png', () => ({ default: 'mocked-png' }))
vi.mock('*.jpg', () => ({ default: 'mocked-jpg' }))
vi.mock('*.jpeg', () => ({ default: 'mocked-jpeg' }))

// Configure Vue Test Utils
config.global.mocks = {
  $t: (key: string) => key, // Mock i18n if used
}

// Stub global components
config.global.stubs = {
  teleport: true,
  transition: false,
}

// Mock Nuxt composables if needed
vi.mock('#app', () => ({
  useNuxtApp: () => ({
    $api: vi.fn(),
  }),
  useRuntimeConfig: () => ({
    public: {
      apiBase: 'http://localhost:8000',
    },
  }),
  navigateTo: vi.fn(),
  useRoute: () => ({
    params: {},
    query: {},
  }),
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn(),
  }),
}))

// Mock cookies
vi.mock('cookie-es', () => ({
  useCookie: (name: string) => ({
    value: name === 'accessToken' ? 'mock-token' : null,
  }),
}))

