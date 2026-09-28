<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { useCartStore } from '~/stores/cart'
import { usePersian } from '~/composables/usePersian'

const props = withDefaults(defineProps<{
  currentStep?: 1 | 2 | 3
}>(), {
  currentStep: 1
})

const emit = defineEmits<{
  (e: 'update:currentStep', step: 1 | 2 | 3): void
  (e: 'pay'): void
}>()

const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()
const toast = useToast()
const { formatPrice } = usePersian()

// Check if wallet covers 100% of payable
const isFullyCoveredByWallet = computed(() => {
  const total = checkoutStore.previewFinalPayable ?? checkoutStore.previewPricing?.final_payable ?? 0
  return checkoutStore.useWallet && checkoutStore.walletBalance >= total && total > 0
})

const buttonLabel = computed(() => {
  if (props.currentStep === 1) {
    return 'انتخاب شیوه ارسال'
  }
  if (props.currentStep === 2) {
    return 'ادامه به مرحله پرداخت'
  }
  if (isFullyCoveredByWallet.value) {
    return 'تسویه کامل از کیف پول و ثبت سفارش'
  }
  if (checkoutStore.selectedGateway === 'card_to_card') {
    return 'ثبت سفارش و دریافت شماره کارت'
  }
  return 'پرداخت و ثبت نهایی سفارش'
})

const isButtonTrailingIcon = computed(() => props.currentStep < 3)

const buttonIcon = computed(() => {
  if (props.currentStep < 3) {
    return 'i-lucide-arrow-left'
  }
  return 'i-lucide-lock'
})

function handlePrimaryClick() {
  if (props.currentStep === 1) {
    if (!checkoutStore.selectedAddressId) {
      toast.add({
        title: 'انتخاب آدرس الزامی است',
        description: 'لطفاً آدرس تحویل سفارش را مشخص فرمایید.',
        color: 'warning'
      })
      return
    }
    emit('update:currentStep', 2)
    return
  }

  if (props.currentStep === 2) {
    if (checkoutStore.selectedShippingMethod?.requires_time_slot && (!checkoutStore.selectedDeliveryDate || !checkoutStore.selectedDeliveryTimeSlot)) {
      toast.add({
        title: 'انتخاب بازه تحویل الزامی است',
        description: 'لطفاً روز و بازه زمانی تحویل سفارش را انتخاب فرمایید.',
        color: 'warning'
      })
      return
    }
    emit('update:currentStep', 3)
    return
  }

  emit('pay')
}

function handleGoPrev() {
  if (props.currentStep > 1) {
    emit('update:currentStep', (props.currentStep - 1) as 1 | 2)
  }
}
</script>

