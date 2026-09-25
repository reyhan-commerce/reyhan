<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { useCartStore } from '~/stores/cart'
import { usePersian } from '~/composables/usePersian'

const emit = defineEmits<{
  (e: 'pay'): void
}>()

const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()
const { formatPrice } = usePersian()
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

      <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
        <span class="font-black text-neutral-900 dark:text-white text-sm sm:text-base">
          مبلغ نهایی پرداخت:
        </span>
        <span class="font-black text-primary text-lg sm:text-xl">
          {{ formatPrice(checkoutStore.previewFinalPayable ?? cartStore.pricing?.final_payable) }}
        </span>
      </div>
    </div>

    <!-- Pay CTA -->
    <UButton
      color="primary"
      variant="solid"
      size="xl"
      block
      icon="i-lucide-lock"
      :loading="checkoutStore.isSubmittingOrder"
      class="rounded-2xl font-black shadow-md shadow-primary/25 min-h-12 cursor-pointer"
      @click="emit('pay')"
    >
      پرداخت و ثبت نهایی سفارش
    </UButton>

    <!-- Safe shopping badge -->
    <div class="flex items-center justify-center gap-2 text-[11px] text-neutral-400 pt-2">
      <UIcon
        name="i-lucide-shield-check"
        class="size-4 text-emerald-500"
      />
      <span>ضمانت اصالت و بازگشت وجه تا ۷ روز</span>
    </div>
  </aside>
</template>
