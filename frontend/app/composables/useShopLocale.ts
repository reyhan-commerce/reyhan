import faDict from '~/locales/fa.json'
import enDict from '~/locales/en.json'

type LocaleDictionary = typeof faDict

export const useShopLocale = () => {
  const localeCookie = useCookie<'fa' | 'en'>('app_locale', {
    default: () => 'fa',
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production'
  })

  const currentLocale = useState<'fa' | 'en'>('reyhan_locale', () => localeCookie.value || 'fa')
  const appConfig = useAppConfig()

  const dictionaries: Record<'fa' | 'en', LocaleDictionary> = {
    fa: faDict,
    en: enDict
  }

  // Synchronize document direction and lang with current locale
  useHead(() => ({
    htmlAttrs: {
      lang: currentLocale.value === 'fa' ? 'fa-IR' : 'en-US',
      dir: currentLocale.value === 'fa' ? 'rtl' : 'ltr'
    }
  }))

  /**
   * Translate a dotted key with dynamic parameter interpolation and fallback.
   * Allows user override from appConfig.reyhan.translations.
   */
  const t = (key: string, params?: Record<string, string | number> | string, fallback?: string): string => {
    const fallbackText = typeof params === 'string' ? params : fallback
    const variables = typeof params === 'object' && params !== null ? params : {}

    let translated: string | undefined

    // 1. Check user custom override in app.config.ts
    const userOverrides = (appConfig as any)?.reyhan?.translations?.[currentLocale.value]
    if (userOverrides && typeof userOverrides === 'object') {
      const parts = key.split('.')
      let target: any = userOverrides
      for (const part of parts) {
        if (target && typeof target === 'object' && part in target) {
          target = target[part]
        } else {
          target = undefined
          break
        }
      }
      if (typeof target === 'string') {
        translated = target
      }
    }

    // 2. Resolve from built-in locale dictionary
    if (!translated) {
      const dict = dictionaries[currentLocale.value] || faDict
      const parts = key.split('.')
      let current: any = dict

      for (const part of parts) {
        if (current && typeof current === 'object' && part in current) {
          current = current[part]
        } else {
          current = undefined
          break
        }
      }

      if (typeof current === 'string') {
        translated = current
      }
    }

    let result = translated || fallbackText || key

    // Interpolate dynamic parameters like {name} or :name
    for (const [pKey, pVal] of Object.entries(variables)) {
      result = result.replace(new RegExp(`{${pKey}}`, 'g'), String(pVal))
      result = result.replace(new RegExp(`:${pKey}`, 'g'), String(pVal))
    }

    return result
  }

  const setLocale = (locale: 'fa' | 'en') => {
    currentLocale.value = locale
    localeCookie.value = locale
  }

  const isRtl = computed(() => currentLocale.value === 'fa')

  return {
    locale: readonly(currentLocale),
    isRtl,
    t,
    setLocale
  }
}

