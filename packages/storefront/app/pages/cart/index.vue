<script setup lang="ts">
import CartItemRow from '~/components/cart/CartItemRow.vue'

useSeoMeta({
  title: 'سبد خرید',
  description: 'مشاهده و بررسی اقلام سبد خرید، اعمال کد تخفیف و نهایی‌سازی سفارش'
})

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()
const toast = useToast()
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
    toast.add({
      title: 'سبد خرید خالی شد',
      color: 'neutral'
    })
  }
}
</script>

<template>
  <div class="py-6 sm:py-10 flex flex-col gap-6 sm:gap-8 max-w-6xl mx-auto">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col gap-3">
      <nav class="flex items-center gap-2 text-xs text-neutral-400">
        <NuxtLink
          to="/"
          class="hover:text-primary transition-colors"
        >
          صفحه اصلی
        </NuxtLink>
        <UIcon
          name="i-lucide-chevron-left"
          class="size-3.5"
        />
        <span class="text-neutral-800 dark:text-neutral-200 font-medium">سبد خرید</span>
      </nav>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
            سبد خرید شما
          </h1>
          <p class="text-xs sm:text-sm text-neutral-500 mt-1">
            بررسی اقلام انتخابی، مدیریت تعداد و اعمال تخفیف پیش از ثبت سفارش
          </p>
        </div>

        <div
          v-if="!cartStore.isEmpty"
          class="flex items-center gap-2 self-start sm:self-auto"
        >
          <span class="px-3.5 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary text-xs font-black border border-primary-200/50 dark:border-primary-800/40 shadow-2xs">
            {{ toPersianDigits(cartStore.itemsCount) }} قلم کالا
          </span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="cartStore.isEmpty"
      class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-8 sm:p-14 text-center max-w-lg mx-auto shadow-xs my-6"
    >
      <div class="size-24 rounded-3xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center mx-auto mb-6 shadow-xs">
        <UIcon
          name="i-lucide-shopping-bag"
          class="size-12 text-primary"
        />
      </div>
      <h2 class="text-xl font-black text-neutral-900 dark:text-white mb-2">
        سبد خرید شما در حال حاضر خالی است
      </h2>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mb-8 leading-relaxed">
        شما هنوز محصولی به سبد خرید خود اضافه نکرده‌اید. با مراجعه به کاتالوگ فروشگاه می‌توانید کالاهای مورد نظر خود را انتخاب فرمایید.
      </p>
      <UButton
        to="/products"
        color="primary"
        variant="solid"
        size="xl"
        icon="i-lucide-arrow-left"
        trailing
        class="rounded-2xl font-black shadow-md shadow-primary/25 min-h-12 px-6 cursor-pointer"
      >
        مشاهده کاتالوگ محصولات
      </UButton>
    </div>

    <!-- Active Cart Flow -->
    <div
      v-else
      class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start"
    >
      <!-- Main Items Column (8 cols) -->
      <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- Free Shipping Progress Card -->
        <div
          v-if="cartStore.pricing"
          class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 shadow-xs"
        >
          <div class="flex items-center justify-between text-xs sm:text-sm font-medium mb-3">
            <div class="flex items-center gap-2.5">
              <div class="size-8 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center shrink-0">
                <UIcon
                  name="i-lucide-truck"
                  class="size-4.5"
                />
              </div>
              <span
                v-if="cartStore.pricing.is_free_shipping"
                class="font-black text-emerald-600 dark:text-emerald-400"
              >
                تبریک! ارسال این سفارش رایگان شد 🎉
              </span>
              <span
                v-else
                class="text-neutral-700 dark:text-neutral-200"
              >
                تنها <strong class="font-black text-primary">{{ formatPrice(cartStore.pricing.remaining_for_free_shipping) }}</strong> تا ارسال رایگان سفارش
              </span>
            </div>

            <span class="text-xs font-black text-primary px-2.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/50">
              {{ toPersianDigits(cartStore.pricing.free_shipping_progress) }}٪
            </span>
          </div>

          <div class="w-full h-2 bg-neutral-100 dark:bg-neutral-800 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-primary-600 to-primary-400 rounded-full transition-all duration-500 ease-out"
              :style="{ width: `${cartStore.pricing.free_shipping_progress}%` }"
            />
          </div>
        </div>

        <!-- Items List Card -->
        <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-5 sm:p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800 mb-2">
            <div class="flex items-center gap-2.5">
              <h2 class="text-base sm:text-lg font-black text-neutral-900 dark:text-white">
                اقلام سفارش
              </h2>
              <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                {{ toPersianDigits(cartStore.itemsCount) }} قلم
              </span>
            </div>

            <button
              type="button"
              class="flex items-center gap-1.5 text-xs text-neutral-400 hover:text-red-500 transition-colors cursor-pointer py-1.5 px-2.5 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/30"
              @click="handleClearCart"
            >
              <UIcon
                name="i-lucide-trash-2"
                class="size-3.5"
              />
              <span>خالی کردن سبد</span>
            </button>
          </div>

          <div class="divide-y divide-neutral-100 dark:divide-neutral-800/80">
            <CartItemRow
              v-for="item in cartStore.cart?.items"
              :key="item.id"
              :item="item"
            />
          </div>
        </div>

        <!-- Trust Badges Card -->
        <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-5 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
          <div class="flex flex-col items-center gap-2 p-2">
            <div class="size-10 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-shield-check"
                class="size-5"
              />
            </div>
            <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100">ضمانت اصالت</span>
            <span class="text-[11px] text-neutral-400">۱۰۰٪ کالای اورجینال</span>
          </div>

          <div class="flex flex-col items-center gap-2 p-2">
            <div class="size-10 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-truck"
                class="size-5"
              />
            </div>
            <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100">ارسال سریع پستی</span>
            <span class="text-[11px] text-neutral-400">به سراسر کشور</span>
          </div>

          <div class="flex flex-col items-center gap-2 p-2">
            <div class="size-10 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-refresh-cw"
                class="size-5"
              />
            </div>
            <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100">۷ روز ضمانت بازگشت</span>
            <span class="text-[11px] text-neutral-400">در صورت مغایرت کالا</span>
          </div>

          <div class="flex flex-col items-center gap-2 p-2">
            <div class="size-10 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-headphones"
                class="size-5"
              />
            </div>
            <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100">پشتیبانی سفارشات</span>
            <span class="text-[11px] text-neutral-400">پاسخگویی آنلاین و تلفنی</span>
          </div>
        </div>
      </div>

      <!-- Sidebar Summary Column (4 cols) -->
      <div class="lg:col-span-4 flex flex-col gap-6 sticky top-24">
        <!-- Coupon Card -->
        <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-5 shadow-xs">
          <h3 class="text-sm font-black text-neutral-900 dark:text-white mb-3.5 flex items-center gap-2">
            <UIcon
              name="i-lucide-ticket"
              class="size-4.5 text-primary"
            />
            <span>کد تخفیف</span>
          </h3>

          <!-- Active applied coupon -->
          <div
            v-if="cartStore.pricing?.applied_coupon"
            class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50"
          >
            <div>
              <div class="font-mono font-black text-emerald-700 dark:text-emerald-300 text-sm">
                {{ cartStore.pricing.applied_coupon.code }}
              </div>
              <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                {{ formatPrice(cartStore.pricing.coupon_discount) }} تخفیف اعمال شد
              </div>
            </div>
            <button
              type="button"
              class="size-8 rounded-xl flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer"
              :disabled="cartStore.isApplyingCoupon"
              title="حذف کد تخفیف"
              @click="handleRemoveCoupon"
            >
              <UIcon
                name="i-lucide-trash-2"
                class="size-4"
              />
            </button>
          </div>

          <!-- Coupon input -->
          <div
            v-else
            class="flex gap-2"
          >
            <UInput
              v-model="couponInput"
              placeholder="کد تخفیف را وارد کنید"
              size="md"
              class="flex-1"
              :disabled="cartStore.isApplyingCoupon"
              @keydown.enter.prevent="handleApplyCoupon"
            />
            <UButton
              color="neutral"
              variant="outline"
              size="md"
              class="rounded-xl font-bold"
              :loading="cartStore.isApplyingCoupon"
              :disabled="!couponInput.trim()"
              @click="handleApplyCoupon"
            >
              ثبت
            </UButton>
          </div>
        </div>

        <!-- Order Summary Card -->
        <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 shadow-xs flex flex-col gap-5">
          <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
            <h3 class="font-black text-neutral-900 dark:text-white text-base flex items-center gap-2">
              <UIcon
                name="i-lucide-receipt"
                class="size-5 text-primary"
              />
              <span>خلاصه فاکتور سفارش</span>
            </h3>
            <span class="text-xs text-neutral-400">
              {{ toPersianDigits(cartStore.itemsCount) }} قلم کالا
            </span>
          </div>

          <div class="space-y-3 text-xs sm:text-sm">
            <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400">
              <span>قیمت کل کالاها:</span>
              <span class="font-bold text-neutral-900 dark:text-white">
                {{ formatPrice(cartStore.pricing?.original_items_subtotal) }}
              </span>
            </div>

            <div
              v-if="cartStore.pricing && cartStore.pricing.catalog_discount > 0"
              class="flex justify-between items-center text-primary font-bold"
            >
              <span>تخفیف محصولات:</span>
              <span>{{ formatPrice(cartStore.pricing.catalog_discount) }}-</span>
            </div>

            <div
              v-if="cartStore.pricing && cartStore.pricing.coupon_discount > 0"
              class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-bold"
            >
              <span>تخفیف کوپن:</span>
              <span>{{ formatPrice(cartStore.pricing.coupon_discount) }}-</span>
            </div>

            <div
              v-if="cartStore.pricing && cartStore.pricing.tax_amount > 0"
              class="flex justify-between items-center text-neutral-600 dark:text-neutral-400"
            >
              <span>مالیات بر ارزش افزوده (۱۰٪):</span>
              <span class="font-bold text-neutral-900 dark:text-white">
                {{ formatPrice(cartStore.pricing.tax_amount) }}+
              </span>
            </div>

            <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400">
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

            <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
              <span class="font-black text-neutral-900 dark:text-white text-sm sm:text-base">
                مبلغ نهایی پرداخت:
              </span>
              <span class="font-black text-primary text-lg sm:text-xl">
                {{ formatPrice(cartStore.pricing?.final_payable) }}
              </span>
            </div>
          </div>

          <!-- Checkout CTA -->
          <UButton
            color="primary"
            variant="solid"
            size="xl"
            block
            trailing
            icon="i-lucide-arrow-left"
            class="rounded-2xl font-black shadow-md shadow-primary/25 min-h-12 cursor-pointer"
            @click="handleProceedToCheckout"
          >
            ادامه فرایند خرید و تسویه
          </UButton>

          <!-- Safe shopping badge -->
          <div class="flex items-center justify-center gap-2 text-[11px] text-neutral-400 pt-1">
            <UIcon
              name="i-lucide-shield-check"
              class="size-4 text-emerald-500"
            />
            <span>پرداخت امن و تحویل با ضمانت کیفیت</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
