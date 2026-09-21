<script setup lang="ts">
import AddressModal from '~/components/checkout/AddressModal.vue'
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useCheckoutStore } from '~/stores/checkout'

const authStore = useAuthStore()
const cartStore = useCartStore()
const checkoutStore = useCheckoutStore()
const { formatPrice } = usePersian()
const toast = useToast()

useSeoMeta({
  title: 'تکمیل سفارش و تسویه‌حساب - فروشگاه ایزیشاپ',
  description: 'انتخاب آدرس تحویل، انتخاب شیوه ارسال و اتصال به درگاه امن بانکی',
})

// Current active step
const currentStep = ref<1 | 2 | 3>(1)
const isAddressModalOpen = ref(false)

// Initialize data
onMounted(async () => {
  // If not logged in, prompt user
  if (!authStore.isAuthenticated) {
    authStore.openAuthModal()
    return
  }

  await cartStore.fetchCart()

  if (cartStore.itemsCount === 0) {
    navigateTo('/cart')
    return
  }

  await Promise.all([
    checkoutStore.fetchAddresses(),
    checkoutStore.fetchGateways(),
    checkoutStore.fetchPreview(),
  ])
})

// When address or shipping method changes, update preview
watch(() => checkoutStore.selectedAddressId, () => {
  checkoutStore.fetchPreview()
})

watch(() => checkoutStore.selectedShippingMethod, () => {
  checkoutStore.fetchPreview()
})

// Shipping methods definition
const shippingMethods = [
  {
    id: 'pishtaz' as const,
    title: 'پست پیشتاز سراسری',
    time: '۲ تا ۴ روز کاری',
    icon: 'i-lucide-truck',
    desc: 'ارسال با پست پیشتاز شرکت ملی پست به سراسر ایران',
  },
  {
    id: 'express' as const,
    title: 'پیک موتوری فوری (اکسپرس)',
    time: 'تحویل در همان روز (تهران)',
    icon: 'i-lucide-zap',
    desc: 'ویژه سفارش‌های پایتخت با هماهنگی تلفنی قبل از ارسال',
  },
]

// Submit checkout order
async function handlePay() {
  if (!checkoutStore.selectedAddressId) {
    currentStep.value = 1
    toast.add({
      title: 'انتخاب آدرس الزامی است',
      description: 'لطفاً آدرس تحویل سفارش را مشخص فرمایید.',
      color: 'warning',
    })
    return
  }

  try {
    const result = await checkoutStore.submitOrder()
    if (result.redirectUrl) {
      window.location.href = result.redirectUrl
    }
  } catch (err: any) {
    toast.add({
      title: 'خطا در ثبت سفارش',
      description: err?.message || 'مشکلی در اتصال به درگاه پرداخت رخ داده است.',
      color: 'error',
    })
  }
}
</script>

