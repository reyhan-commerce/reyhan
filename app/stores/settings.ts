import { defineStore } from 'pinia'

export interface StoreSettings {
  store_name: string
  store_slogan: string | null
  store_logo: string | null
  store_favicon: string | null
  support_phone: string | null
  support_email: string | null
  address: string | null
  postal_code: string | null
  free_shipping_threshold: number
  is_store_open: boolean
  maintenance_message: string | null
  instagram_url: string | null
  telegram_url: string | null
  enamad_code: string | null
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
    free_shipping_threshold: 500000,
    is_store_open: true,
    maintenance_message: null,
    instagram_url: 'https://instagram.com/easyshop',
    telegram_url: 'https://t.me/easyshop',
    enamad_code: null
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
