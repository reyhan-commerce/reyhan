<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'
import { usePersian } from '~/composables/usePersian'

defineProps<{
  active: boolean
}>()

const emit = defineEmits<{
  (e: 'next' | 'openAddressModal'): void
}>()

const checkoutStore = useCheckoutStore()
const { toPersianDigits } = usePersian()
</script>

<template>
  <section
    v-show="active"
    class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-8 flex flex-col gap-6 shadow-xs"
  >
    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-5">
      <div class="flex items-center gap-3">
        <div class="size-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
          <UIcon
            name="i-lucide-map-pin"
            class="size-5.5"
          />
        </div>
        <div>
          <h2 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
            نشانی و مشخصات تحویل‌گیرنده
          </h2>
          <p class="text-xs text-neutral-500 mt-0.5">
            سفارش به این نشانی پستی ارسال خواهد شد
          </p>
        </div>
      </div>

      <UButton
        color="primary"
        variant="soft"
        size="sm"
        icon="i-lucide-plus"
        class="rounded-xl font-bold"
        @click="emit('openAddressModal')"
      >
        افزودن آدرس جدید
      </UButton>
    </div>

    <!-- Loading Addresses -->
    <div
      v-if="checkoutStore.isLoadingAddresses"
      class="space-y-3"
    >
      <USkeleton class="h-24 w-full rounded-2xl" />
      <USkeleton class="h-24 w-full rounded-2xl" />
    </div>

    <!-- Empty Addresses State -->
    <div
      v-else-if="checkoutStore.addresses.length === 0"
      class="text-center py-10 border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-2xl flex flex-col items-center gap-4"
    >
      <div class="size-16 rounded-full bg-primary/10 text-primary flex items-center justify-center">
        <UIcon
          name="i-lucide-map-pin-off"
          class="size-8"
        />
      </div>
      <div>
        <h4 class="font-bold text-neutral-800 dark:text-neutral-200">
          هنوز آدرسی ثبت نکرده‌اید
        </h4>
        <p class="text-xs text-neutral-400 mt-1">
          برای ادامه سفارش، لطفاً نشانی پستی تحویل‌گیرنده را ثبت کنید.
        </p>
      </div>
      <UButton
        color="primary"
        variant="solid"
        icon="i-lucide-plus"
        class="rounded-xl font-bold px-6"
        @click="emit('openAddressModal')"
      >
        ثبت اولین آدرس
      </UButton>
    </div>

    <!-- Addresses Grid -->
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
          : 'border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-800/40 hover:border-neutral-300 dark:hover:border-neutral-700'"
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

          <span
            class="text-xs font-mono text-neutral-500 [direction:ltr]"
          >
            {{ addr.recipient_mobile }}
          </span>
        </div>

        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed ps-8">
          {{ addr.full_address }}
        </p>

        <div class="flex items-center justify-between text-xs text-neutral-400 ps-8 pt-1 border-t border-neutral-100 dark:border-neutral-800/60">
          <span>کد پستی: <strong class="font-mono text-neutral-600 dark:text-neutral-300">{{ toPersianDigits(addr.postal_code) }}</strong></span>

          <button
            type="button"
            class="text-red-500 hover:underline text-xs cursor-pointer"
            @click.stop="checkoutStore.deleteAddress(addr.id)"
          >
            حذف این آدرس
          </button>
        </div>
      </div>
    </div>

    <!-- Step 1 Actions -->
    <div
      v-if="checkoutStore.addresses.length > 0"
      class="flex justify-end pt-4 border-t border-neutral-100 dark:border-neutral-800"
    >
      <UButton
        color="primary"
        variant="solid"
        size="lg"
        trailing-icon="i-lucide-arrow-left"
        class="rounded-2xl px-8 font-bold shadow-md shadow-primary/25"
        @click="emit('next')"
      >
        انتخاب شیوه ارسال
      </UButton>
    </div>
  </section>
</template>
