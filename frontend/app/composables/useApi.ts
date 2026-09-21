export interface ApiResponse<T = unknown> {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

export interface ApiErrorResponse {
  success: boolean
  message: string
  errors?: Record<string, string[]>
}

export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://localhost:8000/api/v1'
  const toast = useToast()
  const tokenCookie = useCookie<string | null>('auth_token')
  const cartSessionCookie = useCookie<string | null>('cart_session', {
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production'
  })

  const client = $fetch.create({
    baseURL: apiBase,
    onRequest({ options }) {
      // Attach auth token if available in cookie or store
      const token = tokenCookie.value

      if (token) {
        options.headers.set('Authorization', `Bearer ${token}`)
      }

      if (cartSessionCookie.value) {
        options.headers.set('X-Cart-Session', cartSessionCookie.value)
      }

      options.headers.set('Accept', 'application/json')
    },
    onResponse({ response }) {
      const sessionHeader = response.headers.get('x-cart-session')
      if (sessionHeader) {
        cartSessionCookie.value = sessionHeader
      }
    },
    onResponseError({ response }) {
      const errorData = response._data as ApiErrorResponse | undefined
      const message = errorData?.message || 'خطایی در برقراری ارتباط با سرور رخ داد.'

      if (response.status === 401) {
        // Clear token on 401 unauthorized
        tokenCookie.value = null
      }

      // Show user-friendly toast for client errors
      if (response.status >= 400 && response.status < 500) {
        toast.add({
          title: 'خطا',
          description: message,
          color: 'error',
          icon: 'i-lucide-alert-circle'
        })
      } else if (response.status >= 500) {
        toast.add({
          title: 'خطای سرور',
          description: 'متاسفانه در حال حاضر ارتباط با سرور ممکن نیست. لطفاً دقایقی دیگر تلاش فرمایید.',
          color: 'error',
          icon: 'i-lucide-server-crash'
        })
      }
    }
  })

  return client
}
