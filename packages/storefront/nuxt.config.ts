import { fileURLToPath } from 'node:url'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
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
    pageTransition: { name: 'page', mode: 'out-in' },
    head: {
      htmlAttrs: {
        dir: 'rtl',
        lang: 'fa-IR'
      },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
        { name: 'theme-color', content: '#10b981' }
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/icon.svg' }
      ]
    }
  },

  css: [fileURLToPath(new URL('./app/assets/css/main.css', import.meta.url))],

  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000',
    defaultLocale: 'fa-IR',
    name: process.env.NUXT_PUBLIC_SITE_NAME || 'فروشگاه اینترنتی ریحان',
    description: process.env.NUXT_PUBLIC_SITE_DESCRIPTION || 'پلتفرم خرید آنلاین با ضمانت اصالت کالا و ارسال سریع'
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1'
    }
  },

  compatibilityDate: '2026-06-30',

  typescript: {
    strict: true
  }
})
