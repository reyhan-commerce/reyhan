import { defineStore } from 'pinia'
import type { User, CaptchaChallenge } from '~/types/user'
import { useAuthService } from '~/services/authService'

// Re-export types for backward compatibility
export type { User, CaptchaChallenge }

export const useAuthStore = defineStore('auth', () => {
  const authService = useAuthService()
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
    return authService.getCaptcha()
  }

  const solveCaptcha = async (key: string, nonce: string, elapsedMs: number): Promise<boolean> => {
    try {
      return await authService.solveCaptcha(key, nonce, elapsedMs)
    } catch (error) {
      console.error('[authStore] solveCaptcha failed:', error)
      return false
    }
  }

  const requestOtp = async (mobile: string, captchaToken: string): Promise<boolean> => {
    isLoading.value = true
    try {
      const res = await authService.requestOtp(mobile, captchaToken)
      return res.success
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const verifyOtp = async (mobile: string, code: string): Promise<boolean> => {
    isLoading.value = true
    try {
      const res = await authService.verifyOtp(mobile, code)
      if (res?.token) {
        token.value = res.token
        tokenCookie.value = res.token
        user.value = res.user
        closeAuthModal()

        // Sync guest cart to the newly-authenticated user account.
        // syncGuestCart reads the token directly from authStore.token (not via cookie),
        // so no nextTick delay is needed here.
        const cartStore = useCartStore()
        await cartStore.syncGuestCart()

        return true
      }
      return false
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const fetchUser = async (force = false): Promise<User | null> => {
    if (!token.value) return null
    if (user.value && !force) return user.value

    isLoading.value = true
    try {
      const userData = await authService.getMe()
      if (userData) {
        user.value = userData
        return userData
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
      await authService.logout()
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
