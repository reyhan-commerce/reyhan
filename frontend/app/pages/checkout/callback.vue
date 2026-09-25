<script setup lang="ts">
import { useCartStore } from '~/stores/cart'

definePageMeta({
  layout: 'checkout'
})

useSeoMeta({
  title: 'نتیجه پرداخت و وضعیت سفارش',
  description: 'نتیجه تراکنش بانکی و بررسی وضعیت سفارش'
})

const route = useRoute()
const api = useApi()
const cartStore = useCartStore()
const { formatPrice } = usePersian()

interface VerifyData {
  order_number?: string
  tracking_code?: string
  reference_id?: string
  amount?: number
  paid_at?: string
  message?: string
}

const isLoading = ref(true)
const isSuccess = ref<boolean | null>(null)
const verifyData = ref<VerifyData | null>(null)
const errorMessage = ref('')
const isCopied = ref(false)

async function copyOrderNumber() {
  if (!verifyData.value?.order_number) return
  try {
    await navigator.clipboard.writeText(verifyData.value.order_number)
    isCopied.value = true
    setTimeout(() => {
      isCopied.value = false
    }, 2000)
  } catch {
    // fallback
  }
}

onMounted(async () => {
  const authority = (route.query.Authority || route.query.authority) as string | undefined
  const status = (route.query.Status || route.query.status) as string | undefined

  if (!authority) {
    isLoading.value = false
    isSuccess.value = false
    errorMessage.value = 'اطلاعات و شناسه تراکنش پرداخت از درگاه بانک دریافت نشد.'
    return
  }

  try {
    const res = await api<{ success: boolean, data?: VerifyData, message?: string }>('/payment/verify', {
      method: 'POST',
      body: {
        Authority: authority,
        Status: status,
        ...route.query
      }
    })

    if (res?.data || res?.success) {
      isSuccess.value = true
      verifyData.value = res.data || null
      // Refresh cart to reflect that the paid cart items have been cleared
      await cartStore.fetchCart()
    } else {
      isSuccess.value = false
      errorMessage.value = res?.message || 'تراکنش توسط درگاه پرداخت بانکی تایید نشد.'
    }
  } catch (err: unknown) {
    isSuccess.value = false
    const errObj = err as { data?: { message?: string }, message?: string }
    errorMessage.value
      = errObj?.data?.message
        || errObj?.message
        || 'خطایی در بررسی نتیجه پرداخت رخ داده است.'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6">
    <div class="w-full max-w-xl">
      <!-- 1. LOADING STATE -->
      <div
        v-if="isLoading"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-8 sm:p-12 text-center shadow-xl shadow-neutral-950/5 flex flex-col items-center justify-center gap-5"
      >
        <div class="relative flex items-center justify-center">
          <div class="w-20 h-20 rounded-full border-4 border-primary-500/20 dark:border-primary-400/20 animate-ping absolute inset-0" />
          <div class="w-20 h-20 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center relative">
            <UIcon
              name="i-lucide-loader-2"
              class="w-10 h-10 animate-spin"
            />
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
            در حال استعلام وضعیت پرداخت...
          </h2>
          <p class="text-sm text-neutral-500 dark:text-neutral-400 max-w-sm mx-auto">
            در حال ارتباط امن با درگاه شاپرک جهت اعتبارسنجی تراکنش. لطفاً پنجره مرورگر را نبندید.
          </p>
        </div>
      </div>

      <!-- 2. SUCCESS STATE -->
      <div
        v-else-if="isSuccess"
        class="bg-white dark:bg-neutral-900 border border-emerald-500/30 dark:border-emerald-500/20 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-emerald-500/5 relative overflow-hidden"
      >
        <!-- Background decorative blur -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 flex flex-col items-center text-center">
          <!-- Icon Badge -->
          <div class="w-20 h-20 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-5 ring-8 ring-emerald-500/10 shadow-lg shadow-emerald-500/20">
            <UIcon
              name="i-lucide-check-circle-2"
              class="w-11 h-11"
            />
          </div>

          <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 mb-2">
            پرداخت با موفقیت انجام شد!
          </h1>
          <p class="text-sm text-neutral-500 dark:text-neutral-400 max-w-md mb-8">
            سفارش شما با موفقیت تایید و ثبت شد و بلافاصله وارد چرخه آماده‌سازی و بسته‌بندی در انبار مرکزی گردید.
          </p>

          <!-- Receipt Details Card -->
          <div class="w-full bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200/80 dark:border-neutral-700/60 rounded-2xl p-5 mb-6 text-sm flex flex-col gap-3.5 divide-y divide-neutral-200/60 dark:divide-neutral-700/50">
            <!-- Order Number -->
            <div class="flex items-center justify-between pt-1">
              <span class="text-neutral-500 dark:text-neutral-400">شماره سفارش:</span>
              <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-neutral-900 dark:text-neutral-100">
                  {{ verifyData?.order_number }}
                </span>
                <button
                  v-if="verifyData?.order_number"
                  type="button"
                  class="text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors p-1"
                  title="کپی شماره سفارش"
                  @click="copyOrderNumber"
                >
                  <UIcon
                    :name="isCopied ? 'i-lucide-check' : 'i-lucide-copy'"
                    class="w-4 h-4"
                    :class="{ 'text-emerald-500': isCopied }"
                  />
                </button>
              </div>
            </div>

            <!-- Tracking / Ref ID -->
            <div
              v-if="verifyData?.tracking_code || verifyData?.reference_id"
              class="flex items-center justify-between pt-3.5"
            >
              <span class="text-neutral-500 dark:text-neutral-400">کد رهگیری بانکی:</span>
              <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">
                {{ verifyData.tracking_code || verifyData.reference_id }}
              </span>
            </div>

            <!-- Amount -->
            <div
              v-if="verifyData?.amount"
              class="flex items-center justify-between pt-3.5"
            >
              <span class="text-neutral-500 dark:text-neutral-400">مبلغ پرداخت شده:</span>
              <span class="font-bold text-emerald-600 dark:text-emerald-400 text-base">
                {{ formatPrice(verifyData.amount) }}
              </span>
            </div>

            <!-- Payment Status -->
            <div class="flex items-center justify-between pt-3.5">
              <span class="text-neutral-500 dark:text-neutral-400">وضعیت پرداخت:</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                تایید شده و موفق
              </span>
            </div>
          </div>

          <!-- Notification Notice -->
          <div class="w-full bg-primary-50/60 dark:bg-primary-950/30 border border-primary-100 dark:border-primary-900/50 rounded-xl p-3.5 text-xs text-primary-800 dark:text-primary-300 flex items-center gap-3 mb-8 text-right">
            <UIcon
              name="i-lucide-bell"
              class="w-5 h-5 shrink-0 text-primary-600 dark:text-primary-400"
            />
            <span>
              اطلاعات سفارش و کد رهگیری به شماره تلفن همراه شما پیامک گردید.
            </span>
          </div>

          <!-- Action Buttons -->
          <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UButton
              to="/profile"
              color="primary"
              size="lg"
              block
              icon="i-lucide-package"
              class="font-bold cursor-pointer"
            >
              پیگیری سفارش
            </UButton>
            <UButton
              to="/"
              variant="outline"
              color="neutral"
              size="lg"
              block
              icon="i-lucide-arrow-left"
              trailing
              class="cursor-pointer"
            >
              بازگشت به صفحه اصلی
            </UButton>
          </div>
        </div>
      </div>

      <!-- 3. FAILURE / CANCEL STATE -->
      <div
        v-else
        class="bg-white dark:bg-neutral-900 border border-red-500/30 dark:border-red-500/20 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-red-500/5 relative overflow-hidden"
      >
        <!-- Background decorative blur -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 flex flex-col items-center text-center">
          <!-- Icon Badge -->
          <div class="w-20 h-20 rounded-full bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center mb-5 ring-8 ring-red-500/10 shadow-lg shadow-red-500/20">
            <UIcon
              name="i-lucide-alert-triangle"
              class="w-11 h-11"
            />
          </div>

          <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 mb-2">
            پرداخت ناموفق بود
          </h1>
          <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-md mb-6">
            {{ errorMessage || 'فرآیند پرداخت در درگاه بانکی ناتمام ماند یا توسط کاربر لغو گردید.' }}
          </p>

          <!-- Warning Advice Box -->
          <div class="w-full bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/40 rounded-2xl p-4 text-xs text-amber-900 dark:text-amber-200 mb-8 text-right flex items-start gap-3">
            <UIcon
              name="i-lucide-info"
              class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5"
            />
            <div class="space-y-1">
              <div class="font-bold">
                آیا مبلغی از حساب شما کسر گردیده است؟
              </div>
              <div class="text-amber-800 dark:text-amber-300/80 leading-relaxed">
                چنانچه وجهی از حسابتان برداشت شده باشد، حداکثر ظرف مدت ۷۲ ساعت کاری آینده به صورت خودکار توسط سیستم شاپرک به حسابتان عودت داده خواهد شد.
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UButton
              to="/checkout"
              color="primary"
              size="lg"
              block
              icon="i-lucide-refresh-cw"
              class="font-bold cursor-pointer"
            >
              تلاش مجدد برای پرداخت
            </UButton>
            <UButton
              to="/cart"
              variant="outline"
              color="neutral"
              size="lg"
              block
              icon="i-lucide-shopping-cart"
              class="cursor-pointer"
            >
              بازگشت به سبد خرید
            </UButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
