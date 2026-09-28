<script setup lang="ts">
import { useCompareStore } from '~/stores/compare'
import { useCartStore } from '~/stores/cart'

const compareStore = useCompareStore()
const cartStore = useCartStore()
const { formatPrice, toPersianDigits } = usePersian()

useSeoMeta({
  title: 'مقایسه تخصصی کالاها — EasyShop',
  description: 'مقایسه مشخصات فنی، قیمت و ویژگی‌های کالاهای مختلف به صورت جدول اختصاصی'
})

onMounted(() => {
  compareStore.fetchComparison()
})

watch(() => compareStore.selectedSlugs.length, () => {
  compareStore.fetchComparison()
})
</script>

<template>
  <div class="flex flex-col gap-8 py-6 pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-neutral-200 dark:border-neutral-800">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
          مقایسه تخصصی کالاها
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-1">
          بررسی دقیق تفاوت‌ها و تطبیق مشخصات فنی تا ۴ کالا در یک نگاه
        </p>
      </div>

      <div
        v-if="compareStore.selectedSlugs.length > 0"
        class="flex items-center gap-2"
      >
        <UButton
          to="/products"
          color="neutral"
          variant="outline"
          size="sm"
          leading-icon="i-lucide-plus"
        >
          افزودن کالای دیگر
        </UButton>
        <UButton
          color="error"
          variant="ghost"
          size="sm"
          leading-icon="i-lucide-trash-2"
          @click="compareStore.clearCompare"
        >
          پاک‌کردن لیست
        </UButton>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="compareStore.selectedSlugs.length === 0"
      class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-xs gap-4"
    >
      <div class="w-16 h-16 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400">
        <UIcon
          name="i-lucide-arrow-left-right"
          class="size-8"
        />
      </div>
      <h2 class="text-lg font-bold text-neutral-800 dark:text-neutral-200">
        کالایی برای مقایسه انتخاب نشده است
      </h2>
      <p class="text-xs sm:text-sm text-neutral-500 max-w-md">
        برای شروع مقایسه، به بخش کاتالوگ یا صفحه جزئیات کالاها مراجعه کرده و روی دکمه «مقایسه کالا» کلیک کنید.
      </p>
      <UButton
        to="/products"
        color="primary"
        size="md"
        trailing-icon="i-lucide-arrow-left"
        class="font-bold mt-2"
      >
        مشاهده کاتالوگ محصولات
      </UButton>
    </div>

    <!-- Loading Skeleton -->
    <div
      v-else-if="compareStore.isLoading && !compareStore.compareData"
      class="flex flex-col gap-6"
    >
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="i in 4"
          :key="i"
          class="h-64 bg-neutral-100 dark:bg-neutral-800 rounded-2xl animate-pulse"
        />
      </div>
    </div>

    <!-- Comparison Table Layout -->
    <div
      v-else-if="compareStore.compareData && compareStore.compareData.products.length > 0"
      class="flex flex-col gap-8 overflow-x-auto"
    >
      <!-- Top Sticky Products Header Bar -->
      <div class="min-w-[640px] grid grid-cols-12 gap-4 pb-6 border-b border-neutral-200 dark:border-neutral-800">
        <!-- Label Col (3 cols) -->
        <div class="col-span-3 flex flex-col justify-end p-2 text-xs font-bold text-neutral-400">
          کالاهای منتخب برای مقایسه:
        </div>

        <!-- Product Cards Cols -->
        <div
          v-for="p in compareStore.compareData.products"
          :key="p.id"
          class="flex flex-col gap-3 p-3 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 shadow-xs relative"
          :class="[
            compareStore.compareData.products.length === 1 ? 'col-span-9' : '',
            compareStore.compareData.products.length === 2 ? 'col-span-4 sm:col-span-4' : '',
            compareStore.compareData.products.length === 3 ? 'col-span-3' : '',
            compareStore.compareData.products.length === 4 ? 'col-span-2 sm:col-span-2' : '',
          ]"
        >
          <!-- Remove CTA -->
          <button
            type="button"
            class="absolute top-2 left-2 size-7 rounded-full bg-neutral-100 dark:bg-neutral-800 hover:bg-error/10 hover:text-error text-neutral-400 flex items-center justify-center transition-colors"
            title="حذف از مقایسه"
            @click="compareStore.removeFromCompare(p.slug)"
          >
            <UIcon
              name="i-lucide-x"
              class="size-4"
            />
          </button>

          <!-- Thumbnail -->
          <NuxtLink
            :to="`/products/${p.slug}`"
            class="flex items-center justify-center aspect-square rounded-xl bg-neutral-50 dark:bg-neutral-800/50 p-2 overflow-hidden"
          >
            <img
              v-if="p.thumbnail"
              :src="p.thumbnail"
              :alt="p.name"
              class="object-contain w-full h-full"
            >
            <UIcon
              v-else
              name="i-lucide-image"
              class="size-12 text-neutral-300"
            />
          </NuxtLink>

          <!-- Title & Brand -->
          <div class="flex flex-col gap-1 min-h-12">
            <span
              v-if="p.brand"
              class="text-[11px] font-bold text-primary"
            >
              {{ p.brand.name }}
            </span>
            <NuxtLink
              :to="`/products/${p.slug}`"
              class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-white line-clamp-2 hover:text-primary transition-colors leading-snug"
            >
              {{ p.name }}
            </NuxtLink>
          </div>

          <!-- Price -->
          <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800 flex flex-col">
            <template v-if="p.has_stock && p.price_range.min">
              <span class="text-xs sm:text-sm font-black text-neutral-900 dark:text-white">
                {{ formatPrice(p.price_range.min) }}
              </span>
            </template>
            <span
              v-else
              class="text-xs font-bold text-neutral-400"
            >
              ناموجود
            </span>
          </div>
        </div>
      </div>

      <!-- Specification Groups Comparison -->
      <div class="min-w-[640px] flex flex-col gap-8">
        <div
          v-for="group in compareStore.compareData.specification_groups"
          :key="group.id"
          class="flex flex-col gap-3"
        >
          <!-- Group Title -->
          <div class="flex items-center gap-2 pb-1 border-b border-neutral-200 dark:border-neutral-800">
            <div class="w-1.5 h-4 rounded-full bg-primary" />
            <h3 class="font-bold text-sm sm:text-base text-neutral-900 dark:text-white">
              {{ group.name }}
            </h3>
          </div>

          <!-- Specs Rows -->
          <div class="flex flex-col rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800 text-xs sm:text-sm">
            <div
              v-for="(item, idx) in group.items"
              :key="item.id"
              class="grid grid-cols-12 gap-2 p-3 items-center"
              :class="idx % 2 === 0 ? 'bg-neutral-50/70 dark:bg-neutral-900/60' : 'bg-white dark:bg-neutral-900'"
            >
              <!-- Spec Name (3 cols) -->
              <div class="col-span-3 font-semibold text-neutral-700 dark:text-neutral-300">
                {{ item.name }}
              </div>

              <!-- Product Values (matching column layout) -->
              <div
                v-for="p in compareStore.compareData.products"
                :key="p.id"
                class="text-neutral-900 dark:text-neutral-100 font-normal leading-relaxed"
                :class="[
                  compareStore.compareData.products.length === 1 ? 'col-span-9' : '',
                  compareStore.compareData.products.length === 2 ? 'col-span-4' : '',
                  compareStore.compareData.products.length === 3 ? 'col-span-3' : '',
                  compareStore.compareData.products.length === 4 ? 'col-span-2' : '',
                ]"
              >
                {{ item.values[String(p.id)] || '—' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
