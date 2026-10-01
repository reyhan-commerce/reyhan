<script setup lang="ts">
import { useCompareStore } from '~/stores/compare'

const compareStore = useCompareStore()
const { toPersianDigits } = usePersian()
</script>

<template>
  <div
    v-if="compareStore.selectedSlugs.length > 0"
    class="fixed bottom-20 sm:bottom-6 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 z-40 bg-neutral-900/95 dark:bg-neutral-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-neutral-700 p-3 sm:px-5 flex items-center justify-between gap-4 max-w-xl w-full"
  >
    <div class="flex items-center gap-3">
      <div class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-primary text-white">
        <UIcon
          name="i-lucide-arrow-left-right"
          class="size-5"
        />
        <span class="absolute -top-1.5 -right-1.5 size-5 rounded-full bg-white text-neutral-900 text-[10px] font-black flex items-center justify-center">
          {{ toPersianDigits(compareStore.selectedSlugs.length) }}
        </span>
      </div>

      <div class="flex flex-col">
        <span class="text-xs sm:text-sm font-bold">
          لیست مقایسه کالاها
        </span>
        <span class="text-[11px] text-neutral-400">
          {{ toPersianDigits(compareStore.selectedSlugs.length) }} از ۴ کالا انتخاب شده
        </span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <UButton
        color="neutral"
        variant="ghost"
        size="xs"
        class="text-neutral-400 hover:text-white"
        @click="compareStore.clearCompare"
      >
        حذف همه
      </UButton>

      <UButton
        to="/compare"
        color="primary"
        size="sm"
        trailing-icon="i-lucide-arrow-left"
        class="font-bold"
      >
        مشاهده جدول مقایسه
      </UButton>
    </div>
  </div>
</template>
