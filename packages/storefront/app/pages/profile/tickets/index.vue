<script setup lang="ts">
import type { SupportTicket } from '~/types/customerCare'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'تیکت‌های پشتیبانی من',
  description: 'سیستم ثبت و پیگیری تیکت‌های پشتیبانی آنلاین'
})

const api = useApi()
const settingsStore = useSettingsStore()

const { data: response, pending: isLoading, refresh } = await useAsyncData('user-tickets', () =>
  api<ApiResponse<{ data: SupportTicket[] }>>('/tickets')
)

const tickets = computed(() => response.value?.data?.data ?? [])
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white flex items-center gap-2.5">
          <UIcon
            name="i-lucide-headset"
            class="size-6 text-primary"
          />
          <span>تیکت‌های پشتیبانی</span>
        </h1>
        <p class="text-xs text-neutral-500 mt-1">
          {{ settingsStore.settings.support_work_hours_notice || 'ارتباط مستقیم با کارشناسان فنی، امور مالی، فروش و پیگیری سفارشات' }}
        </p>
      </div>

      <NuxtLink
        to="/profile/tickets/create"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-xs hover:bg-primary/90 transition-colors"
      >
        <UIcon
          name="i-lucide-plus"
          class="size-4"
        />
        <span>ارسال تیکت جدید</span>
      </NuxtLink>
    </div>

    <!-- Empty State -->
    <div
      v-if="!tickets || tickets.length === 0"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-12 text-center flex flex-col items-center justify-center gap-3"
    >
      <div class="size-16 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
        <UIcon
          name="i-lucide-message-square"
          class="size-8"
        />
      </div>
      <h3 class="font-bold text-base text-neutral-900 dark:text-white">
        هنوز تیکت پشتیبانی ثبت نکرده‌اید
      </h3>
      <p class="text-xs text-neutral-500 max-w-sm">
        در صورتی که هرگونه پرسش یا نیازی به راهنمایی دارید، کارشناسان ما آماده پاسخگویی هستند.
      </p>
      <NuxtLink
        to="/profile/tickets/create"
        class="mt-2 text-xs font-bold text-primary hover:underline"
      >
        ارسال اولین تیکت پشتیبانی ←
      </NuxtLink>
    </div>

    <!-- Tickets List -->
    <div
      v-else
      class="flex flex-col gap-3"
    >
      <NuxtLink
        v-for="t in tickets"
        :key="t.id"
        :to="`/profile/tickets/${t.ticket_number}`"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl p-5 shadow-xs hover:border-primary/50 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group"
      >
        <div class="flex items-start sm:items-center gap-3.5">
          <div
            class="size-11 rounded-xl flex items-center justify-center shrink-0"
            :class="{
              'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': t.status === 'open',
              'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': t.status === 'answered',
              'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400': t.status === 'awaiting_reply',
              'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400': t.status === 'closed'
            }"
          >
            <UIcon
              name="i-lucide-message-square-text"
              class="size-5.5"
            />
          </div>

          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
              <span class="font-mono text-xs text-neutral-400 [direction:ltr]">{{ t.ticket_number }}</span>
              <span class="text-neutral-300 dark:text-neutral-700">|</span>
              <span class="text-[11px] font-bold text-neutral-500 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-md">
                {{ t.department_label }}
              </span>
            </div>
            <h3 class="font-bold text-sm text-neutral-900 dark:text-white group-hover:text-primary transition-colors">
              {{ t.subject }}
            </h3>
            <span class="text-[11px] text-neutral-400">
              آخرین بروزرسانی: {{ t.last_reply_at_jalali || t.created_at_jalali }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-3 self-end sm:self-center">
          <span
            class="px-2.5 py-1 rounded-full text-xs font-bold"
            :class="{
              'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400': t.status === 'open',
              'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400': t.status === 'answered',
              'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400': t.status === 'awaiting_reply',
              'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400': t.status === 'closed'
            }"
          >
            {{ t.status_label }}
          </span>
          <UIcon
            name="i-lucide-chevron-left"
            class="size-4 text-neutral-400 group-hover:text-primary transition-colors"
          />
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
