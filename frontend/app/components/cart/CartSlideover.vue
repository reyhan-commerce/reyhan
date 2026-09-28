<script setup lang="ts">
import CartItemSkeleton from '~/components/skeletons/CartItemSkeleton.vue'

const cartStore = useCartStore()
const { formatPrice, toPersianDigits } = usePersian()
const couponInput = ref('')

const isOpen = computed({
  get: () => cartStore.isSlideoverOpen,
  set: (val: boolean) => {
    cartStore.isSlideoverOpen = val
  }
})

const handleApplyCoupon = async () => {
  if (!couponInput.value) return
  const success = await cartStore.applyCoupon(couponInput.value)
  if (success) {
    couponInput.value = ''
  }
}

const handleRemoveCoupon = async () => {
  await cartStore.removeCoupon()
}
</script>

<template>
  <USlideover
    v-model:open="isOpen"
    side="left"
    dir="rtl"
    inset
    :title="`سبد خرید شما (${toPersianDigits(cartStore.itemsCount)} کالا)`"
    :description="cartStore.isEmpty ? 'سبد خرید شما در حال حاضر خالی است' : `${toPersianDigits(cartStore.itemsCount)} قلم کالا در سبد خرید شما موجود است`"
  >
    <slot />

    <template #body>
      <!-- Free shipping progress bar -->
      <div
        v-if="cartStore.pricing && !cartStore.isEmpty"
        class="mb-4 p-3.5 rounded-2xl bg-primary-50/60 dark:bg-primary-950/30 border border-primary-100 dark:border-primary-900/40"
      >
        <div class="flex items-center justify-between text-xs font-medium mb-2">
          <div class="flex items-center gap-1.5 text-primary-700 dark:text-primary-300">
            <UIcon
              name="i-lucide-truck"
              class="size-4 shrink-0"
            />
            <span
              v-if="cartStore.pricing.is_free_shipping"
              class="font-bold text-emerald-600 dark:text-emerald-400"
            >
              تبریک! ارسال این سفارش رایگان شد 🎉
            </span>
            <span v-else>
              تنها {{ formatPrice(cartStore.pricing.remaining_for_free_shipping) }} تا ارسال رایگان
            </span>
          </div>
          <span class="text-[11px] font-black text-primary">
            {{ toPersianDigits(cartStore.pricing.free_shipping_progress) }}٪
          </span>
        </div>

        <div class="w-full h-1.5 bg-neutral-200/80 dark:bg-neutral-800 rounded-full overflow-hidden">
          <div
            class="h-full bg-primary rounded-full transition-all duration-500 ease-out"
            :style="{ width: `${cartStore.pricing.free_shipping_progress}%` }"
          />
        </div>
      </div>

      <!-- Loading Skeleton State -->
      <div
        v-if="cartStore.isLoading && cartStore.isEmpty"
        class="space-y-3 p-1"
      >
        <CartItemSkeleton
          v-for="i in 3"
          :key="i"
        />
      </div>

      <!-- Empty State -->
      <div
        v-else-if="cartStore.isEmpty"
        class="flex flex-col items-center justify-center py-16 text-center"
      >
        <div class="size-20 rounded-3xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4 text-neutral-400 dark:text-neutral-500 shadow-xs">
          <UIcon
            name="i-lucide-shopping-bag"
            class="size-10 text-primary"
          />
        </div>
        <h3 class="text-base font-black text-neutral-900 dark:text-white mb-1.5">
          سبد خرید شما خالی است
        </h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-6 max-w-xs leading-relaxed">
          می‌توانید برای مشاهده و خرید محصولات به کاتالوگ فروشگاه مراجعه کنید.
        </p>
        <UButton
          to="/products"
          color="primary"
          icon="i-lucide-arrow-left"
          trailing
          class="rounded-xl font-bold shadow-sm shadow-primary/25"
          @click="cartStore.closeSlideover"
        >
          مشاهده کاتالوگ محصولات
        </UButton>
      </div>

      <!-- Cart Items List -->
      <div
        v-else
        class="divide-y divide-neutral-100 dark:divide-neutral-800/80"
      >
        <CartItemRow
          v-for="item in cartStore.cart?.items"
          :key="item.id"
          :item="item"
          compact
        />

        <!-- Coupon section -->
        <div class="pt-4 mt-2">
          <!-- Active applied coupon -->
          <div
            v-if="cartStore.pricing?.applied_coupon"
            class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50"
          >
            <div class="flex items-center gap-2">
              <UIcon
                name="i-lucide-ticket"
                class="size-4 text-emerald-600 dark:text-emerald-400"
              />
              <div class="text-xs">
                <span class="font-black font-mono text-emerald-700 dark:text-emerald-300">
                  {{ cartStore.pricing.applied_coupon.code }}
                </span>
                <span class="text-emerald-600 dark:text-emerald-400 mr-2">
                  ({{ formatPrice(cartStore.pricing.coupon_discount) }} تخفیف)
                </span>
              </div>
            </div>
            <UButton
              color="neutral"
              variant="ghost"
              size="xs"
              icon="i-lucide-trash-2"
              class="text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg"
              :loading="cartStore.isApplyingCoupon"
              @click="handleRemoveCoupon"
            />
          </div>

          <!-- Coupon input -->
          <div
            v-else
            class="flex items-center gap-2"
          >
            <UInput
              v-model="couponInput"
              placeholder="کد تخفیف دارید؟"
              size="sm"
              class="flex-1"
              :disabled="cartStore.isApplyingCoupon"
              @keydown.enter.prevent="handleApplyCoupon"
            />
            <UButton
              color="neutral"
              variant="outline"
              size="sm"
              class="rounded-xl"
              :loading="cartStore.isApplyingCoupon"
              :disabled="!couponInput.trim()"
              @click="handleApplyCoupon"
            >
              اعمال
            </UButton>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div
        v-if="!cartStore.isEmpty"
        class="w-full space-y-3"
      >
        <!-- Breakdown table -->
        <div class="space-y-2 text-xs text-neutral-600 dark:text-neutral-400">
          <div class="flex justify-between">
            <span>مجموع خرید:</span>
            <span class="font-bold text-neutral-900 dark:text-white">{{ formatPrice(cartStore.pricing?.original_items_subtotal) }}</span>
          </div>

          <div
            v-if="cartStore.pricing && cartStore.pricing.catalog_discount > 0"
            class="flex justify-between text-primary font-bold"
          >
            <span>سود شما از تخفیف‌ها:</span>
            <span>{{ formatPrice(cartStore.pricing.catalog_discount) }}-</span>
          </div>

          <div
            v-if="cartStore.pricing && cartStore.pricing.coupon_discount > 0"
            class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold"
          >
            <span>تخفیف کوپن:</span>
            <span>{{ formatPrice(cartStore.pricing.coupon_discount) }}-</span>
          </div>

          <div
            v-if="cartStore.pricing && cartStore.pricing.tax_amount > 0"
            class="flex justify-between"
          >
            <span>مالیات بر ارزش افزوده (۱۰٪):</span>
            <span class="font-bold text-neutral-900 dark:text-white">
              {{ formatPrice(cartStore.pricing.tax_amount) }}+
            </span>
          </div>

          <div class="flex justify-between">
            <span>هزینه ارسال:</span>
            <span
              v-if="cartStore.pricing?.is_free_shipping"
              class="text-emerald-600 dark:text-emerald-400 font-black"
            >
              رایگان
            </span>
            <span
              v-else
              class="font-bold text-neutral-900 dark:text-white"
            >
              {{ formatPrice(cartStore.pricing?.shipping_fee) }}
            </span>
          </div>

          <div class="pt-2.5 border-t border-neutral-100 dark:border-neutral-800 flex justify-between items-center text-sm font-black text-neutral-900 dark:text-white">
            <span>مبلغ قابل پرداخت:</span>
            <span class="text-primary text-base font-black">
              {{ formatPrice(cartStore.pricing?.final_payable) }}
            </span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="grid grid-cols-2 gap-2 pt-2">
          <UButton
            to="/cart"
            color="neutral"
            variant="outline"
            block
            class="rounded-xl font-bold"
            @click="cartStore.closeSlideover"
          >
            مشاهده سبد خرید
          </UButton>
          <UButton
            to="/cart"
            color="primary"
            block
            trailing
            icon="i-lucide-arrow-left"
            class="rounded-xl font-bold shadow-sm shadow-primary/25"
            @click="cartStore.closeSlideover"
          >
            تکمیل سفارش
          </UButton>
        </div>
      </div>
    </template>
  </USlideover>
</template>
