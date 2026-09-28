<script setup lang="ts">
import type { OrderReturn } from '~/types/customerCare'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'درخواست‌های مرجوعی کالا (RMA)',
  description: 'پیگیری وضعیت مرجوعی کالاها و استرداد وجه سفارش‌ها'
})

const api = useApi()
const settingsStore = useSettingsStore()
const { formatPrice } = usePersian()

const { data: response, pending: isLoading, refresh } = await useAsyncData('user-returns', () =>
  api<ApiResponse<{ data: OrderReturn[] }>>('/profile/returns')
)

const returns = computed(() => response.value?.data?.data ?? [])
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white flex items-center gap-2.5">
          <UIcon
            name="i-lucide-undo-2"
            class="size-6 text-primary"
          />
          <span>مرجوعی کالا و استرداد وجه (RMA)</span>
        </h1>
        <p class="text-xs text-neutral-500 mt-1">
          {{ settingsStore.settings.return_policy_notice || `امکان استرداد کالا تا ${settingsStore.settings.return_guarantee_days || 7} روز پس از تحویل مطابق با قوانین فروشگاه` }}
        </p>
      </div>

      <NuxtLink
        to="/profile/orders"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary/90 transition-colors"
      >
        <UIcon
          name="i-lucide-package"
          class="size-4"
        />
        <span>انتخاب سفارش جهت مرجوعی</span>
      </NuxtLink>
    </div>

    <!-- Empty State -->
    <div
      v-if="!returns || returns.length === 0"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-12 text-center flex flex-col items-center justify-center gap-3"
    >
      <div class="size-16 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
        <UIcon
          name="i-lucide-check-circle"
          class="size-8 text-emerald-500"
        />
      </div>
      <h3 class="font-bold text-base text-neutral-900 dark:text-white">
        درخواست مرجوعی فعالی ندارید
      </h3>
      <p class="text-xs text-neutral-500 max-w-sm">
        {{ settingsStore.settings.return_policy_notice || `در صورت مغایرت یا وجود ایراد در کالاهای دریافتی، از بخش سفارشات می‌توانید تا ${settingsStore.settings.return_guarantee_days || 7} روز درخواست مرجوعی ثبت کنید.` }}
      </p>
      <NuxtLink
        to="/profile/orders"
        class="mt-2 text-xs font-bold text-primary hover:underline"
      >
        مشاهده سفارش‌های تحویل شده ←
      </NuxtLink>
    </div>

    <!-- Returns List -->
    <div
      v-else
      class="flex flex-col gap-4"
    >
      <div
        v-for="item in returns"
        :key="item.id"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex flex-col gap-4"
      >
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-neutral-100 dark:border-neutral-800">
          <div class="flex items-center gap-3">
            <span class="font-mono font-black text-sm text-neutral-900 dark:text-white [direction:ltr]">
              {{ item.return_number }}
            </span>
            <span class="text-neutral-300 dark:text-neutral-700">|</span>
            <span class="text-xs text-neutral-500">
              سفارش: <strong class="text-neutral-700 dark:text-neutral-300 font-mono">{{ item.order_number }}</strong>
            </span>
          </div>

          <!-- Status Badge -->
          <span
            class="px-3 py-1 rounded-full text-xs font-bold"
            :class="{
              'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400': item.status === 'pending',
              'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-400': item.status === 'approved',
              'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-400': item.status === 'item_received',
              'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400': item.status === 'refunded',
              'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-400': item.status === 'rejected'
            }"
          >
            {{ item.status_label }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
          <div>
            <span class="text-neutral-500 block mb-0.5">علت درخواست:</span>
            <span class="font-bold text-neutral-800 dark:text-neutral-200">{{ item.reason }}</span>
          </div>
          <div>
            <span class="text-neutral-500 block mb-0.5">مبلغ برآوردی استرداد:</span>
            <span class="font-black text-primary font-mono text-sm">{{ formatPrice(item.refund_amount) }}</span>
          </div>
          <div>
            <span class="text-neutral-500 block mb-0.5">تاریخ ثبت درخواست:</span>
            <span class="text-neutral-600 dark:text-neutral-400">{{ item.created_at_jalali }}</span>
          </div>
        </div>

        <div
          v-if="item.admin_notes"
          class="p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 text-xs"
        >
          <span class="font-bold text-neutral-700 dark:text-neutral-300 block mb-0.5">پیام کارشناس پشتیبانی:</span>
          <span class="text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ item.admin_notes }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
