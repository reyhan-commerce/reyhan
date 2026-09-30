import faDict from '~/locales/fa.json'
import enDict from '~/locales/en.json'

type LocaleDictionary = typeof faDict

export const useShopLocale = () => {
  const currentLocale = useState<'fa' | 'en'>('easyshop_locale', () => 'fa')
  const appConfig = useAppConfig()

  const dictionaries: Record<'fa' | 'en', LocaleDictionary> = {
    fa: faDict,
    en: enDict
  }

  /**
   * Translate a dotted key with optional fallback.
   * Allows user override from appConfig.easyshop.translations.
   */
  const t = (key: string, fallback?: string): string => {
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
        return target
      }
    }

    // 2. Resolve from built-in locale dictionary
    const dict = dictionaries[currentLocale.value] || faDict
    const parts = key.split('.')
    let current: any = dict

    for (const part of parts) {
      if (current && typeof current === 'object' && part in current) {
        current = current[part]
      } else {
        return fallback || key
      }
    }

    return typeof current === 'string' ? current : (fallback || key)
  }

  const setLocale = (locale: 'fa' | 'en') => {
    currentLocale.value = locale
  }

  return {
    locale: readonly(currentLocale),
    t,
    setLocale
  }
}
