import { $api } from '@/utils/api'
import type { ApiResponse } from '@/types/api'

const API_URL = '/v1/onboarding'

// Onboarding types
export interface OnboardingStatus {
  step: number
  is_completed: boolean
  can_proceed: boolean
  required_fields?: string[]
  message?: string
}

export interface OnboardingStep {
  id: number
  title: string
  description: string
  completed: boolean
  required: boolean
  route?: string
}

export interface OnboardingProgress {
  current_step: number
  total_steps: number
  completion_percentage: number
  steps: OnboardingStep[]
}

export interface UpdateOnboardingPayload {
  step: number
  data?: Record<string, any>
}

export interface CompleteOnboardingPayload {
  [key: string]: any
}

export class OnboardingApi {
  private readonly baseUrl = API_URL

  /**
   * Get onboarding status
   */
  async getStatus(): Promise<ApiResponse<OnboardingStatus>> {
    return await $api(`${this.baseUrl}/status`)
  }

  /**
   * Get onboarding progress
   */
  async getProgress(): Promise<ApiResponse<OnboardingProgress>> {
    return await $api(`${this.baseUrl}/progress`)
  }

  /**
   * Update onboarding step
   */
  async updateStep(payload: UpdateOnboardingPayload): Promise<ApiResponse<OnboardingStatus>> {
    return await $api(`${this.baseUrl}/step`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Complete onboarding
   */
  async complete(payload?: CompleteOnboardingPayload): Promise<ApiResponse<{ completed: boolean }>> {
    return await $api(`${this.baseUrl}/complete`, {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Reset onboarding (admin only)
   */
  async reset(userId?: number): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/reset`, {
      method: 'POST',
      body: userId ? { user_id: userId } : {},
    })
  }
}

export const onboardingApi = new OnboardingApi()
