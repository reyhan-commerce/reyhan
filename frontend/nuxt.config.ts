// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/image',
    '@nuxtjs/seo'
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
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://easyshop.ir',
    defaultLocale: 'fa-IR',
    name: 'فروشگاه اینترنتی ایزیشاپ',
    description: 'مرجع تخصصی خرید آنلاین محصولات آرایشی، بهداشتی و مراقبت از پوست اورجینال'
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1'
    }
  },

  routeRules: {
    '/': { prerender: true }
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
