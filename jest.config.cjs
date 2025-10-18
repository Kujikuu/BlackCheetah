module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/ts/$1',
    '^@core/(.*)$': '<rootDir>/resources/ts/@core/$1',
  },
  roots: ['<rootDir>/resources/ts'],
  transform: {
    '^.+\.(t|j)sx?$': 'ts-jest',
    '^.+\.vue$': '@vue/vue3-jest',
  },
  testMatch: ['**/__tests__/**/*.spec.ts'],
  testPathIgnorePatterns: ['/node_modules/', 'setup.ts'],
  moduleFileExtensions: ['js', 'json', 'jsx', 'ts', 'tsx', 'vue'],
  setupFilesAfterEnv: ['<rootDir>/resources/ts/__tests__/setup.ts'],
}
