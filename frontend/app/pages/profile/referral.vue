<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'معرفی دوستان و دریافت هدیه',
  description: 'کد و لینک دعوت اختصاصی خود را با دوستان به اشتراک بگذارید و به ازای هر خرید موفق آن‌ها، هدیه نقدی دریافت کنید.'
})

const api = useApi()
const toast = useToast()
const settingsStore = useSettingsStore()
const { toPersianDigits, formatPrice } = usePersian()

interface ReferralItem {
  id: number
  referred_name: string
  referred_mobile: string
  status: string
  status_label: string
  status_color: string
  order_number: string | null
  reward_amount: number
  reward_amount_toman: number
  completed_at: string | null
  completed_at_jalali: string | null
  created_at: string
  created_at_jalali: string
}

interface ReferralDashboardData {
  referral_code: string
  share_url: string
  total_referrals_count: number
  completed_referrals_count: number
  total_earned_rial: number
  total_earned_toman: number
  reward_per_referral_rial: number
  reward_per_referral_toman: number
  referred_by: {
    name: string | null
    code: string
  } | null
  referrals: ReferralItem[]
}

const { data: response, pending: isLoading, refresh } = await useAsyncData('user-referral', () =>
  api<ApiResponse<ReferralDashboardData>>('/referral')
)

const referralData = computed(() => response.value?.data)

const inputClaimCode = ref('')
const isSubmittingClaim = ref(false)

const copyCode = async () => {
  if (!referralData.value?.referral_code) return
  try {
    await navigator.clipboard.writeText(referralData.value.referral_code)
    toast.add({
      title: 'کپی شد!',
      description: 'کد معرف در حافظه موقت کپی گردید.',
      color: 'success'
    })
  } catch {
    toast.add({
      title: 'خطا در کپی',
      description: 'امکان کپی خودکار وجود ندارد.',
      color: 'error'
    })
  }
}

const copyLink = async () => {
  if (!referralData.value?.share_url) return
  try {
    await navigator.clipboard.writeText(referralData.value.share_url)
    toast.add({
      title: 'لینک کپی شد!',
      description: 'لینک اختصاصی دعوت از دوستان در حافظه موقت کپی گردید.',
      color: 'success'
    })
  } catch {
    toast.add({
      title: 'خطا در کپی',
      description: 'امکان کپی خودکار وجود ندارد.',
      color: 'error'
    })
  }
}

const submitClaim = async () => {
  if (!inputClaimCode.value.trim()) {
    toast.add({
      title: 'کد معرف خالی است',
      description: 'لطفاً کد معرف را وارد نمایید.',
      color: 'warning'
    })
    return
  }

  isSubmittingClaim.value = true
  try {
    const res = await api<{ success: boolean; message: string }>('/referral/claim', {
      method: 'POST',
      body: { code: inputClaimCode.value.trim() }
    })

    toast.add({
      title: 'موفقیت‌آمیز',
      description: res.message || 'کد معرف با موفقیت ثبت شد.',
      color: 'success'
    })
    inputClaimCode.value = ''
    await refresh()
  } catch (err: any) {
    const errorMsg = err?.data?.message || err?.message || 'خطا در ثبت کد معرف'
    toast.add({
      title: 'خطا',
      description: errorMsg,
      color: 'error'
    })
  } finally {
    isSubmittingClaim.value = false
  }
}
</script>

