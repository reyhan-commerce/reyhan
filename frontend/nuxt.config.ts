// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/image',
    '@nuxtjs/seo',
    '@vite-pwa/nuxt'
  ],

  devtools: {
    enabled: false
  },

  app: {
    // Only keep page transition; layout transition + nested <Transition> components
    // can cause "Symbol(_leaveCb)" crashes during rapid navigation (Vue SSR bug).
    pageTransition: { name: 'page', mode: 'out-in' },
    head: {
      htmlAttrs: {
        dir: 'rtl',
        lang: 'fa-IR'
      },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
        { name: 'theme-color', content: '#2563eb' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'default' }
      ],
      link: [
        { rel: 'manifest', href: '/manifest.webmanifest' },
        { rel: 'icon', type: 'image/svg+xml', href: '/icon.svg' },
        { rel: 'apple-touch-icon', href: '/icon.svg' }
      ]
    }
  },

  css: ['~/assets/css/main.css'],

  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://shop.local',
    defaultLocale: 'fa-IR',
    name: process.env.NUXT_PUBLIC_SITE_NAME || 'فروشگاه اینترنتی',
    description: process.env.NUXT_PUBLIC_SITE_DESCRIPTION || 'خرید آنلاین با ضمانت اصالت کالا و ارسال سریع'
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1'
    }
  },

  routeRules: {
    '/': { prerender: true },
    '/about': { swr: 3600 },
    '/contact': { swr: 3600 },
    '/terms': { swr: 3600 },
    '/faq': { swr: 3600 }
  },

  compatibilityDate: '2026-06-30',

  typescript: {
    strict: true
  },

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },

  ogImage: {
    enabled: true,
    // Provide explicit dimensions to suppress "og:image:width/height missing" warning
    defaults: {
      width: 1200,
      height: 630
    }
  },

  pwa: {
    registerType: 'autoUpdate',
    manifest: {
      name: process.env.NUXT_PUBLIC_SITE_NAME || 'فروشگاه اینترنتی',
      short_name: process.env.NUXT_PUBLIC_SITE_SHORT_NAME || 'فروشگاه',
      description: process.env.NUXT_PUBLIC_SITE_DESCRIPTION || 'خرید آنلاین با ضمانت اصالت کالا و ارسال سریع',
      theme_color: '#2563eb',
      background_color: '#ffffff',
      display: 'standalone',
      orientation: 'portrait',
      scope: '/',
      start_url: '/',
      dir: 'rtl',
      lang: 'fa-IR',
      categories: ['shopping', 'lifestyle'],
      icons: [
        {
          src: '/icon.svg',
          sizes: 'any',
          type: 'image/svg+xml',
          purpose: 'any'
        },
        {
          src: '/icon.svg',
          sizes: '192x192 512x512',
          type: 'image/svg+xml',
          purpose: 'maskable'
        }
      ]
    },
    workbox: {
      navigateFallback: '/',
      globPatterns: ['**/*.{js,css,html,png,svg,ico,woff,woff2}'],
      runtimeCaching: [
        {
          urlPattern: /^https:\/\/fonts\.(?:googleapis|gstatic)\.com\/.*/i,
          handler: 'CacheFirst',
          options: {
            cacheName: 'google-fonts',
            expiration: {
              maxEntries: 10,
              maxAgeSeconds: 60 * 60 * 24 * 365
            }
          }
        },
        {
          urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|avif)$/i,
          handler: 'StaleWhileRevalidate',
          options: {
            cacheName: 'image-assets',
            expiration: {
              maxEntries: 60,
              maxAgeSeconds: 60 * 60 * 24 * 30
            }
          }
        }
      ]
    },
    client: {
      installPrompt: true
    },
    devOptions: {
      enabled: false,
      type: 'module'
    }
  },

  robots: {
    disallow: ['/cart', '/checkout', '/profile'],
    allow: ['/products', '/categories', '/pages', '/about', '/contact', '/terms', '/faq']
  },

  sitemap: {
    sources: [
      (process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1') + '/sitemap/urls'
    ]
  }
})
