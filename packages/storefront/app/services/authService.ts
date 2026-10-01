import type { User, CaptchaChallenge, UserProfileResponse, UpdateUserProfilePayload } from '~/types/user'
import type { ApiResponse } from '~/types/api'

export function useAuthService() {
  const api = useApi()

  async function getCaptcha(): Promise<CaptchaChallenge | null> {
    const res = await api<ApiResponse<CaptchaChallenge>>('/captcha/generate')
    return res.data || null
  }

  async function solveCaptcha(key: string, nonce: string, elapsedMs: number): Promise<boolean> {
    const res = await api<ApiResponse<{ message: string }>>('/captcha/solve', {
      method: 'POST',
      body: { key, nonce, elapsed_ms: elapsedMs }
    })
    return Boolean(res.success)
  }

  async function requestOtp(mobile: string, captchaToken: string): Promise<{ success: boolean, message: string }> {
    const res = await api<ApiResponse<{ message: string }>>('/auth/otp/request', {
      method: 'POST',
      body: { mobile, captcha_token: captchaToken }
    })
    return { success: res.success, message: res.message || '' }
  }

  async function verifyOtp(mobile: string, code: string): Promise<{ token: string, user: User }> {
    const res = await api<ApiResponse<{ token: string, user: User }>>('/auth/otp/verify', {
      method: 'POST',
      body: { mobile, code }
    })
    if (!res.data?.token) {
      throw new Error(res.message || 'کد تایید نامعتبر است')
    }
    return res.data
  }

  async function getProfile(): Promise<UserProfileResponse | null> {
    const res = await api<ApiResponse<UserProfileResponse>>('/profile')
    return res.data || null
  }

  async function updateProfile(payload: UpdateUserProfilePayload): Promise<User> {
    const res = await api<ApiResponse<User>>('/profile', {
      method: 'PUT',
      body: payload
    })
    if (!res.data) {
      throw new Error(res.message || 'خطا در به‌روزرسانی اطلاعات')
    }
    return res.data
  }

  async function getMe(): Promise<User | null> {
    const res = await api<ApiResponse<User>>('/auth/me')
    return res.data || null
  }

  async function logout(): Promise<void> {
    await api('/auth/logout', { method: 'POST' }).catch(() => {})
  }

  return {
    getCaptcha,
    solveCaptcha,
    requestOtp,
    verifyOtp,
    getMe,
    getProfile,
    updateProfile,
    logout
  }
}
