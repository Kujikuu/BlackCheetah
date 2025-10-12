module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'node',
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/ts/$1',
  },
  roots: ['<rootDir>/resources/ts'],
  transform: {
    '^.+\\.(t|j)sx?$': 'ts-jest',
  },
};
