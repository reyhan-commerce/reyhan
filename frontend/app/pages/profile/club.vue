<script setup lang="ts">
import type { LoyaltySummary, LoyaltyTierInfo, LoyaltyTransaction, RedeemResult } from '~/types/loyalty'

definePageMeta({
  middleware: [
    'auth',
    () => {
      const features = useFeatures()
      if (!features.hasFeature('loyalty')) {
        return navigateTo('/profile')
      }
    }
  ]
})

const api = useApi()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const toast = useToast()
const { toPersianDigits, formatPrice } = usePersian()

const storeName = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')

useSeoMeta({
  title: () => `باشگاه مشتریان و امتیازات - ${storeName.value}`,
  description: 'کسب امتیاز با هر خرید، ارتقای سطح عضویت به طلایی و تبدیل امتیاز به کدهای تخفیف شگفت‌انگیز.'
})

// Fetch summary and tiers
const { data: summaryResponse, refresh: refreshSummary } = await useAsyncData('loyalty-summary', () =>
  api<ApiResponse<LoyaltySummary>>('/loyalty/summary')
)
const summary = computed(() => summaryResponse.value?.data)

const { data: tiersResponse } = await useAsyncData('loyalty-tiers', () =>
  api<ApiResponse<LoyaltyTierInfo[]>>('/loyalty/tiers')
)
const tiers = computed(() => tiersResponse.value?.data ?? [])

// Fetch transaction history
const { data: transactionsResponse, refresh: refreshTransactions } = await useAsyncData('loyalty-transactions', () =>
  api<ApiResponse<LoyaltyTransaction[]>>('/loyalty/transactions')
)
const transactions = computed(() => transactionsResponse.value?.data ?? [])

// Redemption state
const pointsToRedeem = ref<number>(100)
const isRedeeming = ref(false)
const latestCoupon = ref<RedeemResult | null>(null)

const calculatedDiscount = computed(() => {
  const rate = summary.value?.point_value || 500
  return (pointsToRedeem.value || 0) * rate
})

const handleRedeem = async () => {
  const currentBalance = summary.value?.balance ?? 0
  if (pointsToRedeem.value < 10) {
    toast.add({
      title: 'خطای تبدیل',
      description: 'حداقل امتیاز مجاز برای تبدیل ۱۰ امتیاز است.',
      color: 'error'
    })
    return
  }

  if (pointsToRedeem.value > currentBalance) {
    toast.add({
      title: 'امتیاز ناکافی',
      description: `موجودی امتیاز شما (${toPersianDigits(currentBalance)}) برای تبدیل این مقدار کافی نیست.`,
      color: 'error'
    })
    return
  }

  isRedeeming.value = true
  try {
    const res = await api<{ success: boolean, message: string, data: RedeemResult }>('/loyalty/redeem', {
      method: 'POST',
      body: {
        points: pointsToRedeem.value
      }
    })

    if (res.data) {
      latestCoupon.value = res.data
      toast.add({
        title: 'کد تخفیف صادر شد! 🎉',
        description: `کد تخفیف ${res.data.code} با مبلغ ${formatPrice(res.data.discount_amount)} برای شما فعال گردید.`,
        color: 'success'
      })
      await Promise.all([refreshSummary(), refreshTransactions()])
    }
  } catch (error: unknown) {
    const msg = (error as { data?: { message?: string } })?.data?.message || 'خطا در تبدیل امتیاز. لطفاً مجدداً تلاش فرمایید.'
    toast.add({
      title: 'خطای صدور کد تخفیف',
      description: msg,
      color: 'error'
    })
  } finally {
    isRedeeming.value = false
  }
}

const copyCouponCode = async (code: string) => {
  if (import.meta.client) {
    try {
      await navigator.clipboard.writeText(code)
      toast.add({
        title: 'کد کپی شد',
        description: `کد تخفیف ${code} در حافظه موقت کپی گردید.`,
        color: 'success'
      })
    } catch {
      // fallback
    }
  }
}

