<script setup lang="ts">
import HeaderSearch from '~/components/navigation/HeaderSearch.vue'
import CategoryMegaMenu from '~/components/navigation/CategoryMegaMenu.vue'
import MobileBottomNav from '~/components/navigation/MobileBottomNav.vue'
import AppFooter from '~/components/navigation/AppFooter.vue'

const authStore = useAuthStore()
const cartStore = useCartStore()
const catalogStore = useCatalogStore()
const settingsStore = useSettingsStore()
const { toPersianDigits } = usePersian()

// Fetch public store settings and category tree in SSR
await Promise.all([
  useAsyncData('app-settings', () => settingsStore.fetchSettings()),
  useAsyncData('app-category-tree', () => catalogStore.fetchCategoryTree())
])

useHead({
  htmlAttrs: {
    dir: 'rtl',
    lang: 'fa-IR'
  },
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
    { name: 'description', content: 'خرید آنلاین باکیفیت‌ترین محصولات آرایشی، مراقبت پوست و مو با تضمین اصالت کالا و ارسال سریع' }
  ],
  link: [
    { rel: 'icon', href: settingsStore.settings.store_favicon || '/favicon.ico' }
  ]
})

const title = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')
const description = computed(() => settingsStore.settings.store_slogan || 'خرید آنلاین باکیفیت‌ترین محصولات آرایشی، مراقبت پوست و مو با تضمین اصالت کالا و ارسال سریع')

useSeoMeta({
  title,
  description,
  ogTitle: title,
  ogDescription: description
})

// Fetch cart and user on mount
onMounted(() => {
  cartStore.fetchCart()
  if (authStore.isAuthenticated) {
    authStore.fetchUser()
  }
})
</script>

