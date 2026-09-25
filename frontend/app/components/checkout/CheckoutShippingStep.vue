<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { usePersian } from '~/composables/usePersian'

defineProps<{
  active: boolean
}>()

const emit = defineEmits<{
  (e: 'prev' | 'next'): void
}>()

const checkoutStore = useCheckoutStore()
const { formatPrice } = usePersian()

const shippingMethods = [
  {
    id: 'pishtaz' as const,
    title: 'پست پیشتاز سراسری',
    time: '۲ تا ۴ روز کاری',
    icon: 'i-lucide-truck',
    desc: 'ارسال با پست پیشتاز شرکت ملی پست به سراسر ایران'
  },
  {
    id: 'express' as const,
    title: 'پیک موتوری فوری (اکسپرس)',
    time: 'تحویل در همان روز (تهران)',
    icon: 'i-lucide-zap',
    desc: 'ویژه سفارش‌های پایتخت با هماهنگی تلفنی قبل از ارسال'
  }
]
</script>

<template>
  <section
    v-show="active"
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
          انتخاب شیوه و زمان ارسال
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
          : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-800/40 hover:border-neutral-300 dark:hover:border-neutral-700'"
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
        @click="emit('prev')"
      >
        بازگشت به آدرس
      </UButton>
      <UButton
        color="primary"
        variant="solid"
        size="lg"
        trailing-icon="i-lucide-arrow-left"
        class="rounded-2xl px-8 font-bold shadow-md shadow-primary/25"
        @click="emit('next')"
      >
        مرحله بعد: درگاه پرداخت
      </UButton>
    </div>
  </section>
</template>
