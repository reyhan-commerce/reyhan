<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useFeatures } from '~/composables/useFeatures'
import { usePersian } from '~/composables/usePersian'
import type { ApiResponse } from '~/types/api'

const authStore = useAuthStore()
const features = useFeatures()
const { toPersianDigits, formatPrice } = usePersian()
const api = useApi()

const isOpen = ref(false)
const walletBalance = ref<number | null>(null)
const loyaltyPoints = ref<number | null>(null)
const loyaltyTier = ref<string | null>(null)
const isFetchingStats = ref(false)

async function fetchQuickStats() {
  if (!authStore.isAuthenticated) return
  isFetchingStats.value = true
  try {
    const promises: Promise<unknown>[] = []
    if (features.hasFeature('wallet')) {
      promises.push(api<ApiResponse<{ balance: number }>>('/wallet').catch(() => null))
    }
    if (features.hasFeature('loyalty')) {
      promises.push(api<ApiResponse<{ balance: number, current_tier?: { name: string } }>>('/loyalty/summary').catch(() => null))
    }
    const results = await Promise.all(promises)
    let idx = 0
    if (features.hasFeature('wallet')) {
      const walletRes = results[idx++] as ApiResponse<{ balance: number }> | null
      if (walletRes?.data?.balance !== undefined) {
        walletBalance.value = walletRes.data.balance
      }
    }
    if (features.hasFeature('loyalty')) {
      const loyaltyRes = results[idx++] as ApiResponse<{ balance: number, current_tier?: { name: string } }> | null
      if (loyaltyRes?.data) {
        loyaltyPoints.value = loyaltyRes.data.balance ?? 0
        loyaltyTier.value = loyaltyRes.data.current_tier?.name ?? null
      }
    }
  } catch {
    // quiet fallback
  } finally {
    isFetchingStats.value = false
  }
}

// Fetch stats when dropdown opens
watch(isOpen, (val) => {
  if (val) {
    fetchQuickStats()
  }
})

function closeMenu() {
  isOpen.value = false
}

async function handleLogout() {
  closeMenu()
  await authStore.logout()
}
</script>

