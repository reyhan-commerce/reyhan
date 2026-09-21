<script setup lang="ts">
useHead({
  title: 'سبد خرید | ایزیشاپ'
})

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()
const { formatPrice, toPersianDigits } = usePersian()
const couponInput = ref('')

onMounted(() => {
  cartStore.fetchCart()
})

const handleProceedToCheckout = () => {
  if (!authStore.isAuthenticated) {
    authStore.openAuthModal()
    return
  }
  router.push('/checkout')
}

const handleApplyCoupon = async () => {
  if (!couponInput.value.trim()) return
  const success = await cartStore.applyCoupon(couponInput.value)
  if (success) {
    couponInput.value = ''
  }
}

const handleRemoveCoupon = async () => {
  await cartStore.removeCoupon()
}

const handleClearCart = async () => {
  if (confirm('آیا از خالی کردن سبد خرید خود اطمینان دارید؟')) {
    await cartStore.clearCart()
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50/50 dark:bg-gray-950 py-8">
    <UContainer>
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-6">
        <NuxtLink
          to="/"
          class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          خانه
        </NuxtLink>
        <UIcon
          name="i-lucide-chevron-left"
          class="w-3.5 h-3.5"
        />
        <span class="text-gray-900 dark:text-gray-100 font-medium">سبد خرید</span>
      </nav>

      <!-- Empty State -->
      <div
        v-if="cartStore.isEmpty"
        class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-12 text-center max-w-lg mx-auto shadow-xs"
      >
        <div class="w-24 h-24 rounded-full bg-primary-50 dark:bg-primary-950/30 text-primary-500 flex items-center justify-center mx-auto mb-6">
          <UIcon
            name="i-lucide-shopping-cart"
            class="w-12 h-12"
          />
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
          سبد خرید شما در حال حاضر خالی است
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
          شما هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. با مراجعه به کاتالوگ محصولات می‌توانید کالاهای مورد نظر خود را انتخاب فرمایید.
        </p>
        <UButton
          to="/products"
          color="primary"
          size="lg"
          icon="i-lucide-arrow-left"
          trailing
        >
          مشاهده کاتالوگ محصولات
        </UButton>
      </div>

      <!-- Active Cart -->
      <div
        v-else
        class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start"
      >
        <!-- Main items list (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
          <!-- Free shipping bar -->
          <div
            v-if="cartStore.pricing"
            class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-xs"
          >
            <div class="flex items-center justify-between text-sm font-medium mb-2">
              <div class="flex items-center gap-2 text-primary-700 dark:text-primary-300">
                <UIcon
                  name="i-lucide-truck"
                  class="w-5 h-5 shrink-0"
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
              <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                {{ toPersianDigits(cartStore.pricing.free_shipping_progress) }}٪
              </span>
            </div>

            <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
              <div
                class="h-full bg-primary-500 rounded-full transition-all duration-500 ease-out"
                :style="{ width: `${cartStore.pricing.free_shipping_progress}%` }"
              />
            </div>
          </div>

          <!-- Items list card -->
          <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-2">
              <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">
                  اقلام سبد خرید
                </h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400">
                  {{ toPersianDigits(cartStore.itemsCount) }} کالا
                </span>
              </div>

              <UButton
                color="neutral"
                variant="ghost"
                size="xs"
                icon="i-lucide-trash-2"
                class="text-gray-400 hover:text-red-500"
                @click="handleClearCart"
              >
                خالی کردن سبد
              </UButton>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
              <CartItemRow
                v-for="item in cartStore.cart?.items"
                :key="item.id"
                :item="item"
              />
            </div>
          </div>

          <!-- Trust Badges -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-center">
            <div class="flex flex-col items-center gap-1.5 p-2">
              <UIcon
                name="i-lucide-shield-check"
                class="w-6 h-6 text-primary-500"
              />
              <span class="text-xs font-bold text-gray-800 dark:text-gray-200">ضمانت اصالت</span>
              <span class="text-[11px] text-gray-400">۱۰۰٪ کالای اصل</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 p-2">
              <UIcon
                name="i-lucide-truck"
                class="w-6 h-6 text-primary-500"
              />
              <span class="text-xs font-bold text-gray-800 dark:text-gray-200">ارسال سریع پستی</span>
              <span class="text-[11px] text-gray-400">سراسر کشور</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 p-2">
              <UIcon
                name="i-lucide-refresh-cw"
                class="w-6 h-6 text-primary-500"
              />
              <span class="text-xs font-bold text-gray-800 dark:text-gray-200">۷ روز ضمانت بازگشت</span>
              <span class="text-[11px] text-gray-400">در صورت عدم رضایت</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 p-2">
              <UIcon
                name="i-lucide-headphones"
                class="w-6 h-6 text-primary-500"
              />
              <span class="text-xs font-bold text-gray-800 dark:text-gray-200">پشتیبانی ۲۴/۷</span>
              <span class="text-[11px] text-gray-400">پاسخگویی آنلاین</span>
            </div>
          </div>
        </div>

        <!-- Sidebar Summary (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
          <!-- Coupon card -->
          <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-xs">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
              <UIcon
                name="i-lucide-ticket"
                class="w-4 h-4 text-primary-500"
              />
              کد تخفیف
            </h3>

            <!-- Active coupon -->
            <div
              v-if="cartStore.pricing?.applied_coupon"
              class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50"
            >
              <div>
                <div class="font-mono font-en font-bold text-emerald-700 dark:text-emerald-300 text-sm">
                  {{ cartStore.pricing.applied_coupon.code }}
                </div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                  {{ formatPrice(cartStore.pricing.coupon_discount) }} تخفیف لحاظ شد
                </div>
              </div>
              <UButton
                color="neutral"
                variant="ghost"
                size="xs"
                icon="i-lucide-trash-2"
                class="text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30"
                :loading="cartStore.isApplyingCoupon"
                @click="handleRemoveCoupon"
              />
            </div>

            <!-- Coupon input -->
            <div
              v-else
              class="flex gap-2"
            >
              <UInput
                v-model="couponInput"
                placeholder="کد تخفیف را وارد کنید"
                class="flex-1"
                :disabled="cartStore.isApplyingCoupon"
                @keydown.enter.prevent="handleApplyCoupon"
              />
              <UButton
                color="neutral"
                variant="outline"
                :loading="cartStore.isApplyingCoupon"
                :disabled="!couponInput.trim()"
                @click="handleApplyCoupon"
              >
                ثبت
              </UButton>
            </div>
          </div>

          <!-- Price breakdown card -->
          <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 pb-3 border-b border-gray-100 dark:border-gray-800">
              خلاصه سفارش
            </h3>

            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
              <div class="flex justify-between items-center">
                <span>قیمت کل کالاها:</span>
                <span class="font-medium text-gray-800 dark:text-gray-200">
                  {{ formatPrice(cartStore.pricing?.original_items_subtotal) }}
                </span>
              </div>

              <div
                v-if="cartStore.pricing && cartStore.pricing.catalog_discount > 0"
                class="flex justify-between items-center text-red-500 font-medium"
              >
                <span>تخفیف محصولات:</span>
                <span>{{ formatPrice(cartStore.pricing.catalog_discount) }}-</span>
              </div>

              <div
                v-if="cartStore.pricing && cartStore.pricing.coupon_discount > 0"
                class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-medium"
              >
                <span>تخفیف کوپن:</span>
                <span>{{ formatPrice(cartStore.pricing.coupon_discount) }}-</span>
              </div>

              <div class="flex justify-between items-center">
                <span>هزینه ارسال:</span>
                <span
                  v-if="cartStore.pricing?.is_free_shipping"
                  class="text-emerald-600 dark:text-emerald-400 font-bold"
                >
                  رایگان
                </span>
                <span
                  v-else
                  class="font-medium text-gray-800 dark:text-gray-200"
                >
                  {{ formatPrice(cartStore.pricing?.shipping_fee) }}
                </span>
              </div>

              <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                <span class="text-base font-bold text-gray-900 dark:text-gray-100">
                  مبلغ نهایی پرداخت:
                </span>
                <span class="text-lg font-black text-primary-600 dark:text-primary-400">
                  {{ formatPrice(cartStore.pricing?.final_payable) }}
                </span>
              </div>
            </div>

            <UButton
              color="primary"
              size="lg"
              block
              trailing
              icon="i-lucide-arrow-left"
              class="mt-4 font-bold shadow-sm cursor-pointer"
              @click="handleProceedToCheckout"
            >
              ادامه ثبت سفارش
            </UButton>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
