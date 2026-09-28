<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { useSettingsStore } from '~/stores/settings'

const props = defineProps<{
  active: boolean
}>()

const emit = defineEmits<{
  (e: 'prev' | 'pay'): void
}>()

const checkoutStore = useCheckoutStore()
const settingsStore = useSettingsStore()
const { toPersianDigits, formatPrice } = usePersian()

onMounted(async () => {
  if (props.active) {
    await checkoutStore.fetchWalletBalance()
  }
})

watch(() => props.active, async (isActive) => {
  if (isActive) {
    await checkoutStore.fetchWalletBalance()
  }
})

// Check if wallet covers 100% of payable
const isFullyCoveredByWallet = computed(() => {
  const total = checkoutStore.previewFinalPayable ?? checkoutStore.previewPricing?.final_payable ?? 0
  return checkoutStore.useWallet && checkoutStore.walletBalance >= total && total > 0
})

const walletDeductionAmount = computed(() => {
  if (!checkoutStore.useWallet) return 0
  const total = checkoutStore.previewFinalPayable ?? checkoutStore.previewPricing?.final_payable ?? 0
  return Math.min(checkoutStore.walletBalance, total)
})
</script>

<template>
  <section
    v-show="active"
    class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
  >
    <!-- Step Header -->
    <div class="flex items-center gap-3 border-b border-neutral-100 dark:border-neutral-800 pb-5">
      <div class="size-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
        <UIcon
          name="i-lucide-credit-card"
          class="size-5.5"
        />
      </div>
      <div>
        <h2 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
          انتخاب شیوه و اطلاعات پرداخت
        </h2>
        <p class="text-xs text-neutral-500 mt-0.5">
          انتخاب درگاه بانکی شاپرک، اعتباری اقساطی (اسنپ‌پی)، کارت‌به‌کارت و کیف پول
        </p>
      </div>
    </div>

    <!-- 1. WALLET BALANCE DEDUCTION BOX -->
    <div
      v-if="checkoutStore.walletBalance > 0"
      class="rounded-2xl border p-4.5 transition-all flex flex-col gap-3"
      :class="checkoutStore.useWallet
        ? 'border-emerald-500/50 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs'
        : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-800/30'"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="size-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <UIcon
              name="i-lucide-wallet"
              class="size-5"
            />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h4 class="font-bold text-neutral-900 dark:text-white text-sm">
                استفاده از موجودی کیف پول
              </h4>
              <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-100/60 dark:bg-emerald-900/60 px-2 py-0.5 rounded-full">
                موجودی: {{ formatPrice(checkoutStore.walletBalance) }}
              </span>
            </div>
            <p class="text-xs text-neutral-500 mt-0.5">
              کسر خودکار از اعتبار حساب بدون نیاز به مراجعه به درگاه بانکی
            </p>
          </div>
        </div>

        <input
          v-model="checkoutStore.useWallet"
          type="checkbox"
          class="size-5 rounded-md border-neutral-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
        >
      </div>

      <div
        v-if="checkoutStore.useWallet"
        class="text-xs pt-2 border-t border-emerald-200/50 dark:border-emerald-800/50 flex flex-wrap items-center justify-between gap-2"
      >
        <span class="text-emerald-800 dark:text-emerald-300 font-medium">
          مبلغ قابل کسر از کیف پول: <strong>{{ formatPrice(walletDeductionAmount) }}</strong>
        </span>
        <span
          v-if="isFullyCoveredByWallet"
          class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1"
        >
          <UIcon
            name="i-lucide-check-circle"
            class="size-3.5"
          />
          کل مبلغ سفارش از کیف پول پرداخت می‌شود
        </span>
        <span
          v-else
          class="text-neutral-500"
        >
          مانده قابل پرداخت آنلاین: <strong>{{ formatPrice(checkoutStore.effectivePayable) }}</strong>
        </span>
      </div>
    </div>

    <!-- 2. PAYMENT GATEWAYS (Hidden if 100% covered by wallet) -->
    <div
      v-if="!isFullyCoveredByWallet"
      class="flex flex-col gap-3"
    >
      <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
        شیوه پرداخت مبلغ نهایی:
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="gw in checkoutStore.gateways"
          :key="gw.id"
          class="relative rounded-2xl border p-5 transition-all cursor-pointer flex flex-col justify-between gap-3"
          :class="checkoutStore.selectedGateway === gw.id
            ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs ring-1 ring-primary/30'
            : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-800/40 hover:border-neutral-300 dark:hover:border-neutral-700'"
          @click="checkoutStore.selectedGateway = gw.id"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div
                class="size-10 rounded-xl flex items-center justify-center shrink-0"
                :class="checkoutStore.selectedGateway === gw.id ? 'bg-primary text-white' : 'bg-neutral-200/70 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
              >
                <UIcon
                  :name="gw.id === 'snapp_pay' ? 'i-lucide-percent' : gw.id === 'card_to_card' ? 'i-lucide-receipt' : 'i-lucide-shield-check'"
                  class="size-5"
                />
              </div>
              <div>
                <h4 class="font-bold text-neutral-900 dark:text-white text-sm">
                  {{ gw.name }}
                </h4>
                <span
                  v-if="gw.id === 'sandbox'"
                  class="text-[10px] px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold"
                >
                  محیط آزمایشی (بدون کسر وجه)
                </span>
                <span
                  v-else-if="gw.id === 'snapp_pay'"
                  class="text-[10px] px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-600 dark:text-rose-400 font-bold"
                >
                  اقساط ۴ ماهه بدون بهره
                </span>
                <span
                  v-else-if="gw.id === 'card_to_card'"
                  class="text-[10px] px-2 py-0.5 rounded-md bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold"
                >
                  واریز مستقیم به شماره کارت
                </span>
              </div>
            </div>

            <div
              class="size-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
              :class="checkoutStore.selectedGateway === gw.id ? 'border-primary' : 'border-neutral-400'"
            >
              <div
                v-if="checkoutStore.selectedGateway === gw.id"
                class="size-2.5 rounded-full bg-primary"
              />
            </div>
          </div>

          <p class="text-xs text-neutral-500 leading-relaxed">
            {{ gw.description }}
          </p>
        </div>
      </div>
    </div>

    <!-- 3. CARD-TO-CARD RECEIPT DETAILS (Visible if card_to_card selected) -->
    <div
      v-if="!isFullyCoveredByWallet && checkoutStore.selectedGateway === 'card_to_card'"
      class="rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 p-5 flex flex-col gap-4"
    >
      <div class="flex items-center gap-2 text-blue-950 dark:text-blue-200 font-bold text-sm">
        <UIcon
          name="i-lucide-landmark"
          class="size-5 text-blue-600"
        />
        <span>اطلاعات حساب بانکی جهت واریز کارت‌به‌کارت</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-white dark:bg-neutral-900 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40">
        <div>
          <span class="text-neutral-500 block mb-1">شماره کارت:</span>
          <span class="font-mono font-black text-sm text-neutral-900 dark:text-white [direction:ltr] select-all">
            ۶۰۳۷-۹۹۷۹-۱۲۳۴-۵۶۷۸
          </span>
        </div>
        <div>
          <span class="text-neutral-500 block mb-1">نام صاحب حساب:</span>
          <span class="font-bold text-neutral-900 dark:text-white">
            {{ settingsStore.settings.store_name || 'فروشگاه ایزی‌شاپ' }}
          </span>
        </div>
        <div>
          <span class="text-neutral-500 block mb-1">شماره شبا:</span>
          <span class="font-mono text-neutral-800 dark:text-neutral-200 [direction:ltr] select-all text-[11px]">
            IR1201700000001234567890
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="space-y-1">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            شماره پیگیری / ارجاع واریز <span class="text-red-500">*</span>
          </label>
          <input
            v-model="checkoutStore.cardTrackingNumber"
            type="text"
            placeholder="مثال: ۱۲۹۴۸۵۷۳۶"
            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs font-mono font-bold focus:outline-none focus:border-blue-500"
          >
        </div>

        <div class="space-y-1">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            ۴ رقم آخر کارت مبدا (اختیاری)
          </label>
          <input
            v-model="checkoutStore.cardSourceNumber"
            type="text"
            maxlength="16"
            placeholder="مثال: ۶۰۳۷...۴۵۱۲"
            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs font-mono focus:outline-none focus:border-blue-500"
          >
        </div>
      </div>
      <p class="text-[11px] text-neutral-500">
        سفارش شما پس از ثبت در وضعیت «در انتظار پرداخت» قرار گرفته و پس از تطابق فیش بانکی توسط کارشناس مالی، پردازش خواهد شد.
      </p>
    </div>

    <!-- 4. CORPORATE TAX INVOICE CHECKBOX (ماده ۱۹) -->
    <div class="rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 p-5 bg-neutral-50/50 dark:bg-neutral-800/20 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <UIcon
            name="i-lucide-building-2"
            class="size-5 text-neutral-600 dark:text-neutral-400"
          />
          <div>
            <h4 class="font-bold text-neutral-900 dark:text-white text-xs sm:text-sm">
              درخواست صدور فاکتور رسمی حقوقی (ماده ۱۹ ارزش افزوده)
            </h4>
            <p class="text-[11px] text-neutral-500">
              ویژه شرکت‌ها، سازمان‌ها و خرید با نام شخص حقوقی با درج کد اقتصادی و شناسه ملی
            </p>
          </div>
        </div>

        <input
          v-model="checkoutStore.isCorporateInvoice"
          type="checkbox"
          class="size-5 rounded-md border-neutral-300 text-primary focus:ring-primary cursor-pointer"
        >
      </div>

      <!-- Corporate Fields -->
      <div
        v-if="checkoutStore.isCorporateInvoice"
        class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-neutral-200 dark:border-neutral-700"
      >
        <div class="space-y-1">
          <label class="text-[11px] font-bold text-neutral-700 dark:text-neutral-300">
            نام کامل شرکت / سازمان <span class="text-red-500">*</span>
          </label>
          <input
            v-model="checkoutStore.corporateData.company_name"
            type="text"
            placeholder="مثال: شرکت داده‌ورزی رایان پارس"
            class="w-full px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs focus:outline-none focus:border-primary"
          >
        </div>

        <div class="space-y-1">
          <label class="text-[11px] font-bold text-neutral-700 dark:text-neutral-300">
            شناسه ملی ۱۱ رقمی <span class="text-red-500">*</span>
          </label>
          <input
            v-model="checkoutStore.corporateData.national_id"
            type="text"
            maxlength="14"
            placeholder="مثال: ۱۰۳۲۰۸۷۶۵۴۳"
            class="w-full px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs font-mono focus:outline-none focus:border-primary"
          >
        </div>

        <div class="space-y-1">
          <label class="text-[11px] font-bold text-neutral-700 dark:text-neutral-300">
            کد اقتصادی (اختیاری)
          </label>
          <input
            v-model="checkoutStore.corporateData.economic_code"
            type="text"
            placeholder="مثال: ۴۱۱۴۸۵۲۹۷۵۳۱"
            class="w-full px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs font-mono focus:outline-none focus:border-primary"
          >
        </div>

        <div class="space-y-1">
          <label class="text-[11px] font-bold text-neutral-700 dark:text-neutral-300">
            شماره ثبت شرکت (اختیاری)
          </label>
          <input
            v-model="checkoutStore.corporateData.registration_number"
            type="text"
            placeholder="مثال: ۵۸۲۱۴۰"
            class="w-full px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white text-xs font-mono focus:outline-none focus:border-primary"
          >
        </div>
      </div>
    </div>

    <!-- 5. ORDER NOTES -->
    <div class="space-y-2 pt-1 border-t border-neutral-100 dark:border-neutral-800">
      <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300 flex items-center gap-1.5">
        <UIcon
          name="i-lucide-message-square"
          class="size-4 text-primary"
        />
        <span>یادداشت یا توضیحات سفارش (اختیاری)</span>
      </label>
      <UTextarea
        v-model="checkoutStore.notes"
        placeholder="در صورتی که نکته‌ای برای تحویل یا زمان ارسال دارید اینجا بنویسید..."
        :rows="2"
        class="w-full"
      />
    </div>
  </section>
</template>