<template>
  <div class="py-6 sm:py-10 flex flex-col gap-8 pb-28 sm:pb-16 max-w-6xl mx-auto">
    <!-- Stepper Navigation -->
    <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-4 sm:p-6 shadow-xs">
      <div class="flex items-center justify-between max-w-2xl mx-auto">
        <!-- Step 1 -->
        <button
          type="button"
          class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start"
          :class="currentStep === 1 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
          @click="currentStep = 1"
        >
          <div
            class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-mono font-bold text-sm transition-all"
            :class="currentStep >= 1 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
          >
            <UIcon
              v-if="currentStep > 1"
              name="i-lucide-check"
              class="size-5"
            />
            <span v-else>۱</span>
          </div>
          <div class="hidden sm:flex flex-col">
            <span class="text-xs font-bold">مرحله اول</span>
            <span class="text-sm">آدرس تحویل</span>
          </div>
        </button>

        <div class="flex-1 h-0.5 mx-3 sm:mx-6 bg-neutral-200 dark:bg-neutral-800 rounded-full overflow-hidden">
          <div
            class="h-full bg-primary transition-all duration-300"
            :style="{ width: currentStep >= 2 ? '100%' : '0%' }"
          />
        </div>

        <!-- Step 2 -->
        <button
          type="button"
          class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start"
          :class="currentStep === 2 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
          @click="currentStep = 2"
        >
          <div
            class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-mono font-bold text-sm transition-all"
            :class="currentStep >= 2 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
          >
            <UIcon
              v-if="currentStep > 2"
              name="i-lucide-check"
              class="size-5"
            />
            <span v-else>۲</span>
          </div>
          <div class="hidden sm:flex flex-col">
            <span class="text-xs font-bold">مرحله دوم</span>
            <span class="text-sm">شیوه ارسال</span>
          </div>
        </button>

        <div class="flex-1 h-0.5 mx-3 sm:mx-6 bg-neutral-200 dark:bg-neutral-800 rounded-full overflow-hidden">
          <div
            class="h-full bg-primary transition-all duration-300"
            :style="{ width: currentStep >= 3 ? '100%' : '0%' }"
          />
        </div>

        <!-- Step 3 -->
        <button
          type="button"
          class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start"
          :class="currentStep === 3 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
          @click="currentStep = 3"
        >
          <div
            class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-mono font-bold text-sm transition-all"
            :class="currentStep === 3 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
          >
            <span>۳</span>
          </div>
          <div class="hidden sm:flex flex-col">
            <span class="text-xs font-bold">مرحله سوم</span>
            <span class="text-sm">پرداخت و ثبت</span>
          </div>
        </button>
      </div>
    </div>

    <!-- Main Layout: Wizard Steps (8 cols) + Invoice Summary (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Steps Container (8 cols) -->
      <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- STEP 1: Address Selection -->
        <section
          v-show="currentStep === 1"
          class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-100 dark:border-neutral-800 pb-5">
            <div class="flex items-center gap-3">
              <div class="size-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <UIcon
                  name="i-lucide-map-pin"
                  class="size-5.5"
                />
              </div>
              <div>
                <h2 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
                  انتخاب آدرس تحویل سفارش
                </h2>
                <p class="text-xs text-neutral-500 mt-0.5">
                  مرسوله به این نشانی ارسال خواهد شد
                </p>
              </div>
            </div>

            <UButton
              color="primary"
              variant="outline"
              size="sm"
              icon="i-lucide-plus"
              class="rounded-xl px-4 font-bold shrink-0 self-start sm:self-auto"
              @click="isAddressModalOpen = true"
            >
              افزودن آدرس جدید
            </UButton>
          </div>

          <!-- Address Cards -->
          <div
            v-if="checkoutStore.isLoadingAddresses"
            class="py-12 flex items-center justify-center"
          >
            <UIcon
              name="i-lucide-loader-2"
              class="size-7 animate-spin text-primary"
            />
          </div>

          <div
            v-else-if="checkoutStore.addresses.length === 0"
            class="py-12 flex flex-col items-center justify-center text-center gap-4 bg-neutral-50/50 dark:bg-neutral-800/20 rounded-2xl border border-dashed border-neutral-200 dark:border-neutral-800 p-8"
          >
            <div class="size-16 rounded-full bg-primary/10 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-map-pin-off"
                class="size-8"
              />
            </div>
            <div>
              <p class="font-bold text-neutral-800 dark:text-neutral-200">
                هنوز هیچ آدرسی ثبت نکرده‌اید
              </p>
              <p class="text-xs text-neutral-400 mt-1">
                جهت تکمیل خرید، لطفاً حداقل یک آدرس ثبت فرمایید.
              </p>
            </div>
            <UButton
              color="primary"
              variant="solid"
              size="md"
              icon="i-lucide-plus"
              class="rounded-xl font-bold shadow-md shadow-primary/25"
              @click="isAddressModalOpen = true"
            >
              ثبت اولین آدرس
            </UButton>
          </div>

          <div
            v-else
            class="grid grid-cols-1 gap-4"
          >
            <div
              v-for="addr in checkoutStore.addresses"
              :key="addr.id"
              class="relative rounded-2xl border p-5 transition-all cursor-pointer flex flex-col gap-3"
              :class="checkoutStore.selectedAddressId === addr.id
                ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs ring-1 ring-primary/30'
                : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-850 hover:border-neutral-300 dark:hover:border-neutral-700'"
              @click="checkoutStore.selectedAddressId = addr.id"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div
                    class="size-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                    :class="checkoutStore.selectedAddressId === addr.id ? 'border-primary' : 'border-neutral-400'"
                  >
                    <div
                      v-if="checkoutStore.selectedAddressId === addr.id"
                      class="size-2.5 rounded-full bg-primary"
                    />
                  </div>

                  <span class="font-bold text-neutral-900 dark:text-white text-sm">
                    {{ addr.recipient_name }}
                  </span>

                  <span
                    v-if="addr.is_default"
                    class="px-2 py-0.5 rounded-md bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 text-[10px] font-bold"
                  >
                    پیش‌فرض
                  </span>
                </div>

                <span class="text-xs font-mono text-neutral-500" dir="ltr">
                  {{ addr.recipient_mobile }}
                </span>
              </div>

              <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed ps-8">
                {{ addr.full_address }}
              </p>

              <div class="flex items-center justify-between text-xs text-neutral-400 ps-8 pt-1 border-t border-neutral-100 dark:border-neutral-800/60">
                <span>کد پستی: <strong class="font-mono text-neutral-600 dark:text-neutral-300">{{ addr.postal_code }}</strong></span>

                <button
                  type="button"
                  class="text-rose-500 hover:underline text-xs"
                  @click.stop="checkoutStore.deleteAddress(addr.id)"
                >
                  حذف آدرس
                </button>
              </div>
            </div>
          </div>

          <!-- Step 1 Next Action -->
          <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-neutral-800">
            <UButton
              color="primary"
              variant="solid"
              size="lg"
              trailing-icon="i-lucide-arrow-left"
              :disabled="!checkoutStore.selectedAddressId"
              class="rounded-2xl px-8 font-bold shadow-md shadow-primary/25"
              @click="currentStep = 2"
            >
              مرحله بعد: شیوه ارسال
            </UButton>
          </div>
        </section>

        <!-- STEP 2: Shipping Method Selection -->
        <section
          v-show="currentStep === 2"
          class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
        >
          <div class="flex items-center gap-3 border-b border-neutral-100 dark:border-neutral-800 pb-5">
            <div class="size-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-truck"
                class="size-5.5"
              />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
                انتخاب نحوه ارسال مرسوله
              </h2>
              <p class="text-xs text-neutral-500 mt-0.5">
                سرعت تحویل و هزینه بسته بر اساس مقصد
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="method in shippingMethods"
              :key="method.id"
              class="relative rounded-2xl border p-5 transition-all cursor-pointer flex flex-col justify-between gap-4"
              :class="checkoutStore.selectedShippingMethod === method.id
                ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs ring-1 ring-primary/30'
                : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-850 hover:border-neutral-300 dark:hover:border-neutral-700'"
              @click="checkoutStore.selectedShippingMethod = method.id"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="size-10 rounded-xl flex items-center justify-center shrink-0"
                    :class="checkoutStore.selectedShippingMethod === method.id ? 'bg-primary text-white' : 'bg-neutral-200/70 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
                  >
                    <UIcon
                      :name="method.icon"
                      class="size-5"
                    />
                  </div>
                  <div>
                    <h4 class="font-bold text-neutral-900 dark:text-white text-sm">
                      {{ method.title }}
                    </h4>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                      {{ method.time }}
                    </span>
                  </div>
                </div>

                <div
                  class="size-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                  :class="checkoutStore.selectedShippingMethod === method.id ? 'border-primary' : 'border-neutral-400'"
                >
                  <div
                    v-if="checkoutStore.selectedShippingMethod === method.id"
                    class="size-2.5 rounded-full bg-primary"
                  />
                </div>
              </div>

              <p class="text-xs text-neutral-500 leading-relaxed">
                {{ method.desc }}
              </p>

              <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between text-xs font-bold">
                <span>هزینه ارسال:</span>
                <span
                  v-if="checkoutStore.previewPricing?.is_free_shipping"
                  class="text-emerald-600 dark:text-emerald-400 font-black"
                >
                  ارسال رایگان
                </span>
                <span
                  v-else
                  class="text-neutral-800 dark:text-neutral-200"
                >
                  {{ formatPrice(checkoutStore.previewPricing?.shipping_fee || 650000) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Step 2 Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-800">
            <UButton
              color="neutral"
              variant="ghost"
              size="lg"
              icon="i-lucide-arrow-right"
              class="rounded-2xl px-5 font-bold"
              @click="currentStep = 1"
            >
              بازگشت به آدرس
            </UButton>
            <UButton
              color="primary"
              variant="solid"
              size="lg"
              trailing-icon="i-lucide-arrow-left"
              class="rounded-2xl px-8 font-bold shadow-md shadow-primary/25"
              @click="currentStep = 3"
            >
              مرحله بعد: درگاه پرداخت
            </UButton>
          </div>
        </section>

        <!-- STEP 3: Payment Gateway & Notes -->
        <section
          v-show="currentStep === 3"
          class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
        >
          <div class="flex items-center gap-3 border-b border-neutral-100 dark:border-neutral-800 pb-5">
            <div class="size-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
              <UIcon
                name="i-lucide-credit-card"
                class="size-5.5"
              />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
                انتخاب درگاه پرداخت الکترونیک
              </h2>
              <p class="text-xs text-neutral-500 mt-0.5">
                کلیه درگاه‌ها به سامانه شاپرک متصل و ایمن هستند
              </p>
            </div>
          </div>

          <!-- Gateways Options -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="gw in checkoutStore.gateways"
              :key="gw.id"
              class="relative rounded-2xl border p-5 transition-all cursor-pointer flex flex-col justify-between gap-3"
              :class="checkoutStore.selectedGateway === gw.id
                ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs ring-1 ring-primary/30'
                : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-850 hover:border-neutral-300 dark:hover:border-neutral-700'"
              @click="checkoutStore.selectedGateway = gw.id"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="size-10 rounded-xl flex items-center justify-center shrink-0"
                    :class="checkoutStore.selectedGateway === gw.id ? 'bg-primary text-white' : 'bg-neutral-200/70 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
                  >
                    <UIcon
                      name="i-lucide-shield-check"
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

          <!-- Order Notes -->
          <div class="space-y-2 pt-3 border-t border-neutral-100 dark:border-neutral-800">
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

          <!-- Step 3 Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-800">
            <UButton
              color="neutral"
              variant="ghost"
              size="lg"
              icon="i-lucide-arrow-right"
              class="rounded-2xl px-5 font-bold"
              @click="currentStep = 2"
            >
              بازگشت به شیوه ارسال
            </UButton>
            <UButton
              color="primary"
              variant="solid"
              size="lg"
              icon="i-lucide-lock"
              :loading="checkoutStore.isSubmittingOrder"
              class="rounded-2xl px-8 font-black shadow-lg shadow-primary/25"
              @click="handlePay"
            >
              پرداخت و ثبت نهایی سفارش
            </UButton>
          </div>
        </section>
      </div>

      <!-- Invoice Summary Sidebar (4 cols) -->
      <div class="lg:col-span-4 sticky top-24 space-y-4">
        <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 flex flex-col gap-5 shadow-xs">
          <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
            <h3 class="font-black text-neutral-900 dark:text-white text-base flex items-center gap-2">
              <UIcon
                name="i-lucide-receipt"
                class="size-5 text-primary"
              />
              <span>خلاصه فاکتور سفارش</span>
            </h3>
            <span class="text-xs text-neutral-400 font-mono">
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
              class="flex justify-between items-center text-rose-500 font-bold"
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
            class="rounded-2xl font-black shadow-md shadow-primary/25 min-h-12"
            @click="handlePay"
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
        </div>
      </div>
    </div>

    <!-- Address Modal -->
    <AddressModal
      v-model:open="isAddressModalOpen"
      @saved="(id) => { checkoutStore.selectedAddressId = id }"
    />
  </div>
</template>