const formatJalaliDate = (isoString?: string | null) => {
  if (!isoString) return ''
  try {
    const d = new Date(isoString)
    return new Intl.DateTimeFormat('fa-IR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    }).format(d)
  } catch {
    return ''
  }
}
</script>

<template>
  <div class="flex flex-col gap-8 max-w-4xl mx-auto w-full">
    <!-- Header Title -->
    <div class="flex flex-col gap-1.5 text-right">
      <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white flex items-center gap-2">
        <UIcon
          name="i-lucide-crown"
          class="size-6 text-amber-500"
        />
        <span>باشگاه مشتریان و امتیازات وفاداری</span>
      </h1>
      <p class="text-xs text-neutral-400">
        امتیازهای شما با هر خرید و تعامل در {{ storeName }} افزایش یافته و قابل تبدیل به کدهای تخفیف شگفت‌انگیز است.
      </p>
    </div>

    <!-- VIP Loyalty Member Card -->
    <div
      v-if="summary"
      class="relative overflow-hidden rounded-3xl p-6 sm:p-8 bg-gradient-to-tr from-neutral-950 via-neutral-900 to-neutral-800 text-white shadow-2xl border border-neutral-800"
    >
      <!-- Decorative background lighting -->
      <div class="absolute -top-20 -left-20 size-60 rounded-full bg-amber-500/15 blur-3xl pointer-events-none" />
      <div class="absolute -bottom-20 -right-20 size-60 rounded-full bg-primary-500/15 blur-3xl pointer-events-none" />

      <div class="relative z-10 flex flex-col justify-between gap-8">
        <!-- Card Top Bar: Logo & Tier Badge -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="size-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 text-neutral-950 flex items-center justify-center font-black text-base shadow-md">
              <UIcon
                name="i-lucide-crown"
                class="size-5"
              />
            </div>
            <div class="flex flex-col text-right">
              <span class="text-xs font-bold tracking-wider uppercase text-neutral-300">VIP CLUB</span>
              <span class="text-[11px] text-neutral-400 font-medium">{{ storeName }}</span>
            </div>
          </div>

          <!-- Tier Badge -->
          <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-800/80 border border-neutral-700/80 backdrop-blur-md">
            <UIcon
              :name="summary.tier.icon"
              class="size-4 text-amber-400"
            />
            <span class="text-xs font-black text-amber-300">
              سطح {{ summary.tier.label }}
            </span>
          </div>
        </div>

        <!-- Card Middle: Points Counter & Monetary Value -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div class="flex flex-col gap-1 text-right">
            <span class="text-xs text-neutral-400 font-medium">موجودی امتیاز قابل استفاده</span>
            <div class="flex items-baseline gap-2">
              <span class="text-3xl sm:text-5xl font-black text-white font-mono tracking-tight">
                {{ toPersianDigits(summary.balance) }}
              </span>
              <span class="text-sm font-bold text-amber-400">امتیاز</span>
            </div>
            <span class="text-xs text-neutral-400">
              معادل <strong class="text-white">{{ formatPrice(summary.monetary_worth) }}</strong> تخفیف روی فاکتور خرید
            </span>
          </div>

          <!-- Total Earned & Spent Stats -->
          <div class="flex items-center gap-4 text-xs text-neutral-300 bg-neutral-900/60 p-3 rounded-2xl border border-neutral-800 self-start sm:self-auto">
            <div class="flex flex-col gap-0.5 text-right">
              <span class="text-[10px] text-neutral-500">مجموع کسب‌شده:</span>
              <span class="font-bold text-emerald-400 font-mono">+{{ toPersianDigits(summary.total_earned) }}</span>
            </div>
            <div class="h-6 w-px bg-neutral-800" />
            <div class="flex flex-col gap-0.5 text-right">
              <span class="text-[10px] text-neutral-500">خرج‌شده:</span>
              <span class="font-bold text-neutral-400 font-mono">-{{ toPersianDigits(summary.total_spent) }}</span>
            </div>
          </div>
        </div>

        <!-- Card Bottom Bar: Member Name & Chip -->
        <div class="flex items-center justify-between pt-4 border-t border-neutral-800/80 text-xs">
          <div class="flex flex-col text-right">
            <template v-if="authStore.user">
              <span class="font-bold text-neutral-200">{{ authStore.user.full_name }}</span>
              <span class="text-[11px] text-neutral-500 font-mono [direction:ltr] text-right">{{ authStore.user.mobile }}</span>
            </template>
            <template v-else>
              <USkeleton class="w-24 h-4 rounded mb-1 bg-neutral-800" />
              <USkeleton class="w-20 h-3 rounded bg-neutral-800" />
            </template>
          </div>

          <div class="flex items-center gap-1.5 opacity-60">
            <div class="w-7 h-5 rounded bg-amber-400/20 border border-amber-400/40" />
            <span class="text-[10px] font-mono tracking-widest text-neutral-400">LOYALTY</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tier Progress & Next Level Perks -->
    <div
      v-if="summary && summary.tier.next_points"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex flex-col gap-4"
    >
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
          مسیر رسیدن به سطح بعدی:
        </span>
        <span class="text-xs font-bold text-primary font-mono">
          {{ toPersianDigits(summary.progress) }}٪ تکمیل شده
        </span>
      </div>

      <!-- Progress Bar -->
      <div class="w-full h-3 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden relative">
        <div
          class="h-full rounded-full bg-gradient-to-r from-primary to-amber-400 transition-all duration-700 ease-out"
          :style="{ width: `${summary.progress}%` }"
        />
      </div>

      <div class="flex items-center justify-between text-xs text-neutral-400">
        <span>سطح فعلی: {{ summary.tier.label }}</span>
        <span>
          نیاز به <strong class="text-neutral-700 dark:text-neutral-200">{{ toPersianDigits(summary.tier.next_points - summary.balance) }}</strong> امتیاز دیگر
        </span>
      </div>
    </div>

    <!-- Points Redemption Section (تبدیل به کد تخفیف) -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-6">
      <div class="flex items-center gap-3">
        <div class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
          <UIcon
            name="i-lucide-ticket"
            class="size-5"
          />
        </div>
        <div class="flex flex-col text-right">
          <h2 class="text-base font-black text-neutral-900 dark:text-white">
            تبدیل امتیاز به کوپن تخفیف خرید
          </h2>
          <p class="text-xs text-neutral-400">
            تعداد امتیازی که می‌خواهید تبدیل شود را تعیین کرده و فوراً کد تخفیف یکبار مصرف دریافت کنید.
          </p>
        </div>
      </div>

      <!-- Latest Issued Coupon Banner -->
      <div
        v-if="latestCoupon"
        class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/40 flex flex-col sm:flex-row items-center justify-between gap-4"
      >
        <div class="flex items-center gap-3 text-right">
          <div class="size-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <UIcon
              name="i-lucide-check-circle"
              class="size-5"
            />
          </div>
          <div class="flex flex-col">
            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
              کد تخفیف شما آماده استفاده است:
            </span>
            <span class="text-lg font-black text-emerald-950 dark:text-emerald-100 font-mono tracking-wider">
              {{ latestCoupon.code }}
            </span>
            <span class="text-[11px] text-emerald-600 dark:text-emerald-400">
              مبلغ تخفیف: {{ formatPrice(latestCoupon.discount_amount) }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <UButton
            color="success"
            size="sm"
            icon="i-lucide-copy"
            class="font-bold cursor-pointer"
            @click="copyCouponCode(latestCoupon.code)"
          >
            کپی کد تخفیف
          </UButton>
          <UButton
            to="/products"
            color="neutral"
            variant="outline"
            size="sm"
            class="font-bold cursor-pointer"
          >
            شروع خرید
          </UButton>
        </div>
      </div>

      <!-- Redemption Form -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
        <div class="sm:col-span-6 flex flex-col gap-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            امتیاز برای تبدیل:
          </label>
          <UInput
            v-model.number="pointsToRedeem"
            type="number"
            min="10"
            :max="summary?.balance || 1000"
            size="lg"
            class="w-full font-mono text-left"
            placeholder="100"
          />
        </div>

        <div class="sm:col-span-6 flex items-center justify-between gap-3 p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800">
          <div class="flex flex-col text-right">
            <span class="text-[11px] text-neutral-400">مبلغ کد تخفیف حاصله:</span>
            <span class="text-base font-black text-primary">
              {{ formatPrice(calculatedDiscount) }}
            </span>
          </div>

          <UButton
            color="primary"
            size="lg"
            :loading="isRedeeming"
            :disabled="!summary || summary.balance < 10 || pointsToRedeem > summary.balance"
            icon="i-lucide-sparkles"
            class="font-bold cursor-pointer"
            @click="handleRedeem"
          >
            دریافت کد تخفیف
          </UButton>
        </div>
      </div>
    </div>

    <!-- Tiers Benefits Grid -->
    <div class="flex flex-col gap-4">
      <h3 class="text-base font-black text-neutral-900 dark:text-white flex items-center gap-2">
        <span class="w-1.5 h-4 rounded-full bg-primary" />
        <span>سطوح عضویت و امتیازات هر سطح</span>
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
          v-for="t in tiers"
          :key="t.key"
          class="bg-white dark:bg-neutral-900 border rounded-3xl p-5 shadow-xs flex flex-col gap-3 transition-all"
          :class="
            summary?.tier.key === t.key
              ? 'border-primary ring-2 ring-primary/20'
              : 'border-neutral-200/80 dark:border-neutral-800/80'
          "
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <UIcon
                :name="t.icon"
                class="size-5 text-amber-500"
              />
              <span class="font-black text-sm text-neutral-900 dark:text-white">{{ t.label }}</span>
            </div>
            <UBadge
              v-if="summary?.tier.key === t.key"
              color="primary"
              size="xs"
              class="font-bold"
            >
              سطح شما
            </UBadge>
          </div>

          <span class="text-xs text-neutral-400">
            {{ toPersianDigits(t.min_points) }} {{ t.max_points ? `تا ${toPersianDigits(t.max_points)}` : 'امتیاز به بالا' }} امتیاز
          </span>

          <ul class="flex flex-col gap-1.5 pt-2 border-t border-neutral-100 dark:border-neutral-800 text-xs text-neutral-600 dark:text-neutral-400">
            <li
              v-for="p in t.perks"
              :key="p"
              class="flex items-center gap-1.5"
            >
              <UIcon
                name="i-lucide-check"
                class="size-3.5 text-emerald-500 shrink-0"
              />
              <span>{{ p }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Transaction History Table -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex flex-col gap-4">
      <h3 class="text-base font-black text-neutral-900 dark:text-white flex items-center gap-2">
        <UIcon
          name="i-lucide-history"
          class="size-4.5 text-primary"
        />
        <span>تاریخچه تراکنش‌های امتیاز</span>
      </h3>

      <div
        v-if="transactions.length > 0"
        class="overflow-x-auto"
      >
        <table class="w-full text-xs text-right">
          <thead>
            <tr class="border-b border-neutral-100 dark:border-neutral-800 text-neutral-400">
              <th class="py-3 px-2 font-medium">
                تاریخ
              </th>
              <th class="py-3 px-2 font-medium">
                شرح رویداد
              </th>
              <th class="py-3 px-2 font-medium text-left">
                تغییر امتیاز
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            <tr
              v-for="t in transactions"
              :key="t.id"
              class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/40 transition-colors"
            >
              <td class="py-3 px-2 text-neutral-400 whitespace-nowrap">
                {{ formatJalaliDate(t.created_at) }}
              </td>
              <td class="py-3 px-2 font-medium text-neutral-800 dark:text-neutral-200">
                {{ t.description }}
              </td>
              <td class="py-3 px-2 font-mono font-bold text-left whitespace-nowrap">
                <span
                  :class="t.points >= 0 ? 'text-emerald-500' : 'text-red-500'"
                >
                  {{ t.points >= 0 ? '+' : '' }}{{ toPersianDigits(t.points) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-else
        class="py-10 text-center flex flex-col items-center gap-2 text-neutral-400 text-xs"
      >
        <UIcon
          name="i-lucide-sparkles"
          class="size-8 text-neutral-300 dark:text-neutral-700"
        />
        <p>هنوز تراکنش امتیازی در حساب شما ثبت نشده است.</p>
        <p class="text-[11px]">
          با ثبت اولین سفارش یا دیدگاه، امتیاز دریافت کنید!
        </p>
      </div>
    </div>
  </div>
</template>
