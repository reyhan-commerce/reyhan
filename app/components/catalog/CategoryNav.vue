<script setup lang="ts">
import type { CategoryTreeItem } from '~/stores/catalog'

defineProps<{
  categories: CategoryTreeItem[]
}>()

const iconMap: Record<string, string> = {
  skincare: 'i-lucide-sparkles',
  makeup: 'i-lucide-heart',
  haircare: 'i-lucide-scissors',
  fragrance: 'i-lucide-flame'
}
</script>

<template>
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <NuxtLink
      v-for="category in categories"
      :key="category.id"
      :to="`/categories/${category.slug}`"
      class="group relative flex flex-col items-center justify-center p-4 sm:p-5 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-200 hover:shadow-md min-h-24 text-center"
    >
      <div class="size-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
        <UIcon
          :name="iconMap[category.slug] || category.icon || 'i-lucide-tag'"
          class="size-7"
        />
      </div>

      <span class="font-bold text-sm text-neutral-800 dark:text-neutral-100 group-hover:text-primary transition-colors">
        {{ category.name }}
      </span>

      <span
        v-if="category.children && category.children.length > 0"
        class="text-xs text-neutral-400 mt-1"
      >
        {{ category.children.length }} زیرمجموعه
      </span>
    </NuxtLink>
  </div>
</template>
