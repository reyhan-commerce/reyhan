<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useWishlistStore } from '~/stores/wishlist'

import type { User } from '~/types/user'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'حساب کاربری من - فروشگاه ایزیشاپ',
  description: 'مدیریت سفارش‌ها، آدرس‌های تحویل، لیست علاقه‌مندی‌ها و اطلاعات هویتی'
})

const authStore = useAuthStore()
const wishlistStore = useWishlistStore()
const route = useRoute()
const router = useRouter()
const api = useApi()
const { toPersianDigits } = usePersian()

const stats = ref({
  orders: 0,
  wishlist: 0,
  addresses: 0
})

const fetchProfileStats = async () => {
  try {
    const res = await api<{
      success: boolean
      data: {
        user: User
        counts: { orders: number, wishlist: number, addresses: number }
      }
    }>('/profile')
    if (res.data?.counts) {
      stats.value = res.data.counts
    }
  } catch {
    // fallback
  }
}

onMounted(() => {
  fetchProfileStats()
  wishlistStore.fetchWishlistIds()
})

const features = useFeatures()

const navItems = computed(() => {
  const items = [
    {
      label: 'سفارش‌های من',
      to: '/profile/orders',
      icon: 'i-lucide-package'
    },
    {
      label: 'آدرس‌های من',
      to: '/profile/addresses',
      icon: 'i-lucide-map-pin'
    },
    {
      label: 'لیست علاقه‌مندی‌ها',
      to: '/profile/wishlist',
      icon: 'i-lucide-heart'
    }
  ]

  if (features.hasFeature('loyalty')) {
    items.push({
      label: 'باشگاه مشتریان (VIP)',
      to: '/profile/club',
      icon: 'i-lucide-crown'
    })
  }

  items.push({
    label: 'اطلاعات حساب',
    to: '/profile/settings',
    icon: 'i-lucide-user-cog'
  })

  return items
})

const handleLogout = async () => {
  if (confirm('آیا از خروج از حساب کاربری خود اطمینان دارید؟')) {
    await authStore.logout()
    router.push('/')
  }
}
</script>

<template>
  <div class="py-6 sm:py-10 flex flex-col gap-8 pb-24 sm:pb-16 max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">حساب کاربری</span>
    </nav>

    <!-- Top Profile Card / Hero Banner -->
    <div
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs relative overflow-hidden"
    >
      <div class="absolute -top-16 -left-16 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl pointer-events-none" />

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <!-- User Info -->
        <div class="flex items-center gap-4">
          <template v-if="authStore.user">
            <div
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-primary-500/25 shrink-0"
            >
              {{ authStore.user.first_name?.[0] || 'ک' }}
            </div>
            <div class="flex flex-col gap-1">
              <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-neutral-100">
                {{ authStore.user.full_name }}
              </h1>
              <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <span class="font-mono font-medium [direction:ltr]">{{ authStore.user.mobile }}</span>
                <span class="w-1 h-1 rounded-full bg-neutral-300 dark:bg-neutral-700" />
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                  <UIcon
                    name="i-lucide-check-circle"
                    class="w-3.5 h-3.5"
                  />
                  شماره تایید شده
                </span>
              </div>
            </div>
          </template>
          <template v-else>
            <USkeleton class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shrink-0" />
            <div class="flex flex-col gap-2">
              <USkeleton class="w-36 h-6 rounded-lg" />
              <USkeleton class="w-28 h-4 rounded-md" />
            </div>
          </template>
        </div>

        <!-- Quick Summary Stats -->
        <div class="grid grid-cols-3 gap-3 sm:gap-6 border-t md:border-t-0 md:border-r border-neutral-100 dark:border-neutral-800 pt-4 md:pt-0 md:pr-6">
          <NuxtLink
            to="/profile/orders"
            class="flex flex-col items-center justify-center p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-colors group text-center"
          >
            <span class="text-lg sm:text-xl font-black text-neutral-900 dark:text-neutral-100 group-hover:text-primary-600 transition-colors">
              {{ toPersianDigits(stats.orders) }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">سفارش‌ها</span>
          </NuxtLink>

          <NuxtLink
            to="/profile/wishlist"
            class="flex flex-col items-center justify-center p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-colors group text-center"
          >
            <span class="text-lg sm:text-xl font-black text-neutral-900 dark:text-neutral-100 group-hover:text-primary-600 transition-colors">
              {{ toPersianDigits(stats.wishlist) }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">علاقه‌مندی‌ها</span>
          </NuxtLink>

          <NuxtLink
            to="/profile/addresses"
            class="flex flex-col items-center justify-center p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-colors group text-center"
          >
            <span class="text-lg sm:text-xl font-black text-neutral-900 dark:text-neutral-100 group-hover:text-primary-600 transition-colors">
              {{ toPersianDigits(stats.addresses) }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">آدرس‌ها</span>
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Main Content Area: Sidebar + Active Page -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Profile Navigation Sidebar -->
      <aside class="lg:col-span-3">
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-3 shadow-xs sticky top-24 flex flex-col gap-1.5">
          <NuxtLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-200"
            :class="[
              route.path === item.to || (item.to === '/profile/orders' && route.path === '/profile')
                ? 'bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 font-bold shadow-xs'
                : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800/60 hover:text-neutral-900 dark:hover:text-neutral-200'
            ]"
          >
            <UIcon
              :name="item.icon"
              class="w-5 h-5 shrink-0"
            />
            <span>{{ item.label }}</span>
          </NuxtLink>

          <div class="border-t border-neutral-100 dark:border-neutral-800 my-1" />

          <!-- Logout Button -->
          <button
            type="button"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors w-full text-right cursor-pointer"
            @click="handleLogout"
          >
            <UIcon
              name="i-lucide-log-out"
              class="w-5 h-5 shrink-0"
            />
            <span>خروج از حساب</span>
          </button>
        </div>
      </aside>

      <!-- Active Child Page Content -->
      <main class="lg:col-span-9">
        <NuxtPage />
      </main>
    </div>
  </div>
</template>
