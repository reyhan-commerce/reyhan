<script setup lang="ts">
import { fa_ir } from '@nuxt/ui/locale'
import AuthModal from '~/components/auth/AuthModal.vue'

const settingsStore = useSettingsStore()
const catalogStore = useCatalogStore()
const features = useFeatures()
const { themeCssVariables } = useTheme()

const authStore = useAuthStore()
const cartStore = useCartStore()

// Fetch public store settings, features, category tree, cart and authenticated user in SSR
await Promise.all([
  useAsyncData('app-settings', () => settingsStore.fetchSettings().then(v => v ?? null)),
  useAsyncData('app-features', () => features.fetchFeatures()),
  useAsyncData('app-category-tree', () => catalogStore.fetchCategoryTree().then(v => v.length > 0 ? v : null)),
  useAsyncData('app-cart', () => cartStore.fetchCart().then(() => true)),
  authStore.isAuthenticated
    ? useAsyncData('app-user', () => authStore.fetchUser().then(v => v ?? null))
    : Promise.resolve(null)
])

useHead({
  htmlAttrs: {
    dir: 'rtl',
    lang: 'fa-IR'
  },
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
    { name: 'description', content: 'خرید آنلاین با بهترین قیمت، تضمین اصالت کالا و ارسال سریع' }
  ],
  link: [
    { rel: 'icon', href: settingsStore.settings.store_favicon || '/favicon.ico' }
  ],
  style: [
    { innerHTML: () => themeCssVariables.value, id: 'app-dynamic-theme' }
  ]
})

const storeName = computed(() => settingsStore.settings.store_name || 'فروشگاه آنلاین')
const description = computed(() => settingsStore.settings.store_slogan || 'خرید آنلاین با تضمین اصالت کالا و ارسال سریع')

// Global title template: every page just provides its own chunk,
// this appends store name automatically — no more hardcoded ایزیشاپ in pages.
useHead({
  titleTemplate: (titleChunk) => titleChunk ? `${titleChunk} | ${storeName.value}` : storeName.value
})

useSeoMeta({
  title: storeName,
  description,
  ogTitle: storeName,
  ogDescription: description
})
</script>

<template>
  <UApp :locale="fa_ir">
    <!-- Nuxt Progress Bar Indicator (Themed) -->
    <NuxtLoadingIndicator
      color="repeating-linear-gradient(to right, var(--color-primary-500, #0284c7) 0%, var(--color-primary-400, #38bdf8) 50%, var(--color-primary-600, #0369a1) 100%)"
      :height="3"
      :duration="2000"
      :throttle="100"
      error-color="#ef4444"
    />

    <!-- Nuxt Layout Router Outlet -->
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>

    <!-- Global Auth Modal accessible from any page -->
    <AuthModal />
  </UApp>
</template>
