<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'

defineProps<{
  active: boolean
}>()

const emit = defineEmits<{
  (e: 'prev' | 'pay'): void
}>()

const checkoutStore = useCheckoutStore()
</script>

<template>
  <section
    v-show="active"
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
        @click="emit('prev')"
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
        @click="emit('pay')"
      >
        پرداخت و ثبت نهایی سفارش
      </UButton>
    </div>
  </section>
</template>
