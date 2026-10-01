import { defineEventHandler, setHeader } from 'h3'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://localhost:8000/api/v1'

  // Fetch live settings and theme styling tokens from backend
  const [settingsRes, themeRes] = await Promise.all([
    $fetch<{ data?: Record<string, unknown> }>(`${apiBase}/settings/public`).catch(() => null),
    $fetch<{ data?: Record<string, unknown> }>(`${apiBase}/settings/theme`).catch(() => null)
  ])

  const settings = settingsRes?.data ?? {}
  const theme = themeRes?.data ?? {}

  const storeName = typeof settings.store_name === 'string' && settings.store_name.trim()
    ? settings.store_name.trim()
    : 'فروشگاه اینترنتی'

  const storeSlogan = typeof settings.store_slogan === 'string' && settings.store_slogan.trim()
    ? settings.store_slogan.trim()
    : 'خرید آنلاین با ضمانت اصالت کالا و ارسال سریع'

  const themeColor = typeof theme.primary_color === 'string' && theme.primary_color.trim()
    ? theme.primary_color.trim()
    : '#2563eb'

  const appIcon = typeof settings.store_favicon === 'string' && settings.store_favicon.trim()
    ? settings.store_favicon.trim()
    : '/icon.svg'

  const manifest = {
    name: storeName,
    short_name: storeName,
    description: storeSlogan,
    theme_color: themeColor,
    background_color: '#ffffff',
    display: 'standalone',
    orientation: 'portrait',
    scope: '/',
    start_url: '/',
    dir: 'rtl',
    lang: 'fa-IR',
    categories: ['shopping', 'beauty', 'lifestyle'],
    icons: [
      {
        src: appIcon,
        sizes: 'any',
        type: 'image/svg+xml',
        purpose: 'any'
      },
      {
        src: appIcon,
        sizes: '192x192 512x512',
        type: 'image/svg+xml',
        purpose: 'maskable'
      }
    ]
  }

  setHeader(event, 'Content-Type', 'application/manifest+json; charset=utf-8')
  setHeader(event, 'Cache-Control', 'public, max-age=300, s-maxage=3600')

  return manifest
})