<template>
  <div class="py-6 sm:py-8 max-w-5xl mx-auto px-4 sm:px-6">
    <!-- Back to Profile Breadcrumb -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-2 text-sm text-neutral-500">
        <NuxtLink to="/profile" class="hover:text-primary-600 transition flex items-center gap-1">
          <UIcon name="i-lucide-user" class="w-4 h-4" />
          حساب کاربری
        </NuxtLink>
        <span>/</span>
        <span class="text-neutral-800 dark:text-neutral-200 font-medium">معرفی به دوستان</span>
      </div>
      <NuxtLink to="/profile" class="text-xs text-primary-600 hover:underline flex items-center gap-1">
        بازگشت به حساب
        <UIcon name="i-lucide-arrow-left" class="w-3.5 h-3.5" />
      </NuxtLink>
    </div>

    <!-- Hero Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 text-white p-6 sm:p-10 shadow-xl mb-8">
      <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none" />
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-xs font-medium mb-3">
            <UIcon name="i-lucide-sparkles" class="w-4 h-4 text-amber-300" />
            برنامه پاداش و همکاری در فروش {{ settingsStore.settings.store_name }}
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">
            {{ settingsStore.settings.referral_banner_title || 'دوستانت را دعوت کن، هدیه نقدی بگیر!' }}
          </h1>
          <p class="text-primary-100 text-sm sm:text-base max-w-xl leading-relaxed">
            <template v-if="settingsStore.settings.referral_banner_desc">
              {{ settingsStore.settings.referral_banner_desc }}
            </template>
            <template v-else>
              با دعوت دوستان، آن‌ها با تخفیف ویژه خرید می‌کنند و با اولین خرید موفق هر دوست،
              مبلغ
              <span class="font-bold text-amber-300">
                {{ toPersianDigits(formatPrice(referralData?.reward_per_referral_toman || settingsStore.settings.referral_reward_toman || 50000)) }} تومان
              </span>
              مستقیماً به کیف پول شما واریز می‌شود.
            </template>
          </p>
        </div>

        <div class="w-full md:w-auto bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-2xl flex flex-col gap-3 min-w-[280px]">
          <div class="text-xs text-primary-200 font-medium">کد معرف اختصاصی شما:</div>
          <div class="flex items-center justify-between gap-3 bg-white/20 px-4 py-2.5 rounded-xl">
            <span class="font-mono text-xl font-bold tracking-widest text-amber-300">
              {{ referralData?.referral_code || '--------' }}
            </span>
            <UButton
              size="xs"
              color="white"
              variant="solid"
              icon="i-lucide-copy"
              @click="copyCode"
            >
              کپی کد
            </UButton>
          </div>

          <UButton
            block
            color="primary"
            variant="solid"
            class="bg-white text-primary-900 hover:bg-neutral-100 font-semibold"
            icon="i-lucide-share-2"
            @click="copyLink"
          >
            کپی لینک اشتراک‌گذاری
          </UButton>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 flex items-center justify-center">
          <UIcon name="i-lucide-users" class="w-6 h-6" />
        </div>
        <div>
          <div class="text-xs text-neutral-500 font-medium">دوستان دعوت‌شده</div>
          <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
            {{ toPersianDigits(referralData?.total_referrals_count || 0) }} نفر
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
          <UIcon name="i-lucide-check-circle" class="w-6 h-6" />
        </div>
        <div>
          <div class="text-xs text-neutral-500 font-medium">خریدهای تکمیل‌شده</div>
          <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
            {{ toPersianDigits(referralData?.completed_referrals_count || 0) }} خرید
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
          <UIcon name="i-lucide-coins" class="w-6 h-6" />
        </div>
        <div>
          <div class="text-xs text-neutral-500 font-medium">کل پاداش واریزی به کیف پول</div>
          <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
            {{ toPersianDigits(formatPrice(referralData?.total_earned_toman || 0)) }} <span class="text-sm font-normal text-neutral-500">تومان</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Register Referrer Code (if user was not referred yet) -->
    <div
      v-if="!referralData?.referred_by"
      class="bg-white dark:bg-neutral-900 p-6 rounded-2xl border border-dashed border-primary-300 dark:border-primary-800 mb-8 flex flex-col md:flex-row items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-950 flex items-center justify-center text-primary-600 shrink-0">
          <UIcon name="i-lucide-gift" class="w-5 h-5" />
        </div>
        <div>
          <div class="font-bold text-neutral-900 dark:text-neutral-100 text-sm">
            دوستی شما را به {{ settingsStore.settings.store_name }} معرفی کرده است؟
          </div>
          <div class="text-xs text-neutral-500 mt-0.5">
            کد معرف دریافتی از دوست خود را اینجا وارد کنید تا در سفارش اول پاداش مشترک دریافت کنید.
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto">
        <UInput
          v-model="inputClaimCode"
          placeholder="کد معرف (مثال: ABCD1234)"
          class="w-full md:w-64 font-mono uppercase"
        />
        <UButton
          color="primary"
          :loading="isSubmittingClaim"
          @click="submitClaim"
        >
          ثبت کد
        </UButton>
      </div>
    </div>

    <div
      v-else
      class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-xl p-4 mb-8 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm"
    >
      <UIcon name="i-lucide-check" class="w-5 h-5 text-emerald-600" />
      <span>
        شما با کد معرف
        <span class="font-mono font-bold">{{ referralData.referred_by.code }}</span>
        معرفی شده‌اید.
      </span>
    </div>

    <!-- Referral History Table -->
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">
          تاریخچه دوستان دعوت‌شده
        </h2>
        <span class="text-xs text-neutral-500">
          {{ toPersianDigits(referralData?.referrals?.length || 0) }} رکورد
        </span>
      </div>

      <div v-if="isLoading" class="p-8 text-center text-neutral-400">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin mx-auto mb-2 text-primary-500" />
        در حال بارگذاری اطلاعات...
      </div>

      <div v-else-if="!referralData?.referrals || referralData.referrals.length === 0" class="p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400">
          <UIcon name="i-lucide-user-plus" class="w-8 h-8" />
        </div>
        <p class="text-neutral-600 dark:text-neutral-400 font-medium text-sm">
          هنوز کسی با کد معرف شما ثبت‌نام نکرده است.
        </p>
        <p class="text-xs text-neutral-400 mt-1">
          لینک دعوت اختصاصی خود را در شبکه‌های اجتماعی با دوستانتان به اشتراک بگذارید!
        </p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-right text-sm">
          <thead class="bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500 text-xs">
            <tr>
              <th class="px-6 py-3 font-medium">کاربر دعوت‌شده</th>
              <th class="px-6 py-3 font-medium">تاریخ عضویت</th>
              <th class="px-6 py-3 font-medium">وضعیت</th>
              <th class="px-6 py-3 font-medium">شماره سفارش اول</th>
              <th class="px-6 py-3 font-medium">پاداش دریافتی</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            <tr
              v-for="item in referralData.referrals"
              :key="item.id"
              class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition"
            >
              <td class="px-6 py-4">
                <div class="font-medium text-neutral-900 dark:text-neutral-100">
                  {{ item.referred_name }}
                </div>
                <div class="text-xs text-neutral-400 font-mono">
                  {{ toPersianDigits(item.referred_mobile) }}
                </div>
              </td>
              <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400 text-xs">
                {{ toPersianDigits(item.created_at_jalali) }}
              </td>
              <td class="px-6 py-4">
                <UBadge
                  :color="item.status === 'completed' ? 'success' : (item.status === 'pending' ? 'warning' : 'neutral')"
                  variant="subtle"
                  size="xs"
                >
                  {{ item.status_label }}
                </UBadge>
              </td>
              <td class="px-6 py-4 text-xs font-mono text-neutral-500">
                {{ item.order_number ? toPersianDigits(item.order_number) : '—' }}
              </td>
              <td class="px-6 py-4 font-semibold text-xs" :class="item.status === 'completed' ? 'text-emerald-600' : 'text-neutral-400'">
                {{ item.status === 'completed' ? toPersianDigits(formatPrice(item.reward_amount_toman)) + ' تومان' : 'در انتظار خرید' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
