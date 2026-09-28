<script setup lang="ts">
import type { WalletData, WalletTransaction } from '~/types/wallet'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'کیف پول و موجودی',
  description: 'مدیریت موجودی کیف پول، افزایش اعتبار آنی و تاریخچه تراکنش‌های مالی'
})

const api = useApi()
const toast = useToast()
const { toPersianDigits, formatPrice } = usePersian()

const page = ref(1)

// Fetch wallet balance and transactions
const { data: walletResponse, pending: isLoading, refresh: refreshWallet } = await useAsyncData(
  'user-wallet',
  () => api<ApiResponse<WalletData>>('/wallet', { query: { page: page.value } }),
  { watch: [page] }
)

const wallet = computed(() => walletResponse.value?.data ?? { balance: 0, transactions: [] })

const transactions = computed<WalletTransaction[]>(() => {
  const tx = walletResponse.value?.data?.transactions
  if (!tx) return []
  if (Array.isArray(tx)) return tx
  if (Array.isArray((tx as any)?.data)) return (tx as any).data
  return []
})

const paginationMeta = computed(() => {
  const tx = walletResponse.value?.data?.transactions
  if (tx && !Array.isArray(tx) && typeof tx === 'object') {
    return {
      currentPage: Number((tx as any).current_page ?? 1),
      lastPage: Number((tx as any).last_page ?? 1),
      total: Number((tx as any).total ?? 0),
      perPage: Number((tx as any).per_page ?? 15),
    }
  }
  return null
})

// Top-up modal state
const isTopUpModalOpen = ref(false)
const topUpAmountToman = ref<number>(200000)
const isSubmittingTopUp = ref(false)

const quickAmounts = [
  { label: '۱۰۰ هزار تومان', value: 100000 },
  { label: '۲۰۰ هزار تومان', value: 200000 },
  { label: '۵۰۰ هزار تومان', value: 500000 },
  { label: '۱ میلیون تومان', value: 1000000 },
  { label: '۲ میلیون تومان', value: 2000000 },
]

