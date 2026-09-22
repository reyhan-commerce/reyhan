<script setup lang="ts">
import type { FaqResponse } from '~/types/content'

const api = useApi()
const settingsStore = useSettingsStore()

const storeName = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')

useSeoMeta({
  title: () => `پرسش‌های متداول (FAQ) - ${storeName.value}`,
  description: 'پاسخ به سوالات متداول مشتریان پیرامون شیوه ارسال، درگاه‌های پرداخت، اصالت کالا و مرجوعی.',
})

const selectedCategory = ref<string>('all')

const { data: faqData, status } = await useAsyncData('faqs', () =>
  api<ApiResponse<FaqResponse>>('/faqs')
)

const categories = computed(() => {
  const cats = faqData.value?.data?.categories ?? []
  return ['all', ...cats]
})

const filteredFaqs = computed(() => {
  const items = faqData.value?.data?.items ?? []
  if (selectedCategory.value === 'all') {
    return items
  }
  return items.filter((f) => f.category === selectedCategory.value)
})

const accordionItems = computed(() =>
  filteredFaqs.value.map((f) => ({
    label: f.question,
    content: f.answer,
  }))
)
</script>

<template>
  <div class="py-8 sm:py-12 flex flex-col gap-10 max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">پرسش‌های متداول</span>
    </nav>

    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto flex flex-col gap-3">
      <span class="px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 text-xs font-bold w-fit mx-auto border border-primary-100 dark:border-primary-900/50">
        راهنمای خرید و پاسخ به سوالات
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        پرسش‌های متداول مشتریان
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
        پاسخ کامل به رایج‌ترین پرسش‌های خریداران در مورد نحوه ثبت سفارش، تحویل کالا، ضمانت و پشتیبانی
      </p>
    </div>

    <!-- Category Filter Tabs -->
    <div
      v-if="categories.length > 2"
      class="flex items-center gap-2 overflow-x-auto pb-2 justify-center flex-wrap"
    >
      <button
        v-for="cat in categories"
        :key="cat"
        type="button"
        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border"
        :class="
          selectedCategory === cat
            ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/25'
            : 'bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300 border-neutral-200 dark:border-neutral-800 hover:border-primary-300 dark:hover:border-primary-800'
        "
        @click="selectedCategory = cat"
      >
        {{ cat === 'all' ? 'همه پرسش‌ها' : cat }}
      </button>
    </div>

    <!-- FAQ Accordion Card -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs">
      <!-- Loading Skeleton -->
      <div
        v-if="status === 'pending'"
        class="space-y-4"
      >
        <div
          v-for="i in 4"
          :key="i"
          class="animate-pulse space-y-2 py-3 border-b border-neutral-100 dark:border-neutral-800 last:border-0"
        >
          <div class="h-4 bg-neutral-200 dark:bg-neutral-800 rounded w-3/4" />
          <div class="h-3 bg-neutral-100 dark:bg-neutral-800/60 rounded w-full" />
        </div>
      </div>

      <!-- FAQ Accordion -->
      <UAccordion
        v-else-if="accordionItems.length > 0"
        :items="accordionItems"
        :ui="{
          item: 'border-b border-neutral-100 dark:border-neutral-800 last:border-0 py-4',
          trigger: 'text-sm font-bold text-neutral-800 dark:text-neutral-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors py-2 text-right cursor-pointer',
          content: 'text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-loose pt-2 pb-4 text-right',
        }"
      />

      <!-- Empty State -->
      <div
        v-else
        class="py-12 text-center flex flex-col items-center gap-3 text-neutral-400"
      >
        <UIcon
          name="i-lucide-help-circle"
          class="size-10 text-neutral-300 dark:text-neutral-700"
        />
        <p class="text-sm font-bold text-neutral-600 dark:text-neutral-300">
          پرسشی در این دسته‌بندی یافت نشد
        </p>
        <p class="text-xs">
          در صورت نیاز به راهنمایی بیشتر، با تیم پشتیبانی در ارتباط باشید.
        </p>
      </div>
    </div>

    <!-- Need More Help Banner -->
    <div class="bg-gradient-to-tr from-primary-50 to-primary-100/50 dark:from-primary-950/30 dark:to-primary-900/20 border border-primary-100 dark:border-primary-900/40 rounded-3xl p-6 sm:p-8 text-center flex flex-col items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-white dark:bg-neutral-800 text-primary-600 dark:text-primary-400 flex items-center justify-center shadow-xs">
        <UIcon
          name="i-lucide-headset"
          class="w-6 h-6"
        />
      </div>
      <div class="flex flex-col gap-1">
        <h3 class="font-bold text-base text-neutral-900 dark:text-white">
          پاسخ سوال خود را پیدا نکردید؟
        </h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
          تیم پشتیبانی {{ storeName }} آماده راهنمایی و پاسخگویی به تمامی سوالات شماست.
        </p>
      </div>
      <UButton
        to="/contact"
        color="primary"
        size="md"
        icon="i-lucide-phone"
        class="font-bold cursor-pointer"
      >
        تماس با پشتیبانی
      </UButton>
    </div>
  </div>
</template>
