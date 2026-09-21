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
    enabled: true
  },

  app: {
    pageTransition: { name: 'page', mode: 'out-in' },
    layoutTransition: { name: 'layout', mode: 'out-in' },
    head: {
      htmlAttrs: {
        dir: 'rtl',
        lang: 'fa-IR'
      },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' }
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

  sitemap: {
    sources: [
      (process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1') + '/sitemap/urls'
    ]
  },

  robots: {
    disallow: ['/cart', '/checkout', '/profile'],
    allow: ['/products', '/categories', '/pages', '/about', '/contact', '/terms', '/faq']
  },

  ogImage: {
    enabled: true
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
  }
})