<template>
  <aside
    aria-label="خلاصه فاکتور سفارش"
    class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 flex flex-col gap-5 shadow-xs sticky top-24"
  >
    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
      <h3 class="font-black text-neutral-900 dark:text-white text-base flex items-center gap-2">
        <UIcon
          name="i-lucide-receipt"
          class="size-5 text-primary"
        />
        <span>خلاصه فاکتور سفارش</span>
      </h3>
      <span class="text-xs text-neutral-400">
        {{ cartStore.itemsCount }} قلم کالا
      </span>
    </div>

    <!-- Pricing Items -->
    <div class="space-y-3 text-xs sm:text-sm">
      <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400">
        <span>قیمت کالاها:</span>
        <span class="font-bold text-neutral-900 dark:text-white">
          {{ formatPrice(checkoutStore.previewPricing?.original_items_subtotal || cartStore.pricing?.original_items_subtotal) }}
        </span>
      </div>

      <div
        v-if="(checkoutStore.previewPricing?.catalog_discount || cartStore.pricing?.catalog_discount || 0) > 0"
        class="flex justify-between items-center text-primary font-bold"
      >
        <span>تخفیف کالاها:</span>
        <span>
          {{ formatPrice(checkoutStore.previewPricing?.catalog_discount || cartStore.pricing?.catalog_discount) }}-
        </span>
      </div>

      <div
        v-if="(checkoutStore.previewPricing?.coupon_discount || cartStore.pricing?.coupon_discount || 0) > 0"
        class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-bold"
      >
        <span>تخفیف کوپن:</span>
        <span>
          {{ formatPrice(checkoutStore.previewPricing?.coupon_discount || cartStore.pricing?.coupon_discount) }}-
        </span>
      </div>

      <div
        v-if="(checkoutStore.previewPricing?.tax_amount || cartStore.pricing?.tax_amount || 0) > 0"
        class="flex justify-between items-center text-neutral-600 dark:text-neutral-400"
      >
        <span>مالیات بر ارزش افزوده (۱۰٪):</span>
        <span class="font-bold text-neutral-900 dark:text-white">
          {{ formatPrice(checkoutStore.previewPricing?.tax_amount || cartStore.pricing?.tax_amount) }}+
        </span>
      </div>

      <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400">
        <span>هزینه ارسال:</span>
        <span
          v-if="checkoutStore.previewPricing?.is_free_shipping || cartStore.pricing?.is_free_shipping"
          class="text-emerald-600 dark:text-emerald-400 font-black"
        >
          رایگان
        </span>
        <span
          v-else
          class="font-bold text-neutral-900 dark:text-white"
        >
          {{ formatPrice(checkoutStore.previewPricing?.shipping_fee ?? cartStore.pricing?.shipping_fee) }}
        </span>
      </div>

      <div
        v-if="checkoutStore.useWallet && checkoutStore.walletBalance > 0"
        class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-bold"
      >
        <span>کسر از کیف پول:</span>
        <span>
          {{ formatPrice(Math.min(checkoutStore.walletBalance, checkoutStore.previewFinalPayable ?? cartStore.pricing?.final_payable ?? 0)) }}-
        </span>
      </div>

      <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
        <span class="font-black text-neutral-900 dark:text-white text-sm sm:text-base">
          {{ checkoutStore.useWallet ? 'مانده قابل پرداخت:' : 'مبلغ نهایی پرداخت:' }}
        </span>
        <span class="font-black text-primary text-lg sm:text-xl">
          {{ formatPrice(checkoutStore.useWallet ? checkoutStore.effectivePayable : (checkoutStore.previewFinalPayable ?? cartStore.pricing?.final_payable)) }}
        </span>
      </div>
    </div>

    <!-- Step-Aware Primary Action Button -->
    <div class="flex flex-col gap-2 pt-1">
      <UButton
        color="primary"
        variant="solid"
        size="xl"
        block
        :trailing="isButtonTrailingIcon"
        :icon="buttonIcon"
        :loading="currentStep === 3 && checkoutStore.isSubmittingOrder"
        class="rounded-2xl font-black shadow-md shadow-primary/25 min-h-12 cursor-pointer"
        @click="handlePrimaryClick"
      >
        {{ buttonLabel }}
      </UButton>

      <!-- Back to previous step link (only visible in steps 2 and 3) -->
      <button
        v-if="currentStep > 1"
        type="button"
        class="text-xs text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100 flex items-center justify-center gap-1.5 transition-colors cursor-pointer py-1.5"
        @click="handleGoPrev"
      >
        <UIcon
          name="i-lucide-arrow-right"
          class="size-3.5"
        />
        <span>بازگشت به {{ currentStep === 2 ? 'مرحله اول (آدرس تحویل)' : 'مرحله دوم (شیوه ارسال)' }}</span>
      </button>
    </div>

    <!-- Safe shopping badge -->
    <div class="flex items-center justify-center gap-2 text-[11px] text-neutral-400 pt-1">
      <UIcon
        name="i-lucide-shield-check"
        class="size-4 text-emerald-500"
      />
      <span>ضمانت اصالت و بازگشت وجه تا ۷ روز</span>
    </div>
  </aside>
</template>

