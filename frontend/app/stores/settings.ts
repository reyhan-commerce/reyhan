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

export interface TrustBadge {
  icon: string
  title: string
  desc: string
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
  // Announcement Bar
  announcement_enabled: boolean
  announcement_text: string | null
  announcement_link: string | null
  // Hero Banner
  hero_badge_text: string | null
  hero_primary_button_text: string | null
  hero_secondary_button_text: string | null
  // Trust Badges
  trust_badges: TrustBadge[]
  // Home Section Titles & Buttons
  categories_title: string | null
  categories_button_text: string | null
  flash_deals_title: string | null
  flash_deals_subtitle: string | null
  featured_products_title: string | null
  featured_products_button_text: string | null
  blog_title: string | null
  blog_button_text: string | null
  brands_title: string | null
  // Footer
  footer_about_text: string | null
  footer_copyright_text: string | null
  footer_designer_credit: string | null
  theme?: ThemeTokens
}

export const useSettingsStore = defineStore('settings', () => {
  const api = useApi()

  const settings = ref<StoreSettings>({
    store_name: 'فروشگاه آنلاین',
    store_slogan: null,
    store_logo: null,
    store_favicon: null,
    support_phone: null,
    support_email: null,
    address: null,
    postal_code: null,
    work_hours: null,
    free_shipping_threshold: 500000,
    is_store_open: true,
    maintenance_message: null,
    instagram_url: null,
    telegram_url: null,
    whatsapp_url: null,
    enamad_code: null,
    announcement_enabled: true,
    announcement_text: null,
    announcement_link: null,
    hero_badge_text: null,
    hero_primary_button_text: null,
    hero_secondary_button_text: null,
    trust_badges: [],
    categories_title: null,
    categories_button_text: null,
    flash_deals_title: null,
    flash_deals_subtitle: null,
    featured_products_title: null,
    featured_products_button_text: null,
    blog_title: null,
    blog_button_text: null,
    brands_title: null,
    footer_about_text: null,
    footer_copyright_text: null,
    footer_designer_credit: null,
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
