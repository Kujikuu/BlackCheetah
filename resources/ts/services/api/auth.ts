import { $api } from '@/utils/api'

// Type definitions
export interface LoginPayload {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: string
}

export interface ForgotPasswordPayload {
  email: string
}

export interface ResetPasswordPayload {
  email: string
  token: string
  password: string
  password_confirmation: string
}

export interface ValidateTokenPayload {
  email: string
  token: string
}

export interface AuthResponse {
  accessToken: string
  userData: {
    id: number
    fullName: string
    username: string
    avatar?: string
    email: string
    role: string
    status?: string
    nationality?: string
  }
  userAbilityRules: any[]
}

export interface ApiResponse<T = any> {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

export class AuthApi {
  /**
   * Login user
   */
  async login(payload: LoginPayload): Promise<AuthResponse> {
    return await $api('/v1/auth/login', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Register new user
   */
  async register(payload: RegisterPayload): Promise<ApiResponse> {
    return await $api('/v1/auth/register', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Logout user
   */
  async logout(): Promise<ApiResponse> {
    return await $api('/v1/auth/logout', {
      method: 'POST',
    })
  }

  /**
   * Get authenticated user
   */
  async me(): Promise<ApiResponse> {
    return await $api('/v1/auth/me', {
      method: 'GET',
    })
  }

  /**
   * Send password reset link
   */
  async forgotPassword(payload: ForgotPasswordPayload): Promise<ApiResponse> {
    return await $api('/v1/auth/forgot-password', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Reset password with token
   */
  async resetPassword(payload: ResetPasswordPayload): Promise<ApiResponse> {
    return await $api('/v1/auth/reset-password', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Validate password reset token
   */
  async validateResetToken(payload: ValidateTokenPayload): Promise<ApiResponse> {
    return await $api('/v1/auth/validate-reset-token', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Send email verification notification
   */
  async sendVerificationEmail(): Promise<ApiResponse> {
    return await $api('/v1/auth/email/verification-notification', {
      method: 'POST',
    })
  }

  /**
   * Check email verification status
   */
  async checkVerificationStatus(): Promise<ApiResponse> {
    return await $api('/v1/auth/email/verification-status', {
      method: 'GET',
    })
  }

  /**
   * Verify email with signed URL
   */
  async verifyEmail(id: number, hash: string, expires: string, signature: string): Promise<ApiResponse> {
    return await $api(`/v1/auth/email/verify/${id}/${hash}?expires=${expires}&signature=${signature}`, {
      method: 'GET',
    })
  }
}

export const authApi = new AuthApi()