<template>
  <UPopover
    v-if="authStore.user"
    v-model:open="isOpen"
    :content="{ align: 'start', sideOffset: 10 }"
    :ui="{
      content: 'w-80 sm:w-88 p-3 rounded-3xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-xl border border-neutral-200/80 dark:border-neutral-800 shadow-2xl shadow-neutral-900/10 dark:shadow-neutral-950/60 z-50 overflow-hidden'
    }"
  >
    <!-- Trigger Pill Button -->
    <button
      type="button"
      class="group flex items-center gap-2.5 min-h-10 px-3 py-1.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800/80 hover:bg-neutral-200/80 dark:hover:bg-neutral-700/80 transition-all border border-neutral-200/50 dark:border-neutral-700/50 cursor-pointer text-start focus:outline-none focus:ring-2 focus:ring-primary/20"
      :class="{ 'ring-2 ring-primary/30 border-primary/40': isOpen }"
      aria-label="منوی حساب کاربری"
    >
      <div class="size-7 rounded-lg bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-xs font-black shadow-xs shadow-primary-500/20 shrink-0">
        {{ authStore.user.first_name?.[0] || 'ک' }}
      </div>
      <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 max-w-[110px] truncate">
        {{ authStore.user.full_name }}
      </span>
      <UIcon
        name="i-lucide-chevron-down"
        class="size-3.5 text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-transform duration-200 shrink-0"
        :class="{ 'rotate-180 text-primary': isOpen }"
      />
    </button>

    <!-- Dropdown Content Card -->
    <template #content>
      <div class="flex flex-col gap-3">
        <!-- 1. Profile Identity Header Card -->
        <NuxtLink
          to="/profile"
          class="flex items-center gap-3 p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 hover:bg-primary-50/60 dark:hover:bg-primary-950/40 border border-neutral-100 dark:border-neutral-800/80 transition-all group cursor-pointer"
          @click="closeMenu"
        >
          <div class="relative shrink-0">
            <div class="size-11 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-base font-black shadow-md shadow-primary-500/20">
              {{ authStore.user.first_name?.[0] || 'ک' }}
            </div>
            <div class="absolute -bottom-0.5 -right-0.5 size-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-neutral-900" />
          </div>

          <div class="flex flex-col min-w-0 flex-1">
            <div class="flex items-center justify-between gap-1.5">
              <span class="text-xs font-black text-neutral-900 dark:text-neutral-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ authStore.user.full_name }}
              </span>
              <UBadge
                v-if="loyaltyTier"
                color="primary"
                variant="subtle"
                size="xs"
                class="rounded-full text-[9px] font-bold px-1.5 py-0"
              >
                {{ loyaltyTier }}
              </UBadge>
            </div>
            <span class="text-[11px] font-mono text-neutral-400 dark:text-neutral-500 truncate [direction:ltr] text-right mt-0.5">
              {{ authStore.user.mobile || 'مشاهده داشبورد' }}
            </span>
          </div>

          <UIcon
            name="i-lucide-chevron-left"
            class="size-4 text-neutral-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-transform group-hover:-translate-x-1 shrink-0"
          />
        </NuxtLink>

        <!-- 2. Quick-Action Summary Widgets (Wallet & Club / Wishlist) -->
        <div
          v-if="features.hasFeature('wallet') || features.hasFeature('loyalty') || features.hasFeature('wishlist')"
          class="grid grid-cols-2 gap-2"
        >
          <!-- Wallet Widget -->
          <NuxtLink
            v-if="features.hasFeature('wallet')"
            to="/profile/wallet"
            class="flex flex-col gap-1 p-2.5 rounded-2xl bg-emerald-500/5 hover:bg-emerald-500/10 dark:bg-emerald-950/20 dark:hover:bg-emerald-950/40 border border-emerald-500/15 dark:border-emerald-800/30 transition-all group cursor-pointer"
            @click="closeMenu"
          >
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 group-hover:text-emerald-600 transition-colors">
                کیف پول
              </span>
              <UIcon
                name="i-lucide-wallet"
                class="size-4 text-emerald-500 group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="text-xs font-black text-neutral-900 dark:text-white mt-0.5 truncate">
              <template v-if="walletBalance !== null">
                {{ formatPrice(walletBalance) }} <span class="text-[9px] font-normal text-neutral-400">تومان</span>
              </template>
              <template v-else-if="isFetchingStats">
                <USkeleton class="h-4 w-16 rounded" />
              </template>
              <template v-else>
                ۰ <span class="text-[9px] font-normal text-neutral-400">تومان</span>
              </template>
            </div>
          </NuxtLink>

          <!-- Loyalty Club Widget -->
          <NuxtLink
            v-if="features.hasFeature('loyalty')"
            to="/profile/club"
            class="flex flex-col gap-1 p-2.5 rounded-2xl bg-amber-500/5 hover:bg-amber-500/10 dark:bg-amber-950/20 dark:hover:bg-amber-950/40 border border-amber-500/15 dark:border-amber-800/30 transition-all group cursor-pointer"
            @click="closeMenu"
          >
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 group-hover:text-amber-600 transition-colors">
                باشگاه مشتریان
              </span>
              <UIcon
                name="i-lucide-crown"
                class="size-4 text-amber-500 group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="text-xs font-black text-neutral-900 dark:text-white mt-0.5 truncate">
              <template v-if="loyaltyPoints !== null">
                {{ toPersianDigits(loyaltyPoints) }} <span class="text-[9px] font-normal text-neutral-400">امتیاز</span>
              </template>
              <template v-else-if="isFetchingStats">
                <USkeleton class="h-4 w-16 rounded" />
              </template>
              <template v-else>
                ورود به کلاب
              </template>
            </div>
          </NuxtLink>

          <!-- Fallback Wishlist Quick Widget if Loyalty is disabled -->
          <NuxtLink
            v-else-if="features.hasFeature('wishlist')"
            to="/profile/wishlist"
            class="flex flex-col gap-1 p-2.5 rounded-2xl bg-rose-500/5 hover:bg-rose-500/10 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 border border-rose-500/15 dark:border-rose-800/30 transition-all group cursor-pointer"
            @click="closeMenu"
          >
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 group-hover:text-rose-600 transition-colors">
                علاقه‌مندی‌ها
              </span>
              <UIcon
                name="i-lucide-heart"
                class="size-4 text-rose-500 group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="text-xs font-black text-neutral-900 dark:text-white mt-0.5 truncate">
              مشاهده لیست
            </div>
          </NuxtLink>
        </div>

        <!-- 3. Categorized Navigation Links -->
        <div class="flex flex-col divide-y divide-neutral-100 dark:divide-neutral-800/60 pt-1">
          <!-- Group 1: Shopping & Orders -->
          <div class="flex flex-col gap-0.5 pb-2">
            <NuxtLink
              to="/profile/orders"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-package"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">سفارش‌های من</span>
            </NuxtLink>

            <NuxtLink
              v-if="features.hasFeature('returns')"
              to="/profile/returns"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-rotate-ccw"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">درخواست‌های مرجوعی</span>
            </NuxtLink>

            <NuxtLink
              to="/profile/addresses"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-map-pin"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">آدرس‌های تحویل</span>
            </NuxtLink>
          </div>

          <!-- Group 2: Perks & Engagement -->
          <div
            v-if="features.hasFeature('wishlist') || features.hasFeature('referral')"
            class="flex flex-col gap-0.5 py-2"
          >
            <NuxtLink
              v-if="features.hasFeature('wishlist')"
              to="/profile/wishlist"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-heart"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">لیست علاقه‌مندی‌ها</span>
            </NuxtLink>

            <NuxtLink
              v-if="features.hasFeature('referral')"
              to="/profile/referral"
              class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <div class="flex items-center gap-2.5">
                <UIcon
                  name="i-lucide-gift"
                  class="size-4 text-amber-500 shrink-0"
                />
                <span>دعوت از دوستان</span>
              </div>
              <UBadge
                color="warning"
                variant="subtle"
                size="xs"
                class="rounded-full text-[9px] font-bold px-1.5 py-0"
              >
                کسب هدیه
              </UBadge>
            </NuxtLink>
          </div>

          <!-- Group 3: Support & Settings -->
          <div class="flex flex-col gap-0.5 py-2">
            <NuxtLink
              v-if="features.hasFeature('tickets')"
              to="/profile/tickets"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-headphones"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">تیکت‌های پشتیبانی</span>
            </NuxtLink>

            <NuxtLink
              to="/profile/settings"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/70 hover:text-primary transition-colors cursor-pointer group"
              @click="closeMenu"
            >
              <UIcon
                name="i-lucide-user-cog"
                class="size-4 text-neutral-400 group-hover:text-primary transition-colors shrink-0"
              />
              <span class="flex-1">تنظیمات و امنیت حساب</span>
            </NuxtLink>
          </div>

          <!-- Group 4: Logout -->
          <div class="pt-2">
            <button
              type="button"
              class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors cursor-pointer group text-start"
              @click="handleLogout"
            >
              <UIcon
                name="i-lucide-log-out"
                class="size-4 text-red-500 group-hover:scale-110 transition-transform shrink-0"
              />
              <span class="flex-1">خروج از حساب کاربری</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </UPopover>
</template>
