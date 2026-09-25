<script setup lang="ts">
import HeaderSearch from '~/components/navigation/HeaderSearch.vue'
import CategoryMegaMenu from '~/components/navigation/CategoryMegaMenu.vue'
import MobileBottomNav from '~/components/navigation/MobileBottomNav.vue'
import AppFooter from '~/components/navigation/AppFooter.vue'
import CartSlideover from '~/components/cart/CartSlideover.vue'

const authStore = useAuthStore()
const cartStore = useCartStore()
const settingsStore = useSettingsStore()
const { toPersianDigits } = usePersian()
const features = useFeatures()

// Fetch cart and user on mount if not already populated
onMounted(() => {
  if (!cartStore.cart) {
    cartStore.fetchCart()
  }
  if (authStore.isAuthenticated && !authStore.user) {
    authStore.fetchUser()
  }
})

const userDropdownItems = computed(() => {
  const profileItems = [
    {
      label: 'سفارش‌های من',
      description: 'پیگیری و مشاهده فاکتورها',
      icon: 'i-lucide-package',
      to: '/profile/orders'
    },
    ...(features.hasFeature('loyalty')
      ? [{
          label: 'باشگاه مشتریان (VIP)',
          description: 'امتیازات و سطوح وفاداری',
          icon: 'i-lucide-crown',
          to: '/profile/club'
        }]
      : []),
    {
      label: 'لیست علاقه‌مندی‌ها',
      description: 'کالاهای برگزیده شما',
      icon: 'i-lucide-heart',
      to: '/profile/wishlist'
    },
    {
      label: 'آدرس‌های تحویل',
      description: 'مدیریت مقاصد ارسال سفارش',
      icon: 'i-lucide-map-pin',
      to: '/profile/addresses'
    },
    {
      label: 'اطلاعات حساب کاربری',
      description: 'ویرایش مشخصات و امنیت',
      icon: 'i-lucide-user-cog',
      to: '/profile/settings'
    }
  ]

  return [
    profileItems,
    [
      {
        label: 'خروج از حساب',
        icon: 'i-lucide-log-out',
        color: 'error' as const,
        onSelect: () => authStore.logout()
      }
    ]
  ]
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-stone-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 transition-colors">
    <!-- Top Announcement Bar (fully dynamic from backend) -->
    <component
      :is="settingsStore.settings.announcement_link ? 'a' : 'aside'"
      v-if="settingsStore.settings.announcement_enabled && settingsStore.settings.announcement_text"
      :href="settingsStore.settings.announcement_link || undefined"
      aria-label="اعلان‌های ویژه"
      class="bg-neutral-900 text-neutral-100 dark:bg-neutral-950 dark:border-b dark:border-neutral-800 text-xs py-2 px-4 transition-colors block"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2">
          <UIcon
            name="i-lucide-sparkles"
            class="size-4 text-primary animate-pulse"
          />
          <span class="font-medium text-[11px] sm:text-xs">
            {{ settingsStore.settings.announcement_text }}
          </span>
        </div>
        <div class="hidden md:flex items-center gap-6 text-[11px] text-neutral-400">
          <span
            v-if="settingsStore.settings.support_phone"
            class="flex items-center gap-1.5"
          >
            <UIcon
              name="i-lucide-headphones"
              class="size-3.5 text-primary"
            />
            {{ settingsStore.settings.support_phone }}
          </span>
          <NuxtLink
            to="/cart"
            class="hover:text-primary transition-colors"
          >
            پیگیری سفارشات
          </NuxtLink>
        </div>
      </div>
    </component>

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
            <div class="size-10 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-400 flex items-center justify-center text-white font-black text-xl shadow-md shadow-primary/25">
              {{ settingsStore.settings.store_name?.charAt(0) || 'E' }}
            </div>
            <div class="flex flex-col">
              <span class="font-black text-lg sm:text-xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                {{ settingsStore.settings.store_name }}
              </span>
              <span class="text-[10px] text-neutral-400 font-medium hidden sm:block">
                {{ settingsStore.settings.store_slogan || 'فروشگاه اینترنتی مدرن' }}
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
                v-if="authStore.user"
                :items="userDropdownItems"
                :content="{ align: 'start', sideOffset: 8 }"
                :ui="{
                  content: 'w-64 p-2 rounded-2xl shadow-xl shadow-neutral-900/10 dark:shadow-neutral-950/40 border border-neutral-200/80 dark:border-neutral-800 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md',
                  item: 'rounded-xl px-2.5 py-2 text-xs font-medium cursor-pointer transition-colors',
                  itemLeadingIcon: 'size-4.5',
                  itemLabel: 'font-bold text-xs',
                  itemDescription: 'text-[10px] text-neutral-400 dark:text-neutral-500'
                }"
              >
                <button
                  type="button"
                  class="group hidden sm:flex items-center gap-2.5 min-h-10 px-3 py-1.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800/80 hover:bg-neutral-200/80 dark:hover:bg-neutral-700/80 transition-all border border-neutral-200/50 dark:border-neutral-700/50 cursor-pointer text-start"
                >
                  <div class="size-6.5 rounded-lg bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-[11px] font-black shadow-xs shadow-primary-500/20 shrink-0">
                    {{ authStore.user.first_name?.[0] || 'ک' }}
                  </div>
                  <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 max-w-[100px] truncate">
                    {{ authStore.user.full_name }}
                  </span>
                  <UIcon
                    name="i-lucide-chevron-down"
                    class="size-3.5 text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-transform duration-200"
                  />
                </button>

                <template #content-top>
                  <NuxtLink
                    to="/profile"
                    class="flex items-center gap-3 p-2.5 mb-1.5 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 hover:bg-primary-50/70 dark:hover:bg-primary-950/40 border border-neutral-100 dark:border-neutral-800/80 transition-colors group cursor-pointer"
                  >
                    <div class="size-9 rounded-xl bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-sm font-black shadow-sm shadow-primary-500/25 shrink-0">
                      {{ authStore.user.first_name?.[0] || 'ک' }}
                    </div>
                    <div class="flex flex-col min-w-0 flex-1">
                      <span class="text-xs font-black text-neutral-900 dark:text-neutral-100 truncate group-hover:text-primary-600 transition-colors">
                        {{ authStore.user.full_name }}
                      </span>
                      <span class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 truncate [direction:ltr] text-right mt-0.5">
                        {{ authStore.user.mobile || 'مشاهده پنل کاربری' }}
                      </span>
                    </div>
                    <UIcon
                      name="i-lucide-chevron-left"
                      class="size-4 text-neutral-400 group-hover:text-primary-600 transition-transform group-hover:-translate-x-0.5 shrink-0"
                    />
                  </NuxtLink>
                </template>
              </UDropdownMenu>

              <!-- Skeleton Button while user data is loading -->
              <div
                v-else
                class="hidden sm:flex items-center gap-2.5 min-h-10 px-3 py-1.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800/80 border border-neutral-200/50 dark:border-neutral-700/50"
              >
                <USkeleton class="size-6.5 rounded-lg" />
                <USkeleton class="w-16 h-3.5 rounded-md" />
              </div>
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
                class="flex items-center gap-1.5 hover:text-primary transition-colors text-primary-600 dark:text-primary-400"
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

              <NuxtLink
                v-if="features.hasFeature('blog')"
                to="/blog"
                class="flex items-center gap-1.5 hover:text-primary transition-colors"
              >
                <UIcon
                  name="i-lucide-book-open"
                  class="size-4"
                />
                <span>مجله و مقالات</span>
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
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-20 lg:pb-8">
      <slot />
    </main>

    <!-- Comprehensive Modern Footer -->
    <AppFooter />

    <!-- Mobile Sticky Bottom Navigation Bar (Hidden on desktop) -->
    <MobileBottomNav />
  </div>
</template>