<template>
  <UApp>
    <!-- Top Announcement Bar -->
    <div class="bg-neutral-900 text-neutral-100 dark:bg-neutral-950 dark:border-b dark:border-neutral-800 text-xs py-2 px-4 transition-colors">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2">
          <UIcon
            name="i-lucide-sparkles"
            class="size-4 text-primary animate-pulse"
          />
          <span class="font-medium text-[11px] sm:text-xs">
            ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان • تضمین ۱۰۰٪ اصالت کالا
          </span>
        </div>
        <div class="hidden md:flex items-center gap-6 text-[11px] text-neutral-400">
          <span class="flex items-center gap-1.5">
            <UIcon
              name="i-lucide-headphones"
              class="size-3.5 text-primary"
            />
            مشاوره پوستی رایگان: ۰۲۱-۸۸۸۸۹۹۹۹
          </span>
          <NuxtLink
            to="/cart"
            class="hover:text-primary transition-colors"
          >
            پیگیری سفارشات
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Sticky Main Header -->
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border-b border-neutral-200/80 dark:border-neutral-800 transition-colors">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Tier 1: Logo + Live Search + User Controls -->
        <div class="h-18 flex items-center justify-between gap-4 sm:gap-6">
          <!-- Logo & Brand Name -->
          <NuxtLink
            to="/"
            class="flex items-center gap-2.5 shrink-0 focus-visible:outline-none"
          >
            <div class="size-10 rounded-2xl bg-gradient-to-tr from-primary to-rose-400 flex items-center justify-center text-white font-black text-xl shadow-md shadow-primary/25">
              {{ settingsStore.settings.store_name?.charAt(0) || 'E' }}
            </div>
            <div class="flex flex-col">
              <span class="font-black text-lg sm:text-xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                {{ settingsStore.settings.store_name || 'ایزیشاپ' }}
              </span>
              <span class="text-[10px] text-neutral-400 font-medium hidden sm:block">
                فروشگاه تخصصی زیبایی و سلامت
              </span>
            </div>
          </NuxtLink>

          <!-- Desktop Live Search -->
          <div class="hidden md:flex flex-1 justify-center px-4">
            <HeaderSearch />
          </div>

          <!-- Actions Group (Theme, Cart, Auth) -->
          <div class="flex items-center gap-2 sm:gap-3">
            <!-- Theme Toggle -->
            <UColorModeButton class="min-h-10 px-2.5 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-xl" />

            <!-- Cart Slideover Trigger -->
            <CartSlideover>
              <button
                type="button"
                class="relative flex items-center justify-center min-h-10 px-3 rounded-xl bg-neutral-100/80 dark:bg-neutral-800/80 hover:bg-neutral-200/80 dark:hover:bg-neutral-700/80 text-neutral-700 dark:text-neutral-200 transition-colors"
                aria-label="سبد خرید"
              >
                <UIcon
                  name="i-lucide-shopping-bag"
                  class="size-5"
                />
                <UBadge
                  v-if="cartStore.itemsCount > 0"
                  color="primary"
                  size="xs"
                  class="absolute -top-1 -right-1 font-bold min-w-5 h-5 flex items-center justify-center rounded-full text-xs shadow-xs"
                >
                  {{ toPersianDigits(cartStore.itemsCount) }}
                </UBadge>
              </button>
            </CartSlideover>

            <!-- User Auth / Profile Button -->
            <template v-if="authStore.isAuthenticated">
              <UDropdownMenu
                :items="[
                  [{
                    label: authStore.user?.full_name || 'کاربر گرامی',
                    icon: 'i-lucide-user',
                    disabled: true
                  }],
                  [{
                    label: 'خروج از حساب',
                    icon: 'i-lucide-log-out',
                    onSelect: () => authStore.logout()
                  }]
                ]"
              >
                <UButton
                  color="neutral"
                  variant="subtle"
                  icon="i-lucide-user"
                  class="min-h-10 px-3 font-bold rounded-xl hidden sm:flex"
                >
                  {{ authStore.user?.full_name || 'حساب من' }}
                </UButton>
              </UDropdownMenu>
            </template>
            <template v-else>
              <UButton
                color="primary"
                variant="solid"
                icon="i-lucide-log-in"
                class="min-h-10 px-4 font-bold rounded-xl shadow-sm shadow-primary/20 hidden sm:flex"
                @click="authStore.openAuthModal"
              >
                ورود / ثبت‌نام
              </UButton>
            </template>
          </div>
        </div>

        <!-- Mobile Search Row (Only visible on mobile) -->
        <div class="md:hidden pb-3">
          <HeaderSearch />
        </div>

        <!-- Header Tier 2: Category Mega Menu & Fast Links (Desktop) -->
        <div class="hidden lg:flex items-center justify-between border-t border-neutral-100 dark:border-neutral-800/80 py-2.5">
          <div class="flex items-center gap-6">
            <!-- Category Mega Menu Button -->
            <CategoryMegaMenu />

            <!-- Nav Links -->
            <nav class="flex items-center gap-5 text-xs font-bold text-neutral-600 dark:text-neutral-300">
              <NuxtLink
                to="/products?sort=featured"
                class="flex items-center gap-1.5 hover:text-primary transition-colors text-rose-600 dark:text-rose-400"
              >
                <UIcon
                  name="i-lucide-flame"
                  class="size-4"
                />
                <span>پیشنهادات شگفت‌انگیز</span>
              </NuxtLink>

              <NuxtLink
                to="/products?sort=popular"
                class="flex items-center gap-1.5 hover:text-primary transition-colors"
              >
                <UIcon
                  name="i-lucide-sparkles"
                  class="size-4"
                />
                <span>پرفروش‌ترین‌ها</span>
              </NuxtLink>

              <NuxtLink
                to="/products"
                class="flex items-center gap-1.5 hover:text-primary transition-colors"
              >
                <UIcon
                  name="i-lucide-store"
                  class="size-4"
                />
                <span>کاتالوگ محصولات</span>
              </NuxtLink>

              <NuxtLink
                to="/categories"
                class="flex items-center gap-1.5 hover:text-primary transition-colors"
              >
                <UIcon
                  name="i-lucide-layers"
                  class="size-4"
                />
                <span>نقشه دسته‌بندی‌ها</span>
              </NuxtLink>
            </nav>
          </div>

          <div class="flex items-center gap-3 text-xs text-neutral-400">
            <span class="flex items-center gap-1 font-medium">
              <UIcon
                name="i-lucide-map-pin"
                class="size-3.5 text-primary"
              />
              ارسال به سراسر ایران
            </span>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Body Container with 8pt spacing and mobile nav safe padding -->
    <main class="min-h-[calc(100vh-18rem)] max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-20 lg:pb-8">
      <NuxtPage />
    </main>

    <!-- Comprehensive Modern Footer -->
    <AppFooter />

    <!-- Mobile Sticky Bottom Navigation Bar (Hidden on desktop) -->
    <MobileBottomNav />

    <!-- Global Auth Modal -->
    <AuthModal />
  </UApp>
</template>
