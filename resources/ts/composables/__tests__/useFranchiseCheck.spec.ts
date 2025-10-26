import { describe, it, expect, vi } from 'vitest'
import { useFranchiseCheck } from '../useFranchiseCheck'

describe('useFranchiseCheck', () => {
  it('initializes with correct default values', () => {
    const { isChecking, requiresRegistration } = useFranchiseCheck()

    expect(isChecking.value).toBe(false)
    expect(requiresRegistration.value).toBe(false)
  })

  it('returns false if user is not a franchisor', async () => {
    // Mock useCookie to return non-franchisor user
    global.useCookie = vi.fn((key: string) => ({
      value: key === 'userData'
        ? { role: 'broker', id: 1, email: 'broker@example.com' }
        : null,
    })) as any

    const { checkAndRedirect } = useFranchiseCheck()
    const result = await checkAndRedirect()

    expect(result).toBe(false)
  })

  it('composable exports expected functions and refs', () => {
    const composable = useFranchiseCheck()

    expect(composable).toHaveProperty('isChecking')
    expect(composable).toHaveProperty('requiresRegistration')
    expect(composable).toHaveProperty('checkAndRedirect')
    expect(typeof composable.checkAndRedirect).toBe('function')
  })

  it('returns reactive refs for state', () => {
    const { isChecking, requiresRegistration } = useFranchiseCheck()

    // Check that these are reactive refs
    expect(isChecking.value).toBeDefined()
    expect(requiresRegistration.value).toBeDefined()
    
    // Should be booleans
    expect(typeof isChecking.value).toBe('boolean')
    expect(typeof requiresRegistration.value).toBe('boolean')
  })
})

// Note: Full integration tests with API mocking are better suited for E2E tests
// These unit tests verify the composable structure and basic functionality

