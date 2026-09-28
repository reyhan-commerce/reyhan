<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { usePersian } from '~/composables/usePersian'
import type { AvailableShippingMethod } from '~/types/order'

defineProps<{
  active: boolean
}>()

const emit = defineEmits<{
  (e: 'prev' | 'next'): void
}>()

const checkoutStore = useCheckoutStore()
const { formatPrice } = usePersian()
const toast = useToast()

// Default fallback options if API is still loading
const fallbackMethods = [
  {
    id: 1,
    name: 'پست پیشتاز سراسری',
    slug: 'pishtaz',
    description: 'ارسال با پست پیشتاز شرکت ملی پست به سراسر ایران با رهگیری آنلاین',
    icon: 'i-lucide-truck',
    shipping_fee: 650000,
    is_free: false,
    estimated_delivery_days: '۲ تا ۴ روز کاری',
    requires_time_slot: false,
    time_slots: []
  },
  {
    id: 2,
    name: 'پیک اکسپرس موتوری (تحویل فوری)',
    slug: 'express_courier',
    description: 'ویژه سفارش‌های پایتخت و حومه با هماهنگی و تحویل در بازه انتخابی',
    icon: 'i-lucide-zap',
    shipping_fee: 950000,
    is_free: false,
    estimated_delivery_days: 'تحویل در همان روز یا روز بعد',
    requires_time_slot: true,
    time_slots: []
  }
]

const displayedMethods = computed<AvailableShippingMethod[]>(() => {
  if (checkoutStore.shippingMethods.length > 0) {
    return checkoutStore.shippingMethods
  }
  return fallbackMethods as AvailableShippingMethod[]
})

function handleMethodSelect(method: AvailableShippingMethod) {
  checkoutStore.selectShippingMethod(method)
}

function handleNext() {
  if (checkoutStore.selectedShippingMethod?.requires_time_slot) {
    if (!checkoutStore.selectedDeliveryDate || !checkoutStore.selectedDeliveryTimeSlot) {
      toast.add({
        title: 'انتخاب بازه زمانی تحویل',
        description: 'لطفاً روز و بازه زمانی تحویل مرسوله توسط پیک را مشخص فرمایید.',
        color: 'warning'
      })
      return
    }
  }
  emit('next')
}
</script>