function formatJalaliDate(isoString: string | null | undefined): string {
  if (!isoString) return '—'
  try {
    const date = new Date(isoString)
    return new Intl.DateTimeFormat('fa-IR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  } catch {
    return isoString
  }
}

async function handleTopUp() {
  if (!topUpAmountToman.value || topUpAmountToman.value < 10000) {
    toast.add({
      title: 'مبلغ نامعتبر',
      description: 'حداقل مبلغ شارژ کیف پول ۱۰,۰۰۰ تومان می‌باشد.',
      color: 'error'
    })
    return
  }

  isSubmittingTopUp.value = true
  try {
    const callbackUrl = typeof window !== 'undefined'
      ? `${window.location.origin}/profile/wallet`
      : 'http://localhost:3000/profile/wallet'

    const res = await api<{
      success: boolean
      message: string
      data: { redirect_url?: string; action?: string; payment_id?: number }
    }>('/wallet/top-up', {
      method: 'POST',
      body: {
        amount: topUpAmountToman.value * 10, // Convert Toman to Rial for backend
        callback_url: callbackUrl
      }
    })

    if (res.data?.redirect_url) {
      toast.add({
        title: 'انتقال به درگاه پرداخت',
        description: 'در حال هدایت به درگاه شاپرک جهت شارژ کیف پول...',
        color: 'info'
      })
      window.location.href = res.data.redirect_url
    } else {
      toast.add({
        title: 'شارژ موفق',
        description: res.message || 'کیف پول شما با موفقیت شارژ گردید.',
        color: 'success'
      })
      isTopUpModalOpen.value = false
      await refreshWallet()
    }
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در افزایش موجودی',
      description: errorObj.response?._data?.message || 'برقراری ارتباط با درگاه پرداخت با خطا مواجه شد.',
      color: 'error'
    })
  } finally {
    isSubmittingTopUp.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Wallet Overview Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-950 p-6 sm:p-8 text-white shadow-xl">
      <!-- Glow & Ambient Background Effects -->
      <div class="absolute -top-12 -left-12 size-48 rounded-full bg-primary-500/20 blur-3xl pointer-events-none" />
      <div class="absolute -bottom-12 -right-12 size-48 rounded-full bg-emerald-500/20 blur-3xl pointer-events-none" />

      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
          <div class="size-16 sm:size-18 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center text-primary-400 shrink-0 shadow-inner">
            <UIcon name="i-lucide-wallet" class="size-8 sm:size-9" />
          </div>

          <div class="flex flex-col gap-1.5">
            <span class="text-xs sm:text-sm text-neutral-300 font-medium">موجودی فعلی کیف پول شما</span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl sm:text-4xl font-black tracking-tight text-white font-mono">
                {{ formatPrice(wallet.balance) }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
          <UButton
            color="primary"
            size="lg"
            icon="i-lucide-plus-circle"
            class="font-black w-full md:w-auto justify-center cursor-pointer shadow-lg shadow-primary-500/20"
            @click="isTopUpModalOpen = true"
          >
            افزایش اعتبار کیف پول
          </UButton>
        </div>
      </div>

      <!-- Feature Badges -->
      <div class="relative z-10 mt-6 pt-5 border-t border-white/10 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-neutral-300">
        <div class="flex items-center gap-2">
          <UIcon name="i-lucide-zap" class="size-4 text-amber-400 shrink-0" />
          <span>خرید سریع بدون ورود به درگاه بانکی</span>
        </div>
        <div class="flex items-center gap-2">
          <UIcon name="i-lucide-corner-down-left" class="size-4 text-emerald-400 shrink-0" />
          <span>بازگشت آنی وجه در صورت انصراف یا مرجوعی</span>
        </div>
        <div class="flex items-center gap-2">
          <UIcon name="i-lucide-gift" class="size-4 text-purple-400 shrink-0" />
          <span>واریز پاداش نقدی و کش‌بک‌های جشنواره</span>
        </div>
      </div>
    </div>

    <!-- Transactions History Section -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <UIcon name="i-lucide-history" class="size-5 text-neutral-500" />
          <h2 class="font-black text-base sm:text-lg text-neutral-900 dark:text-white">
            تاریخچه تراکنش‌های کیف پول
          </h2>
        </div>

        <UButton
          color="neutral"
          variant="ghost"
          size="xs"
          icon="i-lucide-rotate-cw"
          :loading="isLoading"
          class="font-medium"
          @click="() => refreshWallet()"
        >
          بروزرسانی
        </UButton>
      </div>

      <!-- Empty State -->
      <div
        v-if="transactions.length === 0"
        class="py-12 flex flex-col items-center justify-center gap-3 text-center border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-2xl"
      >
        <div class="size-14 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
          <UIcon name="i-lucide-receipt" class="size-7" />
        </div>
        <p class="font-bold text-neutral-700 dark:text-neutral-300 text-sm">
          هنوز تراکنشی در کیف پول شما ثبت نشده است
        </p>
        <p class="text-xs text-neutral-500 max-w-sm">
          با افزایش موجودی کیف پول، می‌توانید خریدهای خود را سریع‌تر و امن‌تر نهایی کنید.
        </p>
      </div>

      <!-- Transactions List -->
      <div v-else class="flex flex-col divide-y divide-neutral-100 dark:divide-neutral-800">
        <div
          v-for="item in transactions"
          :key="item.id"
          class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 rounded-xl px-2 transition-colors"
        >
          <div class="flex items-start sm:items-center gap-3">
            <!-- Transaction Icon -->
            <div
              class="size-10 rounded-xl flex items-center justify-center shrink-0"
              :class="{
                'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800': item?.type === 'deposit' || item?.type === 'refund' || item?.type === 'cashback',
                'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800': item?.type === 'withdraw',
                'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800': item?.type === 'admin_adjustment',
              }"
            >
              <UIcon
                :name="item?.type === 'withdraw' ? 'i-lucide-arrow-up-right' : 'i-lucide-arrow-down-left'"
                class="size-5"
              />
            </div>

            <!-- Details -->
            <div class="flex flex-col gap-0.5">
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-neutral-900 dark:text-white">
                  {{ item?.type_label || (item?.type === 'withdraw' ? 'برداشت از کیف پول' : 'واریز به کیف پول') }}
                </span>
                <span
                  v-if="item?.order_number"
                  class="text-[11px] font-mono text-neutral-500 bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded"
                >
                  سفارش {{ item.order_number }}
                </span>
              </div>
              <span v-if="item?.description" class="text-xs text-neutral-500 leading-relaxed">
                {{ item.description }}
              </span>
              <span class="text-[11px] text-neutral-400 font-medium">
                {{ item?.created_at_jalali || formatJalaliDate(item?.created_at) }}
              </span>
            </div>
          </div>

          <!-- Amounts -->
          <div class="flex flex-row sm:flex-col items-end justify-between sm:justify-center gap-1">
            <span
              class="font-black text-sm sm:text-base font-mono"
              :class="item?.type === 'withdraw' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              {{ item?.type === 'withdraw' ? '-' : '+' }}
              {{ formatPrice(item?.amount ?? 0) }}
            </span>
            <span class="text-[11px] text-neutral-400">
              مانده پس از تراکنش: <span class="font-mono">{{ formatPrice(item?.balance_after ?? 0) }}</span>
            </span>
          </div>
        </div>

        <!-- Pagination Controls -->
        <div
          v-if="paginationMeta && paginationMeta.lastPage > 1"
          class="flex justify-center pt-6 border-t border-neutral-100 dark:border-neutral-800"
        >
          <UPagination
            v-model:page="page"
            :total="paginationMeta.total"
            :items-per-page="paginationMeta.perPage"
          />
        </div>
      </div>
    </div>

    <!-- ==================== TOP-UP MODAL ==================== -->
    <UModal v-model:open="isTopUpModalOpen">
      <template #content>
        <div class="p-6 flex flex-col gap-5">
          <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-wallet" class="size-5 text-primary-500" />
              <h3 class="font-black text-base text-neutral-900 dark:text-white">
                شارژ موجودی کیف پول
              </h3>
            </div>
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-lucide-x"
              size="xs"
              @click="isTopUpModalOpen = false"
            />
          </div>

          <!-- Quick Amount Buttons -->
          <div class="flex flex-col gap-2">
            <span class="text-xs font-bold text-neutral-600 dark:text-neutral-400">
              انتخاب مبالغ پرکاربرد:
            </span>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <button
                v-for="qa in quickAmounts"
                :key="qa.value"
                type="button"
                class="px-3 py-2 rounded-xl text-xs font-bold border transition-all text-center cursor-pointer"
                :class="topUpAmountToman === qa.value
                  ? 'bg-primary-50 dark:bg-primary-950/60 border-primary-500 text-primary-600 dark:text-primary-400 shadow-xs'
                  : 'bg-neutral-50 dark:bg-neutral-800/60 border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:border-neutral-300'"
                @click="topUpAmountToman = qa.value"
              >
                {{ qa.label }}
              </button>
            </div>
          </div>

          <!-- Custom Amount Input -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
              یا مبلغ دلخواه را وارد کنید (به تومان):
            </label>
            <div class="relative">
              <input
                v-model.number="topUpAmountToman"
                type="number"
                min="10000"
                step="10000"
                placeholder="مثال: ۲۵۰,۰۰۰"
                class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white font-mono font-bold text-base focus:outline-none focus:border-primary-500 pl-16"
              />
              <span class="absolute left-3 top-3 text-xs text-neutral-400 font-bold pointer-events-none">
                تومان
              </span>
            </div>
            <span class="text-[11px] text-neutral-400">
              معادل: <span class="font-mono font-bold text-neutral-600 dark:text-neutral-300">{{ formatPrice((topUpAmountToman || 0) * 10) }}</span>
            </span>
          </div>

          <!-- Gateway Note -->
          <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 text-xs text-blue-900 dark:text-blue-300 flex items-start gap-2">
            <UIcon name="i-lucide-shield-check" class="size-4 shrink-0 mt-0.5" />
            <span>اتصال امن به درگاه پرداخت اینترنتی شبکه شاپرک با تمامی کارت‌های بانکی عضو شتاب.</span>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <UButton
              color="neutral"
              variant="outline"
              size="sm"
              @click="isTopUpModalOpen = false"
            >
              انصراف
            </UButton>
            <UButton
              color="primary"
              size="sm"
              icon="i-lucide-credit-card"
              :loading="isSubmittingTopUp"
              class="font-black cursor-pointer"
              @click="handleTopUp"
            >
              پرداخت و افزایش موجودی
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
