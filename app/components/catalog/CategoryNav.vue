<script setup lang="ts">
import type { CategoryTreeItem } from '~/stores/catalog'

defineProps<{
  categories: CategoryTreeItem[]
}>()

const { toPersianDigits } = usePersian()

const iconMap: Record<string, string> = {
  skincare: 'i-lucide-sparkles',
  makeup: 'i-lucide-heart',
  haircare: 'i-lucide-scissors',
  fragrance: 'i-lucide-flame'
}

const colorMap: Record<string, { bg: string, text: string }> = {
  skincare: { bg: 'bg-rose-500/10 dark:bg-rose-500/20', text: 'text-rose-600 dark:text-rose-400' },
  makeup: { bg: 'bg-pink-500/10 dark:bg-pink-500/20', text: 'text-pink-600 dark:text-pink-400' },
  haircare: { bg: 'bg-purple-500/10 dark:bg-purple-500/20', text: 'text-purple-600 dark:text-purple-400' },
  fragrance: { bg: 'bg-amber-500/10 dark:bg-amber-500/20', text: 'text-amber-600 dark:text-amber-400' }
}
</script>

<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <NuxtLink
      v-for="category in categories"
      :key="category.id"
      :to="`/categories/${category.slug}`"
      class="group relative flex flex-col items-center justify-center p-6 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 text-center overflow-hidden"
    >
      <!-- Glow effect on hover -->
      <div class="absolute -top-12 -right-12 size-24 rounded-full bg-primary/10 blur-xl group-hover:scale-150 transition-transform duration-500 pointer-events-none" />

      <!-- Category Icon Bubble -->
      <div
        class="size-16 rounded-2xl flex items-center justify-center mb-3.5 transition-all duration-300 group-hover:scale-110 shadow-xs"
        :class="colorMap[category.slug]?.bg || 'bg-primary/10'"
      >
        <UIcon
          :name="iconMap[category.slug] || category.icon || 'i-lucide-tag'"
          class="size-8"
          :class="colorMap[category.slug]?.text || 'text-primary'"
        />
      </div>

      <!-- Title -->
      <span class="font-black text-sm sm:text-base text-neutral-800 dark:text-neutral-100 group-hover:text-primary transition-colors">
        {{ category.name }}
      </span>

      <!-- Subtitle or count -->
      <span
        v-if="category.children && category.children.length > 0"
        class="text-[11px] font-medium text-neutral-400 mt-1.5 px-2.5 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800/80"
      >
        {{ toPersianDigits(category.children.length) }} گروه تخصصی
      </span>
      <span
        v-else
        class="text-[11px] text-neutral-400 mt-1"
      >
        مشاهده محصولات
      </span>
    </NuxtLink>
  </div>
</template>
