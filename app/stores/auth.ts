import { defineStore } from 'pinia'

export interface User {
  id: number
  first_name: string | null
  last_name: string | null
  full_name: string
  national_code: string | null
  mobile: string
  email: string | null
  avatar: string | null
  is_active: boolean
  created_at?: string
}

export interface CaptchaChallenge {
  key: string
  salt: string
  difficulty: number
}

export const useAuthStore = defineStore('auth', () => {
  const api = useApi()
  const tokenCookie = useCookie<string | null>('auth_token', {
    maxAge: 60 * 60 * 24 * 30, // 30 days
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production'
  })

  const token = ref<string | null>(tokenCookie.value)
  const user = ref<User | null>(null)
  const isAuthModalOpen = ref(false)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => Boolean(token.value))

  const openAuthModal = () => {
    isAuthModalOpen.value = true
  }

  const closeAuthModal = () => {
    isAuthModalOpen.value = false
  }

  const fetchCaptcha = async (): Promise<CaptchaChallenge | null> => {
    try {
      const response = await api<ApiResponse<CaptchaChallenge>>('/captcha/generate')
      return response.data || null
    } catch {
      return null
    }
  }

  const solveCaptcha = async (key: string, nonce: string, elapsedMs: number): Promise<boolean> => {
    try {
      const response = await api<ApiResponse<{ message: string }>>('/captcha/solve', {
        method: 'POST',
        body: {
          key,
          nonce,
          elapsed_ms: elapsedMs
        }
      })
      return Boolean(response.success)
    } catch (error) {
      console.error('[authStore] solveCaptcha failed:', error)
      return false
    }
  }

  const requestOtp = async (mobile: string, captchaToken: string): Promise<boolean> => {
    isLoading.value = true
    try {
      await api<ApiResponse<{ expires_in: number }>>('/auth/otp/request', {
        method: 'POST',
        body: {
          mobile,
          captcha_token: captchaToken
        }
      })
      return true
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const verifyOtp = async (mobile: string, code: string, deviceName = 'browser'): Promise<boolean> => {
    isLoading.value = true
    try {
      const response = await api<ApiResponse<{ token: string, user: User }>>('/auth/otp/verify', {
        method: 'POST',
        body: {
          mobile,
          code,
          device_name: deviceName
        }
      })

      if (response.data) {
        token.value = response.data.token
        tokenCookie.value = response.data.token
        user.value = response.data.user
        closeAuthModal()
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const fetchUser = async (): Promise<User | null> => {
    if (!token.value) return null

    isLoading.value = true
    try {
      const response = await api<ApiResponse<User>>('/auth/me')
      if (response.data) {
        user.value = response.data
        return response.data
      }
      return null
    } catch {
      user.value = null
      token.value = null
      tokenCookie.value = null
      return null
    } finally {
      isLoading.value = false
    }
  }

  const logout = async (): Promise<void> => {
    if (token.value) {
      try {
        await api('/auth/logout', { method: 'POST' })
      } catch {
        // Ignore logout network errors
      }
    }

    token.value = null
    tokenCookie.value = null
    user.value = null
  }

  return {
    token,
    user,
    isAuthenticated,
    isAuthModalOpen,
    isLoading,
    openAuthModal,
    closeAuthModal,
    fetchCaptcha,
    solveCaptcha,
    requestOtp,
    verifyOtp,
    fetchUser,
    logout
  }
})
