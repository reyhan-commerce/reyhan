import { defineStore } from 'pinia'

export interface ThemeTokens {
  primary_color: string
  secondary_color: string
  border_radius: string
  spacing_scale: string
  shadow_scale: string
  blur_scale: string
  font_family: string
  font_scale: string
  logo_light: string | null
  logo_dark: string | null
  favicon: string | null
}

export interface StoreSettings {
  store_name: string
  store_slogan: string | null
  store_logo: string | null
  store_favicon: string | null
  support_phone: string | null
  support_email: string | null
  address: string | null
  postal_code: string | null
  work_hours: string | null
  free_shipping_threshold: number
  is_store_open: boolean
  maintenance_message: string | null
  instagram_url: string | null
  telegram_url: string | null
  whatsapp_url: string | null
  enamad_code: string | null
  theme?: ThemeTokens
}

export const useSettingsStore = defineStore('settings', () => {
  const api = useApi()

  const settings = ref<StoreSettings>({
    store_name: 'فروشگاه اینترنتی ایزیشاپ',
    store_slogan: 'تخصصی‌ترین مرجع لوازم آرایشی و بهداشتی اصل',
    store_logo: null,
    store_favicon: null,
    support_phone: '۰۲۱-۸۸۸۸۸۸۸۸',
    support_email: 'support@easyshop.local',
    address: 'تهران، خیابان ولیعصر',
    postal_code: '1999999999',
    work_hours: 'شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴',
    free_shipping_threshold: 500000,
    is_store_open: true,
    maintenance_message: null,
    instagram_url: 'https://instagram.com/easyshop',
    telegram_url: 'https://t.me/easyshop',
    whatsapp_url: null,
    enamad_code: null,
    theme: {
      primary_color: '#e11d48',
      secondary_color: '#0284c7',
      border_radius: '0.25rem',
      spacing_scale: 'normal',
      shadow_scale: 'md',
      blur_scale: 'md',
      font_family: 'Vazirmatn',
      font_scale: '1rem',
      logo_light: null,
      logo_dark: null,
      favicon: null
    }
  })

  const isLoading = ref(false)
  const isLoaded = ref(false)

  const fetchSettings = async (): Promise<StoreSettings> => {
    if (isLoaded.value) {
      return settings.value
    }

    isLoading.value = true
    try {
      const response = await api<ApiResponse<StoreSettings>>('/app/settings')
      if (response.data) {
        settings.value = response.data
        isLoaded.value = true
      }
    } catch (error) {
      console.error('[SettingsStore] Failed to fetch store settings:', error)
    } finally {
      isLoading.value = false
    }

    return settings.value
  }

  return {
    settings,
    isLoading,
    isLoaded,
    fetchSettings
  }
})