<template>
  <section
    v-show="active"
    class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
  >
    <!-- Section Header -->
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
          سرعت تحویل، هزینه مرسوله و بازه زمانی توزیع بر اساس آدرس مقصد
        </p>
      </div>
    </div>

    <!-- Shipping Methods Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div
        v-for="method in displayedMethods"
        :key="method.id"
        class="relative rounded-2xl border p-5 transition-all cursor-pointer flex flex-col justify-between gap-4"
        :class="checkoutStore.selectedShippingMethodId === method.id || (!checkoutStore.selectedShippingMethodId && method.slug === 'pishtaz')
          ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs ring-1 ring-primary/30'
          : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-800/40 hover:border-neutral-300 dark:hover:border-neutral-700'"
        @click="handleMethodSelect(method)"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div
              class="size-10 rounded-xl flex items-center justify-center shrink-0"
              :class="(checkoutStore.selectedShippingMethodId === method.id || (!checkoutStore.selectedShippingMethodId && method.slug === 'pishtaz')) ? 'bg-primary text-white' : 'bg-neutral-200/70 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
            >
              <UIcon
                :name="method.icon || 'i-lucide-truck'"
                class="size-5"
              />
            </div>
            <div>
              <h4 class="font-bold text-neutral-900 dark:text-white text-sm">
                {{ method.name }}
              </h4>
              <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                {{ method.estimated_delivery_days || 'ارسال در سریع‌ترین زمان' }}
              </span>
            </div>
          </div>

          <div
            class="size-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
            :class="(checkoutStore.selectedShippingMethodId === method.id || (!checkoutStore.selectedShippingMethodId && method.slug === 'pishtaz')) ? 'border-primary' : 'border-neutral-400'"
          >
            <div
              v-if="checkoutStore.selectedShippingMethodId === method.id || (!checkoutStore.selectedShippingMethodId && method.slug === 'pishtaz')"
              class="size-2.5 rounded-full bg-primary"
            />
          </div>
        </div>

        <p
          v-if="method.description"
          class="text-xs text-neutral-500 leading-relaxed"
        >
          {{ method.description }}
        </p>

        <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between text-xs font-bold">
          <span>هزینه ارسال:</span>
          <span
            v-if="method.is_free || checkoutStore.previewPricing?.is_free_shipping"
            class="text-emerald-600 dark:text-emerald-400 font-black"
          >
            ارسال رایگان
          </span>
          <span
            v-else
            class="text-neutral-800 dark:text-neutral-200"
          >
            {{ formatPrice(method.shipping_fee) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Delivery Time Slot Selection (For Express Courier) -->
    <div
      v-if="checkoutStore.selectedShippingMethod?.requires_time_slot && checkoutStore.availableTimeSlots.length > 0"
      class="mt-2 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200/80 dark:border-neutral-800 p-5 flex flex-col gap-4"
    >
      <div class="flex items-center gap-2 text-sm font-black text-neutral-900 dark:text-white">
        <UIcon
          name="i-lucide-calendar-clock"
          class="size-4.5 text-primary"
        />
        <span>انتخاب روز و بازه زمانی تحویل پیک اکسپرس</span>
      </div>

      <!-- Date Selection Pills -->
      <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
        <button
          v-for="day in checkoutStore.availableTimeSlots"
          :key="day.date"
          type="button"
          class="px-4 py-2.5 rounded-xl border text-xs font-bold transition-all flex flex-col items-center gap-1 shrink-0 cursor-pointer min-w-24"
          :class="checkoutStore.selectedDeliveryDate === day.date
            ? 'border-primary bg-primary text-white shadow-xs'
            : 'border-neutral-200/80 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:border-neutral-400'"
          @click="checkoutStore.selectedDeliveryDate = day.date"
        >
          <span class="text-xs">{{ day.day_name }}</span>
          <span class="text-[11px] opacity-85 font-mono">{{ day.jalali_date }}</span>
        </button>
      </div>

      <!-- Time Slots for Selected Date -->
      <div
        v-if="checkoutStore.selectedDeliveryDate"
        class="flex flex-col gap-2 pt-2 border-t border-neutral-200/60 dark:border-neutral-800"
      >
        <span class="text-xs text-neutral-500 font-bold">بازه زمانی تحویل:</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="slot in checkoutStore.availableTimeSlots.find(d => d.date === checkoutStore.selectedDeliveryDate)?.slots || []"
            :key="slot"
            class="rounded-xl border p-3 flex items-center justify-between cursor-pointer transition-all text-xs font-bold"
            :class="checkoutStore.selectedDeliveryTimeSlot === slot
              ? 'border-primary bg-primary/10 dark:bg-primary/20 text-primary ring-1 ring-primary/40'
              : 'border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:border-neutral-300'"
            @click="checkoutStore.selectedDeliveryTimeSlot = slot"
          >
            <div class="flex items-center gap-2">
              <UIcon
                name="i-lucide-clock"
                class="size-4"
              />
              <span>{{ slot }}</span>
            </div>
            <UIcon
              v-if="checkoutStore.selectedDeliveryTimeSlot === slot"
              name="i-lucide-check-circle"
              class="size-4.5 text-primary"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Tracking SMS Notice -->
    <div class="rounded-2xl bg-amber-500/10 border border-amber-500/20 p-4 flex items-center gap-3 text-xs text-amber-800 dark:text-amber-300">
      <UIcon
        name="i-lucide-shield-check"
        class="size-5 shrink-0 text-amber-600 dark:text-amber-400"
      />
      <span>
        به محض تحویل مرسوله به شرکت حمل‌ونقل، کد رهگیری پستی و لینک پیگیری لحظه‌ای از طریق پیامک برای شما ارسال خواهد شد.
      </span>
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
        @click="handleNext"
      >
        مرحله بعد: درگاه پرداخت
      </UButton>
    </div>
  </section>
</template>
