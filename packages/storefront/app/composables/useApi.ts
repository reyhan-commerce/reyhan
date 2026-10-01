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

function toEnglishDigits(value: string | number | null | undefined): string {
  if (value === null || value === undefined) return ''
  let str = String(value)
  const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']
  for (let i = 0; i < 10; i++) {
    str = str.replace(new RegExp(persianDigits[i]!, 'g'), String(i))
    str = str.replace(new RegExp(arabicDigits[i]!, 'g'), String(i))
  }
  return str
}

const NUMERIC_KEYS = new Set([
  'mobile', 'phone', 'recipient_mobile', 'postal_code', 'code', 'otp',
  'building_number', 'unit', 'quantity', 'national_code', 'price',
  'min_price', 'max_price', 'card_number', 'id', 'user_id', 'product_id',
  'variant_id', 'category_id', 'province_id', 'city_id', 'address_id',
  'order_id', 'item_id'
])

function normalizeNumericPayload(data: unknown): unknown {
  if (!data || typeof data !== 'object') return data

  if (Array.isArray(data)) {
    return data.map(item => normalizeNumericPayload(item))
  }

  const result: Record<string, unknown> = {}
  for (const [key, value] of Object.entries(data as Record<string, unknown>)) {
    if (typeof value === 'string') {
      const lowerKey = key.toLowerCase()
      // Only normalize strictly numeric fields or purely numeric strings (never general text like names/comments)
      if (NUMERIC_KEYS.has(lowerKey) || /^[\d\u06F0-\u06F9\u0660-\u0669\s\-+]+$/.test(value.trim())) {
        result[key] = toEnglishDigits(value)
      } else {
        result[key] = value
      }
    } else if (typeof value === 'object' && value !== null) {
      result[key] = normalizeNumericPayload(value)
    } else {
      result[key] = value
    }
  }
  return result
}

export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://localhost:8000/api/v1'
  const toast = useToast()
  const tokenCookie = useCookie<string | null>('auth_token')
  const localeCookie = useCookie<string>('app_locale', {
    default: () => 'fa',
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production'
  })
  const cartSessionCookie = useCookie<string | null>('cart_session', {
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production'
  })

  const client = $fetch.create({
    baseURL: apiBase,
    onRequest({ options }) {
      // Normalize any numeric payloads (e.g. mobile, OTP, postal codes) to English ASCII digits
      if (options.body && typeof options.body === 'object') {
        options.body = normalizeNumericPayload(options.body) as Record<string, unknown>
      }
      if (options.params && typeof options.params === 'object') {
        options.params = normalizeNumericPayload(options.params) as Record<string, unknown>
      }

      // Attach auth token if available in cookie or store
      const token = tokenCookie.value

      if (token) {
        options.headers.set('Authorization', `Bearer ${token}`)
      }

      if (cartSessionCookie.value) {
        options.headers.set('X-Cart-Session', cartSessionCookie.value)
      }

      // Send requested language to backend via standard Accept-Language header
      const requestedLocale = localeCookie.value || 'fa'
      options.headers.set('Accept-Language', requestedLocale)
      options.headers.set('Accept', 'application/json')
    },
    onResponse({ response }) {
      // Primary: read session from X-Cart-Session response header (requires CORS exposed_headers)
      const sessionHeader = response.headers.get('x-cart-session')
      if (sessionHeader) {
        cartSessionCookie.value = sessionHeader
      }

      // Fallback: read session_id from cart response body (works even if header is CORS-blocked)
      const body = response._data as { data?: { session_id?: string | null } } | undefined
      if (!sessionHeader && body?.data?.session_id) {
        cartSessionCookie.value = body.data.session_id
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
